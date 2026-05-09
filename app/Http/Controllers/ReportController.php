<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Message;
use App\Models\Customer;
use App\Models\Deal;
use App\Models\DealActivity;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Get business hours configuration from settings.
     */
    private function getBusinessHours()
    {
        $daysJson = Setting::get('business_hours_days', '["1","2","3","4","5","6"]');
        $days = json_decode($daysJson, true) ?: [1, 2, 3, 4, 5, 6];
        $days = array_map('intval', $days);

        return [
            'days'  => $days,                                          // 1=Mon .. 7=Sun (ISO)
            'start' => Setting::get('business_hours_start', '07:30'),  // HH:MM
            'end'   => Setting::get('business_hours_end', '16:30'),    // HH:MM
        ];
    }

    /**
     * Check if a given Carbon timestamp falls within business hours.
     */
    private function isWithinBusinessHours(Carbon $dt, array $bh): bool
    {
        if (!in_array($dt->dayOfWeekIso, $bh['days'])) {
            return false;
        }
        $timeStr = $dt->format('H:i');
        return $timeStr >= $bh['start'] && $timeStr < $bh['end'];
    }

    /**
     * Get the business hours start time for a given date.
     */
    private function getBusinessStart(Carbon $date, array $bh): Carbon
    {
        $parts = explode(':', $bh['start']);
        return $date->copy()->setTime((int)$parts[0], (int)$parts[1], 0);
    }

    /**
     * Get the business hours end time for a given date.
     */
    private function getBusinessEnd(Carbon $date, array $bh): Carbon
    {
        $parts = explode(':', $bh['end']);
        return $date->copy()->setTime((int)$parts[0], (int)$parts[1], 0);
    }

    /**
     * Find the previous working day's business hours end, looking back up to 7 days.
     */
    private function getPreviousBusinessEnd(Carbon $date, array $bh): ?Carbon
    {
        $check = $date->copy()->subDay();
        for ($i = 0; $i < 7; $i++) {
            if (in_array($check->dayOfWeekIso, $bh['days'])) {
                return $this->getBusinessEnd($check, $bh);
            }
            $check->subDay();
        }
        return null;
    }

    public function daily(Request $request)
    {
        $bh = $this->getBusinessHours();
        $now = Carbon::now();
        
        $dateInput = $request->input('date', date('Y-m-d'));
        $requestedDate = Carbon::parse($dateInput);
        
        $isToday = $requestedDate->isToday();
        $bhEndToday = $this->getBusinessEnd($requestedDate, $bh);
        $isBeforeEndToday = $now->lt($bhEndToday);
        
        $date = $requestedDate->copy();
        $isTemporaryToday = $isToday && $isBeforeEndToday;

        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();

        $yesterdayStart = $startOfDay->copy()->subDay();
        $yesterdayEnd = $endOfDay->copy()->subDay();

        // Load business hours for the selected date
        $bhStart = $this->getBusinessStart($date, $bh);
        $bhEnd   = $this->getBusinessEnd($date, $bh);
        $isWorkingDay = in_array($date->dayOfWeekIso, $bh['days']);

        // Previous business day end (to find carry-over messages)
        $prevBhEnd = $this->getPreviousBusinessEnd($date, $bh);

        // ====================================================================
        // 1. Kualitas & Performa CS
        // ====================================================================
        // Total chat counts (full day, for overview)
        $chatIn = Message::whereBetween('created_at', [$startOfDay, $endOfDay])
            ->where('direction', 'in')->count();
        $chatOut = Message::whereBetween('created_at', [$startOfDay, $endOfDay])
            ->where('direction', 'out')->count();

        $yesterdayChatIn = Message::whereBetween('created_at', [$yesterdayStart, $yesterdayEnd])
            ->where('direction', 'in')->count();
        $chatInTrend = $this->calculateTrend($chatIn, $yesterdayChatIn);

        // --- Missed Chats ---
        // Only counted AFTER business hours end on a working day.
        $missedChats = 0;
        
        // Check if we should calculate missed chats (must be a working day and business hours must be over)
        $isReportingToday = $date->isToday();
        $canCalculateMissed = $isWorkingDay && (!$isReportingToday || $now->gte($bhEnd));

        if ($canCalculateMissed) {
            $customersInBH = Message::whereBetween('created_at', [$bhStart, $bhEnd])
                ->where('direction', 'in')
                ->pluck('customer_id')
                ->unique();

            foreach ($customersInBH as $cId) {
                // Check if any outgoing reply exists for this customer before business hours end
                $firstInTime = Message::where('customer_id', $cId)
                    ->where('direction', 'in')
                    ->whereBetween('created_at', [$bhStart, $bhEnd])
                    ->orderBy('created_at', 'asc')
                    ->value('created_at');

                if (!$firstInTime) continue;

                $hasReply = Message::where('customer_id', $cId)
                    ->where('direction', 'out')
                    ->where('created_at', '>=', $firstInTime)
                    ->where('created_at', '<=', $bhEnd)
                    ->exists();

                if (!$hasReply) $missedChats++;
            }
        }

        // --- FRT & Response Time ---
        // Two sources of conversations to measure:
        // (A) Messages received during today's business hours → FRT = reply - message time
        // (B) Carry-over: messages received outside business hours (last night, weekend, etc.)
        //     that get their first reply today → FRT = reply - today's business start (07:30)
        $customersChattedIn = Message::whereBetween('created_at', [$startOfDay, $endOfDay])
            ->where('direction', 'in')
            ->pluck('customer_id')
            ->unique();

        // Also find carry-over customers: sent message after prev business end, no reply yet
        $carryOverCustomerIds = collect();
        if ($isWorkingDay && $prevBhEnd) {
            $carryOverCustomerIds = Message::where('direction', 'in')
                ->where('created_at', '>', $prevBhEnd)
                ->where('created_at', '<', $bhStart)
                ->pluck('customer_id')
                ->unique();

            // Filter to only those who didn't get a reply before today's business start
            $carryOverCustomerIds = $carryOverCustomerIds->filter(function ($cId) use ($prevBhEnd, $bhStart) {
                return !Message::where('customer_id', $cId)
                    ->where('direction', 'out')
                    ->where('created_at', '>', $prevBhEnd)
                    ->where('created_at', '<', $bhStart)
                    ->exists();
            });
        }

        $allRelevantCustomers = $customersChattedIn->merge($carryOverCustomerIds)->unique();

        // Fetch all messages for these customers today
        $messages = Message::whereIn('customer_id', $allRelevantCustomers)
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->orderBy('customer_id')
            ->orderBy('created_at', 'asc')
            ->get();

        $responseTimes = [];
        $firstResponseTimes = [];
        $agentResponseTimes = []; // [user_id => [seconds, ...]]

        // Group messages by customer for processing
        $messagesByCustomer = $messages->groupBy('customer_id');

        foreach ($allRelevantCustomers as $cId) {
            $customerMessages = $messagesByCustomer->get($cId, collect());
            $isCarryOver = $carryOverCustomerIds->contains($cId);

            // Determine the effective "first incoming" time
            if ($isCarryOver && !$customersChattedIn->contains($cId)) {
                // Pure carry-over: no new messages today, but might get a reply today
                // The effective start time is today's business start
                $effectiveInTime = $bhStart->copy();
            } else {
                $effectiveInTime = null;
            }

            $lastIn = null;
            $hasRespondedInSession = false;

            foreach ($customerMessages as $msg) {
                if ($msg->direction === 'in') {
                    if (!$lastIn) {
                        $lastIn = $msg;
                    }
                } elseif ($msg->direction === 'out' && ($lastIn || ($isCarryOver && !$hasRespondedInSession))) {
                    // Determine the reference time for response calculation
                    if ($lastIn) {
                        // Message arrived today
                        $inTime = $lastIn->created_at;
                        // If message arrived before business hours, clamp to business start
                        if ($isWorkingDay && $inTime->format('H:i') < $bh['start']) {
                            $refTime = $bhStart->copy();
                        } else {
                            $refTime = $inTime->copy();
                        }
                    } elseif ($isCarryOver && $effectiveInTime) {
                        // Carry-over message: FRT starts from today's business hours start
                        $refTime = $effectiveInTime->copy();
                    } else {
                        continue;
                    }

                    $timeDiff = max(0, $msg->created_at->diffInSeconds($refTime));
                    $responseTimes[] = $timeDiff;

                    if ($msg->user_id) {
                        if (!isset($agentResponseTimes[$msg->user_id])) {
                            $agentResponseTimes[$msg->user_id] = [];
                        }
                        $agentResponseTimes[$msg->user_id][] = $timeDiff;
                    }

                    if (!$hasRespondedInSession) {
                        $firstResponseTimes[] = $timeDiff;
                        $hasRespondedInSession = true;
                    }

                    $lastIn = null;
                }
            }
        }

        $avgResponseTime = count($responseTimes) > 0 ? (array_sum($responseTimes) / count($responseTimes)) : 0;
        $avgFRT = count($firstResponseTimes) > 0 ? (array_sum($firstResponseTimes) / count($firstResponseTimes)) : 0;

        // ====================================================================
        // 2. Pipeline & Sales
        // ====================================================================
        $statusChanges = DealActivity::whereBetween('created_at', [$startOfDay, $endOfDay])
            ->where('activity_type', 'stage_change')->count();
        $followUps = DealActivity::whereBetween('created_at', [$startOfDay, $endOfDay])
            ->where('activity_type', 'note')->count();
        $newDealsValue = Deal::whereBetween('created_at', [$startOfDay, $endOfDay])->sum('expected_value');
        $newDealsCount = Deal::whereBetween('created_at', [$startOfDay, $endOfDay])->count();

        $yesterdayDealsCount = Deal::whereBetween('created_at', [$yesterdayStart, $yesterdayEnd])->count();
        $dealsTrend = $this->calculateTrend($newDealsCount, $yesterdayDealsCount);

        $uniqueChattersCount = $customersChattedIn->count();
        $conversionRate = $uniqueChattersCount > 0 ? ($newDealsCount / $uniqueChattersCount) * 100 : 0;

        // ====================================================================
        // 3. Performa per CS (Detailed)
        // ====================================================================
        $agents = User::all(); // Include all users, including SuperAdmins if they are active
        
        $dealsByUser = Deal::whereBetween('created_at', [$startOfDay, $endOfDay])
            ->get()
            ->groupBy('assigned_user_id');

        $activitiesByUser = DealActivity::whereBetween('created_at', [$startOfDay, $endOfDay])
            ->get()
            ->groupBy('created_by');

        $agentPerformance = [];
        foreach ($agents as $agent) {
            $userId = $agent->id;
            
            // Chats handled by this agent (incoming to customers assigned to them)
            $customersAssigned = Customer::where('assigned_user_id', $userId)->pluck('id');
            
            $agentChatIn = Message::whereBetween('created_at', [$startOfDay, $endOfDay])
                ->where('direction', 'in')
                ->whereIn('customer_id', $customersAssigned)
                ->count();
                
            $agentChatOut = Message::whereBetween('created_at', [$startOfDay, $endOfDay])
                ->where('direction', 'out')
                ->where('user_id', $userId)
                ->count();

            // Missed chats for this agent
            $agentMissed = 0;
            if ($canCalculateMissed) {
                $customersInBH = Message::whereBetween('created_at', [$bhStart, $bhEnd])
                    ->where('direction', 'in')
                    ->whereIn('customer_id', $customersAssigned)
                    ->pluck('customer_id')
                    ->unique();

                foreach ($customersInBH as $cId) {
                    $firstInTime = Message::where('customer_id', $cId)
                        ->where('direction', 'in')
                        ->whereBetween('created_at', [$bhStart, $bhEnd])
                        ->orderBy('created_at', 'asc')
                        ->value('created_at');

                    if ($firstInTime) {
                        $hasReply = Message::where('customer_id', $cId)
                            ->where('direction', 'out')
                            ->where('created_at', '>=', $firstInTime)
                            ->where('created_at', '<=', $bhEnd)
                            ->exists();
                        if (!$hasReply) $agentMissed++;
                    }
                }
            }

            // Response times
            $aRTs = $agentResponseTimes[$userId] ?? [];
            $avgAgentRT = count($aRTs) > 0 ? array_sum($aRTs) / count($aRTs) : 0;

            // Deals
            $agentDeals = $dealsByUser->get($userId, collect());
            $agentDealsCount = $agentDeals->count();
            $agentDealsValue = $agentDeals->sum('expected_value');
            
            // Activities
            $agentActivities = $activitiesByUser->get($userId, collect());
            $agentFollowUps = $agentActivities->where('activity_type', 'note')->count();

            // Conversion
            $agentUniqueChatters = Message::whereBetween('created_at', [$startOfDay, $endOfDay])
                ->where('direction', 'in')
                ->whereIn('customer_id', $customersAssigned)
                ->pluck('customer_id')
                ->unique()
                ->count();
            $agentConvRate = $agentUniqueChatters > 0 ? ($agentDealsCount / $agentUniqueChatters) * 100 : 0;

            // Only add if there's any activity or they have assigned customers
            if ($agentChatIn > 0 || $agentChatOut > 0 || $agentDealsCount > 0 || $agentActivities->count() > 0) {
                $agentPerformance[] = [
                    'user_id' => $userId,
                    'name' => $agent->name,
                    'chat_in' => $agentChatIn,
                    'chat_out' => $agentChatOut,
                    'missed' => $agentMissed,
                    'avg_response_time' => $avgAgentRT,
                    'deals_count' => $agentDealsCount,
                    'deals_value' => $agentDealsValue,
                    'follow_ups' => $agentFollowUps,
                    'conversion_rate' => $agentConvRate
                ];
            }
        }

        // ====================================================================
        // 4. Konteks & Tren
        // ====================================================================
        $peakHourData = Message::whereBetween('created_at', [$startOfDay, $endOfDay])
            ->where('direction', 'in')
            ->selectRaw('HOUR(created_at) as hour, count(*) as total')
            ->groupBy('hour')
            ->orderByDesc('total')
            ->first();

        $peakHour = $peakHourData
            ? sprintf('%02d:00 - %02d:00', $peakHourData->hour, $peakHourData->hour + 1)
            : '-';

        // ====================================================================
        // 5. Customer
        // ====================================================================
        $customers = Customer::whereIn('id', $customersChattedIn)->get();
        $newCustomersCount = $customers->where('created_at', '>=', $startOfDay)->count();
        $oldCustomersCount = $customers->where('created_at', '<', $startOfDay)->count();

        // Pass to view
        $data = [
            'date' => $date->format('Y-m-d'),
            'chatIn' => $chatIn,
            'chatOut' => $chatOut,
            'chatInTrend' => $chatInTrend,
            'missedChats' => $missedChats,
            'avgResponseTime' => $avgResponseTime,
            'avgFRT' => $avgFRT,
            'isTemporaryToday' => $isTemporaryToday,
            'isWorkingDay' => $isWorkingDay,
            'businessHours' => $bh,

            'statusChanges' => $statusChanges,
            'followUps' => $followUps,
            'newDealsCount' => $newDealsCount,
            'dealsTrend' => $dealsTrend,
            'newDealsValue' => $newDealsValue,
            'conversionRate' => $conversionRate,

            'agentPerformance' => $agentPerformance,

            'peakHour' => $peakHour,

            'newCustomersCount' => $newCustomersCount,
            'oldCustomersCount' => $oldCustomersCount,
        ];

        return view('reports.daily', $data);
    }

    public function agentDetail(Request $request, $id)
    {
        $agent = User::findOrFail($id);
        $dateInput = $request->input('date', date('Y-m-d'));
        $date = Carbon::parse($dateInput);
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();
        
        $bh = $this->getBusinessHours();
        $bhStart = $this->getBusinessStart($date, $bh);
        $bhEnd = $this->getBusinessEnd($date, $bh);
        $isWorkingDay = in_array($date->dayOfWeekIso, $bh['days']);

        // 1. Chat Statistics
        $customersAssigned = Customer::where('assigned_user_id', $id)->pluck('id');
        
        $chatsIn = Message::whereBetween('created_at', [$startOfDay, $endOfDay])
            ->where('direction', 'in')
            ->whereIn('customer_id', $customersAssigned)
            ->with('customer')
            ->get();

        $chatsOut = Message::whereBetween('created_at', [$startOfDay, $endOfDay])
            ->where('direction', 'out')
            ->where('user_id', $id)
            ->with('customer')
            ->get();

        // 2. Deals
        $deals = Deal::whereBetween('created_at', [$startOfDay, $endOfDay])
            ->where('assigned_user_id', $id)
            ->with('customer')
            ->get();

        // 3. Activities
        $activities = DealActivity::whereBetween('created_at', [$startOfDay, $endOfDay])
            ->where('created_by', $id)
            ->with(['deal', 'deal.customer'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('reports.agent_detail', [
            'agent' => $agent,
            'date' => $date->format('Y-m-d'),
            'chatsIn' => $chatsIn,
            'chatsOut' => $chatsOut,
            'deals' => $deals,
            'activities' => $activities,
            'businessHours' => $bh,
            'isWorkingDay' => $isWorkingDay
        ]);
    }

    private function calculateTrend($today, $yesterday)
    {
        if ($yesterday == 0) {
            return $today > 0 ? 100 : 0;
        }
        return (($today - $yesterday) / $yesterday) * 100;
    }
}

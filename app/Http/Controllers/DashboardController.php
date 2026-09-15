<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Deal;
use App\Models\DealStage;
use App\Models\Message;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today();
        $todayStart = $today->copy()->startOfDay();
        $todayEnd = $today->copy()->endOfDay();
        $yesterdayStart = $today->copy()->subDay()->startOfDay();
        $yesterdayEnd = $today->copy()->subDay()->endOfDay();

        $startDate14 = $today->copy()->subDays(13)->startOfDay();

        // 14-day data aggregation in single queries
        $leadsByDate = Customer::where('created_at', '>=', $startDate14)
            ->selectRaw("DATE(created_at) as d, count(*) as total")
            ->groupBy('d')
            ->pluck('total', 'd')
            ->toArray();

        $msgInByDate = Message::where('created_at', '>=', $startDate14)
            ->where('direction', 'in')
            ->selectRaw("DATE(created_at) as d, count(*) as total")
            ->groupBy('d')
            ->pluck('total', 'd')
            ->toArray();

        $msgOutByDate = Message::where('created_at', '>=', $startDate14)
            ->where('direction', 'out')
            ->selectRaw("DATE(created_at) as d, count(*) as total")
            ->groupBy('d')
            ->pluck('total', 'd')
            ->toArray();

        $dailyDates = [];
        $dailyFullDates = [];
        $dailyLeads = [];
        $dailyMsgIn = [];
        $dailyMsgOut = [];

        for ($i = 13; $i >= 0; $i--) {
            $day = $today->copy()->subDays($i);
            $dateKey = $day->format('Y-m-d');
            $labelKey = $day->format('d M');

            $dailyDates[] = $labelKey;
            $dailyFullDates[] = $dateKey;
            $dailyLeads[] = (int) ($leadsByDate[$dateKey] ?? 0);
            $dailyMsgIn[] = (int) ($msgInByDate[$dateKey] ?? 0);
            $dailyMsgOut[] = (int) ($msgOutByDate[$dateKey] ?? 0);
        }

        $leadsToday = Customer::whereBetween('created_at', [$todayStart, $todayEnd])->count();
        $leadsYesterday = Customer::whereBetween('created_at', [$yesterdayStart, $yesterdayEnd])->count();
        $leadsDiff = $leadsToday - $leadsYesterday;

        $chatInToday = Message::whereBetween('created_at', [$todayStart, $todayEnd])->where('direction', 'in')->count();
        $chatOutToday = Message::whereBetween('created_at', [$todayStart, $todayEnd])->where('direction', 'out')->count();

        $uniqueChattersToday = Message::whereBetween('created_at', [$todayStart, $todayEnd])
            ->where('direction', 'in')
            ->distinct('customer_id')
            ->count('customer_id');

        $totalCustomers = Customer::count();
        $totalLeads14Days = array_sum($dailyLeads);

        $recentCustomers = Customer::with('labels')
            ->orderBy('last_chat_at', 'desc')
            ->limit(8)
            ->get();

        $waSession = Setting::get('gowa_device_id', 'crm-session');

        $stats = [
            'total_customers' => $totalCustomers,
            'leads_today' => $leadsToday,
            'leads_yesterday' => $leadsYesterday,
            'leads_diff' => $leadsDiff,
            'messages_today' => [
                'total_in' => $chatInToday,
                'total_out' => $chatOutToday,
                'unique_chatters' => $uniqueChattersToday,
            ],
            'daily' => [
                'dates' => $dailyDates,
                'full_dates' => $dailyFullDates,
                'leads' => $dailyLeads,
                'messages_in' => $dailyMsgIn,
                'messages_out' => $dailyMsgOut,
                'total_leads_14d' => $totalLeads14Days,
            ],
            'wa_session' => $waSession,
        ];

        return view('dashboard', compact(
            'stats',
            'leadsToday',
            'leadsYesterday',
            'leadsDiff',
            'chatInToday',
            'chatOutToday',
            'uniqueChattersToday',
            'totalCustomers',
            'dailyDates',
            'dailyFullDates',
            'dailyLeads',
            'dailyMsgIn',
            'dailyMsgOut',
            'totalLeads14Days',
            'recentCustomers',
            'waSession'
        ));
    }

    /**
     * Get detailed list of new leads created on a specific date.
     */
    public function leadsDetail(Request $request)
    {
        $dateStr = $request->input('date', date('Y-m-d'));
        try {
            $date = Carbon::parse($dateStr);
        } catch (\Exception $e) {
            $date = Carbon::today();
            $dateStr = $date->format('Y-m-d');
        }

        $start = $date->copy()->startOfDay();
        $end = $date->copy()->endOfDay();

        try {
            $leads = Customer::with(['labels', 'assignedUser', 'company'])
                ->where(function ($q) use ($start, $end, $dateStr) {
                    $q->whereBetween('created_at', [$start, $end])
                      ->orWhereDate('created_at', $dateStr);
                })
                ->orderBy('created_at', 'desc')
                ->get();

            $data = $leads->map(function ($lead) {
                $name = (string) ($lead->name ?: 'Tanpa Nama');
                $phone = (string) ($lead->wa_number ?: '');

                return [
                    'id' => $lead->id,
                    'name' => $name,
                    'initials' => generate_initials($name),
                    'wa_number' => $phone,
                    'formatted_phone' => format_phone_display($phone),
                    'created_at_time' => $lead->created_at ? $lead->created_at->format('H:i') . ' WIB' : '-',
                    'source' => $lead->source ?: 'WhatsApp',
                    'assigned_user' => $lead->assignedUser ? $lead->assignedUser->name : '-',
                    'company' => $lead->company ? $lead->company->name : null,
                    'labels' => $lead->labels ? $lead->labels->map(function ($lbl) {
                        return [
                            'name' => $lbl->name ?? '',
                            'color' => $lbl->color ?: '#00A884',
                            'wa_label_id' => $lbl->wa_label_id ?? null,
                        ];
                    }) : [],
                    'chat_url' => url('chat?customer=' . $lead->id),
                ];
            });

            return response()->json([
                'success' => true,
                'date' => $dateStr,
                'formatted_date' => $date->translatedFormat('d F Y'),
                'day_name' => $date->translatedFormat('l'),
                'total' => $leads->count(),
                'leads' => $data,
                'customer_list_url' => route('admin.customers.index', ['created_date' => $dateStr]),
            ], 200, [], JSON_INVALID_UTF8_SUBSTITUTE);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Error in leadsDetail: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'date' => $dateStr,
                'formatted_date' => $date->translatedFormat('d F Y'),
                'day_name' => $date->translatedFormat('l'),
                'total' => 0,
                'leads' => [],
                'customer_list_url' => route('admin.customers.index', ['created_date' => $dateStr]),
            ], 200, [], JSON_INVALID_UTF8_SUBSTITUTE);
        }
    }
}

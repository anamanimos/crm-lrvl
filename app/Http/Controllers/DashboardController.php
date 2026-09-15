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
        $dailyLeads = [];
        $dailyMsgIn = [];
        $dailyMsgOut = [];

        for ($i = 13; $i >= 0; $i--) {
            $day = $today->copy()->subDays($i);
            $dateKey = $day->format('Y-m-d');
            $labelKey = $day->format('d M');

            $dailyDates[] = $labelKey;
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
            'dailyLeads',
            'dailyMsgIn',
            'dailyMsgOut',
            'totalLeads14Days',
            'recentCustomers',
            'waSession'
        ));
    }
}

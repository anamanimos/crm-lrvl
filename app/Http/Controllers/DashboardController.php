<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Deal;
use App\Models\DealStage;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_customers' => Customer::count(),
            'messages_today' => [
                'total_in' => Message::whereDate('created_at', today())->where('direction', 'in')->count(),
                'total_out' => Message::whereDate('created_at', today())->where('direction', 'out')->count(),
            ],
            'deal_stats' => DealStage::withCount('deals')
                ->withSum('deals', 'expected_value')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(),
            'total_deals' => Deal::count(),
        ];

        $recent_customers = Customer::orderBy('last_chat_at', 'desc')
            ->limit(10)
            ->get();

        return view('dashboard', compact('stats', 'recent_customers'));
    }
}

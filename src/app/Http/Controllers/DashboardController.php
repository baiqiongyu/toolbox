<?php

namespace App\Http\Controllers;

use App\Models\QueryLog;
use App\Models\Tool;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $tools = Tool::enabled()->get();

        // 统计面板数据
        $todayQueries = QueryLog::today()->count();
        $todaySuccess = QueryLog::today()->where('status', 'success')->count();
        $todayFailed = QueryLog::today()->where('status', 'failed')->count();
        $todayUsers = QueryLog::today()->distinct('user_id')->count('user_id');

        // 各工具查询次数
        $toolStats = QueryLog::today()
            ->select('tool', DB::raw('count(*) as total'))
            ->groupBy('tool')
            ->pluck('total', 'tool');

        // 最近 10 条操作记录
        $recentLogs = QueryLog::with('user')
            ->latest()
            ->limit(10)
            ->get();

        return view('dashboard', compact(
            'tools',
            'todayQueries', 'todaySuccess', 'todayFailed', 'todayUsers',
            'toolStats', 'recentLogs'
        ));
    }
}

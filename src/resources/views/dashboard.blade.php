<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>易和国际物流 · 内部工具平台</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', 'PingFang SC', 'Microsoft YaHei', sans-serif;
            color: #1e293b;
            min-height: 100vh;
            background: #f1f4f9;
        }

        /* ===== PAGE ===== */
        .page { max-width: 1280px; margin: 0 auto; padding: 28px 32px 48px; }

        /* ===== PAGE HEADER ===== */
        .page-header {
            margin-bottom: 28px;
        }
        .page-header h1 {
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
        }
        .page-header-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 4px;
        }
        .page-header p { font-size: 14px; color: #64748b; }
        .page-header-date {
            font-size: 13px;
            color: #94a3b8;
        }

        /* ===== STATS ===== */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
            margin-bottom: 28px;
        }
        .stat-box {
            background: #ffffff;
            border-radius: 10px;
            border: 1px solid #e6e8ec;
            padding: 16px 18px;
            transition: box-shadow .15s;
        }
        .stat-box:hover { box-shadow: 0 2px 8px rgba(0,0,0,.04); }
        .stat-box-label {
            font-size: 11px; font-weight: 500; color: #94a3b8;
            margin-bottom: 2px;
        }
        .stat-box-value {
            font-size: 22px; font-weight: 700; color: #0f172a;
        }
        .stat-box-value .sub {
            font-size: 11px; font-weight: 500; color: #16a34a;
            margin-left: 4px;
        }
        .stat-box-value .sub.fail { color: #ef4444; }

        /* ===== TWO-COLUMN LAYOUT ===== */
        .main-grid {
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 20px;
            align-items: start;
        }
        .main-grid .left-col { min-width: 0; }
        .main-grid .right-col { min-width: 0; }

        /* ===== LOG TABLE ===== */
        .log-empty {
            background: #fff; border-radius: 10px; border: 1px solid #e6e8ec;
            padding: 32px 20px; text-align: center; font-size: 13px; color: #94a3b8;
        }
        .log-table {
            width: 100%; border-collapse: collapse;
        }
        .log-table th {
            padding: 10px 16px; text-align: left; font-size: 11px; font-weight: 600;
            color: #94a3b8; text-transform: uppercase; letter-spacing: .04em;
            background: #f8fafc; border-bottom: 1px solid #e6e8ec;
        }
        .log-table td {
            padding: 10px 16px; font-size: 13px; color: #475569;
            border-bottom: 1px solid #f1f4f9;
        }
        .log-table tr:last-child td { border-bottom: none; }
        .log-status {
            display: inline-block; padding: 1px 7px; border-radius: 4px;
            font-size: 11px; font-weight: 600;
        }
        .log-status.success { background: #f0fdf4; color: #16a34a; }
        .log-status.failed { background: #fef2f2; color: #ef4444; }

        /* ===== SECTION ===== */
        .section-label {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: .05em;
            text-transform: uppercase;
            color: #94a3b8;
            margin-bottom: 12px;
        }

        /* ===== TOOL CARDS ===== */
        .tool-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(270px, 1fr));
            gap: 12px;
        }
        .tool-card {
            background: #ffffff;
            border-radius: 10px;
            border: 1px solid #e6e8ec;
            padding: 20px;
            text-decoration: none;
            display: block;
            transition: all .2s ease;
        }
        .tool-card:hover {
            border-color: #bfdbfe;
            box-shadow: 0 4px 16px rgba(30,64,175,.06);
            transform: translateY(-1px);
        }
        .tool-card-icon {
            width: 38px; height: 38px;
            border-radius: 9px;
            display: flex;
            align-items: center; justify-content: center;
            font-size: 17px;
            margin-bottom: 14px;
        }
        .tool-card-title {
            font-size: 14px; font-weight: 600; color: #0f172a;
            margin-bottom: 3px;
            transition: color .15s;
        }
        .tool-card:hover .tool-card-title { color: #1e40af; }
        .tool-card-desc {
            font-size: 12px; color: #94a3b8;
            line-height: 1.5;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }
        .tool-card-meta {
            margin-top: 14px;
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 12px;
            font-weight: 500;
            color: #cbd5e1;
            transition: all .2s;
        }
        .tool-card:hover .tool-card-meta { color: #1e40af; }
        .tool-card-meta svg {
            width: 14px; height: 14px;
            transition: transform .2s;
        }
        .tool-card:hover .tool-card-meta svg { transform: translateX(3px); }

        /* ===== EMPTY ===== */
        .empty-box {
            background: #ffffff;
            border-radius: 10px;
            border: 1px solid #e6e8ec;
            padding: 52px 20px;
            text-align: center;
        }
        .empty-box svg { width: 36px; height: 36px; color: #d1d5db; margin-bottom: 10px; }
        .empty-box p { font-size: 13px; color: #94a3b8; }
        .empty-box .sub { font-size: 12px; color: #cbd5e1; margin-top: 2px; }
    </style>
</head>
<body>

    <!-- ===== TOP NAV ===== -->
    @include('partials.nav')

    <!-- ===== PAGE ===== -->
    <div class="page">

        <!-- Page Header -->
        <div class="page-header">
            <h1>工具首页</h1>
            <div class="page-header-row">
                <p>欢迎回来，{{ Auth::user()->name }}</p>
                <span class="page-header-date">{{ now()->format('Y 年 n 月 j 日') }}</span>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-row">
            <div class="stat-box">
                <div class="stat-box-label">可用工具</div>
                <div class="stat-box-value">{{ $tools->count() }}</div>
            </div>
            <div class="stat-box">
                <div class="stat-box-label">今日查询</div>
                <div class="stat-box-value">{{ $todayQueries }}</div>
            </div>
            <div class="stat-box">
                <div class="stat-box-label">查询成功</div>
                <div class="stat-box-value">{{ $todaySuccess }} <span class="sub">{{ $todayQueries > 0 ? round($todaySuccess / $todayQueries * 100) . '%' : '' }}</span></div>
            </div>
            <div class="stat-box">
                <div class="stat-box-label">查询失败</div>
                <div class="stat-box-value">{{ $todayFailed }} @if($todayFailed > 0)<span class="sub fail">{{ $todayQueries > 0 ? round($todayFailed / $todayQueries * 100) . '%' : '' }}</span>@endif</div>
            </div>
            <div class="stat-box">
                <div class="stat-box-label">活跃用户</div>
                <div class="stat-box-value">{{ $todayUsers }}</div>
            </div>
        </div>

        <!-- ===== 工具 + 操作日志 双栏布局 ===== -->
        <div class="main-grid">
            <div class="left-col">
                <div class="section-label">快捷工具</div>

                @if($tools->isEmpty())
                    <div class="empty-box">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M12 9v3m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p>暂无可用工具</p>
                        <p class="sub">请联系管理员添加工具配置</p>
                    </div>
                @else
                    <div class="tool-cards">
                        @foreach($tools as $tool)
                            <a href="{{ $tool->route }}" class="tool-card">
                                <div class="tool-card-icon" style="background:{{ $tool->color }}10;">
                                    <span style="color:{{ $tool->color }}">{{ $tool->icon }}</span>
                                </div>
                                <div class="tool-card-title">{{ $tool->name }}</div>
                                @if($tool->description)
                                    <div class="tool-card-desc">{{ $tool->description }}</div>
                                @endif
                                <div class="tool-card-meta">
                                    进入工具 <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="right-col">
                <div style="background:#fff;border-radius:10px;border:1px solid #e6e8ec;overflow:hidden;">
                    <div style="padding:14px 16px 10px;border-bottom:1px solid #f1f4f9;">
                        <div class="section-label" style="margin-bottom:0;">最近操作</div>
                    </div>
                    @if($recentLogs->isNotEmpty())
                        <div style="padding:0;">
                            @foreach($recentLogs as $log)
                                <div style="padding:10px 16px;border-bottom:1px solid #f1f4f9;font-size:13px;">
                                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:3px;">
                                        <span style="font-weight:500;color:#1e293b;">{{ $log->user->name }}</span>
                                        <span style="font-size:11px;color:#94a3b8;">{{ $log->created_at->format('H:i') }}</span>
                                    </div>
                                    <div style="display:flex;align-items:center;gap:6px;">
                                        <span style="font-size:11px;color:#64748b;background:#f1f4f9;padding:1px 6px;border-radius:3px;">{{ \App\Models\QueryLog::toolName($log->tool) }}</span>
                                        <span style="color:#94a3b8;font-size:12px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:130px;">{{ $log->query_key }}</span>
                                        <span class="log-status {{ $log->status }}" style="margin-left:auto;">{{ $log->status === 'success' ? '成功' : '失败' }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="log-empty" style="border:none;">暂无操作记录</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</body>
</html>

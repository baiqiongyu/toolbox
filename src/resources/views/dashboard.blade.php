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
            background: #f0f2f5;
        }
        .page { max-width: 1280px; margin: 0 auto; padding: 24px 32px 48px; }

        /* ===== WELCOME BANNER ===== */
        .welcome-banner {
            background: linear-gradient(135deg, #1e40af 0%, #2563eb 50%, #3b82f6 100%);
            border-radius: 14px;
            padding: 28px 32px;
            margin-bottom: 24px;
            position: relative;
            overflow: hidden;
        }
        .welcome-banner::before {
            content: '';
            position: absolute;
            top: -60px; right: -60px;
            width: 200px; height: 200px;
            border-radius: 50%;
            background: rgba(255,255,255,.06);
        }
        .welcome-banner::after {
            content: '';
            position: absolute;
            bottom: -40px; right: 40px;
            width: 140px; height: 140px;
            border-radius: 50%;
            background: rgba(255,255,255,.04);
        }
        .welcome-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            z-index: 1;
        }
        .welcome-left { display: flex; align-items: center; gap: 16px; }
        .welcome-avatar-big {
            width: 48px; height: 48px;
            border-radius: 12px;
            background: rgba(255,255,255,.2);
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; font-weight: 700; color: white;
            backdrop-filter: blur(4px);
        }
        .welcome-text h1 {
            font-size: 20px; font-weight: 700; color: white;
            letter-spacing: -.01em;
        }
        .welcome-text p {
            font-size: 13px; color: rgba(255,255,255,.7);
            margin-top: 2px;
        }
        .welcome-date {
            font-size: 13px; color: rgba(255,255,255,.6);
            background: rgba(255,255,255,.1);
            padding: 6px 14px;
            border-radius: 8px;
            backdrop-filter: blur(4px);
        }

        /* ===== STATS ===== */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 12px;
            margin-bottom: 28px;
        }
        .stat-box {
            background: #fff;
            border-radius: 12px;
            padding: 18px 18px;
            position: relative;
            overflow: hidden;
            transition: all .25s ease;
        }
        .stat-box:hover {
            transform: translateY(-2px);
        }
        .stat-box .stat-accent {
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
        }
        .stat-box-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .stat-box-label {
            font-size: 12px; font-weight: 500; color: #94a3b8;
        }
        .stat-box-icon {
            width: 32px; height: 32px;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 15px;
        }
        .stat-box-value {
            font-size: 26px; font-weight: 800;
            letter-spacing: -.02em;
        }
        .stat-box-value .sub {
            font-size: 12px; font-weight: 600;
            margin-left: 4px;
        }

        /* ===== TWO-COLUMN ===== */
        .main-grid {
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 20px;
            align-items: start;
        }
        .main-grid .left-col { min-width: 0; }
        .main-grid .right-col { min-width: 0; }

        /* ===== SECTION LABEL ===== */
        .section-label {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: #94a3b8;
            margin-bottom: 12px;
        }
        .section-label .highlight {
            color: #1e40af;
        }

        /* ===== TOOL CARDS ===== */
        .tool-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 12px;
        }
        .tool-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 22px 20px;
            text-decoration: none;
            display: block;
            transition: all .25s ease;
            position: relative;
        }
        .tool-card::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 12px;
            pointer-events: none;
            box-shadow: 0 1px 3px rgba(0,0,0,.04), 0 1px 2px rgba(0,0,0,.02);
            transition: box-shadow .25s ease;
        }
        .tool-card:hover::after {
            box-shadow: 0 8px 30px rgba(30,64,175,.1), 0 1px 3px rgba(0,0,0,.04);
        }
        .tool-card:hover {
            transform: translateY(-3px);
        }
        .tool-card-icon {
            width: 42px; height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center; justify-content: center;
            font-size: 20px;
            margin-bottom: 16px;
            transition: transform .25s ease;
        }
        .tool-card:hover .tool-card-icon {
            transform: scale(1.1);
        }
        .tool-card-title {
            font-size: 15px; font-weight: 600; color: #0f172a;
            margin-bottom: 4px;
            transition: color .2s;
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
            font-weight: 600;
            color: #cbd5e1;
            transition: all .25s;
        }
        .tool-card:hover .tool-card-meta { color: #2563eb; }
        .tool-card-meta svg {
            width: 14px; height: 14px;
            transition: transform .25s;
        }
        .tool-card:hover .tool-card-meta svg { transform: translateX(4px); }

        /* ===== RIGHT CARD ===== */
        .right-card {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
        }
        .right-card-header {
            padding: 16px 18px 12px;
            border-bottom: 1px solid #f1f4f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .right-card-header .section-label { margin-bottom: 0; }
        .right-card-badge {
            font-size: 10px; font-weight: 600;
            background: #eef2ff; color: #1e40af;
            padding: 2px 8px; border-radius: 5px;
        }
        .log-item {
            padding: 12px 18px;
            border-bottom: 1px solid #f8fafc;
            transition: background .15s;
        }
        .log-item:hover { background: #f8fafc; }
        .log-item:last-child { border-bottom: none; }
        .log-item-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 4px;
        }
        .log-item-user {
            display: flex; align-items: center; gap: 6px;
        }
        .log-item-avatar {
            width: 22px; height: 22px;
            border-radius: 5px;
            background: #eef2ff; color: #1e40af;
            display: flex; align-items: center; justify-content: center;
            font-size: 10px; font-weight: 700;
        }
        .log-item-name { font-size: 13px; font-weight: 600; color: #1e293b; }
        .log-item-time { font-size: 11px; color: #94a3b8; }
        .log-item-bottom {
            display: flex;
            align-items: center;
            gap: 6px;
            padding-left: 28px;
        }
        .log-item-tool {
            font-size: 10px; font-weight: 600;
            background: #f1f4f9; color: #64748b;
            padding: 1px 7px; border-radius: 4px;
            white-space: nowrap;
        }
        .log-item-key {
            font-size: 12px; color: #94a3b8;
            overflow: hidden; text-overflow: ellipsis;
            white-space: nowrap; max-width: 120px;
        }

        .log-status {
            display: inline-block; padding: 1px 7px; border-radius: 4px;
            font-size: 10px; font-weight: 700; margin-left: auto;
        }
        .log-status.success { background: #f0fdf4; color: #16a34a; }
        .log-status.failed { background: #fef2f2; color: #ef4444; }

        .log-empty {
            padding: 32px 18px; text-align: center; font-size: 13px; color: #94a3b8;
        }

        /* ===== EMPTY ===== */
        .empty-box {
            background: #fff;
            border-radius: 12px;
            padding: 52px 20px;
            text-align: center;
        }
        .empty-box svg { width: 36px; height: 36px; color: #d1d5db; margin-bottom: 10px; }
        .empty-box p { font-size: 13px; color: #94a3b8; }
        .empty-box .sub { font-size: 12px; color: #cbd5e1; margin-top: 2px; }
    </style>
</head>
<body>
    @include('partials.nav')

    <div class="page">

        <!-- ===== 欢迎横幅 ===== -->
        <div class="welcome-banner">
            <div class="welcome-row">
                <div class="welcome-left">
                    <div class="welcome-avatar-big">{{ substr(Auth::user()->name, 0, 1) }}</div>
                    <div class="welcome-text">
                        <h1>欢迎回来，{{ Auth::user()->name }}</h1>
                        <p>今日有 {{ $todayQueries }} 次查询记录</p>
                    </div>
                </div>
                <div class="welcome-date">{{ now()->format('Y 年 n 月 j 日 · l') }}</div>
            </div>
        </div>

        <!-- ===== 统计卡片 ===== -->
        <div class="stats-row">
            <div class="stat-box" style="box-shadow: 0 1px 3px rgba(37,99,235,.08);">
                <div class="stat-accent" style="background:linear-gradient(90deg,#2563eb,#3b82f6);"></div>
                <div class="stat-box-top">
                    <span class="stat-box-label">可用工具</span>
                    <div class="stat-box-icon" style="background:#eef2ff;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="1.5">
                            <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
                            <rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/>
                        </svg>
                    </div>
                </div>
                <div class="stat-box-value" style="color:#2563eb;">{{ $tools->count() }}</div>
            </div>

            <div class="stat-box" style="box-shadow: 0 1px 3px rgba(5,150,105,.08);">
                <div class="stat-accent" style="background:linear-gradient(90deg,#059669,#10b981);"></div>
                <div class="stat-box-top">
                    <span class="stat-box-label">今日查询</span>
                    <div class="stat-box-icon" style="background:#f0fdf4;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="1.5">
                            <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
                        </svg>
                    </div>
                </div>
                <div class="stat-box-value" style="color:#059669;">{{ $todayQueries }}</div>
            </div>

            <div class="stat-box" style="box-shadow: 0 1px 3px rgba(37,99,235,.08);">
                <div class="stat-accent" style="background:linear-gradient(90deg,#2563eb,#3b82f6);"></div>
                <div class="stat-box-top">
                    <span class="stat-box-label">查询成功</span>
                    <div class="stat-box-icon" style="background:#eef2ff;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="1.5">
                            <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
                        </svg>
                    </div>
                </div>
                <div class="stat-box-value" style="color:#2563eb;">
                    {{ $todaySuccess }}
                    @if($todayQueries > 0)
                        <span class="sub" style="color:#059669;">{{ round($todaySuccess / $todayQueries * 100) }}%</span>
                    @endif
                </div>
            </div>

            <div class="stat-box" style="box-shadow: 0 1px 3px rgba(234,88,12,.08);">
                <div class="stat-accent" style="background:linear-gradient(90deg,#ea580c,#f97316);"></div>
                <div class="stat-box-top">
                    <span class="stat-box-label">查询失败</span>
                    <div class="stat-box-icon" style="background:#fff7ed;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#ea580c" stroke-width="1.5">
                            <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
                        </svg>
                    </div>
                </div>
                <div class="stat-box-value" style="color:#ea580c;">
                    {{ $todayFailed }}
                    @if($todayFailed > 0)
                        <span class="sub" style="color:#ef4444;">{{ round($todayFailed / $todayQueries * 100) }}%</span>
                    @endif
                </div>
            </div>

            <div class="stat-box" style="box-shadow: 0 1px 3px rgba(124,58,237,.08);">
                <div class="stat-accent" style="background:linear-gradient(90deg,#7c3aed,#a78bfa);"></div>
                <div class="stat-box-top">
                    <span class="stat-box-label">活跃用户</span>
                    <div class="stat-box-icon" style="background:#f5f3ff;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="1.5">
                            <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/>
                        </svg>
                    </div>
                </div>
                <div class="stat-box-value" style="color:#7c3aed;">{{ $todayUsers }}</div>
            </div>
        </div>

        <!-- ===== 双栏布局 ===== -->
        <div class="main-grid">
            <div class="left-col">
                <div class="section-label"><span class="highlight">●</span> 快捷工具</div>

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
                                <div class="tool-card-icon" style="background:{{ $tool->color }}12;color:{{ $tool->color }};">
                                    <span>{{ $tool->icon }}</span>
                                </div>
                                <div class="tool-card-title">{{ $tool->name }}</div>
                                @if($tool->description)
                                    <div class="tool-card-desc">{{ $tool->description }}</div>
                                @endif
                                <div class="tool-card-meta">
                                    进入工具
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
                                    </svg>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="right-col">
                <div class="right-card">
                    <div class="right-card-header">
                        <div class="section-label"><span class="highlight">●</span> 实时动态</div>
                        <span class="right-card-badge">{{ $recentLogs->count() }} 条</span>
                    </div>
                    @if($recentLogs->isNotEmpty())
                        @foreach($recentLogs as $log)
                            <div class="log-item">
                                <div class="log-item-top">
                                    <div class="log-item-user">
                                        <div class="log-item-avatar">{{ substr($log->user->name, 0, 1) }}</div>
                                        <span class="log-item-name">{{ $log->user->name }}</span>
                                    </div>
                                    <span class="log-item-time">{{ $log->created_at->format('H:i') }}</span>
                                </div>
                                <div class="log-item-bottom">
                                    <span class="log-item-tool">{{ \App\Models\QueryLog::toolName($log->tool) }}</span>
                                    <span class="log-item-key" title="{{ $log->query_key }}">{{ $log->query_key }}</span>
                                    <span class="log-status {{ $log->status }}">{{ $log->status === 'success' ? '完成' : '失败' }}</span>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="log-empty">暂无操作记录</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</body>
</html>

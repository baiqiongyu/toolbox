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
            background: #f6f7f9;
            color: #1e293b;
        }

        /* ===== TOP NAV ===== */
        .topbar {
            background: #ffffff;
            border-bottom: 1px solid #eaecf0;
            height: 60px;
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(8px);
            background: rgba(255,255,255,.92);
        }
        .topbar-inner {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 28px;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .topbar-left {
            display: flex;
            align-items: center;
            gap: 32px;
        }
        .topbar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        .topbar-logo {
            width: 30px;
            height: 30px;
            background: #4f46e5;
            border-radius: 7px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .topbar-logo svg { width: 17px; height: 17px; color: white; }
        .topbar-brand-text {
            font-size: 14px;
            font-weight: 600;
            color: #1e293b;
            letter-spacing: -.01em;
        }
        .topbar-nav {
            display: flex;
            align-items: center;
            gap: 2px;
        }
        .topbar-nav-item {
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            color: #64748b;
            text-decoration: none;
            transition: all .15s;
        }
        .topbar-nav-item:hover {
            background: #f1f4f9;
            color: #1e293b;
        }
        .topbar-nav-item.active {
            background: #eef2ff;
            color: #4f46e5;
        }
        .topbar-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .topbar-user {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 4px 12px 4px 8px;
            border-radius: 8px;
        }
        .topbar-avatar {
            width: 28px;
            height: 28px;
            border-radius: 6px;
            background: #eef2ff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 600;
            color: #4f46e5;
        }
        .topbar-name { font-size: 13px; color: #475569; font-weight: 500; }
        .topbar-logout {
            padding: 6px 10px;
            border-radius: 6px;
            border: none;
            background: none;
            color: #94a3b8;
            cursor: pointer;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all .15s;
        }
        .topbar-logout:hover { background: #fef2f2; color: #ef4444; }
        .topbar-logout svg { width: 16px; height: 16px; }

        /* ===== PAGE ===== */
        .page {
            max-width: 1200px;
            margin: 0 auto;
            padding: 32px 28px 48px;
        }

        /* ===== PAGE HEADER ===== */
        .page-header {
            margin-bottom: 32px;
        }
        .page-header h1 {
            font-size: 24px;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -.03em;
            line-height: 1.2;
        }
        .page-header p {
            font-size: 14px;
            color: #94a3b8;
            margin-top: 4px;
        }

        /* ===== STATS ===== */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
            margin-bottom: 36px;
        }
        .stat-box {
            background: white;
            border-radius: 12px;
            border: 1px solid #eaecf0;
            padding: 18px 22px;
        }
        .stat-box-label {
            font-size: 12px;
            font-weight: 500;
            color: #94a3b8;
            margin-bottom: 4px;
        }
        .stat-box-value {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -.02em;
        }
        .stat-box-value.email {
            font-size: 13px;
            font-weight: 500;
            color: #64748b;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* ===== SECTION ===== */
        .section-label {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: #94a3b8;
            margin-bottom: 14px;
        }

        /* ===== TOOL CARDS ===== */
        .tool-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 14px;
        }
        .tool-card {
            background: white;
            border-radius: 12px;
            border: 1px solid #eaecf0;
            padding: 20px;
            text-decoration: none;
            transition: all .2s ease;
            display: block;
            position: relative;
        }
        .tool-card:hover {
            border-color: #dbeafe;
            box-shadow: 0 4px 20px rgba(37,99,235,.06);
            transform: translateY(-2px);
        }
        .tool-card-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            margin-bottom: 14px;
        }
        .tool-card-title {
            font-size: 14px;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 4px;
            transition: color .15s;
        }
        .tool-card:hover .tool-card-title { color: #4f46e5; }
        .tool-card-desc {
            font-size: 12px;
            color: #94a3b8;
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .tool-card-arrow {
            margin-top: 14px;
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 12px;
            font-weight: 500;
            color: #cbd5e1;
            transition: all .2s;
        }
        .tool-card:hover .tool-card-arrow { color: #4f46e5; }
        .tool-card-arrow svg {
            width: 14px;
            height: 14px;
            transition: transform .2s;
        }
        .tool-card:hover .tool-card-arrow svg { transform: translateX(3px); }

        /* ===== EMPTY ===== */
        .empty-box {
            background: white;
            border-radius: 12px;
            border: 1px solid #eaecf0;
            padding: 52px 20px;
            text-align: center;
        }
        .empty-box svg {
            width: 36px; height: 36px;
            color: #e2e8f0;
            margin-bottom: 10px;
        }
        .empty-box p { font-size: 13px; color: #94a3b8; }
        .empty-box .sub { font-size: 12px; color: #cbd5e1; margin-top: 2px; }
    </style>
</head>
<body>

    <!-- ===== TOP NAV ===== -->
    <header class="topbar">
        <div class="topbar-inner">
            <div class="topbar-left">
                <a href="{{ route('dashboard') }}" class="topbar-brand">
                    <div class="topbar-logo">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M20.25 14.15v4.25c0 1.1-.9 2-2 2H5.74c-1.1 0-2-.9-2-2V5c0-1.1.9-2 2-2h7.52"/>
                            <path d="M16.5 3.75h3.75v3.75"/>
                            <path d="M10.5 13.5l3-3 3 3"/>
                            <path d="M12 10.5v6"/>
                        </svg>
                    </div>
                    <span class="topbar-brand-text">易和国际物流</span>
                </a>
                <nav class="topbar-nav">
                    <a href="{{ route('dashboard') }}" class="topbar-nav-item active">工具首页</a>
                </nav>
            </div>
            <div class="topbar-right">
                <div class="topbar-user">
                    <div class="topbar-avatar">{{ substr(Auth::user()->name, 0, 1) }}</div>
                    <span class="topbar-name">{{ Auth::user()->name }}</span>
                </div>
                <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="topbar-logout">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/>
                        </svg>
                        退出
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- ===== PAGE ===== -->
    <div class="page">

        <!-- Page Header -->
        <div class="page-header">
            <h1>工具首页</h1>
            <p>欢迎回来，{{ Auth::user()->name }}</p>
        </div>

        <!-- Stats -->
        <div class="stats-row">
            <div class="stat-box">
                <div class="stat-box-label">可用工具</div>
                <div class="stat-box-value">{{ $tools->count() }}</div>
            </div>
            <div class="stat-box">
                <div class="stat-box-label">今日查询</div>
                <div class="stat-box-value">—</div>
            </div>
            <div class="stat-box">
                <div class="stat-box-label">当前账号</div>
                <div class="stat-box-value email">{{ Auth::user()->email }}</div>
            </div>
        </div>

        <!-- Tools -->
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
                        <div class="tool-card-icon" style="background:{{ $tool->color }}12;">
                            <span style="color:{{ $tool->color }}">{{ $tool->icon }}</span>
                        </div>
                        <div class="tool-card-title">{{ $tool->name }}</div>
                        @if($tool->description)
                            <div class="tool-card-desc">{{ $tool->description }}</div>
                        @endif
                        <div class="tool-card-arrow">
                            进入 <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</body>
</html>

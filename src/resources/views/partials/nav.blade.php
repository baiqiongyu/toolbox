<style>
/* ===== TOP NAV ===== */
.topbar {
    position: sticky; top: 0; z-index: 100;
    background: rgba(255,255,255,.88);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    border-bottom: 1px solid #e2e5ea;
    height: 62px;
    box-shadow: 0 1px 4px rgba(0,0,0,.03);
}
.topbar::after {
    content: '';
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 1px;
    background: linear-gradient(90deg, #1e40af20, #3b82f640, #1e40af20);
}
.topbar-inner {
    max-width: 1280px; margin: 0 auto; padding: 0 40px;
    height: 100%; display: flex; align-items: center; justify-content: space-between;
}
.topbar-left { display: flex; align-items: center; gap: 36px; }
.topbar-brand {
    display: flex; align-items: center; gap: 10px;
    text-decoration: none; flex-shrink: 0;
}
.topbar-logo {
    width: 32px; height: 32px;
    background: linear-gradient(135deg,#1e40af,#3b82f6);
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 2px 6px rgba(30,64,175,.25);
}
.topbar-logo svg { width: 18px; height: 18px; color: white; }
.topbar-brand-text { line-height: 1.4; }
.topbar-brand-name { font-size: 14px; font-weight: 700; color: #0f172a; letter-spacing: -.01em; }
.topbar-brand-sub { font-size: 10px; font-weight: 500; color: #94a3b8; letter-spacing: .02em; margin-top: 3px; }

.topbar-nav { display: flex; align-items: center; gap: 4px; }
.topbar-nav-item {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 7px 14px; border-radius: 7px;
    font-size: 13px; font-weight: 500;
    color: #64748b; text-decoration: none;
    position: relative;
    transition: all .2s ease;
}
.topbar-nav-item svg {
    width: 15px; height: 15px;
    transition: transform .2s ease;
}
.topbar-nav-item:hover {
    background: #f1f4f9;
    color: #1e293b;
}
.topbar-nav-item:hover svg {
    transform: translateY(-1px);
}
.topbar-nav-item.active {
    background: #eef2ff;
    color: #1e40af;
    font-weight: 600;
}
.topbar-nav-item.active::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 50%;
    transform: translateX(-50%);
    width: 20px;
    height: 3px;
    background: #1e40af;
    border-radius: 2px;
}

.topbar-right { display: flex; align-items: center; gap: 14px; }
.topbar-user {
    display: flex; align-items: center; gap: 10px;
    padding: 4px 12px 4px 4px;
    border-radius: 8px;
    transition: all .2s ease;
    cursor: default;
}
.topbar-user:hover { background: #f1f4f9; }
.topbar-avatar {
    width: 32px; height: 32px;
    border-radius: 8px;
    background: linear-gradient(135deg,#1e40af,#3b82f6);
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; font-weight: 700; color: white;
    box-shadow: 0 1px 3px rgba(30,64,175,.2);
}
.topbar-user-info { line-height: 1.1; }
.topbar-user-name { font-size: 13px; font-weight: 600; color: #0f172a; }
.topbar-user-role { font-size: 10px; font-weight: 500; color: #94a3b8; margin-top: 3px; }
.topbar-divider {
    width: 1px; height: 26px;
    background: #e2e5ea;
}
.topbar-logout {
    width: 34px; height: 34px;
    border-radius: 8px; border: none; background: none;
    color: #94a3b8; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: all .2s ease;
}
.topbar-logout:hover {
    background: #fef2f2; color: #ef4444;
    transform: scale(1.05);
}
.topbar-logout svg { width: 17px; height: 17px; }
</style>
@php
    $currentRoute = Route::currentRouteName();
    $isAdmin = Auth::user()->is_admin;
@endphp
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
                <div class="topbar-brand-text">
                    <div class="topbar-brand-name">和兴国际物流</div>
                    <div class="topbar-brand-sub">内部工具平台</div>
                </div>
            </a>
            <nav class="topbar-nav">
                <a href="{{ route('dashboard') }}"
                   class="topbar-nav-item {{ $currentRoute === 'dashboard' ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        <polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>
                    工具首页
                </a>
                @if($isAdmin)
                    <a href="{{ route('admin.users.index') }}"
                       class="topbar-nav-item {{ str_starts_with($currentRoute, 'admin.') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
                        </svg>
                        用户管理
                    </a>
                @endif
            </nav>
        </div>
        <div class="topbar-right">
            <div class="topbar-user">
                <div class="topbar-avatar">{{ substr(Auth::user()->name, 0, 1) }}</div>
                <div class="topbar-user-info">
                    <div class="topbar-user-name">{{ Auth::user()->name }}</div>
                    <div class="topbar-user-role">{{ $isAdmin ? '管理员' : '普通用户' }}</div>
                </div>
            </div>
            <div class="topbar-divider"></div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="topbar-logout" title="退出登录">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</header>

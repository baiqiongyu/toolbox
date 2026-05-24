<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>用户管理 · 易和国际物流</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', 'PingFang SC', 'Microsoft YaHei', sans-serif;
            color: #1e293b; min-height: 100vh; background: #f1f4f9;
        }
        .topbar {
            position: sticky; top: 0; z-index: 100;
            background: #fff; border-bottom: 1px solid #e6e8ec; height: 58px;
        }
        .topbar-inner {
            max-width: 1280px; margin: 0 auto; padding: 0 32px;
            height: 100%; display: flex; align-items: center; justify-content: space-between;
        }
        .topbar-left { display: flex; align-items: center; gap: 32px; }
        .topbar-brand { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .topbar-logo {
            width: 28px; height: 28px; background: #1e40af; border-radius: 6px;
            display: flex; align-items: center; justify-content: center;
        }
        .topbar-logo svg { width: 16px; height: 16px; color: white; }
        .topbar-brand-text { font-size: 14px; font-weight: 600; color: #0f172a; }
        .topbar-nav { display: flex; align-items: center; gap: 2px; }
        .topbar-nav-item {
            padding: 6px 14px; border-radius: 6px; font-size: 13px; font-weight: 500;
            color: #64748b; text-decoration: none; transition: all .15s;
        }
        .topbar-nav-item:hover { background: #f1f4f9; color: #1e293b; }
        .topbar-nav-item.active { background: #e8edf5; color: #1e40af; }
        .topbar-right { display: flex; align-items: center; gap: 12px; }
        .topbar-user { display: flex; align-items: center; gap: 8px; }
        .topbar-avatar {
            width: 28px; height: 28px; border-radius: 6px; background: #e8edf5;
            display: flex; align-items: center; justify-content: center;
            font-size: 11px; font-weight: 600; color: #1e40af;
        }
        .topbar-name { font-size: 13px; color: #475569; font-weight: 500; }
        .topbar-logout {
            padding: 6px 10px; border-radius: 6px; border: none; background: none;
            color: #94a3b8; cursor: pointer; font-size: 13px;
            display: flex; align-items: center; gap: 6px; transition: all .15s;
        }
        .topbar-logout:hover { background: #fef2f2; color: #ef4444; }
        .topbar-logout svg { width: 16px; height: 16px; }
        .page { max-width: 1280px; margin: 0 auto; padding: 28px 32px 48px; }
        .page-header { margin-bottom: 24px; }
        .page-header h1 { font-size: 22px; font-weight: 700; color: #0f172a; }
        .page-header-row { display: flex; align-items: center; justify-content: space-between; margin-top: 4px; }
        .page-header p { font-size: 14px; color: #64748b; }

        .btn-primary {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 18px; border-radius: 7px; border: none;
            background: #1e40af; color: white; font-size: 13px; font-weight: 500;
            cursor: pointer; text-decoration: none; transition: all .15s;
        }
        .btn-primary:hover { background: #1e3a8a; }

        .btn-sm {
            padding: 5px 12px; font-size: 12px; border-radius: 5px;
            border: none; cursor: pointer; text-decoration: none;
            display: inline-flex; align-items: center; gap: 4px; transition: all .15s;
        }
        .btn-edit { background: #e8edf5; color: #1e40af; }
        .btn-edit:hover { background: #d1d9ea; }
        .btn-danger { background: #fef2f2; color: #ef4444; }
        .btn-danger:hover { background: #fee2e2; }

        .alert {
            padding: 12px 18px; border-radius: 8px; font-size: 13px; margin-bottom: 20px;
        }
        .alert-success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
        .alert-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }

        .table-wrap {
            background: #fff; border-radius: 10px; border: 1px solid #e6e8ec; overflow: hidden;
        }
        table { width: 100%; border-collapse: collapse; }
        th {
            padding: 12px 18px; text-align: left; font-size: 11px; font-weight: 600;
            color: #94a3b8; text-transform: uppercase; letter-spacing: .05em;
            background: #f8fafc; border-bottom: 1px solid #e6e8ec;
        }
        td {
            padding: 12px 18px; font-size: 13px; color: #475569;
            border-bottom: 1px solid #f1f4f9;
        }
        tr:last-child td { border-bottom: none; }

        .badge {
            display: inline-block; padding: 2px 8px; border-radius: 4px;
            font-size: 11px; font-weight: 600;
        }
        .badge-admin { background: #e8edf5; color: #1e40af; }
        .badge-user { background: #f1f4f9; color: #64748b; }
        .badge-active { background: #f0fdf4; color: #16a34a; }
        .badge-inactive { background: #fef2f2; color: #ef4444; }

        .actions { display: flex; gap: 6px; }

        /* ===== MODAL ===== */
        .modal-overlay {
            position: fixed; inset: 0; z-index: 200;
            background: rgba(15, 23, 42, .45);
            display: none;
            align-items: center; justify-content: center;
            backdrop-filter: blur(2px);
        }
        .modal-overlay.show { display: flex; }

        .modal-box {
            background: #fff;
            border-radius: 12px;
            padding: 0;
            width: 380px;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,.25);
            animation: modalIn .2s ease-out;
        }
        @keyframes modalIn {
            from { opacity: 0; transform: scale(.95) translateY(8px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }

        .modal-header {
            padding: 20px 24px 0;
            display: flex; align-items: flex-start; gap: 14px;
        }
        .modal-icon {
            width: 40px; height: 40px; border-radius: 10px;
            background: #fef2f2; color: #ef4444;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .modal-icon svg { width: 20px; height: 20px; }
        .modal-title {
            font-size: 15px; font-weight: 600; color: #0f172a;
            margin: 2px 0 4px;
        }
        .modal-desc {
            font-size: 13px; color: #64748b; line-height: 1.5;
        }
        .modal-desc strong { color: #1e293b; }

        .modal-footer {
            padding: 16px 24px 20px;
            display: flex; justify-content: flex-end; gap: 8px;
        }
        .modal-btn {
            padding: 7px 16px; border-radius: 6px; font-size: 13px;
            font-weight: 500; border: none; cursor: pointer;
            transition: all .15s; font-family: inherit;
        }
        .modal-btn-cancel {
            background: #f1f4f9; color: #475569;
        }
        .modal-btn-cancel:hover { background: #e2e5ea; }
        .modal-btn-confirm {
            background: #ef4444; color: white;
        }
        .modal-btn-confirm:hover { background: #dc2626; }
    </style>
</head>
<body>
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
                    <a href="{{ route('dashboard') }}" class="topbar-nav-item">工具首页</a>
                    <a href="{{ route('admin.users.index') }}" class="topbar-nav-item active">用户管理</a>
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

    <div class="page">
        <div class="page-header">
            <div class="page-header-row">
                <div>
                    <h1>用户管理</h1>
                    <p>共 {{ $users->count() }} 个账号</p>
                </div>
                <a href="{{ route('admin.users.create') }}" class="btn-primary">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    新增用户
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>姓名</th>
                        <th>邮箱</th>
                        <th>类型</th>
                        <th>状态</th>
                        <th>创建时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td style="color:#94a3b8;">#{{ $user->id }}</td>
                            <td style="font-weight:500;">
                                {{ $user->name }}
                                @if($user->id === auth()->id())
                                    <span style="font-size:11px;color:#94a3b8;margin-left:4px;">（当前）</span>
                                @endif
                            </td>
                            <td style="color:#64748b;">{{ $user->email }}</td>
                            <td>
                                <span class="badge {{ $user->is_admin ? 'badge-admin' : 'badge-user' }}">
                                    {{ $user->is_admin ? '管理员' : '普通用户' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $user->is_active ? 'badge-active' : 'badge-inactive' }}">
                                    {{ $user->is_active ? '启用' : '禁用' }}
                                </span>
                            </td>
                            <td style="color:#94a3b8;">{{ $user->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                <div class="actions">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn-sm btn-edit">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M17 3a2.83 2.83 0 114 4L7.5 20.5 2 22l1.5-5.5L17 3z"/>
                                        </svg>
                                        编辑
                                    </a>
                                    @if($user->id !== auth()->id())
                                        <button type="button" class="btn-sm btn-danger" onclick="showDeleteModal('{{ $user->name }}', '{{ route('admin.users.destroy', $user) }}')">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
                                            </svg>
                                            删除
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- ===== 删除确认弹窗 ===== -->
    <div class="modal-overlay" id="deleteModal">
        <div class="modal-box">
            <div class="modal-header">
                <div class="modal-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M12 9v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <div class="modal-title">确认删除</div>
                    <div class="modal-desc">
                        确定要删除用户 <strong id="modalUserName"></strong> 吗？<br>
                        此操作不可撤销。
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="modal-btn modal-btn-cancel" onclick="closeDeleteModal()">取消</button>
                <form method="POST" id="deleteForm">
                    @csrf @method('DELETE')
                    <button type="submit" class="modal-btn modal-btn-confirm">确认删除</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showDeleteModal(name, url) {
            document.getElementById('modalUserName').textContent = name;
            document.getElementById('deleteForm').action = url;
            document.getElementById('deleteModal').classList.add('show');
        }
        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.remove('show');
        }
        // 点击蒙层关闭
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) closeDeleteModal();
        });
        // ESC 键关闭
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeDeleteModal();
        });
    </script>
</body>
</html>

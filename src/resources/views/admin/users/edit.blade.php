<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>编辑用户 · 易和国际物流</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', 'PingFang SC', 'Microsoft YaHei', sans-serif;
            color: #1e293b; min-height: 100vh; background: #f1f4f9;
        }
        .card {
            background: #fff; border-radius: 10px; border: 1px solid #e6e8ec;
            padding: 28px 32px;
        }
        .form-group { margin-bottom: 20px; }
        .form-group:last-child { margin-bottom: 0; }
        .form-label {
            display: block; font-size: 13px; font-weight: 500; color: #374151;
            margin-bottom: 6px;
        }
        .form-input {
            width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #d1d5db;
            font-size: 13px; font-family: inherit; color: #1e293b;
            transition: border-color .15s;
        }
        .form-input:focus {
            outline: none; border-color: #1e40af; box-shadow: 0 0 0 2px rgba(30,64,175,.1);
        }
        .form-hint { font-size: 11px; color: #94a3b8; margin-top: 4px; }

        .checkbox-group {
            display: flex; align-items: center; gap: 8px; padding: 4px 0;
        }
        .checkbox-group input[type="checkbox"] {
            width: 16px; height: 16px; border-radius: 3px; border: 1px solid #d1d5db;
            cursor: pointer;
        }
        .checkbox-group label { font-size: 13px; color: #475569; cursor: pointer; }

        .form-actions {
            display: flex; align-items: center; gap: 10px; margin-top: 24px; padding-top: 20px;
            border-top: 1px solid #f1f4f9;
        }
        .btn-primary {
            padding: 8px 20px; border-radius: 7px; border: none;
            background: #1e40af; color: white; font-size: 13px; font-weight: 500;
            cursor: pointer; transition: all .15s;
        }
        .btn-primary:hover { background: #1e3a8a; }
        .btn-danger {
            padding: 8px 20px; border-radius: 7px; border: none;
            background: #fef2f2; color: #ef4444; font-size: 13px; font-weight: 500;
            cursor: pointer; transition: all .15s;
        }
        .btn-danger:hover { background: #fee2e2; }
        .btn-secondary {
            padding: 8px 20px; border-radius: 7px; border: 1px solid #d1d5db;
            background: white; color: #64748b; font-size: 13px; font-weight: 500;
            text-decoration: none; transition: all .15s;
        }
        .btn-secondary:hover { background: #f8fafc; }

        .error-text { font-size: 12px; color: #ef4444; margin-top: 4px; }
        .info-box {
            background: #fffbeb; border: 1px solid #fde68a; border-radius: 6px;
            padding: 10px 14px; font-size: 12px; color: #92400e; margin-bottom: 20px;
        }

        /* ===== PAGE ===== */
        .page { max-width: 680px; margin: 0 auto; padding: 28px 32px 48px; }
        .page-header { margin-bottom: 24px; display: flex; align-items: center; gap: 12px; }
        .page-header h1 { font-size: 22px; font-weight: 700; color: #0f172a; }
        .page-header .back-link {
            color: #94a3b8; font-size: 14px; text-decoration: none;
            display: flex; align-items: center; gap: 4px;
        }
        .page-header .back-link:hover { color: #64748b; }
    </style>
</head>
<body>
    @include('partials.nav')
<div class="page">
        <div class="page-header">
            <a href="{{ route('admin.users.index') }}" class="back-link">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="15 18 9 12 15 6"/>
                </svg>
                返回
            </a>
            <h1>编辑用户</h1>
        </div>

        @if($user->id === auth()->id())
            <div class="info-box">
                这是你当前登录的账号，管理员权限和启用状态不可更改。
            </div>
        @endif

        <div class="card">
            <form method="POST" action="{{ route('admin.users.update', $user) }}">
                @csrf @method('PATCH')

                <div class="form-group">
                    <label class="form-label">姓名</label>
                    <input type="text" name="name" class="form-input" value="{{ old('name', $user->name) }}" required>
                    @error('name') <div class="error-text">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">邮箱</label>
                    <input type="email" name="email" class="form-input" value="{{ old('email', $user->email) }}" required>
                    @error('email') <div class="error-text">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">新密码</label>
                    <input type="password" name="password" class="form-input" placeholder="留空则不修改密码">
                    <div class="form-hint">仅当需要修改密码时填写，至少 6 位</div>
                    @error('password') <div class="error-text">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" name="is_admin" value="1" id="is_admin"
                            {{ $user->id === auth()->id() ? 'checked disabled' : (old('is_admin', $user->is_admin) ? 'checked' : '') }}>
                        <label for="is_admin">管理员</label>
                    </div>
                    <div class="form-hint">管理员可以管理用户</div>
                </div>

                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" name="is_active" value="1" id="is_active"
                            {{ $user->id === auth()->id() ? 'checked disabled' : (old('is_active', $user->is_active) ? 'checked' : '') }}>
                        <label for="is_active">账号启用</label>
                    </div>
                    <div class="form-hint">禁用后用户无法登录</div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">保存修改</button>
                    <a href="{{ route('admin.users.index') }}" class="btn-secondary">取消</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

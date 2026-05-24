<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>新增用户 · 易和国际物流</title>
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
        .btn-secondary {
            padding: 8px 20px; border-radius: 7px; border: 1px solid #d1d5db;
            background: white; color: #64748b; font-size: 13px; font-weight: 500;
            text-decoration: none; transition: all .15s;
        }
        .btn-secondary:hover { background: #f8fafc; }

        .error-text { font-size: 12px; color: #ef4444; margin-top: 4px; }

        /* ===== PAGE ===== */
        .page { max-width: 680px; margin: 0 auto; padding: 28px 32px 48px; }
        .page-header { margin-bottom: 24px; }
        .page-header h1 { font-size: 22px; font-weight: 700; color: #0f172a; }
        .page-header p { font-size: 14px; color: #64748b; margin-top: 4px; }
    </style>
</head>
<body>
    @include('partials.nav')
<div class="page">
        <div class="page-header">
            <h1>新增用户</h1>
            <p>创建一个新的系统账号</p>
        </div>

        <div class="card">
            <form method="POST" action="{{ route('admin.users.store') }}">
                @csrf

                <div class="form-group">
                    <label class="form-label">姓名</label>
                    <input type="text" name="name" class="form-input" value="{{ old('name') }}" placeholder="如：张三" required>
                    @error('name') <div class="error-text">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">邮箱</label>
                    <input type="email" name="email" class="form-input" value="{{ old('email') }}" placeholder="如：zhangsan@e-kc.com" required>
                    @error('email') <div class="error-text">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">密码</label>
                    <input type="password" name="password" class="form-input" placeholder="至少 6 位" required>
                    <div class="form-hint">至少 6 位，建议使用字母+数字组合</div>
                    @error('password') <div class="error-text">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" name="is_admin" value="1" id="is_admin" {{ old('is_admin') ? 'checked' : '' }}>
                        <label for="is_admin">设为管理员</label>
                    </div>
                    <div class="form-hint">管理员可以管理用户、查看操作日志</div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">创建用户</button>
                    <a href="{{ route('admin.users.index') }}" class="btn-secondary">取消</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

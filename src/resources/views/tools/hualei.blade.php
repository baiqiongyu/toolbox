<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>华磊轨迹查询 · 易和国际物流</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Inter','PingFang SC','Microsoft YaHei',sans-serif; color:#1e293b; background:#f1f4f9; }

        .page { max-width:900px; margin:0 auto; padding:28px 32px 48px; }
        .breadcrumb { display:flex; align-items:center; gap:6px; font-size:12px; color:#94a3b8; margin-bottom:8px; }
        .breadcrumb a { color:#94a3b8; text-decoration:none; }
        .breadcrumb a:hover { color:#1e40af; }
        .page-header { margin-bottom:24px; }
        .page-header h1 { font-size:22px; font-weight:700; color:#0f172a; }
        .page-header p { font-size:14px; color:#64748b; margin-top:4px; }

        .search-card { background:#fff; border-radius:10px; border:1px solid #e6e8ec; padding:24px; margin-bottom:20px; }
        .search-row { display:flex; gap:10px; }
        .search-input-wrap { flex:1; position:relative; }
        .search-input-wrap svg { position:absolute; left:12px; top:50%; transform:translateY(-50%); width:18px; height:18px; color:#94a3b8; pointer-events:none; }
        .search-input-wrap input { width:100%; padding:10px 14px 10px 38px; border:1px solid #d1d5db; border-radius:8px; font-size:14px; outline:none; }
        .search-input-wrap input:focus { border-color:#1e40af; box-shadow:0 0 0 3px rgba(30,64,175,.1); }
        .search-row button { padding:10px 28px; background:#1e40af; color:#fff; border:none; border-radius:8px; font-size:14px; font-weight:500; cursor:pointer; }
        .search-row button:hover { background:#1e3a8a; }
        .search-row button:disabled { background:#94a3b8; cursor:not-allowed; }
        .search-hint { font-size:12px; color:#94a3b8; margin-top:8px; }

        .error-box { display:none; background:#fef2f2; border:1px solid #fecaca; border-radius:8px; padding:14px 18px; font-size:13px; color:#dc2626; margin-bottom:16px; }
        .loading-spinner { display:none; text-align:center; padding:40px; }
        .spinner { width:32px; height:32px; border:3px solid #e6e8ec; border-top-color:#1e40af; border-radius:50%; animation:spin .6s linear infinite; margin:0 auto 12px; }
        @keyframes spin { to { transform:rotate(360deg); } }

        .result-card { display:none; background:#fff; border-radius:10px; border:1px solid #e6e8ec; overflow:hidden; }
        .result-header { padding:20px 24px; border-bottom:1px solid #e6e8ec; }
        .result-header .no { font-size:13px; color:#64748b; margin-bottom:4px; }
        .result-header .status { font-size:18px; font-weight:700; color:#0f172a; }

        .timeline { padding:20px 16px; }
        .timeline-item { display:flex; gap:16px; padding-bottom:24px; position:relative; }
        .timeline-item:last-child { padding-bottom:0; }
        .timeline-line { display:flex; flex-direction:column; align-items:center; width:24px; flex-shrink:0; }
        .timeline-dot { width:14px; height:14px; border-radius:50%; background:#dbeafe; border:3px solid #059669; z-index:1; }
        .timeline-item:last-child .timeline-dot { background:#059669; box-shadow:0 0 0 4px rgba(5,150,105,.15); }
        .timeline-line-bar { width:2px; flex:1; background:#dbeafe; margin:4px 0; }
        .timeline-item:last-child .timeline-line-bar { display:none; }
        .timeline-content { flex:1; min-width:0; }
        .timeline-datetime { font-size:12px; font-weight:600; color:#64748b; margin-bottom:4px; }
        .timeline-status { font-size:15px; font-weight:600; color:#0f172a; }

        .empty-state { display:none; text-align:center; padding:60px 20px; color:#94a3b8; font-size:13px; }
    </style>
</head>
<body>
    @include('partials.nav')
<div class="page">
        <div class="breadcrumb">
            <a href="{{ route('dashboard') }}">工具首页</a>
            <span>/</span>
            <span>华磊物流轨迹查询</span>
        </div>
        <div class="page-header">
            <h1>🚚 华磊物流轨迹查询</h1>
            <p>HuaLei Logistics · 批量查询物流轨迹信息</p>
        </div>

        <div class="search-card">
            <div class="search-row">
                <div class="search-input-wrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" id="trackingInput" placeholder="输入快递单号" autocomplete="off">
                </div>
                <button id="searchBtn" disabled>查 询</button>
            </div>
            <p class="search-hint">支持华磊物流快递单号查询</p>
        </div>

        <div class="error-box" id="errorBox"></div>
        <div class="loading-spinner" id="loadingSpinner"><div class="spinner"></div><p>正在查询...</p></div>

        <div class="result-card" id="resultCard">
            <div class="result-header">
                <div class="no" id="trackingNo"></div>
                <div class="status" id="resultStatus">-</div>
            </div>
            <div class="timeline" id="timelineWrap"></div>
        </div>
    </div>

    <script>
        const input = document.getElementById('trackingInput');
        const btn = document.getElementById('searchBtn');
        const errorBox = document.getElementById('errorBox');
        const loading = document.getElementById('loadingSpinner');
        const resultCard = document.getElementById('resultCard');

        function ck() { btn.disabled = !input.value.trim(); }
        input.oninput = ck;
        input.onkeydown = e => { if (e.key === 'Enter' && !btn.disabled) search(); };
        btn.onclick = search;

        function search() {
            const no = input.value.trim();
            if (!no) return;
            errorBox.style.display = 'none';
            resultCard.style.display = 'none';
            loading.style.display = 'block';
            btn.disabled = true;
            btn.textContent = '查询中...';

            fetch('/tools/hualei/track', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ tracking_no: no })
            })
            .then(r => r.json())
            .then(data => {
                loading.style.display = 'none';
                btn.disabled = false;
                btn.textContent = '查 询';
                if (!data.success) {
                    errorBox.textContent = data.message;
                    errorBox.style.display = 'block';
                    return;
                }
                render(data);
            })
            .catch(() => {
                loading.style.display = 'none';
                btn.disabled = false;
                btn.textContent = '查 询';
                errorBox.textContent = '服务器异常，请稍后重试';
                errorBox.style.display = 'block';
            });
        }

        function render(data) {
            resultCard.style.display = 'block';
            document.getElementById('trackingNo').textContent = '单号：' + data.tracking_no;
            document.getElementById('resultStatus').textContent = data.status_cn;

            const wrap = document.getElementById('timelineWrap');
            if (!data.history || !data.history.length) {
                wrap.innerHTML = '<div style="padding:20px;text-align:center;color:#94a3b8;font-size:13px;">暂无轨迹</div>';
                return;
            }

            let html = '';
            data.history.forEach((item, idx) => {
                const isLast = idx === data.history.length - 1;
                html += `
                    <div class="timeline-item">
                        <div class="timeline-line">
                            <div class="timeline-dot" style="${isLast ? 'background:#059669;box-shadow:0 0 0 4px rgba(5,150,105,.15);' : ''}"></div>
                            <div class="timeline-line-bar"></div>
                        </div>
                        <div class="timeline-content">
                            <div class="timeline-datetime">${item.datetime}</div>
                            <div class="timeline-status">${item.status_cn}</div>
                            ${item.location && item.location !== '-' ? `<div style="font-size:12px;color:#94a3b8;margin-top:4px;">📍 ${item.location}</div>` : ''}
                        </div>
                    </div>
                `;
            });
            wrap.innerHTML = html;
        }
    </script>
</body>
</html>

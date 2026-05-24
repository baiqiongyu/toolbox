<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>清关轨迹查询 · 易和国际物流</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800&display=swap" rel="stylesheet" />
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
        .search-input-wrap input { width:100%; padding:10px 14px 10px 38px; border:1px solid #d1d5db; border-radius:8px; font-size:14px; outline:none; transition:border-color .15s; box-sizing:border-box; }
        .search-input-wrap input:focus { border-color:#1e40af; box-shadow:0 0 0 3px rgba(30,64,175,.1); }
        .search-year { width:100px; padding:10px 12px; border:1px solid #d1d5db; border-radius:8px; font-size:14px; outline:none; }
        .search-year:focus { border-color:#1e40af; box-shadow:0 0 0 3px rgba(30,64,175,.1); }
        .search-row button { padding:10px 28px; background:#1e40af; color:#fff; border:none; border-radius:8px; font-size:14px; font-weight:500; cursor:pointer; transition:background .15s; white-space:nowrap; }
        .search-row button:hover { background:#1e3a8a; }
        .search-row button:disabled { background:#94a3b8; cursor:not-allowed; }
        .search-hint { font-size:12px; color:#94a3b8; margin-top:8px; }

        .error-box { display:none; background:#fef2f2; border:1px solid #fecaca; border-radius:8px; padding:14px 18px; font-size:13px; color:#dc2626; margin-bottom:16px; }

        .loading-spinner { display:none; text-align:center; padding:40px; }
        .spinner { width:32px; height:32px; border:3px solid #e6e8ec; border-top-color:#1e40af; border-radius:50%; animation:spin .6s linear infinite; margin:0 auto 12px; }
        @keyframes spin { to { transform:rotate(360deg); } }
        .loading-spinner p { font-size:13px; color:#94a3b8; }

        .result-card { display:none; background:#fff; border-radius:10px; border:1px solid #e6e8ec; overflow:hidden; }
        .result-header { padding:20px 24px; border-bottom:1px solid #e6e8ec; }
        .result-tracking-no { font-size:13px; color:#64748b; margin-bottom:4px; }
        .result-status { font-size:18px; font-weight:700; color:#0f172a; }
        .result-status-sub { font-size:13px; color:#64748b; margin-top:2px; }

        .timeline { padding:20px 16px; }
        .timeline-item { display:flex; gap:16px; padding-bottom:24px; position:relative; }
        .timeline-item:last-child { padding-bottom:0; }
        .timeline-line { display:flex; flex-direction:column; align-items:center; width:24px; flex-shrink:0; }
        .timeline-dot { width:14px; height:14px; border-radius:50%; background:#dbeafe; border:3px solid #6366f1; z-index:1; flex-shrink:0; }
        .timeline-item:last-child .timeline-dot { background:#6366f1; box-shadow:0 0 0 4px rgba(99,102,241,.15); }
        .timeline-line-bar { width:2px; flex:1; background:#dbeafe; margin:4px 0; }
        .timeline-item:last-child .timeline-line-bar { display:none; }
        .timeline-content { flex:1; min-width:0; }
        .timeline-time { font-size:11px; font-weight:600; color:#64748b; margin-bottom:6px; }
        .timeline-time span:first-child { font-weight:700; color:#1e293b; }
        .timeline-time .sep { color:#cbd5e1; margin:0 4px; }
        .timeline-status-ko { font-size:15px; font-weight:700; color:#0f172a; margin-bottom:2px; }
        .timeline-status-en { font-size:12px; font-weight:500; color:#475569; margin-bottom:1px; }
        .timeline-status-cn { font-size:12px; color:#64748b; margin-bottom:4px; }
        .timeline-meta { display:flex; flex-wrap:wrap; gap:6px; margin-top:6px; }
        .timeline-tag { display:inline-flex; align-items:center; padding:2px 8px; border-radius:4px; font-size:11px; font-weight:500; }
        .timeline-tag-purple { background:#eef2ff; color:#4f46e5; }
        .timeline-tag-gray { background:#f1f5f9; color:#64748b; }
        .timeline-content-detail { font-size:12px; color:#94a3b8; margin-top:6px; line-height:1.4; }

        .empty-state { text-align:center; padding:60px 20px; color:#94a3b8; }
        .empty-state svg { width:44px; height:44px; color:#d1d5db; margin-bottom:12px; }
        .empty-state p { font-size:13px; }

        @media (max-width:640px) { .search-row { flex-direction:column; } .page { padding:20px 16px; } }
    </style>
</head>
<body>
    @include('partials.nav')
<div class="page">
        <div class="breadcrumb">
            <a href="{{ route('dashboard') }}">工具首页</a>
            <span>/</span>
            <span>清关轨迹查询</span>
        </div>
        <div class="page-header">
            <h1>🛃 清关轨迹查询</h1>
            <p>Korea Customs Service · 韩国海关 UNI-PASS 通关进度查询</p>
        </div>

        <div class="search-card">
            <div class="search-row">
                <div class="search-input-wrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" id="trackingInput" placeholder="输入提单号 HBL No." autocomplete="off">
                </div>
                <input type="text" id="yearInput" class="search-year" value="{{ date('Y') }}" placeholder="年份">
                <button id="searchBtn" disabled>查 询</button>
            </div>
            <p class="search-hint">韩国海关 UNI-PASS 系统，输入提单号（HBL No.）查询通关进度</p>
        </div>

        <div class="error-box" id="errorBox"></div>

        <div class="loading-spinner" id="loadingSpinner">
            <div class="spinner"></div>
            <p>正在查询韩国海关系统...</p>
        </div>

        <div class="result-card" id="resultCard">
            <div class="result-header">
                <div class="result-tracking-no" id="resultTrackingNo"></div>
                <div class="result-status" id="resultStatus">-</div>
                <div class="result-status-sub" id="resultStatusSub"></div>
            </div>
            <div class="timeline" id="timelineWrap"></div>
        </div>

        <div class="empty-state" id="emptyState" style="display:none;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
            </svg>
            <p id="emptyText">输入提单号开始查询</p>
        </div>
    </div>

    <script>
        const input = document.getElementById('trackingInput');
        const yearInput = document.getElementById('yearInput');
        const btn = document.getElementById('searchBtn');
        const errorBox = document.getElementById('errorBox');
        const loading = document.getElementById('loadingSpinner');
        const resultCard = document.getElementById('resultCard');

        function checkBtn() { btn.disabled = !input.value.trim(); }
        input.oninput = checkBtn;
        input.onkeydown = e => { if (e.key === 'Enter' && !btn.disabled) search(); };
        yearInput.onkeydown = e => { if (e.key === 'Enter' && !btn.disabled) search(); };
        btn.onclick = search;

        function search() {
            const no = input.value.trim();
            if (!no) return;
            hideError();
            resultCard.style.display = 'none';
            loading.style.display = 'block';
            btn.disabled = true;
            btn.textContent = '查询中...';

            fetch('/tools/customs/track', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ tracking_no: no, year: yearInput.value || undefined })
            })
            .then(r => r.json())
            .then(data => {
                loading.style.display = 'none';
                btn.disabled = false;
                btn.textContent = '查 询';
                if (!data.success) { showError(data.message); return; }
                renderResult(data);
            })
            .catch(() => {
                loading.style.display = 'none';
                btn.disabled = false;
                btn.textContent = '查 询';
                showError('服务器异常，请稍后重试');
            });
        }

        function renderResult(data) {
            resultCard.style.display = 'block';

            let headerInfo = '提单号：' + data.tracking_no;
            if (data.product_name) headerInfo += ' · ' + data.product_name;
            if (data.country) headerInfo += ' · ' + data.country;
            if (data.customs_office) headerInfo += ' · ' + data.customs_office;
            document.getElementById('resultTrackingNo').textContent = headerInfo;
            document.getElementById('resultStatus').textContent = data.status_cn || '清关处理中';
            document.getElementById('resultStatusSub').textContent = data.status || '';

            const wrap = document.getElementById('timelineWrap');
            if (!data.history || !data.history.length) {
                wrap.innerHTML = '<div style="padding:20px;text-align:center;color:#94a3b8;font-size:13px;">暂无轨迹数据</div>';
                return;
            }

            let html = '';
            data.history.forEach((item, idx) => {
                const isLast = idx === data.history.length - 1;
                let datePart = item.datetime || '', timePart = '';
                if (datePart.includes(' ')) { const p = datePart.split(' '); datePart = p[0]; timePart = p.slice(1).join(' '); }

                html += `
                    <div class="timeline-item">
                        <div class="timeline-line">
                            <div class="timeline-dot" style="${isLast ? 'background:#6366f1;box-shadow:0 0 0 4px rgba(99,102,241,.15);' : ''}"></div>
                            <div class="timeline-line-bar"></div>
                        </div>
                        <div class="timeline-content">
                            <div class="timeline-time">
                                <span>${datePart || '--'}</span><span class="sep">|</span><span>${timePart || '--:--'}</span>
                            </div>
                            <div class="timeline-status-ko">${item.status_ko}</div>
                            <div class="timeline-status-en">${item.status_en}</div>
                            <div class="timeline-status-cn">${item.status_cn}</div>
                            <div class="timeline-meta">
                                <span class="timeline-tag timeline-tag-purple">${item.status_ko || '통관'}</span>
                                ${item.location && item.location !== '-' ? `<span class="timeline-tag timeline-tag-gray">🏛 ${item.location}</span>` : ''}
                                ${item.dclr_no ? `<span class="timeline-tag timeline-tag-gray">📋 ${item.dclr_no}</span>` : ''}
                                ${item.weight ? `<span class="timeline-tag timeline-tag-gray">⚖ ${item.weight}</span>` : ''}
                            </div>
                            ${item.remark ? `<div class="timeline-content-detail">📌 ${item.remark}</div>` : ''}
                        </div>
                    </div>
                `;
            });
            wrap.innerHTML = html;
        }

        function showError(msg) { errorBox.textContent = msg; errorBox.style.display = 'block'; }
        function hideError() { errorBox.style.display = 'none'; emptyState.style.display = 'none'; }
    </script>
</body>
</html>

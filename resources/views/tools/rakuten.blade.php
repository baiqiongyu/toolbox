<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>乐天物流轨迹查询 · 易和国际物流</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', 'PingFang SC', 'Microsoft YaHei', sans-serif; color: #1e293b; background: #f1f4f9; }

        .topbar { position: sticky; top: 0; z-index: 100; background: #fff; border-bottom: 1px solid #e6e8ec; height: 58px; }
        .topbar-inner { max-width: 1280px; margin: 0 auto; padding: 0 32px; height: 100%; display: flex; align-items: center; justify-content: space-between; }
        .topbar-left { display: flex; align-items: center; gap: 32px; }
        .topbar-brand { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .topbar-logo { width: 28px; height: 28px; background: #1e40af; border-radius: 6px; display: flex; align-items: center; justify-content: center; }
        .topbar-logo svg { width: 16px; height: 16px; color: white; }
        .topbar-brand-text { font-size: 14px; font-weight: 600; color: #0f172a; }
        .topbar-nav { display: flex; align-items: center; gap: 2px; }
        .topbar-nav-item { padding: 6px 14px; border-radius: 6px; font-size: 13px; font-weight: 500; color: #64748b; text-decoration: none; }
        .topbar-nav-item:hover { background: #f1f4f9; color: #1e293b; }
        .topbar-nav-item.active { background: #e8edf5; color: #1e40af; }

        .page { max-width: 900px; margin: 0 auto; padding: 28px 32px 48px; }
        .breadcrumb { display: flex; align-items: center; gap: 6px; font-size: 12px; color: #94a3b8; margin-bottom: 8px; }
        .breadcrumb a { color: #94a3b8; text-decoration: none; }
        .breadcrumb a:hover { color: #1e40af; }
        .page-header { margin-bottom: 24px; }
        .page-header h1 { font-size: 22px; font-weight: 700; color: #0f172a; }
        .page-header p { font-size: 14px; color: #64748b; margin-top: 4px; }

        .search-card { background: #fff; border-radius: 10px; border: 1px solid #e6e8ec; padding: 24px; margin-bottom: 20px; }
        .search-row { display: flex; gap: 10px; }
        .search-input-wrap { flex: 1; position: relative; }
        .search-input-wrap svg {
            position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
            width: 18px; height: 18px; color: #94a3b8; pointer-events: none;
        }
        .search-input-wrap input { width: 100%; padding: 10px 14px 10px 38px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; outline: none; transition: border-color .15s; box-sizing: border-box; }
        .search-input-wrap input:focus { border-color: #1e40af; box-shadow: 0 0 0 3px rgba(30,64,175,.1); }
        .search-row button { padding: 10px 28px; background: #1e40af; color: #fff; border: none; border-radius: 8px; font-size: 14px; font-weight: 500; cursor: pointer; transition: background .15s; white-space: nowrap; }
        .search-row button:hover { background: #1e3a8a; }
        .search-row button:disabled { background: #94a3b8; cursor: not-allowed; }
        .search-hint { font-size: 12px; color: #94a3b8; margin-top: 8px; }

        .error-box { display: none; background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 14px 18px; font-size: 13px; color: #dc2626; margin-bottom: 16px; }

        .result-card { display: none; background: #fff; border-radius: 10px; border: 1px solid #e6e8ec; overflow: hidden; }
        .result-header { padding: 20px 24px; border-bottom: 1px solid #e6e8ec; }
        .result-header-top { display: flex; align-items: center; gap: 12px; }
        .result-status-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
        .result-status-dot.delivered { background: #16a34a; }
        .result-status-dot.transit { background: #2563eb; }
        .result-status-dot.problem { background: #dc2626; }
        .result-tracking-no { font-size: 13px; color: #64748b; margin-top: 4px; }
        .result-status { font-size: 16px; font-weight: 600; }
        .result-status-cn { font-size: 12px; color: #64748b; margin-top: 2px; }

        .timeline { padding: 20px 16px; }
        .timeline-item { display: flex; gap: 16px; padding-bottom: 24px; position: relative; }
        .timeline-item:last-child { padding-bottom: 0; }
        .timeline-line { display: flex; flex-direction: column; align-items: center; width: 24px; flex-shrink: 0; }
        .timeline-dot {
            width: 14px; height: 14px; border-radius: 50%;
            background: #dbeafe; border: 3px solid #1e40af;
            z-index: 1; flex-shrink: 0; transition: all .2s;
        }
        .timeline-item:last-child .timeline-dot {
            background: #1e40af;
            box-shadow: 0 0 0 4px rgba(30,64,175,.15);
        }
        .timeline-dot.delivered { border-color: #16a34a; }
        .timeline-item:last-child .timeline-dot.delivered { background: #16a34a; box-shadow: 0 0 0 4px rgba(22,163,74,.15); }
        .timeline-dot.problem { border-color: #dc2626; }
        .timeline-item:last-child .timeline-dot.problem { background: #dc2626; box-shadow: 0 0 0 4px rgba(220,38,38,.15); }
        .timeline-line-bar { width: 2px; flex: 1; background: #dbeafe; margin: 4px 0; }
        .timeline-item:last-child .timeline-line-bar { display: none; }
        .timeline-content { flex: 1; min-width: 0; padding-top: 0; }
        .timeline-time {
            font-size: 11px; font-weight: 600;
            color: #64748b; letter-spacing: .02em;
            margin-bottom: 6px;
        }
        .timeline-time .time-sep { color: #cbd5e1; margin: 0 4px; }
        .timeline-status-ko {
            font-size: 15px; font-weight: 700;
            color: #0f172a; margin-bottom: 2px;
        }
        .timeline-status-en {
            font-size: 12px; font-weight: 500;
            color: #475569; margin-bottom: 1px;
        }
        .timeline-status-cn {
            font-size: 12px;
            color: #64748b; margin-bottom: 4px;
        }
        .timeline-location {
            font-size: 12px; color: #94a3b8;
            display: flex; align-items: center; gap: 4px;
        }
        .timeline-meta {
            display: flex; flex-wrap: wrap; gap: 8px;
            margin-top: 6px;
        }
        .timeline-tag {
            display: inline-flex; align-items: center;
            padding: 2px 8px; border-radius: 4px;
            font-size: 11px; font-weight: 500;
        }
        .timeline-tag-blue { background: #eef2ff; color: #4f46e5; }
        .timeline-tag-green { background: #dcfce7; color: #16a34a; }
        .timeline-tag-red { background: #fee2e2; color: #dc2626; }
        .timeline-tag-gray { background: #f1f5f9; color: #64748b; }

        .loading-spinner { display: none; text-align: center; padding: 40px; }
        .spinner { width: 32px; height: 32px; border: 3px solid #e6e8ec; border-top-color: #1e40af; border-radius: 50%; animation: spin .6s linear infinite; margin: 0 auto 12px; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .loading-spinner p { font-size: 13px; color: #94a3b8; }

        .empty-state { text-align: center; padding: 60px 20px; }
        .empty-state svg { width: 44px; height: 44px; color: #d1d5db; margin-bottom: 12px; }
        .empty-state p { font-size: 13px; color: #94a3b8; }

        @media (max-width: 640px) {
            .search-row { flex-direction: column; }
            .page { padding: 20px 16px; }
        }
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
                    <a href="" class="topbar-nav-item active">乐天轨迹</a>
                </nav>
            </div>
        </div>
    </header>

    <div class="page">
        <div class="breadcrumb">
            <a href="{{ route('dashboard') }}">工具首页</a>
            <span>/</span>
            <span>乐天物流轨迹查询</span>
        </div>

        <div class="page-header">
            <h1>📦 乐天物流轨迹查询</h1>
            <p>Lotte Global Logistics · 韩国乐天快递单号追踪</p>
        </div>

        <!-- Search -->
        <div class="search-card">
            <div class="search-row">
                <div class="search-input-wrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" id="trackingInput" placeholder="输入乐天快递单号" autocomplete="off">
                </div>
                <button id="searchBtn" disabled>查 询</button>
            </div>
            <p class="search-hint">支持 Lotte Global Logistics 快递单号，自动识别</p>
        </div>

        <!-- Error -->
        <div class="error-box" id="errorBox">
            <div style="display:flex;align-items:flex-start;gap:8px;">
                <svg style="width:16px;height:16px;flex-shrink:0;margin-top:1px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <div>
                    <div style="font-weight:500;margin-bottom:2px;" id="errorTitle">查询失败</div>
                    <div id="errorText" style="color:#991b1b;">-</div>
                </div>
            </div>
        </div>

        <!-- Loading -->
        <div class="loading-spinner" id="loadingSpinner">
            <div class="spinner"></div>
            <p>正在查询乐天物流系统...</p>
        </div>

        <!-- Result -->
        <div class="result-card" id="resultCard">
            <div class="result-header">
                <div class="result-header-top">
                    <div class="result-status-dot" id="statusDot" style="background:#94a3b8;"></div>
                    <div>
                        <div class="result-status" id="resultStatus">查询中...</div>
                        <div class="result-status-cn" id="resultStatusCn"></div>
                    </div>
                </div>
                <div class="result-tracking-no" id="resultTrackingNo"></div>
            </div>
            <div class="timeline" id="timelineWrap"></div>
        </div>

        <!-- No result -->
        <div class="empty-state" id="emptyState" style="display:none;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
            </svg>
            <p id="emptyText">输入单号开始查询</p>
        </div>
    </div>

    <script>
        const input = document.getElementById('trackingInput');
        const btn = document.getElementById('searchBtn');
        const errorBox = document.getElementById('errorBox');
        const errorTitle = document.getElementById('errorTitle');
        const errorText = document.getElementById('errorText');
        const loading = document.getElementById('loadingSpinner');
        const resultCard = document.getElementById('resultCard');
        const emptyState = document.getElementById('emptyState');
        const emptyText = document.getElementById('emptyText');

        input.oninput = function() {
            btn.disabled = !this.value.trim();
        };

        input.onkeydown = function(e) {
            if (e.key === 'Enter' && !btn.disabled) search();
        };

        btn.onclick = search;

        function search() {
            const no = input.value.trim();
            if (!no) return;

            hideError();
            resultCard.style.display = 'none';
            emptyState.style.display = 'none';
            loading.style.display = 'block';
            btn.disabled = true;
            btn.textContent = '查询中...';

            fetch('/tools/rakuten/track', {
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
                    showError(data.message || '请确认单号是否正确');
                    return;
                }

                renderResult(data);
            })
            .catch(err => {
                loading.style.display = 'none';
                btn.disabled = false;
                btn.textContent = '查 询';
                showError('服务器异常，请稍后重试');
                console.error('Rakuten track error:', err);
            });
        }

        function renderResult(data) {
            resultCard.style.display = 'block';

            // Header
            document.getElementById('resultTrackingNo').textContent = '单号：' + data.tracking_no;
            document.getElementById('resultStatus').textContent = data.status_cn || data.status;
            document.getElementById('resultStatusCn').textContent = data.status ? (data.status + ' · ' + data.status_cn) : '';

            // Status dot
            const dot = document.getElementById('statusDot');
            const statusStr = (data.status || '').toLowerCase() + (data.status_cn || '');
            if (statusStr.includes('deliver') || statusStr.includes('签收') || statusStr.includes('完成')) {
                dot.className = 'result-status-dot delivered';
            } else if (statusStr.includes('delay') || statusStr.includes('退回') || statusStr.includes('return')) {
                dot.className = 'result-status-dot problem';
            } else {
                dot.className = 'result-status-dot transit';
            }

            // Timeline
            const wrap = document.getElementById('timelineWrap');
            if (!data.history || data.history.length === 0) {
                wrap.innerHTML = '<div style="padding:20px;text-align:center;color:#94a3b8;font-size:13px;">暂无轨迹数据</div>';
                return;
            }

            const statusTextKo = (data.status_cn || data.status || '').toLowerCase();
            const isDelivered = statusTextKo.includes('deliver') || statusTextKo.includes('签收') || statusTextKo.includes('完成');
            const isProblem = statusTextKo.includes('delay') || statusTextKo.includes('退回') || statusTextKo.includes('return');
            const dotClass = isDelivered ? 'delivered' : (isProblem ? 'problem' : '');

            let html = '';
            data.history.forEach((item, idx) => {
                const isLast = idx === data.history.length - 1;

                // 分离日期和时间
                let datePart = item.datetime || '';
                let timePart = '';
                if (datePart.includes(' ')) {
                    const parts = datePart.split(' ');
                    datePart = parts[0];
                    timePart = parts.slice(1).join(' ');
                }

                // 状态标签颜色
                const statusLower = (item.status_ko + item.status_en).toLowerCase();
                let tagClass = 'timeline-tag-blue';
                if (statusLower.includes('delivered') || statusLower.includes('완료') || statusLower.includes('签收')) {
                    tagClass = 'timeline-tag-green';
                } else if (statusLower.includes('delay') || statusLower.includes('return') || statusLower.includes('반송') || statusLower.includes('退回')) {
                    tagClass = 'timeline-tag-red';
                }

                html += `
                    <div class="timeline-item">
                        <div class="timeline-line">
                            <div class="timeline-dot ${dotClass}"></div>
                            <div class="timeline-line-bar"></div>
                        </div>
                        <div class="timeline-content">
                            <div class="timeline-time">
                                <span style="font-weight:700;color:#1e293b;">${datePart}</span>
                                <span class="time-sep">|</span>
                                <span>${timePart || '--:--'}</span>
                            </div>
                            <div class="timeline-status-ko">${item.status_ko}</div>
                            <div class="timeline-status-en">${item.status_en}</div>
                            <div class="timeline-status-cn">${item.status_cn || item.status_ko}</div>
                            <div class="timeline-meta">
                                <span class="timeline-tag ${tagClass}">${item.status_ko || '배송'}</span>
                                ${item.location && item.location !== '-' ? `<span class="timeline-tag timeline-tag-gray">📍 ${item.location}</span>` : ''}
                            </div>
                            ${item.remark ? `<div style="margin-top:6px;font-size:12px;color:#94a3b8;line-height:1.4;">📌 ${item.remark}</div>` : ''}
                        </div>
                    </div>
                `;
            });
            wrap.innerHTML = html;
        }

        function showError(msg) {
            errorTitle.textContent = '查询失败';
            errorText.textContent = msg || '请确认单号是否正确后重试';
            errorBox.style.display = 'block';
        }
        function hideError() {
            errorBox.style.display = 'none';
            emptyState.style.display = 'none';
        }
    </script>
</body>
</html>

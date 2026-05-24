<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PCC 通关编码校验 · 易和国际物流</title>
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
        .page-header { margin-bottom: 28px; }
        .page-header h1 { font-size: 22px; font-weight: 700; color: #0f172a; }
        .page-header p { font-size: 14px; color: #64748b; margin-top: 4px; }
        .breadcrumb {
            display: flex; align-items: center; gap: 6px;
            font-size: 12px; color: #94a3b8; margin-bottom: 8px;
        }
        .breadcrumb a { color: #94a3b8; text-decoration: none; }
        .breadcrumb a:hover { color: #1e40af; }

        /* ===== LAYOUT ===== */
        .content-grid {
            display: grid;
            grid-template-columns: 360px 1fr;
            gap: 20px;
            align-items: start;
        }
        @media (max-width: 900px) {
            .content-grid { grid-template-columns: 1fr; }
        }

        /* ===== SIDECARD (left) ===== */
        .sidecard {
            background: #ffffff;
            border-radius: 10px;
            border: 1px solid #e6e8ec;
            padding: 24px;
        }
        .sidecard h3 {
            font-size: 13px;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 14px;
        }
        .sidecard ul {
            list-style: none;
        }
        .sidecard li {
            font-size: 13px;
            color: #64748b;
            padding: 6px 0;
            display: flex;
            align-items: flex-start;
            gap: 8px;
            line-height: 1.5;
        }
        .sidecard li .dot {
            display: inline-block;
            width: 5px; height: 5px;
            border-radius: 50%;
            background: #1e40af;
            margin-top: 7px;
            flex-shrink: 0;
            opacity: .5;
        }
        .sidecard li .dot.warning { background: #dc2626; opacity: .7; }
        .sidecard .badge {
            display: inline-block;
            padding: 0 8px;
            height: 20px;
            line-height: 20px;
            font-size: 11px;
            font-weight: 500;
            border-radius: 4px;
            background: #e8edf5;
            color: #1e40af;
        }

        /* ===== MAIN CARD ===== */
        .maincard {
            background: #ffffff;
            border-radius: 10px;
            border: 1px solid #e6e8ec;
            padding: 28px;
        }

        /* ===== UPLOAD ===== */
        .upload-zone {
            border: 2px dashed #d1d5db;
            border-radius: 10px;
            padding: 40px 20px;
            text-align: center;
            cursor: pointer;
            transition: all .2s;
        }
        .upload-zone:hover { border-color: #1e40af; background: #f8faff; }
        .upload-zone.dragover { border-color: #1e40af; background: #eef2ff; }
        .upload-zone svg {
            width: 40px; height: 40px;
            color: #d1d5db;
            margin-bottom: 12px;
        }
        .upload-zone p { font-size: 14px; color: #64748b; }
        .upload-zone .hint { font-size: 12px; color: #9ca3af; margin-top: 6px; }
        .upload-zone .filename {
            font-size: 13px; font-weight: 600; color: #1e40af;
            margin-top: 8px; display: none;
        }
        #fileInput { display: none; }

        /* ===== BUTTONS ===== */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 10px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all .15s;
            text-decoration: none;
        }
        .btn-primary {
            background: #1e40af;
            color: white;
        }
        .btn-primary:hover { background: #1e3a8a; }
        .btn-primary:disabled { background: #94a3b8; cursor: not-allowed; }
        .btn-secondary {
            background: #f1f4f9;
            color: #475569;
        }
        .btn-secondary:hover { background: #e2e8f0; }
        .btn-success {
            background: #16a34a;
            color: white;
        }
        .btn-success:hover { background: #15803d; }

        .actions { margin-top: 20px; display: flex; gap: 10px; }

        /* ===== PROGRESS ===== */
        .progress-section {
            display: none;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #e6e8ec;
        }
        .progress-info {
            font-size: 13px;
            color: #64748b;
            margin-bottom: 12px;
        }
        .progress-bar {
            width: 100%;
            height: 6px;
            background: #e6e8ec;
            border-radius: 3px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            width: 0%;
            background: #1e40af;
            border-radius: 3px;
            transition: width .3s;
        }

        /* ===== RESULTS ===== */
        .results-section {
            display: none;
            margin-top: 24px;
        }
        .results-summary {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }
        .results-stat {
            padding: 14px 18px;
            border-radius: 8px;
            background: #f8fafc;
            border: 1px solid #e6e8ec;
        }
        .results-stat-label { font-size: 11px; color: #94a3b8; margin-bottom: 2px; }
        .results-stat-value { font-size: 18px; font-weight: 700; }
        .results-stat-value.pass { color: #16a34a; }
        .results-stat-value.fail { color: #dc2626; }

        .results-download { margin-bottom: 16px; }

        /* ===== TABLE ===== */
        .table-wrap {
            overflow-x: auto;
            border: 1px solid #e6e8ec;
            border-radius: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        thead { background: #f8fafc; }
        th {
            padding: 10px 14px;
            text-align: left;
            font-weight: 600;
            color: #64748b;
            border-bottom: 1px solid #e6e8ec;
            white-space: nowrap;
        }
        td {
            padding: 10px 14px;
            border-bottom: 1px solid #f1f4f9;
            color: #475569;
        }
        tr:last-child td { border-bottom: none; }
        .tag {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 500;
        }
        .tag-pass { background: #dcfce7; color: #16a34a; }
        .tag-fail { background: #fee2e2; color: #dc2626; }

        /* ===== ERROR ===== */
        .error-message {
            margin-top: 16px;
            padding: 12px 16px;
            border-radius: 8px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            font-size: 13px;
            color: #dc2626;
            display: none;
        }
    </style>
</head>
<body>

    <!-- NAV -->
    @include('partials.nav')
    <!-- PAGE -->
    <div class="page">

        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="{{ route('dashboard') }}">工具首页</a>
            <span>/</span>
            <span>PCC 通关编码校验</span>
        </div>

        <!-- Page Header -->
        <div class="page-header">
            <h1>📋 PCC 通关编码校验</h1>
            <p>批量校验韩国海关 PCC 通关编码，上传 Excel 文件一键验证</p>
        </div>

        <!-- Content -->
        <div class="content-grid">
            <!-- Left: Instructions -->
            <div class="sidecard">
                <h3>校验规则</h3>
                <ul>
                    <li><span class="dot"></span>通关编码(PCC)、韩文名、电话、邮编 为必填项</li>
                    <li><span class="dot"></span>PCC 必须以 <strong>P</strong> 开头，共 <strong>13</strong> 位</li>
                    <li><span class="dot"></span>调用韩国海关 UNI-PASS API 逐条校验</li>
                    <li><span class="dot"></span>每条约 <strong>1~2</strong> 秒，最多 <strong>500</strong> 条</li>
                    <li><span class="dot warning"></span>后台异步处理，可安心等待</li>
                </ul>

                <h3 style="margin-top:20px;">Excel 格式要求</h3>
                <ul>
                    <li><span class="badge">A</span> 通关编码(PCC)</li>
                    <li><span class="badge">B</span> 韩文名</li>
                    <li><span class="badge">C</span> 电话</li>
                    <li><span class="badge">D</span> 邮编</li>
                    <li><span class="badge">E</span> 英文名（选填）</li>
                </ul>
            </div>

            <!-- Right: Main -->
            <div class="maincard">
                <!-- Upload -->
                <div class="upload-zone" id="dropZone">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 16v-6m0 0l-3 3m3-3l3 3"/>
                        <path d="M3 15v4a2 2 0 002 2h14a2 2 0 002-2v-4"/>
                        <path d="M7 10a5 5 0 0110 0"/>
                    </svg>
                    <p>点击选择 Excel 文件，或拖拽到此处</p>
                    <p class="hint">支持 .xlsx / .xls 格式</p>
                    <div class="filename" id="fileName"></div>
                    <input type="file" id="fileInput" accept=".xlsx,.xls">
                </div>

                <div class="actions">
                    <button class="btn btn-primary" id="submitBtn" disabled>开始校验</button>
                    <button class="btn btn-secondary" id="resetBtn" style="display:none;">重新选择</button>
                </div>

                <!-- Progress -->
                <div class="progress-section" id="progressSection">
                    <div class="progress-info" id="progressText">正在提交任务...</div>
                    <div class="progress-bar"><div class="progress-fill" id="progressFill"></div></div>
                </div>

                <!-- Error -->
                <div class="error-message" id="errorMsg"></div>

                <!-- Results -->
                <div class="results-section" id="resultsSection">
                    <div class="results-summary" id="resultsSummary"></div>
                    <div class="results-download" id="resultsDownload"></div>
                    <div class="table-wrap" id="resultsTableWrap"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
    const dz = document.getElementById('dropZone');
    const fi = document.getElementById('fileInput');
    const fn = document.getElementById('fileName');
    const sb = document.getElementById('submitBtn');
    const rs = document.getElementById('resetBtn');
    const ps = document.getElementById('progressSection');
    const pt = document.getElementById('progressText');
    const pf = document.getElementById('progressFill');
    const es = document.getElementById('errorMsg');
    const rss = document.getElementById('resultsSection');
    const rsm = document.getElementById('resultsSummary');
    const rsd = document.getElementById('resultsDownload');
    const rst = document.getElementById('resultsTableWrap');

    let selectedFile = null;
    let pollTimer = null;

    dz.onclick = () => fi.click();
    dz.ondragover = e => { e.preventDefault(); dz.classList.add('dragover'); };
    dz.ondragleave = () => dz.classList.remove('dragover');
    dz.ondrop = e => { e.preventDefault(); dz.classList.remove('dragover'); if(e.dataTransfer.files.length){fi.files=e.dataTransfer.files;onFile();} };
    fi.onchange = onFile;

    function onFile() {
        const f = fi.files[0];
        if (!f) return;
        const ext = f.name.split('.').pop().toLowerCase();
        if (!['xlsx','xls'].includes(ext)) {
            showError('仅支持 .xlsx 和 .xls 格式');
            fi.value = '';
            return;
        }
        selectedFile = f;
        fn.textContent = '✅ ' + f.name;
        fn.style.display = 'block';
        dz.querySelector('p').textContent = '已选择文件';
        sb.disabled = false;
        sb.textContent = '🚀 开始校验';
        hideError();
    }

    sb.onclick = function() {
        if (!selectedFile) return;
        sb.disabled = true;
        sb.textContent = '提交中...';
        rss.style.display = 'none';
        hideError();
        ps.style.display = 'block';
        pt.textContent = '正在上传文件...';
        pf.style.width = '0%';

        const fd = new FormData();
        fd.append('file', selectedFile);

        fetch('/tools/pcc/validate', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: fd
        })
        .then(r => r.json())
        .then(data => {
            if (data.error) {
                showError('❌ ' + data.error);
                ps.style.display = 'none';
                sb.disabled = false;
                sb.textContent = '🚀 开始校验';
                return;
            }
            pt.textContent = '正在调用韩国海关 API 逐条校验，请耐心等待...';
            pollTask(data.task_id);
        })
        .catch(err => {
            showError('❌ 请求失败：' + err.message);
            ps.style.display = 'none';
            sb.disabled = false;
            sb.textContent = '🚀 开始校验';
        });
    };

    function pollTask(taskId) {
        const poll = () => {
            fetch('/tools/pcc/status/' + taskId)
            .then(r => r.json())
            .then(st => {
                if (st.error) { showError(st.error); clearInterval(pollTimer); return; }
                const done = st.done || 0, total = st.total || 0;
                const pct = total > 0 ? Math.round(done / total * 100) : 0;
                pf.style.width = pct + '%';
                pt.textContent = '已校验 ' + done + ' / ' + total + ' 条';

                if (st.complete) {
                    clearInterval(pollTimer);
                    showResults(st);
                }
            })
            .catch(() => { pt.textContent = '连接异常，正在重试...'; });
        };
        poll();
        pollTimer = setInterval(poll, 1500);
    }

    function showResults(st) {
        ps.style.display = 'none';
        rss.style.display = 'block';
        sb.textContent = '✅ 校验完成';

        // Summary
        rsm.innerHTML = `
            <div class="results-stat"><div class="results-stat-label">总计</div><div class="results-stat-value">${st.total}</div></div>
            <div class="results-stat"><div class="results-stat-label">通过</div><div class="results-stat-value pass">${st.pass}</div></div>
            <div class="results-stat"><div class="results-stat-label">失败</div><div class="results-stat-value fail">${st.fail}</div></div>
        `;

        // Download 按钮 — 通过和失败分开下载
        let dlHtml = '';
        if (st.pass > 0 && st.download_id) {
            dlHtml += `<a href="/tools/pcc/download/${st.download_id}" class="btn btn-success">⬇ 下载通过的数据（${st.pass}条）</a> `;
        }
        if (st.fail > 0 && st.download_id_fail) {
            dlHtml += `<a href="/tools/pcc/download-fail/${st.download_id_fail}" class="btn btn-secondary" style="background:#fee2e2;color:#dc2626;">⬇ 下载失败的数据（${st.fail}条）</a>`;
        }
        rsd.innerHTML = dlHtml;

        // Table — 只渲染失败的数据
        const failDetails = st.details ? st.details.filter(r => r.status === 'fail') : [];
        if (failDetails.length > 0) {
            let rows = '<table><thead><tr><th>#</th><th>PCC</th><th>韩文名</th><th>结果</th><th>说明</th></tr></thead><tbody>';
            failDetails.forEach(r => {
                rows += `<tr><td>${r.row}</td><td>${r.pcc}</td><td>${r.hname}</td><td><span class="tag tag-fail">❌ 失败</span></td><td>${r.msg}</td></tr>`;
            });
            rows += '</tbody></table>';
            rst.innerHTML = rows;
        }

    }

    function showError(msg) {
        es.textContent = msg;
        es.style.display = 'block';
    }
    function hideError() { es.style.display = 'none'; }
    </script>
</body>
</html>

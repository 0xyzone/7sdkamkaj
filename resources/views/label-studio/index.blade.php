<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Label Studio · 7 Series Kamkaj</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;500;600;700&family=Montserrat:wght@400;600;800&family=Oswald:wght@400;600&family=Playfair+Display:wght@400;700&family=Poppins:wght@400;500;600;700;800&family=Roboto+Mono:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/label-studio.css') }}?v={{ filemtime(public_path('css/label-studio.css')) }}">
</head>
<body>
<div id="app">
    {{-- ===== Menu bar ===== --}}
    <header class="menubar">
        <a class="brand" href="{{ url('/label-templates') }}" title="Back to panel">
            <img src="{{ asset('images/symbol.png') }}" alt="7">
            <span>Label Studio</span>
        </a>
        <div class="menu-group">
            <button class="mbtn" data-action="new" title="New label (⌥N)"><i data-icon="filePlus"></i><span>New</span></button>
            <div class="mselect">
                <i data-icon="folder"></i>
                <select id="templatePicker" title="Open template"></select>
            </div>
            <button class="mbtn" data-action="save" title="Save (⌘S)"><i data-icon="save"></i><span>Save</span></button>
            <button class="mbtn" data-action="saveAs" title="Save as new template"><span>Save As…</span></button>
        </div>
        <div class="menu-sep"></div>
        <div class="menu-group">
            <button class="mbtn icon" data-action="undo" id="btnUndo" title="Undo (⌘Z)"><i data-icon="undo"></i></button>
            <button class="mbtn icon" data-action="redo" id="btnRedo" title="Redo (⇧⌘Z)"><i data-icon="redo"></i></button>
        </div>
        <div class="doc-title">
            <input id="docName" type="text" spellcheck="false" title="Template name">
            <span id="dirtyDot" class="dirty-dot" title="Unsaved changes"></span>
        </div>
        <div class="menu-right">
            <button class="mbtn" data-action="openQueue" id="btnQueue" title="View print queue & cancel jobs">
                <i data-icon="queue"></i><span>Queue</span><span id="queueBadge" class="q-badge" style="display:none">0</span>
            </button>
            <div class="printer-pill" id="printerPill" title="Printer status">
                <span class="dot"></span><span class="txt">Checking…</span>
            </div>
            <button class="mbtn primary" data-action="preview" title="Print (⌘P)"><i data-icon="print"></i><span>Print</span></button>
            <a class="mbtn" href="{{ url('/label-templates') }}" title="Back to panel"><i data-icon="back"></i><span>Panel</span></a>
        </div>
    </header>

    {{-- ===== Contextual options bar ===== --}}
    <div class="optionsbar" id="optionsBar"></div>

    <main class="main">
        {{-- ===== Left tools ===== --}}
        <aside class="toolbar" id="toolbar"></aside>

        {{-- ===== Workspace ===== --}}
        <section class="workspace" id="workspace">
            <div class="stage" id="stage">
                <div class="ruler-corner"></div>
                <canvas class="ruler ruler-top" id="rulerTop"></canvas>
                <canvas class="ruler ruler-left" id="rulerLeft"></canvas>
                <div class="paper" id="paper"><canvas id="labelCanvas"></canvas></div>
            </div>
        </section>

        {{-- ===== Right panels ===== --}}
        <aside class="panels" id="panels">
            <section class="panel" data-panel="properties">
                <header class="panel-head"><span>Properties</span></header>
                <div class="panel-body" id="propsPanel"></div>
            </section>
            <section class="panel grow" data-panel="layers">
                <header class="panel-head"><span>Layers</span><span class="count" id="layerCount">0</span></header>
                <div class="panel-body flush" id="layersPanel"></div>
            </section>
            <section class="panel" data-panel="document">
                <header class="panel-head"><span>Label &amp; Paper</span></header>
                <div class="panel-body" id="docPanel"></div>
            </section>
            <section class="panel" data-panel="assets">
                <header class="panel-head"><span>Assets &amp; Symbols</span></header>
                <div class="panel-body" id="assetsPanel"></div>
            </section>
            <section class="panel" data-panel="variables">
                <header class="panel-head"><span>Variables (sample data)</span></header>
                <div class="panel-body" id="varsPanel"></div>
            </section>
        </aside>
    </main>

    {{-- ===== Status bar ===== --}}
    <footer class="statusbar">
        <div class="zoom-ctl">
            <button data-action="zoomOut" title="Zoom out (⌘−)">−</button>
            <span id="zoomLabel">100%</span>
            <button data-action="zoomIn" title="Zoom in (⌘+)">+</button>
            <button data-action="zoomFit" title="Fit (⌘0)">Fit</button>
            <button data-action="zoomActual" title="Actual size (1 dot = 1 px)">1:1</button>
        </div>
        <span class="sb-item" id="sbSize"></span>
        <span class="sb-item" id="sbCursor">X — Y —</span>
        <span class="sb-item" id="sbSelection"></span>
        <span class="sb-spacer"></span>
        <span class="sb-item" id="sbSaved">Saved</span>
    </footer>
</div>

{{-- ===== Print queue dialog ===== --}}
<dialog id="queueDialog" class="dialog" style="width: min(640px, 94vw);">
    <div class="dialog-inner">
        <header class="dialog-head">
            <h3>Print Queue <small id="queueCountLabel">0 active jobs</small></h3>
            <button type="button" class="x" onclick="document.getElementById('queueDialog').close()" title="Close">×</button>
        </header>
        <div class="dialog-body" style="display:flex; flex-direction:column; gap:12px; max-height:420px; overflow-y:auto; padding:16px;">
            <div id="queueList"></div>
        </div>
        <footer class="dialog-foot" style="justify-content: space-between;">
            <button type="button" class="btn danger" id="btnCancelAllJobs" style="display:none;">
                <i data-icon="trash"></i> Terminate All Jobs
            </button>
            <div style="margin-left:auto; display:flex; gap:8px;">
                <button type="button" class="btn" onclick="refreshQueueModal()">Refresh</button>
                <button type="button" class="btn" onclick="document.getElementById('queueDialog').close()">Close</button>
            </div>
        </footer>
    </div>
</dialog>

{{-- ===== Print preview dialog ===== --}}
<dialog id="printDialog" class="dialog">
    <form method="dialog" class="dialog-inner" id="printForm">
        <header class="dialog-head">
            <h3>Print preview <small>exact dots sent to the printer</small></h3>
            <button value="cancel" class="x" title="Close">×</button>
        </header>
        <div class="dialog-body">
            <div class="preview-wrap"><canvas id="previewCanvas"></canvas></div>
            <div class="print-side">
                <label>Printer
                    <select id="printPrinter"></select>
                </label>
                <div class="printer-pill wide" id="dialogPrinterPill"><span class="dot"></span><span class="txt">—</span></div>
                <label>Copies
                    <input id="printCopies" type="number" min="1" max="500" value="1">
                </label>
                <label>Darkness <span class="hint" id="thresholdVal"></span>
                    <input id="printThreshold" type="range" min="60" max="230" step="5">
                </label>
                <label class="check"><input id="printDither" type="checkbox"> Dither photos / gradients</label>
                <p class="hint" id="printInfo"></p>
                <a class="hint link" id="downloadPng" download="label.png">Download bitmap (PNG)</a>
            </div>
        </div>
        <footer class="dialog-foot">
            <button value="cancel" class="btn">Cancel</button>
            <button type="button" class="btn" id="btnBluetoothPrint" style="background:#2563eb; color:#fff; border-color:#1d4ed8;">
                <i data-icon="bluetooth"></i> Bluetooth Print
            </button>
            <button type="button" class="btn primary" id="btnDoPrint"><i data-icon="print"></i> Print</button>
        </footer>
    </form>
</dialog>

<input type="file" id="imageInput" accept="image/png,image/jpeg,image/gif,image/webp,image/svg+xml" hidden>
<div id="toasts" class="toasts"></div>

<script>window.STUDIO = @json($config);</script>
<script src="{{ asset('vendor/label-studio/fabric.min.js') }}"></script>
<script src="{{ asset('vendor/label-studio/JsBarcode.all.min.js') }}"></script>
<script src="{{ asset('vendor/label-studio/qrcode.min.js') }}"></script>
<script src="{{ asset('js/label-studio.js') }}?v={{ filemtime(public_path('js/label-studio.js')) }}"></script>
</body>
</html>


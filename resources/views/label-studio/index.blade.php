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

{{-- ===== Bluetooth Help dialog for Brave ===== --}}
<dialog id="bluetoothHelpDialog" class="dialog" style="width: min(520px, 94vw);">
    <div class="dialog-inner">
        <header class="dialog-head" style="border-bottom: 1px solid var(--border);">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="display:inline-flex; align-items:center; justify-content:center; width:28px; height:28px; border-radius:6px; background:#2563eb; color:#fff;">
                    <i data-icon="bluetooth"></i>
                </span>
                <h3 style="margin: 0; font-size: 15px; font-weight: 700;">Enable Web Bluetooth in Brave</h3>
            </div>
            <button type="button" class="x" onclick="document.getElementById('bluetoothHelpDialog').close()" title="Close">×</button>
        </header>
        <div class="dialog-body" style="display:flex; flex-direction:column; gap:14px; padding:20px; font-size:12.5px; line-height:1.6;">
            <p style="margin:0; color:#e2e8f0;">
                Brave blocks website Bluetooth access by default for privacy. To print wirelessly to your <strong>Deli E886BW</strong>, enable the flag once:
            </p>

            <div style="background:var(--field); border:1px solid var(--border); border-radius:8px; padding:12px; display:flex; flex-direction:column; gap:8px;">
                <div style="display:flex; align-items:center; justify-content:space-between; gap:8px;">
                    <span style="font-size:11px; font-weight:600; text-transform:uppercase; color:var(--muted); letter-spacing:0.05em;">Step 1: Open Flag URL</span>
                    <button type="button" class="btn sm" id="btnCopyFlagUrl" style="font-size:11px;">
                        Copy URL
                    </button>
                </div>
                <input type="text" id="flagUrlInput" value="brave://flags/#enable-web-bluetooth" readonly style="font-family:'Roboto Mono',monospace; font-size:12px; font-weight:600; color:#93c5fd; background:#0b0f19; border:1px solid #1e293b; border-radius:6px; padding:8px 10px; width:100%; cursor:pointer;" onclick="this.select()">
            </div>

            <ol style="margin:0; padding-left:18px; color:#cbd5e1; display:flex; flex-direction:column; gap:6px;">
                <li>Paste into a <strong>new tab</strong> in Brave.</li>
                <li>Change <strong>Web Bluetooth</strong> from <span style="background:#334155; padding:1px 6px; border-radius:4px; font-size:11px;">Default</span> to <strong style="color:#22c55e;">Enabled</strong>.</li>
                <li>Click the blue <strong>Relaunch</strong> button at the bottom of Brave.</li>
            </ol>

            <div style="background:rgba(5, 150, 105, 0.15); border:1px solid rgba(5, 150, 105, 0.3); border-radius:8px; padding:10px 12px; color:#6ee7b7; font-size:11.5px; display:flex; align-items:center; gap:8px;">
                <i data-icon="usb" style="flex:none; width:18px; height:18px;"></i>
                <span><strong>Tip:</strong> If you have a USB cable attached right now, tap <strong>"Direct USB Print"</strong> instead—it works instantly in Brave!</span>
            </div>
        </div>
        <footer class="dialog-foot" style="justify-content: flex-end; gap: 8px;">
            <button type="button" class="btn" onclick="document.getElementById('bluetoothHelpDialog').close()">Close</button>
            <button type="button" class="btn primary" id="btnCopiedAndClose" style="background:#2563eb; border-color:#1d4ed8;">
                Copy URL &amp; Done
            </button>
        </footer>
    </div>
</dialog>

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
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                    <label>Copies
                        <input id="printCopies" type="number" min="1" max="500" value="1">
                    </label>
                    <label>Darkness <span class="hint" id="thresholdVal"></span>
                        <input id="printThreshold" type="range" min="60" max="230" step="5">
                    </label>
                </div>
                <label class="check"><input id="printDither" type="checkbox"> Dither photos / gradients</label>

                {{-- Dynamic Variables Form (showing only variables used in this design) --}}
                <div id="printVarsSection" class="print-vars-box">
                    <div class="print-vars-head">
                        <span style="display:flex; align-items:center; gap:6px;">
                            <i data-icon="variable"></i> Design Variables
                        </span>
                        <span class="badge">Live Preview</span>
                    </div>
                    <div id="printVarsFields" style="display:flex; flex-direction:column; gap:8px;"></div>
                </div>

                <p class="hint" id="printInfo"></p>
                <a class="hint link" id="downloadPng" download="label.png">Download bitmap (PNG)</a>
            </div>
        </div>
        <footer class="dialog-foot">
            <button value="cancel" class="btn">Cancel</button>
            <button type="button" class="btn" id="btnUsbPrint" style="background:#059669; color:#fff; border-color:#047857;" title="Direct client USB print via WebUSB in Brave/Chrome/Edge">
                <i data-icon="usb"></i> Direct USB Print
            </button>
            <button type="button" class="btn" id="btnBluetoothPrint" style="background:#2563eb; color:#fff; border-color:#1d4ed8;">
                <i data-icon="bluetooth"></i> Bluetooth Print
            </button>
            <button type="button" class="btn primary" id="btnDoPrint"><i data-icon="print"></i> Spool via Server</button>
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


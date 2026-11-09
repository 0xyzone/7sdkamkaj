/* ==========================================================================
   7 Series Label Studio
   Photoshop-style label designer for 203 dpi TSPL thermal printers (Deli E886BW).

   Coordinate system: 1 canvas unit = 1 printer dot (8 dots per mm @ 203 dpi).
   Printing renders the design off-screen at exactly width_mm*8 × height_mm*8
   dots, converts it to 1-bit black/white in the browser (the preview you see is
   the exact bitmap) and the server wraps it in a TSPL BITMAP job. WYSIWYG.
   ========================================================================== */
(function () {
    'use strict';

    const CFG = window.STUDIO || {};
    const DPMM = 8;                     // dots per millimetre (203 dpi)
    const PT_TO_DOTS = 203 / 72;        // typographic point → dots
    const MAX_WIDTH_MM = 108;           // Deli E886BW print head width

    // Custom properties persisted with every object
    const PROPS = [
        'elType', 'layerName', 'lockedLayer', 'selectable', 'evented',
        'barcodeValue', 'barcodeFormat', 'barcodeModule', 'barcodeHeight', 'barcodeShowText',
        'qrValue', 'qrCell', 'qrEcc',
        'origSrc', 'imgThreshold', 'imgDither', 'imgInvert', 'imgRemoveWhite',
        'imgBrightness', 'imgContrast', 'imageSmoothing', 'strokeUniform',
    ];

    const WEB_FONTS = ['Poppins', 'Inter', 'Montserrat', 'Oswald', 'Bebas Neue', 'Playfair Display', 'Roboto Mono'];
    const FONTS = [...WEB_FONTS, 'Arial', 'Helvetica', 'Arial Black', 'Courier New', 'Georgia', 'Impact', 'Times New Roman', 'Verdana'];
    const PRESETS = [[50, 30], [50, 70], [40, 30], [60, 40], [80, 50], [100, 150]];
    const BARCODE_FORMATS = [
        ['CODE128', 'Code 128 (any text)'], ['CODE39', 'Code 39'], ['EAN13', 'EAN-13 (12–13 digits)'],
        ['EAN8', 'EAN-8 (7–8 digits)'], ['UPC', 'UPC-A (11–12 digits)'], ['ITF14', 'ITF-14 (13–14 digits)'], ['codabar', 'Codabar'],
    ];
    const DEFAULT_VARS = {
        brand: 'SEVEN SERIES', item_name: 'Authentic Denim Jeans', size: '32', color: 'Indigo Blue',
        price: 'Rs. 2,999', barcode: '7S32001', sku: '7S-DNM-32', batch: 'B-2611',
    };

    /* ---------------------------------------------------------------- Icons */
    const ICONS = {
        select: '<path d="M4 3l7 17 2.5-7.5L21 10z"/><path d="M13.5 13.5l6 6"/>',
        hand: '<path d="M18 11V6a2 2 0 0 0-4 0v5M14 10V4a2 2 0 0 0-4 0v6M10 10.5V6a2 2 0 0 0-4 0v8"/><path d="M18 8a2 2 0 1 1 4 0v6a8 8 0 0 1-8 8h-2c-2.8 0-4.5-.9-6-2.3l-3.6-3.6a2 2 0 0 1 2.8-2.8L7 15"/>',
        zoom: '<circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3M8 11h6M11 8v6"/>',
        text: '<path d="M4 7V4h16v3M9 20h6M12 4v16"/>',
        pen: '<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/>',
        eraser: '<path d="m7 21-4.3-4.3a2.4 2.4 0 0 1 0-3.4l9.6-9.6a2.4 2.4 0 0 1 3.4 0l5.6 5.6a2.4 2.4 0 0 1 0 3.4L13 21"/><path d="M22 21H7M5 11l9 9"/>',
        rect: '<rect x="3" y="5" width="18" height="14" rx="1"/>',
        ellipse: '<ellipse cx="12" cy="12" rx="9" ry="7"/>',
        line: '<path d="M5 19 19 5"/>',
        barcode: '<path d="M3 5v14M6 5v14M10 5v14M13 5v14M17 5v14M21 5v14" /><path d="M7.5 5v14M18.5 5v14" stroke-width="1"/>',
        qr: '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><path d="M14 14h3v3h-3zM18 18h3v3h-3zM14 20h2M20 14h1"/>',
        image: '<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-5-5L5 21"/>',
        symbol: '<path d="M12 3 21 19H3z"/>',
        path: '<path d="M3 17c3-6 6 2 9-4s6-2 9-6"/>',
        eye: '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
        eyeOff: '<path d="M3 3l18 18"/><path d="M10.6 5.1A10 10 0 0 1 12 5c6.5 0 10 7 10 7a17 17 0 0 1-3.2 4M6.6 6.6C3.9 8.4 2 12 2 12s3.5 7 10 7a9.7 9.7 0 0 0 5.4-1.6"/>',
        lock: '<rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>',
        unlock: '<rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 7.8-1.2"/>',
        trash: '<path d="M3 6h18M8 6V4h8v2M6 6l1 15h10l1-15"/>',
        copy: '<rect x="8" y="8" width="13" height="13" rx="2"/><path d="M16 8V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h3"/>',
        up: '<path d="m18 15-6-6-6 6"/>',
        down: '<path d="m6 9 6 6 6-6"/>',
        undo: '<path d="M9 14 4 9l5-5"/><path d="M4 9h11a5 5 0 0 1 0 10h-3"/>',
        redo: '<path d="m15 14 5-5-5-5"/><path d="M20 9H9a5 5 0 0 0 0 10h3"/>',
        save: '<path d="M5 3h11l5 5v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z"/><path d="M7 3v5h8M7 21v-7h10v7"/>',
        print: '<path d="M6 9V3h12v6"/><rect x="3" y="9" width="18" height="8" rx="2"/><path d="M6 14h12v7H6z"/>',
        filePlus: '<path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><path d="M14 3v6h6M12 12v6M9 15h6"/>',
        folder: '<path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>',
        back: '<path d="M19 12H5M12 19l-7-7 7-7"/>',
        plus: '<path d="M12 5v14M5 12h14"/>',
        alignL: '<path d="M4 3v18"/><rect x="8" y="6" width="12" height="4" rx="1"/><rect x="8" y="14" width="7" height="4" rx="1"/>',
        alignC: '<path d="M12 3v18"/><rect x="5" y="6" width="14" height="4" rx="1"/><rect x="8" y="14" width="8" height="4" rx="1"/>',
        alignR: '<path d="M20 3v18"/><rect x="4" y="6" width="12" height="4" rx="1"/><rect x="9" y="14" width="7" height="4" rx="1"/>',
        alignT: '<path d="M3 4h18"/><rect x="6" y="8" width="4" height="12" rx="1"/><rect x="14" y="8" width="4" height="7" rx="1"/>',
        alignM: '<path d="M3 12h18"/><rect x="6" y="5" width="4" height="14" rx="1"/><rect x="14" y="8" width="4" height="8" rx="1"/>',
        alignB: '<path d="M3 20h18"/><rect x="6" y="4" width="4" height="12" rx="1"/><rect x="14" y="9" width="4" height="7" rx="1"/>',
        front: '<rect x="4" y="4" width="12" height="12" rx="1"/><rect x="8" y="8" width="12" height="12" rx="1" fill="currentColor"/>',
        toBack: '<rect x="8" y="8" width="12" height="12" rx="1"/><rect x="4" y="4" width="12" height="12" rx="1" fill="currentColor"/>',
        grid: '<path d="M3 9h18M3 15h18M9 3v18M15 3v18"/><rect x="3" y="3" width="18" height="18" rx="1"/>',
        magnet: '<path d="M6 3v8a6 6 0 0 0 12 0V3"/><path d="M6 7h4M14 7h4"/>',
        safe: '<rect x="3" y="3" width="18" height="18" rx="1"/><rect x="7" y="7" width="10" height="10" stroke-dasharray="2 2"/>',
        panels: '<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M15 4v16"/>',
        queue: '<path d="M4 6h16M4 10h16M4 14h16M4 18h16"/><circle cx="8" cy="6" r="1" fill="currentColor"/><circle cx="8" cy="10" r="1" fill="currentColor"/><circle cx="8" cy="14" r="1" fill="currentColor"/><circle cx="8" cy="18" r="1" fill="currentColor"/>',
        bluetooth: '<path d="m7 7 10 10-5 5V2l5 5L7 17"/>',
        usb: '<circle cx="12" cy="18" r="2"/><circle cx="6" cy="10" r="1.5"/><rect x="16.5" y="8" width="3" height="3"/><path d="M12 16V6M12 11l-6-2M12 9l6-2M9 5h6v3H9z"/>',
    };
    const svg = (n) => `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">${ICONS[n] || ''}</svg>`;
    const icon = (n) => `<i data-icon="${n}">${svg(n)}</i>`;

    /* --------------------------------------------------------- Care symbols */
    const SYM = (inner) => `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="100" height="100"><g fill="none" stroke="#000" stroke-width="6" stroke-linejoin="round" stroke-linecap="round">${inner}</g></svg>`;
    const TUB = '<path d="M8 32 L18 86 H82 L92 32"/><path d="M8 32 Q20 22 32 32 T56 32 T80 32 T92 30"/>';
    const IRON = '<path d="M10 76 H90 V60 Q90 32 60 32 H32 Q18 50 10 76 Z"/>';
    const NUM = (t, y = 72, s = 28) => `<text x="50" y="${y}" font-family="Arial" font-size="${s}" font-weight="bold" text-anchor="middle" fill="#000" stroke="none">${t}</text>`;
    const SYMBOLS = [
        { name: 'Wash 30°', svg: SYM(TUB + NUM('30')) },
        { name: 'Wash 40°', svg: SYM(TUB + NUM('40')) },
        { name: 'Do not wash', svg: SYM(TUB + '<path d="M14 20 L86 94 M86 20 L14 94"/>') },
        { name: 'Bleach allowed', svg: SYM('<path d="M50 10 L94 88 H6 Z"/>') },
        { name: 'Do not bleach', svg: SYM('<path d="M50 10 L94 88 H6 Z"/><path d="M26 42 L74 94 M74 42 L26 94"/>') },
        { name: 'Iron low', svg: SYM(IRON + '<circle cx="52" cy="58" r="5" fill="#000" stroke="none"/>') },
        { name: 'Do not iron', svg: SYM(IRON + '<path d="M14 20 L86 92 M86 20 L14 92"/>') },
        { name: 'Tumble dry low', svg: SYM('<rect x="8" y="8" width="84" height="84"/><circle cx="50" cy="50" r="30"/><circle cx="50" cy="50" r="5" fill="#000" stroke="none"/>') },
        { name: 'Do not tumble dry', svg: SYM('<rect x="8" y="8" width="84" height="84"/><circle cx="50" cy="50" r="30"/><path d="M8 8 L92 92 M92 8 L8 92"/>') },
        { name: 'Line dry', svg: SYM('<rect x="8" y="8" width="84" height="84"/><path d="M50 24 V76"/>') },
        { name: 'Dry clean', svg: SYM('<circle cx="50" cy="50" r="40"/>' + NUM('P', 64, 40)) },
        { name: 'Do not dry clean', svg: SYM('<circle cx="50" cy="50" r="40"/><path d="M20 20 L80 80 M80 20 L20 80"/>') },
    ];

    /* -------------------------------------------------------------- Helpers */
    const $ = (s, r = document) => r.querySelector(s);
    const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));
    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const clamp = (v, a, b) => Math.min(b, Math.max(a, v));
    const r1 = (v) => Math.round(v * 10) / 10;
    const W = () => Math.round(state.widthMm * DPMM);
    const H = () => Math.round(state.heightMm * DPMM);
    const isText = (o) => o && (o.type === 'i-text' || o.type === 'text' || o.type === 'textbox');
    const isDynamic = (o) => o && (o.elType === 'barcode' || o.elType === 'qr');
    const debounce = (fn, ms) => { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); }; };
    const prefs = JSON.parse(localStorage.getItem('labelStudio.prefs') || '{}');
    const savePrefs = () => localStorage.setItem('labelStudio.prefs', JSON.stringify({
        grid: state.grid, snap: state.snap, safe: state.safe, threshold: state.threshold, dither: state.dither,
        collapsed: $$('.panel.collapsed').map((p) => p.dataset.panel),
    }));

    function toast(title, msg = '', type = 'info') {
        const el = document.createElement('div');
        el.className = `toast ${type}`;
        el.innerHTML = `<b>${esc(title)}</b>${esc(msg)}`;
        $('#toasts').appendChild(el);
        setTimeout(() => { el.style.opacity = '0'; el.style.transition = 'opacity .3s'; }, type === 'error' ? 6500 : 3200);
        setTimeout(() => el.remove(), type === 'error' ? 7000 : 3600);
    }

    function interpolate(str) {
        return String(str ?? '').replace(/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/g, (m, k) => (k in state.vars ? state.vars[k] : m));
    }

    async function api(url, body) {
        try {
            const r = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': CFG.csrf },
                body: JSON.stringify(body),
            });
            if (r.status === 419) return { success: false, message: 'Your session expired. Reload the page and try again.' };
            if (r.status === 403) return { success: false, message: 'You do not have permission for this action.' };
            const j = await r.json().catch(() => ({}));
            if (!r.ok && j.errors) return { success: false, message: Object.values(j.errors).flat()[0] || j.message };
            if (!r.ok) return { success: false, message: j.message || `Server error (${r.status})` };
            return j;
        } catch (e) {
            return { success: false, message: 'Network error: ' + e.message };
        }
    }

    /* ---------------------------------------------------------------- State */
    const T = CFG.template;
    const state = {
        id: T?.id ?? null,
        name: T?.name ?? 'Untitled label',
        category: T?.category ?? 'garment',
        widthMm: T?.width_mm ?? 50,
        heightMm: T?.height_mm ?? 30,
        gapMm: T?.gap_mm ?? 2,
        direction: T?.direction ?? 1,
        zoom: 1,
        tool: 'select',
        brush: { pen: 3, eraser: 16 },
        shapeStroke: 3,
        ink: '#000000',
        text: { fontFamily: 'Poppins', fontSizePt: 10 },
        keepRatio: true,
        grid: prefs.grid ?? false,
        snap: prefs.snap ?? true,
        safe: prefs.safe ?? true,
        vars: { ...DEFAULT_VARS },
        history: [], future: [], muted: 0, dirty: false,
        printerId: CFG.defaultPrinterId ?? null,
        copies: 1,
        threshold: prefs.threshold ?? 150,
        dither: prefs.dither ?? false,
        guides: { v: false, h: false },
    };

    /* --------------------------------------------------------------- Canvas */
    fabric.Object.prototype.set({
        transparentCorners: false, cornerColor: '#ffffff', cornerStrokeColor: '#dc2626',
        borderColor: '#dc2626', cornerSize: 9, cornerStyle: 'rect', borderScaleFactor: 1.4, padding: 0,
    });
    fabric.IText.prototype.set({ editingBorderColor: '#dc2626', cursorColor: '#dc2626', selectionColor: 'rgba(220,38,38,.25)' });
    try { fabric.filterBackend = new fabric.Canvas2dFilterBackend(); } catch (e) { /* default backend */ }
    if (window.qrcode && qrcode.stringToBytesFuncs) qrcode.stringToBytes = qrcode.stringToBytesFuncs['UTF-8'];

    const canvas = new fabric.Canvas('labelCanvas', {
        backgroundColor: '#ffffff',
        preserveObjectStacking: true,
        stopContextMenu: true,
        selectionColor: 'rgba(220,38,38,.08)',
        selectionBorderColor: '#dc2626',
        selectionLineWidth: 1,
    });
    const ws = $('#workspace');

    /* ------------------------------------------------- Dynamic (barcode/QR) */
    function barcodeDataURL(o) {
        const c = document.createElement('canvas');
        const value = interpolate(o.barcodeValue) || ' ';
        let valid = true;
        try {
            JsBarcode(c, value, {
                format: o.barcodeFormat || 'CODE128',
                width: o.barcodeModule || 2,
                height: o.barcodeHeight || 80,
                displayValue: !!o.barcodeShowText,
                font: 'Arial', fontOptions: 'bold',
                fontSize: Math.max(16, Math.round((o.barcodeModule || 2) * 11)),
                textMargin: 2, margin: 0,
                background: '#ffffff', lineColor: '#000000',
                valid: (v) => { valid = v; },
            });
        } catch (e) { valid = false; }
        if (!valid) {
            c.width = 280; c.height = 72;
            const x = c.getContext('2d');
            x.fillStyle = '#fff'; x.fillRect(0, 0, 280, 72);
            x.strokeStyle = '#000'; x.lineWidth = 2; x.setLineDash([6, 4]); x.strokeRect(1, 1, 278, 70);
            x.fillStyle = '#000'; x.textAlign = 'center';
            x.font = 'bold 16px Arial'; x.fillText(`Invalid ${o.barcodeFormat} data`, 140, 32);
            x.font = '13px Arial'; x.fillText(value.slice(0, 30), 140, 54);
        }
        return c.toDataURL('image/png');
    }

    function qrDataURL(o) {
        const cell = o.qrCell || 4;
        const c = document.createElement('canvas');
        try {
            const q = qrcode(0, o.qrEcc || 'M');
            q.addData(interpolate(o.qrValue) || ' ');
            q.make();
            const n = q.getModuleCount();
            c.width = c.height = n * cell;
            const x = c.getContext('2d');
            x.fillStyle = '#fff'; x.fillRect(0, 0, c.width, c.height);
            x.fillStyle = '#000';
            for (let r = 0; r < n; r++) for (let k = 0; k < n; k++) if (q.isDark(r, k)) x.fillRect(k * cell, r * cell, cell, cell);
        } catch (e) {
            c.width = c.height = 120;
            const x = c.getContext('2d');
            x.fillStyle = '#fff'; x.fillRect(0, 0, 120, 120);
            x.strokeStyle = '#000'; x.setLineDash([5, 4]); x.strokeRect(1, 1, 118, 118);
            x.fillStyle = '#000'; x.font = 'bold 13px Arial'; x.textAlign = 'center'; x.fillText('Data too long', 60, 64);
        }
        return c.toDataURL('image/png');
    }

    const dynamicURL = (o) => (o.elType === 'barcode' ? barcodeDataURL(o) : qrDataURL(o));

    function createDynamic(kind, opts) {
        return new Promise((res) => {
            const o = { ...opts, elType: kind };
            fabric.Image.fromURL(dynamicURL(o), (img) => {
                img.set({ ...o, imageSmoothing: false });
                res(img);
            });
        });
    }

    function refreshDynamic(o) {
        return new Promise((res) => {
            o.setSrc(dynamicURL(o), () => { o.set({ imageSmoothing: false }); o.setCoords(); canvas.requestRenderAll(); res(); });
        });
    }
    const refreshAllDynamic = () => Promise.all(canvas.getObjects().filter(isDynamic).map(refreshDynamic));

    /* ---------------------------------------------------------------- Fonts */
    async function ensureFont(family) {
        if (!WEB_FONTS.includes(family) || !document.fonts) return;
        try {
            await Promise.all([document.fonts.load(`400 24px "${family}"`), document.fonts.load(`700 24px "${family}"`)]);
            fabric.util.clearFabricFontCache(family);
        } catch (e) { /* offline: browser falls back */ }
    }
    async function loadFonts() {
        await Promise.race([Promise.all(WEB_FONTS.map(ensureFont)), new Promise((r) => setTimeout(r, 4000))]);
        fabric.util.clearFabricFontCache();
    }

    /* -------------------------------------------------------------- History */
    const snapshot = () => JSON.stringify(canvas.toJSON(PROPS));
    function pushHistory() {
        if (state.muted) return;
        const s = snapshot();
        if (state.history[state.history.length - 1] === s) return;
        state.history.push(s);
        if (state.history.length > 80) state.history.shift();
        state.future = [];
        setDirty(true);
        updateUndoButtons();
    }
    let historyTimer;
    function scheduleHistory(ms = 60) {
        clearTimeout(historyTimer);
        historyTimer = setTimeout(pushHistory, ms);
    }
    function restore(json) {
        state.muted++;
        canvas.loadFromJSON(json, () => {
            canvas.forEachObject(applyLock);
            canvas.renderAll();
            state.muted--;
            setDirty(true);
            renderLayers(); renderProps(); renderOptions(); updateUndoButtons();
        });
    }
    function undo() {
        if (state.history.length < 2) return;
        state.future.push(state.history.pop());
        restore(state.history[state.history.length - 1]);
    }
    function redo() {
        if (!state.future.length) return;
        const s = state.future.pop();
        state.history.push(s);
        restore(s);
    }
    function updateUndoButtons() {
        $('#btnUndo').disabled = state.history.length < 2;
        $('#btnRedo').disabled = !state.future.length;
    }
    function setDirty(d) {
        state.dirty = d;
        $('#dirtyDot').classList.toggle('on', d);
        const sb = $('#sbSaved');
        sb.textContent = d ? 'Unsaved changes' : (state.id ? 'Saved' : 'Not saved yet');
        sb.classList.toggle('unsaved', d);
    }

    /* ----------------------------------------------------------------- Zoom */
    function setZoom(z, anchorEvent) {
        const old = state.zoom;
        z = clamp(z, 0.1, 12);
        let rel = null;
        if (anchorEvent) {
            const r = $('#paper').getBoundingClientRect();
            rel = { x: (anchorEvent.clientX - r.left) / old, y: (anchorEvent.clientY - r.top) / old, cx: anchorEvent.clientX, cy: anchorEvent.clientY };
        }
        state.zoom = z;
        canvas.setZoom(z);
        canvas.setDimensions({ width: Math.round(W() * z), height: Math.round(H() * z) });
        drawRulers();
        if (rel) {
            const r2 = $('#paper').getBoundingClientRect();
            ws.scrollLeft += (r2.left + rel.x * z) - rel.cx;
            ws.scrollTop += (r2.top + rel.y * z) - rel.cy;
        }
        $('#zoomLabel').textContent = Math.round(z * 100) + '%';
        updateStatus();
    }
    function zoomFit() {
        const aw = ws.clientWidth - 150;
        const ah = ws.clientHeight - 150;
        setZoom(Math.max(0.1, Math.min(aw / W(), ah / H())));
    }

    /* --------------------------------------------------------------- Rulers */
    function drawRuler(c, cw, ch, horizontal) {
        const dpr = window.devicePixelRatio || 1;
        c.style.width = cw + 'px'; c.style.height = ch + 'px';
        c.width = Math.round(cw * dpr); c.height = Math.round(ch * dpr);
        const x = c.getContext('2d');
        x.scale(dpr, dpr);
        x.fillStyle = '#2b2b2d'; x.fillRect(0, 0, cw, ch);
        x.strokeStyle = '#6f6f77'; x.fillStyle = '#a3a3ab'; x.lineWidth = 1;
        x.font = '9px Inter, sans-serif';
        const ppm = DPMM * state.zoom;
        const total = horizontal ? state.widthMm : state.heightMm;
        const every = ppm >= 14 ? 5 : ppm >= 5 ? 10 : 20;
        x.beginPath();
        for (let m = 0; m <= Math.floor(total); m++) {
            const p = Math.round(m * ppm) + 0.5;
            const len = m % 10 === 0 ? 12 : m % 5 === 0 ? 8 : 4;
            if (len === 4 && ppm < 3) continue;
            if (horizontal) { x.moveTo(p, ch); x.lineTo(p, ch - len); } else { x.moveTo(cw, p); x.lineTo(cw - len, p); }
            if (m % every === 0) {
                if (horizontal) x.fillText(String(m), p + 2, 9);
                else x.fillText(String(m), 1, p + 10);
            }
        }
        x.stroke();
    }
    function drawRulers() {
        const w = Math.round(W() * state.zoom), h = Math.round(H() * state.zoom);
        drawRuler($('#rulerTop'), w, 20, true);
        drawRuler($('#rulerLeft'), 20, h, false);
    }

    /* ---------------------------------------------- Overlays: grid / guides */
    canvas.on('after:render', () => {
        const ctx = canvas.getContext();
        const vt = canvas.viewportTransform;
        const z = state.zoom;
        ctx.save();
        ctx.transform(vt[0], vt[1], vt[2], vt[3], vt[4], vt[5]);
        if (state.grid && z * DPMM >= 3) {
            ctx.lineWidth = 1 / z;
            for (let m = 1; m < state.widthMm; m++) {
                ctx.strokeStyle = m % 5 === 0 ? 'rgba(59,130,246,.28)' : 'rgba(59,130,246,.10)';
                ctx.beginPath(); ctx.moveTo(m * DPMM, 0); ctx.lineTo(m * DPMM, H()); ctx.stroke();
            }
            for (let m = 1; m < state.heightMm; m++) {
                ctx.strokeStyle = m % 5 === 0 ? 'rgba(59,130,246,.28)' : 'rgba(59,130,246,.10)';
                ctx.beginPath(); ctx.moveTo(0, m * DPMM); ctx.lineTo(W(), m * DPMM); ctx.stroke();
            }
        }
        if (state.safe) {
            const inset = 1.5 * DPMM;
            ctx.strokeStyle = 'rgba(220,38,38,.45)';
            ctx.lineWidth = 1 / z;
            ctx.setLineDash([4 / z, 3 / z]);
            ctx.strokeRect(inset, inset, W() - inset * 2, H() - inset * 2);
            ctx.setLineDash([]);
        }
        ctx.strokeStyle = '#ec4899';
        ctx.lineWidth = 1 / z;
        if (state.guides.v) { ctx.beginPath(); ctx.moveTo(W() / 2, 0); ctx.lineTo(W() / 2, H()); ctx.stroke(); }
        if (state.guides.h) { ctx.beginPath(); ctx.moveTo(0, H() / 2); ctx.lineTo(W(), H() / 2); ctx.stroke(); }
        ctx.restore();
    });

    /* ---------------------------------------------------------------- Tools */
    const TOOLS = [
        { id: 'select', icon: 'select', key: 'V', tip: 'Move / Select' },
        { id: 'hand', icon: 'hand', key: 'H', tip: 'Hand — pan (hold Space)' },
        { id: 'zoom', icon: 'zoom', key: 'Z', tip: 'Zoom — click in, ⌥-click out' },
        'sep',
        { id: 'text', icon: 'text', key: 'T', tip: 'Text' },
        { id: 'pen', icon: 'pen', key: 'P', tip: 'Pen — freehand' },
        { id: 'eraser', icon: 'eraser', key: 'E', tip: 'Eraser — paints paper white' },
        'sep',
        { id: 'rect', icon: 'rect', key: 'R', tip: 'Rectangle — drag' },
        { id: 'ellipse', icon: 'ellipse', key: 'O', tip: 'Ellipse — drag' },
        { id: 'line', icon: 'line', key: 'L', tip: 'Line — drag, ⇧ to straighten' },
        'sep',
        { id: 'barcode', icon: 'barcode', key: 'B', tip: 'Barcode' },
        { id: 'qr', icon: 'qr', key: 'Q', tip: 'QR code' },
        { id: 'image', icon: 'image', key: 'I', tip: 'Image / logo' },
    ];
    const toolDef = (id) => TOOLS.find((t) => t !== 'sep' && t.id === id);
    let spaceDown = false;

    function buildToolbar() {
        const tb = $('#toolbar');
        tb.innerHTML = TOOLS.map((t) => (t === 'sep'
            ? '<div class="tool-sep"></div>'
            : `<button class="tool" data-tool="${t.id}" data-tip="${esc(t.tip)} (${t.key})">${icon(t.icon)}<span class="key">${t.key}</span></button>`)).join('')
            + '<div class="ink-swatch" data-action="toggleInk" title="Ink colour for new objects (X to swap)"><span class="fg"></span><span class="bg"></span></div>';
        tb.addEventListener('click', (e) => {
            const b = e.target.closest('[data-tool]');
            if (b) setTool(b.dataset.tool);
        });
        renderInk();
    }
    function renderInk() {
        $('.ink-swatch .fg').style.background = state.ink;
        $('.ink-swatch .bg').style.background = state.ink === '#000000' ? '#ffffff' : '#000000';
    }

    function applyToolMode() {
        const t = state.tool;
        const sel = t === 'select' && !spaceDown;
        canvas.isDrawingMode = (t === 'pen' || t === 'eraser') && !spaceDown;
        if (canvas.isDrawingMode) {
            const b = new fabric.PencilBrush(canvas);
            b.color = t === 'pen' ? state.ink : '#ffffff';
            b.width = state.brush[t];
            b.strokeLineCap = 'round';
            b.strokeLineJoin = 'round';
            b.decimate = 0.4;
            canvas.freeDrawingBrush = b;
        }
        canvas.selection = sel;
        canvas.skipTargetFind = !sel;
        const cur = { text: 'text', zoom: 'zoom-in', rect: 'crosshair', ellipse: 'crosshair', line: 'crosshair', barcode: 'copy', qr: 'copy', image: 'copy', hand: 'grab' }[t] || 'default';
        canvas.defaultCursor = spaceDown ? 'grab' : cur;
        canvas.hoverCursor = sel ? 'move' : canvas.defaultCursor;
        ws.classList.toggle('hand', t === 'hand' || spaceDown);
    }

    function setTool(id) {
        if (!toolDef(id)) return;
        state.tool = id;
        if (id !== 'select') {
            const a = canvas.getActiveObject();
            if (a && a.isEditing) a.exitEditing();
            canvas.discardActiveObject();
        }
        applyToolMode();
        $$('.tool').forEach((b) => b.classList.toggle('active', b.dataset.tool === id));
        renderOptions();
        canvas.requestRenderAll();
    }

    /* ------------------------------------------------- Object construction */
    function placeAndSelect(o) {
        canvas.add(o);
        setTool('select');
        canvas.setActiveObject(o);
        canvas.requestRenderAll();
    }

    function addText(p, text = 'Text') {
        const t = new fabric.IText(text, {
            left: p.x, top: p.y,
            fontFamily: state.text.fontFamily,
            fontSize: Math.round(state.text.fontSizePt * PT_TO_DOTS),
            fill: state.ink, backgroundColor: state.ink === '#ffffff' ? '#000000' : '',
            elType: 'text',
        });
        placeAndSelect(t);
        t.enterEditing();
        t.selectAll();
    }

    async function addBarcode(p) {
        const img = await createDynamic('barcode', {
            barcodeValue: '{{barcode}}', barcodeFormat: 'CODE128', barcodeModule: 2, barcodeHeight: 10 * DPMM, barcodeShowText: true,
        });
        img.set({ left: p.x, top: p.y });
        placeAndSelect(img);
    }

    async function addQr(p) {
        const img = await createDynamic('qr', { qrValue: '{{sku}}', qrCell: 4, qrEcc: 'M' });
        img.set({ left: p.x, top: p.y });
        placeAndSelect(img);
    }

    function downscaleDataURL(url, maxPx) {
        return new Promise((res) => {
            const im = new Image();
            im.onload = () => {
                const s = Math.min(1, maxPx / Math.max(im.width, im.height));
                if (s >= 1) return res(url);
                const c = document.createElement('canvas');
                c.width = Math.round(im.width * s); c.height = Math.round(im.height * s);
                c.getContext('2d').drawImage(im, 0, 0, c.width, c.height);
                res(c.toDataURL('image/png'));
            };
            im.onerror = () => res(url);
            im.src = url;
        });
    }

    function convertImageToMonochrome(url, options = {}) {
        return new Promise((resolve) => {
            const threshold = options.threshold ?? 140;
            const dither = !!options.dither;
            const invert = !!options.invert;
            const removeWhite = !!options.removeWhite;

            const img = new Image();
            img.crossOrigin = 'anonymous';
            img.onload = () => {
                const w = img.naturalWidth || img.width;
                const h = img.naturalHeight || img.height;
                if (!w || !h) return resolve(url);

                const c = document.createElement('canvas');
                c.width = w;
                c.height = h;
                const ctx = c.getContext('2d');
                ctx.drawImage(img, 0, 0);

                let imgData;
                try {
                    imgData = ctx.getImageData(0, 0, w, h);
                } catch (e) {
                    return resolve(url);
                }

                const d = imgData.data;
                const g = new Float32Array(w * h);
                const alpha = new Uint8Array(w * h);

                for (let i = 0, p = 0; i < d.length; i += 4, p++) {
                    const a = d[i + 3];
                    alpha[p] = a;
                    // If pixel is transparent, treat as white paper for dithering calculations
                    if (a < 32) {
                        g[p] = 255;
                    } else {
                        g[p] = d[i] * 0.299 + d[i + 1] * 0.587 + d[i + 2] * 0.114;
                    }
                }

                for (let y = 0; y < h; y++) {
                    for (let x = 0; x < w; x++) {
                        const p = y * w + x;
                        const a = alpha[p];

                        // Keep original transparent pixels transparent
                        if (a < 32) {
                            d[p * 4] = 255;
                            d[p * 4 + 1] = 255;
                            d[p * 4 + 2] = 255;
                            d[p * 4 + 3] = 0;
                            continue;
                        }

                        const oldVal = g[p];
                        let v = oldVal < threshold ? 0 : 255;

                        if (dither) {
                            const err = oldVal - v;
                            if (x + 1 < w) g[p + 1] += err * (7 / 16);
                            if (y + 1 < h) {
                                if (x > 0) g[p + w - 1] += err * (3 / 16);
                                g[p + w] += err * (5 / 16);
                                if (x + 1 < w) g[p + w + 1] += err * (1 / 16);
                            }
                        }

                        if (invert) {
                            v = 255 - v;
                        }

                        if (removeWhite && v === 255) {
                            d[p * 4] = 255;
                            d[p * 4 + 1] = 255;
                            d[p * 4 + 2] = 255;
                            d[p * 4 + 3] = 0;
                        } else {
                            d[p * 4] = v;
                            d[p * 4 + 1] = v;
                            d[p * 4 + 2] = v;
                            d[p * 4 + 3] = 255;
                        }
                    }
                }

                ctx.putImageData(imgData, 0, 0);
                resolve(c.toDataURL('image/png'));
            };
            img.onerror = () => resolve(url);
            img.src = url;
        });
    }

    async function updateImageMonochrome(o) {
        if (!o || o.elType !== 'image') return;
        const src = o.origSrc || o.getSrc();
        if (!src) return;
        if (!o.origSrc) o.origSrc = src;

        const bwUrl = await convertImageToMonochrome(o.origSrc, {
            threshold: o.imgThreshold ?? 140,
            dither: !!o.imgDither,
            invert: !!o.imgInvert,
            removeWhite: !!o.imgRemoveWhite,
        });

        const sx = o.scaleX, sy = o.scaleY, left = o.left, top = o.top, angle = o.angle;
        o.setSrc(bwUrl, () => {
            o.set({ scaleX: sx, scaleY: sy, left, top, angle });
            o.setCoords();
            canvas.requestRenderAll();
            scheduleHistory(250);
        });
    }

    const updateImageMonochromeDebounced = debounce(updateImageMonochrome, 120);

    async function addImageFromURL(url, name, p) {
        let srcUrl = url;
        if (!url.startsWith('data:image/svg')) {
            srcUrl = await downscaleDataURL(url, Math.max(W(), H()) * 2);
        }

        // Automatically convert image to 1-bit thermal black & white
        const bwUrl = await convertImageToMonochrome(srcUrl, {
            threshold: state.threshold || 140,
            dither: false,
            invert: false,
            removeWhite: false,
        });

        fabric.Image.fromURL(bwUrl, (img) => {
            if (!img || !img.width) { toast('Image failed to load', name || '', 'error'); return; }
            const s = Math.min(1, (W() * 0.6) / img.width, (H() * 0.6) / img.height);
            img.scale(s);
            const at = p || { x: (W() - img.getScaledWidth()) / 2, y: (H() - img.getScaledHeight()) / 2 };
            img.set({
                left: at.x, top: at.y,
                elType: 'image',
                layerName: name || 'Image',
                origSrc: srcUrl,
                imgThreshold: state.threshold || 140,
                imgDither: false,
                imgInvert: false,
                imgRemoveWhite: false,
            });
            placeAndSelect(img);
            toast('Black & White Converted', 'Image automatically converted for thermal printing.', 'success');
        }, { crossOrigin: 'anonymous' });
    }

    function addSymbol(i, p) {
        const s = SYMBOLS[i];
        if (!s) return;
        fabric.loadSVGFromString(s.svg, (objects, options) => {
            const g = fabric.util.groupSVGElements(objects, options);
            g.scaleToWidth(10 * DPMM);
            const at = p || { x: (W() - g.getScaledWidth()) / 2, y: (H() - g.getScaledHeight()) / 2 };
            g.set({ left: at.x, top: at.y, elType: 'symbol', layerName: s.name });
            placeAndSelect(g);
        });
    }

    let pendingImagePoint = null;
    $('#imageInput').addEventListener('change', async (e) => {
        const file = e.target.files[0];
        e.target.value = '';
        if (!file) return;
        if (file.size > 8 * 1024 * 1024) { toast('Image too large', 'Please use an image under 8 MB.', 'error'); return; }
        const reader = new FileReader();
        reader.onload = async () => {
            let url = reader.result;
            if (!file.type.includes('svg')) url = await downscaleDataURL(url, Math.max(W(), H()) * 2);
            addImageFromURL(url, file.name.replace(/\.[^.]+$/, ''), pendingImagePoint);
            pendingImagePoint = null;
        };
        reader.readAsDataURL(file);
    });

    /* ----------------------------------------- Drag-to-draw (rect/ellipse/line) */
    let draft = null;
    function startDraft(p) {
        state.muted++;
        const common = { stroke: state.ink, strokeWidth: state.shapeStroke, strokeUniform: true, selectable: false, evented: false };
        let o;
        if (state.tool === 'rect') o = new fabric.Rect({ left: p.x, top: p.y, width: 1, height: 1, fill: '', rx: 0, ry: 0, elType: 'rect', ...common });
        else if (state.tool === 'ellipse') o = new fabric.Ellipse({ left: p.x, top: p.y, rx: 0.5, ry: 0.5, fill: '', elType: 'ellipse', ...common });
        else o = new fabric.Line([p.x, p.y, p.x, p.y], { strokeLineCap: 'butt', elType: 'line', ...common });
        canvas.add(o);
        draft = { o, start: p };
    }
    function updateDraft(p, shift) {
        const { o, start } = draft;
        if (o.type === 'line') {
            let x2 = p.x, y2 = p.y;
            if (shift) { if (Math.abs(x2 - start.x) > Math.abs(y2 - start.y)) y2 = start.y; else x2 = start.x; }
            o.set({ x2, y2 });
        } else {
            let w = p.x - start.x, h = p.y - start.y;
            if (shift) { const s = Math.max(Math.abs(w), Math.abs(h)); w = Math.sign(w || 1) * s; h = Math.sign(h || 1) * s; }
            const left = Math.min(start.x, start.x + w), top = Math.min(start.y, start.y + h);
            if (o.type === 'rect') o.set({ left, top, width: Math.abs(w), height: Math.abs(h) });
            else o.set({ left, top, rx: Math.abs(w) / 2, ry: Math.abs(h) / 2 });
        }
        o.setCoords();
        canvas.requestRenderAll();
    }
    function finishDraft() {
        const { o, start } = draft;
        draft = null;
        const b = o.getBoundingRect(true, true);
        if (b.width < 4 && b.height < 4) {
            if (o.type === 'rect') o.set({ width: 20 * DPMM, height: 10 * DPMM });
            else if (o.type === 'ellipse') o.set({ rx: 6 * DPMM, ry: 6 * DPMM });
            else o.set({ x2: start.x + 20 * DPMM, y2: start.y });
        }
        o.set({ selectable: true, evented: true });
        o.setCoords();
        state.muted--;
        setTool('select');
        canvas.setActiveObject(o);
        pushHistory();
    }

    /* --------------------------------------------------------- Mouse input */
    canvas.on('mouse:down', (opt) => {
        const e = opt.e;
        if (spaceDown || state.tool === 'hand' || state.tool === 'select') return;
        const p = canvas.getPointer(e);
        switch (state.tool) {
            case 'zoom': setZoom(state.zoom * (e.altKey ? 1 / 1.5 : 1.5), e); break;
            case 'text': addText(p); break;
            case 'barcode': addBarcode(p); break;
            case 'qr': addQr(p); break;
            case 'image': pendingImagePoint = p; $('#imageInput').click(); break;
            case 'rect': case 'ellipse': case 'line': startDraft(p); break;
        }
    });
    canvas.on('mouse:move', (opt) => {
        const p = canvas.getPointer(opt.e);
        $('#sbCursor').textContent = `X ${r1(p.x / DPMM).toFixed(1)}  Y ${r1(p.y / DPMM).toFixed(1)} mm`;
        if (draft) updateDraft(p, opt.e.shiftKey);
    });
    canvas.on('mouse:up', () => {
        if (draft) finishDraft();
        if (state.guides.v || state.guides.h) { state.guides = { v: false, h: false }; canvas.requestRenderAll(); }
    });
    canvas.on('mouse:dblclick', (opt) => {
        if (isDynamic(opt.target)) setTimeout(() => $('#pValue')?.focus(), 30);
    });

    canvas.on('path:created', ({ path }) => {
        path.set({ elType: state.tool === 'eraser' ? 'eraser' : 'pen', fill: null });
        scheduleHistory();
    });

    // Snapping + centre guides
    canvas.on('object:moving', (e) => {
        const o = e.target;
        if (state.snap && !e.e.altKey) o.set({ left: Math.round(o.left / 4) * 4, top: Math.round(o.top / 4) * 4 });
        o.setCoords();
        const th = 6 / state.zoom;
        state.guides = { v: false, h: false };
        let c = o.getCenterPoint();
        if (Math.abs(c.x - W() / 2) < th) { o.setPositionByOrigin(new fabric.Point(W() / 2, c.y), 'center', 'center'); state.guides.v = true; }
        c = o.getCenterPoint();
        if (Math.abs(c.y - H() / 2) < th) { o.setPositionByOrigin(new fabric.Point(c.x, H() / 2), 'center', 'center'); state.guides.h = true; }
        o.setCoords();
        updateGeomFields();
    });
    canvas.on('object:scaling', updateGeomFields);
    canvas.on('object:rotating', updateGeomFields);

    function bakeScale(o) {
        if (o.type === 'activeSelection' || o.type === 'group') return;
        const sx = o.scaleX, sy = o.scaleY;
        if (Math.abs(sx - 1) < 0.001 && Math.abs(sy - 1) < 0.001) return;
        if (o.type === 'rect') o.set({ width: o.width * sx, height: o.height * sy, scaleX: 1, scaleY: 1 });
        else if (o.type === 'ellipse') o.set({ rx: o.rx * sx, ry: o.ry * sy, scaleX: 1, scaleY: 1 });
        else if (isText(o) && Math.abs(sx - sy) < 0.001) o.set({ fontSize: o.fontSize * sy, scaleX: 1, scaleY: 1 });
        o.setCoords();
    }

    canvas.on('object:modified', (e) => {
        const o = e.target;
        if (isDynamic(o) && (Math.abs(o.scaleX - 1) > 0.01 || Math.abs(o.scaleY - 1) > 0.01)) {
            // Re-render barcodes / QR at integer dot sizes so bars stay razor sharp
            if (o.elType === 'barcode') {
                o.barcodeModule = clamp(Math.round(o.barcodeModule * o.scaleX), 1, 6);
                o.barcodeHeight = clamp(Math.round(o.barcodeHeight * o.scaleY), 16, 2400);
            } else {
                o.qrCell = clamp(Math.round(o.qrCell * Math.max(o.scaleX, o.scaleY)), 1, 40);
            }
            o.set({ scaleX: 1, scaleY: 1 });
            refreshDynamic(o).then(() => { renderProps(); pushHistory(); });
            return;
        }
        if (o) bakeScale(o);
        scheduleHistory();
        renderProps();
        renderLayers();
    });
    canvas.on('object:added', () => { scheduleHistory(); renderLayers(); updateStatus(); });
    canvas.on('object:removed', () => { scheduleHistory(); renderLayers(); updateStatus(); });
    canvas.on('text:changed', () => { scheduleHistory(400); renderLayers(); });
    canvas.on('text:editing:exited', () => { renderProps(); });
    ['selection:created', 'selection:updated', 'selection:cleared'].forEach((ev) => canvas.on(ev, () => {
        renderProps(); renderLayers(); renderOptions(); updateStatus();
    }));

    /* ---------------------------------------------------- Pan & wheel zoom */
    let pan = null;
    ws.addEventListener('mousedown', (e) => {
        if (!(state.tool === 'hand' || spaceDown || e.button === 1)) return;
        e.preventDefault(); e.stopPropagation();
        pan = { x: e.clientX, y: e.clientY, sl: ws.scrollLeft, st: ws.scrollTop };
        ws.classList.add('panning');
    }, true);
    window.addEventListener('mousemove', (e) => {
        if (!pan) return;
        ws.scrollLeft = pan.sl - (e.clientX - pan.x);
        ws.scrollTop = pan.st - (e.clientY - pan.y);
    });
    window.addEventListener('mouseup', () => { if (pan) { pan = null; ws.classList.remove('panning'); } });
    ws.addEventListener('wheel', (e) => {
        if (!(e.ctrlKey || e.metaKey)) return;
        e.preventDefault();
        setZoom(state.zoom * Math.pow(1.0018, -e.deltaY), e);
    }, { passive: false });

    /* --------------------------------------------------- Object operations */
    function applyLock(o) {
        const l = !!o.lockedLayer;
        o.set({ selectable: !l, evented: !l });
    }
    function deleteSel() {
        const objs = canvas.getActiveObjects();
        if (!objs.length) return;
        canvas.discardActiveObject();
        objs.forEach((o) => canvas.remove(o));
        canvas.requestRenderAll();
        pushHistory();
    }
    let clip = null;
    function copySel(cb) {
        const a = canvas.getActiveObject();
        if (!a) return;
        a.clone((c) => { clip = c; if (cb) cb(); }, PROPS);
    }
    function paste() {
        if (!clip) return;
        clip.clone((c) => {
            canvas.discardActiveObject();
            c.set({ left: c.left + 2 * DPMM, top: c.top + 2 * DPMM, evented: true });
            if (c.type === 'activeSelection') {
                c.canvas = canvas;
                c.forEachObject((o) => canvas.add(o));
                c.setCoords();
            } else {
                canvas.add(c);
            }
            clip.set({ left: clip.left + 2 * DPMM, top: clip.top + 2 * DPMM });
            canvas.setActiveObject(c);
            canvas.requestRenderAll();
            pushHistory();
        }, PROPS);
    }
    const duplicate = () => copySel(paste);
    function selectAll() {
        canvas.discardActiveObject();
        const objs = canvas.getObjects().filter((o) => o.selectable && o.visible !== false);
        if (!objs.length) return;
        canvas.setActiveObject(objs.length === 1 ? objs[0] : new fabric.ActiveSelection(objs, { canvas }));
        canvas.requestRenderAll();
    }
    function align(dir) {
        const t = canvas.getActiveObject();
        if (!t) return;
        t.setCoords();
        const b = t.getBoundingRect(true, true);
        let dx = 0, dy = 0;
        if (dir === 'l') dx = -b.left;
        if (dir === 'c') dx = (W() - b.width) / 2 - b.left;
        if (dir === 'r') dx = W() - b.width - b.left;
        if (dir === 't') dy = -b.top;
        if (dir === 'm') dy = (H() - b.height) / 2 - b.top;
        if (dir === 'b') dy = H() - b.height - b.top;
        t.set({ left: t.left + dx, top: t.top + dy });
        t.setCoords();
        canvas.requestRenderAll();
        pushHistory();
        updateGeomFields();
    }
    function insertVar(k) {
        const o = canvas.getActiveObject();
        const token = `{{${k}}}`;
        if (isText(o)) {
            if (o.isEditing) o.exitEditing();
            o.set('text', (o.text === 'Text' ? '' : o.text) + token);
        } else if (o && o.elType === 'barcode') {
            o.barcodeValue = token; refreshDynamic(o);
        } else if (o && o.elType === 'qr') {
            o.qrValue = token; refreshDynamic(o);
        } else {
            navigator.clipboard?.writeText(token);
            toast('Copied ' + token, 'Select a text, barcode or QR layer to insert variables directly.');
            return;
        }
        canvas.requestRenderAll();
        renderProps(); renderLayers(); scheduleHistory();
    }
    function applyImageFilters(o) {
        const F = fabric.Image.filters;
        o.filters = [];
        if (o.imgBrightness) o.filters.push(new F.Brightness({ brightness: o.imgBrightness }));
        if (o.imgContrast) o.filters.push(new F.Contrast({ contrast: o.imgContrast }));
        if (o.imgInvert) o.filters.push(new F.Invert());
        o.applyFilters();
    }

    /* ------------------------------------------------------------- Layers */
    function layerName(o) {
        if (o.layerName) return o.layerName;
        switch (o.elType || o.type) {
            case 'text': case 'i-text': return (o.text || 'Text').split('\n')[0].slice(0, 40) || 'Text';
            case 'barcode': return 'Barcode · ' + o.barcodeValue;
            case 'qr': return 'QR · ' + o.qrValue;
            case 'rect': return 'Rectangle';
            case 'ellipse': return 'Ellipse';
            case 'line': return 'Line';
            case 'pen': return 'Pen stroke';
            case 'eraser': return 'Eraser stroke';
            case 'image': return 'Image';
            case 'symbol': return 'Symbol';
            default: return o.type;
        }
    }
    function layerIcon(o) {
        return { text: 'text', 'i-text': 'text', barcode: 'barcode', qr: 'qr', rect: 'rect', ellipse: 'ellipse', line: 'line', pen: 'path', eraser: 'eraser', image: 'image', symbol: 'symbol', path: 'path' }[o.elType || o.type] || 'rect';
    }
    let layersRaf = 0;
    function renderLayers() {
        cancelAnimationFrame(layersRaf);
        layersRaf = requestAnimationFrame(() => {
            const objs = canvas.getObjects();
            const active = new Set(canvas.getActiveObjects());
            $('#layerCount').textContent = objs.length;
            const rows = objs.slice().reverse().map((o) => {
                const i = objs.indexOf(o);
                const vis = o.visible !== false;
                return `<div class="layer ${active.has(o) ? 'sel' : ''} ${vis ? '' : 'hidden'}" data-i="${i}">
                    <button class="lbtn ${vis ? 'on' : ''}" data-la="vis" title="Show / hide">${icon(vis ? 'eye' : 'eyeOff')}</button>
                    <button class="lbtn ${o.lockedLayer ? 'on' : ''}" data-la="lock" title="Lock / unlock">${icon(o.lockedLayer ? 'lock' : 'unlock')}</button>
                    <span class="lic">${icon(layerIcon(o))}</span>
                    <span class="lname" title="Double-click to rename">${esc(layerName(o))}</span>
                    <span class="lorder">
                        <button class="lbtn" data-la="up" title="Bring forward">${icon('up')}</button>
                        <button class="lbtn" data-la="down" title="Send backward">${icon('down')}</button>
                    </span>
                </div>`;
            }).join('');
            $('#layerList').innerHTML = rows || '<div class="empty">No layers yet.<br>Pick a tool on the left and click the label.</div>';
        });
    }
    function buildLayersPanel() {
        $('#layersPanel').innerHTML = `<div id="layerList" style="flex:1;overflow-y:auto"></div>
            <div class="layers-foot">
                <button class="btn sm" data-action="duplicate" title="Duplicate (⌘D)">${icon('copy')}</button>
                <button class="btn sm danger" data-action="delete" title="Delete (⌫)">${icon('trash')}</button>
            </div>`;
        const list = $('#layerList');
        list.addEventListener('click', (e) => {
            const row = e.target.closest('.layer');
            if (!row) return;
            const o = canvas.getObjects()[+row.dataset.i];
            if (!o) return;
            const act = e.target.closest('[data-la]')?.dataset.la;
            if (act === 'vis') {
                o.visible = o.visible === false;
                if (!o.visible) canvas.discardActiveObject();
            } else if (act === 'lock') {
                o.lockedLayer = !o.lockedLayer;
                applyLock(o);
                if (o.lockedLayer) canvas.discardActiveObject();
            } else if (act === 'up') {
                canvas.bringForward(o);
            } else if (act === 'down') {
                canvas.sendBackwards(o);
            } else {
                if (state.tool !== 'select') setTool('select');
                if (o.visible !== false && !o.lockedLayer) canvas.setActiveObject(o);
                canvas.requestRenderAll();
                return;
            }
            canvas.requestRenderAll();
            renderLayers();
            pushHistory();
        });
        list.addEventListener('dblclick', (e) => {
            const row = e.target.closest('.layer');
            if (!row || !e.target.closest('.lname')) return;
            const o = canvas.getObjects()[+row.dataset.i];
            const n = prompt('Layer name', layerName(o));
            if (n === null) return;
            o.layerName = n.trim() || undefined;
            renderLayers();
            pushHistory();
        });
    }

    /* ---------------------------------------------------------- Properties */
    const fld = (label, inner) => `<label class="fld"><span>${label}</span>${inner}</label>`;
    const numIn = (p, val, { unit = '', step = 0.1, min = '', max = '', id = '', disabled = false } = {}) =>
        `<div class="unit-in"><input type="number" ${id ? `id="${id}"` : ''} data-p="${p}" value="${val}" step="${step}" ${min !== '' ? `min="${min}"` : ''} ${max !== '' ? `max="${max}"` : ''} ${disabled ? 'disabled' : ''}><em>${unit}</em></div>`;
    const seg = (p, opts, cur) => `<div class="seg">${opts.map(([v, l]) => `<button type="button" data-seg="${p}" data-v="${esc(v)}" class="${String(cur) === String(v) ? 'on' : ''}">${l}</button>`).join('')}</div>`;
    const varChips = () => `<div class="btn-row">${Object.keys(state.vars).map((k) => `<button class="btn sm" data-action="insertVar" data-var="${esc(k)}" title="Insert {{${esc(k)}}}">${esc(k)}</button>`).join('')}</div>`;
    const inkSeg = (p, cur, withNone = true) => seg(p, [...(withNone ? [['', 'None']] : []), ['#000000', 'Black'], ['#ffffff', 'White']], cur || '');

    function geom(o) {
        return {
            x: r1(o.left / DPMM), y: r1(o.top / DPMM),
            w: r1(o.getScaledWidth() / DPMM), h: r1(o.getScaledHeight() / DPMM),
            a: Math.round(o.angle || 0),
        };
    }
    function updateGeomFields() {
        const o = canvas.getActiveObject();
        if (!o) return;
        const g = geom(o);
        const set = (id, v) => { const el = document.getElementById(id); if (el && document.activeElement !== el) el.value = v; };
        set('pX', g.x); set('pY', g.y); set('pW', g.w); set('pH', g.h); set('pA', g.a);
        updateStatus();
    }

    function renderProps() {
        const el = $('#propsPanel');
        const objs = canvas.getActiveObjects();
        if (!objs.length) {
            el.innerHTML = `<div class="empty">Nothing selected<br>
                <kbd>V</kbd> select · <kbd>T</kbd> text · <kbd>B</kbd> barcode<br><kbd>I</kbd> image · <kbd>R</kbd> rectangle · <kbd>P</kbd> pen</div>`;
            return;
        }
        const actions = `<div class="btn-row">
            <button class="btn sm grow" data-action="duplicate">${icon('copy')} Duplicate</button>
            <button class="btn sm" data-action="bringFront" title="Bring to front">${icon('front')}</button>
            <button class="btn sm" data-action="sendBack" title="Send to back">${icon('toBack')}</button>
            <button class="btn sm danger" data-action="delete" title="Delete">${icon('trash')}</button></div>`;
        if (objs.length > 1) {
            el.innerHTML = `<div class="hint">${objs.length} layers selected — use the align buttons in the options bar.</div>${actions}`;
            return;
        }
        const o = objs[0];
        const g = geom(o);
        const dyn = isDynamic(o);
        let h = `<div class="row">${fld('X', numIn('x', g.x, { unit: 'mm', id: 'pX' }))}${fld('Y', numIn('y', g.y, { unit: 'mm', id: 'pY' }))}</div>
            <div class="row">${fld('Width', numIn('w', g.w, { unit: 'mm', id: 'pW', disabled: dyn }))}${fld('Height', numIn('h', g.h, { unit: 'mm', id: 'pH', disabled: dyn }))}</div>
            <div class="row">${fld('Rotate', numIn('angle', g.a, { unit: '°', step: 1, id: 'pA' }))}
                <label class="check" style="align-self:end;height:26px"><input type="checkbox" data-p="keepRatio" ${state.keepRatio ? 'checked' : ''}> Keep ratio</label></div>`;

        if (isText(o)) {
            const pt = r1((o.fontSize * (o.scaleY || 1)) / PT_TO_DOTS);
            const colour = o.backgroundColor === '#000000' ? 'invert' : o.fill;
            h += fld('Text', `<textarea data-p="text" rows="2">${esc(o.text)}</textarea>`);
            h += varChips();
            h += `<div class="row">${fld('Font', `<select data-p="fontFamily">${FONTS.map((f) => `<option ${f === o.fontFamily ? 'selected' : ''}>${f}</option>`).join('')}</select>`)}
                ${fld('Size', numIn('fontSizePt', pt, { unit: 'pt', step: 0.5, min: 2 }))}</div>`;
            h += `<div class="row">${fld('Style', `<div class="seg">
                    <button type="button" data-seg="style" data-v="bold" class="${o.fontWeight === 'bold' ? 'on' : ''}"><b>B</b></button>
                    <button type="button" data-seg="style" data-v="italic" class="${o.fontStyle === 'italic' ? 'on' : ''}"><em>I</em></button>
                    <button type="button" data-seg="style" data-v="underline" class="${o.underline ? 'on' : ''}"><u>U</u></button></div>`)}
                ${fld('Align', seg('textAlign', [['left', 'L'], ['center', 'C'], ['right', 'R']], o.textAlign))}</div>`;
            h += `<div class="row">${fld('Letter spacing', numIn('charSpacing', o.charSpacing || 0, { step: 10 }))}${fld('Line height', numIn('lineHeight', o.lineHeight, { step: 0.05, min: 0.5 }))}</div>`;
            h += fld('Colour', seg('textColor', [['#000000', 'Black'], ['#ffffff', 'White'], ['invert', 'White on black']], colour));
            h += '<div class="hint">Centre / right aligned text stays anchored when variables are filled in at print time.</div>';
        } else if (o.elType === 'barcode') {
            h += fld('Value', `<input type="text" id="pValue" data-p="barcodeValue" data-live="1" value="${esc(o.barcodeValue)}">`);
            h += varChips();
            h += fld('Symbology', `<select data-p="barcodeFormat">${BARCODE_FORMATS.map(([v, l]) => `<option value="${v}" ${v === o.barcodeFormat ? 'selected' : ''}>${l}</option>`).join('')}</select>`);
            h += `<div class="row">${fld('Bar width', numIn('barcodeModule', o.barcodeModule, { unit: 'dots', step: 1, min: 1, max: 6 }))}${fld('Bar height', numIn('barcodeHeightMm', r1(o.barcodeHeight / DPMM), { unit: 'mm', step: 0.5, min: 2 }))}</div>`;
            h += `<label class="check"><input type="checkbox" data-p="barcodeShowText" ${o.barcodeShowText ? 'checked' : ''}> Show human-readable text</label>`;
            h += '<div class="hint">Resize handles snap to whole dots so bars stay scannable. 2–3 dot bars scan best.</div>';
        } else if (o.elType === 'qr') {
            h += fld('Value', `<textarea id="pValue" data-p="qrValue" rows="2">${esc(o.qrValue)}</textarea>`);
            h += varChips();
            h += `<div class="row">${fld('Cell size', numIn('qrCell', o.qrCell, { unit: 'dots', step: 1, min: 1, max: 40 }))}
                ${fld('Error correction', `<select data-p="qrEcc">${['L', 'M', 'Q', 'H'].map((v) => `<option ${v === o.qrEcc ? 'selected' : ''}>${v}</option>`).join('')}</select>`)}</div>`;
        } else if (o.type === 'rect' || o.type === 'ellipse') {
            h += fld('Fill', inkSeg('fill', o.fill));
            h += fld('Stroke', inkSeg('stroke', o.stroke));
            h += `<div class="row">${fld('Stroke width', numIn('strokeMm', r1((o.strokeWidth || 0) / DPMM), { unit: 'mm', step: 0.1, min: 0.1 }))}
                ${o.type === 'rect' ? fld('Corner radius', numIn('radiusMm', r1((o.rx || 0) / DPMM), { unit: 'mm', step: 0.5, min: 0 })) : '<span></span>'}</div>`;
        } else if (o.type === 'line') {
            h += fld('Colour', inkSeg('stroke', o.stroke, false));
            h += `<div class="row">${fld('Thickness', numIn('strokeMm', r1(o.strokeWidth / DPMM), { unit: 'mm', step: 0.1, min: 0.1 }))}
                <label class="check" style="align-self:end;height:26px"><input type="checkbox" data-p="dashed" ${o.strokeDashArray ? 'checked' : ''}> Dashed</label></div>`;
        } else if (o.type === 'path') {
            h += fld('Colour', inkSeg('stroke', o.stroke, false));
            h += fld('Thickness', numIn('strokeMm', r1(o.strokeWidth / DPMM), { unit: 'mm', step: 0.1, min: 0.1 }));
        } else if (o.elType === 'image') {
            const thresh = o.imgThreshold ?? 140;
            const threshPct = Math.round(((thresh - 20) / 220) * 100);
            h += `<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
                    <span style="font-size:11px;font-weight:700;color:var(--text);letter-spacing:0.04em;text-transform:uppercase">Thermal B&W Mode</span>
                    <span style="font-size:10px;background:#22c55e;color:#000;padding:1px 6px;border-radius:4px;font-weight:700">1-BIT MONO</span>
                  </div>`;
            h += fld(`Darkness / Threshold <span class="hint" id="imgThreshVal">${threshPct}%</span>`, `<input type="range" min="20" max="240" step="5" data-p="imgThreshold" value="${thresh}">`);
            h += `<div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;margin:4px 0">
                    <label class="check" title="Recommended for photos and continuous gradients"><input type="checkbox" data-p="imgDither" ${o.imgDither ? 'checked' : ''}> Dither (Photos)</label>
                    <label class="check" title="Invert black and white pixels"><input type="checkbox" data-p="imgInvert" ${o.imgInvert ? 'checked' : ''}> Invert (Negative)</label>
                  </div>`;
            h += `<label class="check" style="margin-bottom:8px" title="Converts white pixels into transparent background"><input type="checkbox" data-p="imgRemoveWhite" ${o.imgRemoveWhite ? 'checked' : ''}> Make White Transparent</label>`;
            h += '<div class="btn-row" style="margin-top:6px"><button type="button" class="btn sm grow" data-action="fitImage">Fit to label</button><button type="button" class="btn sm" data-action="reconvertBW" title="Re-convert original image to black & white">Reset B&W</button></div>';
            h += '<div class="hint" style="margin-top:6px">Thermal printers print pure black dots. The canvas displays exactly how it burns onto the sticker.</div>';
        }
        el.innerHTML = h + actions;
    }

    const refreshDynamicDebounced = debounce((o) => refreshDynamic(o).then(() => { scheduleHistory(); updateGeomFields(); renderLayers(); }), 220);

    function applyProp(key, val, ev) {
        if (key === 'keepRatio') { state.keepRatio = !!val; return; }
        const o = canvas.getActiveObject();
        if (!o) return;
        const n = parseFloat(val);
        let rerender = false;
        switch (key) {
            case 'x': if (isFinite(n)) o.set('left', n * DPMM); break;
            case 'y': if (isFinite(n)) o.set('top', n * DPMM); break;
            case 'w': case 'h': {
                if (!(n > 0)) return;
                const target = n * DPMM;
                if (key === 'w') {
                    const s = target / (o.getScaledWidth() / o.scaleX);
                    const ratio = o.scaleY / o.scaleX;
                    o.set('scaleX', s);
                    if (state.keepRatio) o.set('scaleY', s * ratio);
                } else {
                    const s = target / (o.getScaledHeight() / o.scaleY);
                    const ratio = o.scaleX / o.scaleY;
                    o.set('scaleY', s);
                    if (state.keepRatio) o.set('scaleX', s * ratio);
                }
                bakeScale(o);
                rerender = true;
                break;
            }
            case 'angle': o.rotate(isFinite(n) ? n : 0); break;
            case 'text': if (o.isEditing) o.exitEditing(); o.set('text', val); break;
            case 'fontFamily':
                o.set('fontFamily', val);
                ensureFont(val).then(() => { fabric.util.clearFabricFontCache(val); o.initDimensions(); o.setCoords(); canvas.requestRenderAll(); });
                break;
            case 'fontSizePt': if (n > 0) o.set({ fontSize: n * PT_TO_DOTS, scaleX: 1, scaleY: 1 }); break;
            case 'style':
                if (val === 'bold') o.set('fontWeight', o.fontWeight === 'bold' ? 'normal' : 'bold');
                if (val === 'italic') o.set('fontStyle', o.fontStyle === 'italic' ? 'normal' : 'italic');
                if (val === 'underline') o.set('underline', !o.underline);
                rerender = true;
                break;
            case 'textAlign': o.set('textAlign', val); rerender = true; break;
            case 'charSpacing': o.set('charSpacing', n || 0); break;
            case 'lineHeight': if (n > 0) o.set('lineHeight', n); break;
            case 'textColor':
                if (val === 'invert') o.set({ fill: '#ffffff', backgroundColor: '#000000' });
                else o.set({ fill: val, backgroundColor: '' });
                rerender = true;
                break;
            case 'fill': o.set('fill', val || ''); rerender = true; break;
            case 'stroke':
                o.set('stroke', val || null);
                if (val && !o.strokeWidth) o.set('strokeWidth', 3);
                rerender = true;
                break;
            case 'strokeMm':
                if (n > 0) o.set('strokeWidth', Math.max(1, n * DPMM));
                if (o.strokeDashArray) o.set('strokeDashArray', [o.strokeWidth * 3, o.strokeWidth * 2]);
                break;
            case 'radiusMm': o.set({ rx: Math.max(0, (n || 0) * DPMM), ry: Math.max(0, (n || 0) * DPMM) }); break;
            case 'dashed': o.set('strokeDashArray', val ? [o.strokeWidth * 3, o.strokeWidth * 2] : null); break;
            case 'barcodeValue': case 'qrValue': o[key] = val; refreshDynamicDebounced(o); return;
            case 'barcodeFormat': o.barcodeFormat = val; refreshDynamicDebounced(o); return;
            case 'barcodeModule': o.barcodeModule = clamp(Math.round(n) || 1, 1, 6); refreshDynamicDebounced(o); return;
            case 'barcodeHeightMm': o.barcodeHeight = clamp(Math.round((n || 1) * DPMM), 16, 2400); refreshDynamicDebounced(o); return;
            case 'barcodeShowText': o.barcodeShowText = !!val; refreshDynamicDebounced(o); return;
            case 'qrCell': o.qrCell = clamp(Math.round(n) || 1, 1, 40); refreshDynamicDebounced(o); return;
            case 'qrEcc': o.qrEcc = val; refreshDynamicDebounced(o); return;
            case 'imgThreshold':
                o.imgThreshold = clamp(Math.round(n) || 140, 20, 240);
                const valEl = $('#imgThreshVal');
                if (valEl) valEl.textContent = Math.round(((o.imgThreshold - 20) / 220) * 100) + '%';
                updateImageMonochromeDebounced(o);
                return;
            case 'imgDither':
            case 'imgInvert':
            case 'imgRemoveWhite':
                o[key] = !!val;
                updateImageMonochrome(o);
                return;
            case 'imgBrightness': case 'imgContrast': o[key] = n || 0; applyImageFilters(o); break;
            case 'imgInvert': o.imgInvert = !!val; applyImageFilters(o); break;
            default: return;
        }
        o.setCoords();
        canvas.requestRenderAll();
        scheduleHistory(ev === 'input' ? 400 : 0);
        updateGeomFields();
        renderLayers();
        if (rerender) renderProps();
    }

    function bindPropsPanel() {
        const el = $('#propsPanel');
        el.addEventListener('change', (e) => {
            const t = e.target;
            if (!t.dataset.p || t.type === 'range' || t.dataset.live || t.tagName === 'TEXTAREA') return;
            applyProp(t.dataset.p, t.type === 'checkbox' ? t.checked : t.value, 'change');
        });
        el.addEventListener('input', (e) => {
            const t = e.target;
            if (!t.dataset.p) return;
            if (t.type === 'range' || t.dataset.live || t.tagName === 'TEXTAREA') {
                applyProp(t.dataset.p, t.value, 'input');
                if (t.type === 'range') { const lbl = t.closest('.fld')?.querySelector('.hint'); if (lbl) lbl.textContent = t.value; }
            }
        });
        el.addEventListener('click', (e) => {
            const s = e.target.closest('[data-seg]');
            if (s) applyProp(s.dataset.seg, s.dataset.v, 'click');
        });
    }

    /* --------------------------------------------------------- Options bar */
    function renderOptions() {
        const t = toolDef(state.tool);
        let h = `<div class="opt-tool">${icon(t.icon)} ${esc(t.tip.split(' —')[0])}</div>`;
        if (state.tool === 'pen' || state.tool === 'eraser') {
            const v = state.brush[state.tool];
            h += `<div class="opt-group"><label>Size</label><input type="range" min="1" max="80" value="${v}" data-opt-brush><span class="opt-hint" id="brushVal">${r1(v / DPMM)} mm</span></div>`;
            if (state.tool === 'eraser') h += '<span class="opt-hint">The eraser paints white paper over anything below it (it is a layer you can delete).</span>';
        } else if (state.tool === 'text') {
            h += `<div class="opt-group"><label>Font</label><select data-opt-text="fontFamily">${FONTS.map((f) => `<option ${f === state.text.fontFamily ? 'selected' : ''}>${f}</option>`).join('')}</select></div>
                <div class="opt-group"><label>Size</label><input type="number" min="2" step="0.5" value="${state.text.fontSizePt}" data-opt-text="fontSizePt"><span class="opt-hint">pt</span></div>
                <span class="opt-hint">Click on the label to place text</span>`;
        } else if (['rect', 'ellipse', 'line'].includes(state.tool)) {
            h += `<div class="opt-group"><label>Stroke</label><input type="number" min="0.1" step="0.1" value="${r1(state.shapeStroke / DPMM)}" data-opt-stroke><span class="opt-hint">mm</span></div>
                <span class="opt-hint">Drag on the label to draw · hold ⇧ to constrain</span>`;
        } else if (['barcode', 'qr'].includes(state.tool)) {
            h += '<span class="opt-hint">Click on the label to place · values can use variables like {{barcode}}</span>';
        } else if (state.tool === 'image') {
            h += '<span class="opt-hint">Click on the label to upload an image there · or pick brand assets & care symbols in the right panel</span>';
        } else if (state.tool === 'select') {
            if (canvas.getActiveObject()) {
                h += `<div class="opt-group"><label>Align to label</label>
                    <button class="obtn" data-action="align" data-dir="l" title="Left">${icon('alignL')}</button>
                    <button class="obtn" data-action="align" data-dir="c" title="Centre horizontally">${icon('alignC')}</button>
                    <button class="obtn" data-action="align" data-dir="r" title="Right">${icon('alignR')}</button>
                    <button class="obtn" data-action="align" data-dir="t" title="Top">${icon('alignT')}</button>
                    <button class="obtn" data-action="align" data-dir="m" title="Centre vertically">${icon('alignM')}</button>
                    <button class="obtn" data-action="align" data-dir="b" title="Bottom">${icon('alignB')}</button></div>
                    <div class="opt-sep"></div>
                    <div class="opt-group">
                    <button class="obtn" data-action="bringFront" title="Bring to front">${icon('front')}</button>
                    <button class="obtn" data-action="sendBack" title="Send to back">${icon('toBack')}</button>
                    <button class="obtn" data-action="duplicate" title="Duplicate (⌘D)">${icon('copy')}</button>
                    <button class="obtn" data-action="delete" title="Delete (⌫)">${icon('trash')}</button></div>`;
            } else {
                h += '<span class="opt-hint">Click a layer to select · drag to marquee · ⌘-scroll / pinch to zoom · Space-drag to pan</span>';
            }
        } else if (state.tool === 'hand') {
            h += '<span class="opt-hint">Drag to pan the workspace</span>';
        } else if (state.tool === 'zoom') {
            h += '<span class="opt-hint">Click to zoom in · ⌥-click to zoom out</span>';
        }
        h += `<div style="margin-left:auto" class="opt-group">
            <button class="obtn ${state.grid ? 'on' : ''}" data-action="toggleOpt" data-opt="grid" title="Millimetre grid">${icon('grid')}</button>
            <button class="obtn ${state.snap ? 'on' : ''}" data-action="toggleOpt" data-opt="snap" title="Snap to 0.5 mm (hold ⌥ to bypass)">${icon('magnet')}</button>
            <button class="obtn ${state.safe ? 'on' : ''}" data-action="toggleOpt" data-opt="safe" title="Safe print area (1.5 mm)">${icon('safe')}</button></div>`;
        $('#optionsBar').innerHTML = h;
    }
    function bindOptionsBar() {
        const bar = $('#optionsBar');
        bar.addEventListener('input', (e) => {
            const t = e.target;
            if (t.hasAttribute('data-opt-brush')) {
                state.brush[state.tool] = +t.value;
                $('#brushVal').textContent = r1(t.value / DPMM) + ' mm';
                if (canvas.freeDrawingBrush) canvas.freeDrawingBrush.width = +t.value;
            }
        });
        bar.addEventListener('change', (e) => {
            const t = e.target;
            if (t.dataset.optText) {
                state.text[t.dataset.optText] = t.dataset.optText === 'fontSizePt' ? (parseFloat(t.value) || 10) : t.value;
                if (t.dataset.optText === 'fontFamily') ensureFont(t.value);
            }
            if (t.hasAttribute('data-opt-stroke')) state.shapeStroke = Math.max(1, (parseFloat(t.value) || 0.4) * DPMM);
        });
    }

    /* ----------------------------------------------------- Document panel */
    function renderDoc() {
        const cur = `${state.widthMm}x${state.heightMm}`;
        $('#docPanel').innerHTML = `
            <div class="presets">${PRESETS.map(([w, h]) => `<button data-action="preset" data-w="${w}" data-h="${h}" class="${cur === `${w}x${h}` ? 'on' : ''}">${w}×${h}</button>`).join('')}</div>
            <div class="row">
                ${fld('Width', `<div class="unit-in"><input type="number" id="docW" min="10" max="${MAX_WIDTH_MM}" step="0.5" value="${state.widthMm}"><em>mm</em></div>`)}
                ${fld('Height', `<div class="unit-in"><input type="number" id="docH" min="10" max="300" step="0.5" value="${state.heightMm}"><em>mm</em></div>`)}
            </div>
            <div class="row">
                ${fld('Gap between labels', `<div class="unit-in"><input type="number" id="docGap" min="0" max="20" step="0.5" value="${state.gapMm}"><em>mm</em></div>`)}
                ${fld('Category', `<select id="docCat">${[['garment', 'Garment tag'], ['wash_care', 'Wash care'], ['barcode', 'Barcode / stock'], ['shipping', 'Shipping'], ['custom', 'Custom']].map(([v, l]) => `<option value="${v}" ${v === state.category ? 'selected' : ''}>${l}</option>`).join('')}</select>`)}
            </div>
            ${fld('Print orientation', seg('direction', [['1', 'Normal'], ['0', 'Rotated 180°']], String(state.direction)))}
            <div class="hint">Max width ${MAX_WIDTH_MM} mm (Deli E886BW head) · 203 dpi = 8 dots per mm.</div>`;
    }
    function setSize(w, h) {
        let wmm = parseFloat(w), hmm = parseFloat(h);
        if (!(wmm > 0) || !(hmm > 0)) return;
        if (wmm > MAX_WIDTH_MM) toast('Too wide', `The printer head is ${MAX_WIDTH_MM} mm wide — width capped.`, 'error');
        state.widthMm = r1(clamp(wmm, 10, MAX_WIDTH_MM));
        state.heightMm = r1(clamp(hmm, 10, 300));
        setZoom(state.zoom);
        zoomFit();
        renderDoc();
        setDirty(true);
    }
    function bindDocPanel() {
        const el = $('#docPanel');
        el.addEventListener('change', (e) => {
            const id = e.target.id;
            if (id === 'docW' || id === 'docH') setSize($('#docW').value, $('#docH').value);
            if (id === 'docGap') { state.gapMm = clamp(parseFloat(e.target.value) || 0, 0, 20); setDirty(true); }
            if (id === 'docCat') { state.category = e.target.value; setDirty(true); }
        });
        el.addEventListener('click', (e) => {
            const s = e.target.closest('[data-seg="direction"]');
            if (s) { state.direction = +s.dataset.v; renderDoc(); setDirty(true); }
        });
    }

    /* ------------------------------------------------------ Assets panel */
    function renderAssets() {
        $('#assetsPanel').innerHTML = `
            <button class="btn primary" data-action="upload">${icon('image')} Upload image / logo…</button>
            <div class="asset-label">Brand</div>
            <div class="asset-grid">${(CFG.assets || []).map((a) => `<button class="asset" data-action="addAsset" data-url="${esc(a.url)}" title="${esc(a.name)}"><img src="${esc(a.url)}" alt=""></button>`).join('')}</div>
            <div class="asset-label">Care symbols</div>
            <div class="asset-grid">${SYMBOLS.map((s, i) => `<button class="asset" data-action="addSymbol" data-i="${i}" title="${esc(s.name)}">${s.svg}</button>`).join('')}</div>
            <div class="hint">Transparent PNG or SVG logos work best. Everything prints as pure black dots.</div>`;
    }

    /* --------------------------------------------------- Variables panel */
    function renderVars() {
        $('#varsPanel').innerHTML = Object.entries(state.vars).map(([k, v]) => `<div class="var-row">
                <code data-action="insertVar" data-var="${esc(k)}" title="Insert into selected layer">{{${esc(k)}}}</code>
                <input type="text" data-var-input="${esc(k)}" value="${esc(v)}"></div>`).join('')
            + `<div class="btn-row"><button class="btn sm" data-action="addVar">${icon('plus')} Add variable</button></div>
               <div class="hint">Write <b>{{name}}</b> inside text, barcode or QR values. Sample values are saved with the template and used for preview and printing.</div>`;
    }
    const varsChanged = debounce(() => { refreshAllDynamic(); setDirty(true); }, 300);
    function bindVarsPanel() {
        $('#varsPanel').addEventListener('input', (e) => {
            const k = e.target.dataset.varInput;
            if (!k) return;
            state.vars[k] = e.target.value;
            varsChanged();
        });
    }

    /* ------------------------------------------------------- Status bar */
    function updateStatus() {
        $('#sbSize').textContent = `${state.widthMm} × ${state.heightMm} mm · ${W()} × ${H()} dots · ${canvas.getObjects().length} layers`;
        const o = canvas.getActiveObject();
        if (!o) { $('#sbSelection').textContent = ''; return; }
        const n = canvas.getActiveObjects().length;
        const g = geom(o);
        $('#sbSelection').textContent = n > 1 ? `${n} selected` : `${layerName(o)} · ${g.w} × ${g.h} mm`;
    }

    /* ------------------------------------------------------ Printer status */
    function setPill(el, color, text) {
        if (!el) return;
        el.className = el.className.replace(/\b(success|warning|danger|error|info|gray)\b/g, '').trim() + ' ' + (color || 'gray');
        el.querySelector('.txt').textContent = text;
        el.title = text;
    }
    async function refreshPrinterStatus() {
        const pills = [$('#printerPill'), $('#dialogPrinterPill')];
        if (!state.printerId) { pills.forEach((p) => setPill(p, 'danger', 'No printer configured')); return; }
        const name = (CFG.printers || []).find((p) => p.id == state.printerId)?.name || 'Printer';
        try {
            const r = await fetch(CFG.urls.status.replace('__ID__', state.printerId), { headers: { Accept: 'application/json' } });
            const j = await r.json();
            pills.forEach((p) => setPill(p, j.color, `${name} · ${j.message}`));
        } catch (e) {
            pills.forEach((p) => setPill(p, 'gray', `${name} · status unavailable`));
        }

        // Also update queue badge count
        if (CFG.urls.queue) {
            try {
                const qr = await fetch(CFG.urls.queue, { headers: { Accept: 'application/json' } });
                const qj = await qr.json();
                const count = (qj.jobs || []).length;
                const badge = $('#queueBadge');
                if (badge) {
                    badge.textContent = count;
                    badge.style.display = count > 0 ? 'inline-flex' : 'none';
                }
            } catch (e) { /* ignore */ }
        }
    }

    async function openQueueModal() {
        const dlg = $('#queueDialog');
        if (!dlg) return;
        dlg.showModal();
        await refreshQueueModal();
    }
    window.refreshQueueModal = async function() {
        const list = $('#queueList');
        const countLabel = $('#queueCountLabel');
        const btnCancelAll = $('#btnCancelAllJobs');
        list.innerHTML = '<div class="empty">Checking print queue…</div>';

        try {
            const r = await fetch(CFG.urls.queue, { headers: { Accept: 'application/json' } });
            const j = await r.json();
            const jobs = j.jobs || [];

            countLabel.textContent = `${jobs.length} active job${jobs.length === 1 ? '' : 's'}`;
            btnCancelAll.style.display = jobs.length > 0 ? 'inline-flex' : 'none';

            if (!jobs.length) {
                list.innerHTML = `<div class="empty">
                    <div style="font-size:24px; margin-bottom:8px;">✓</div>
                    <b>Queue is completely empty</b><br>
                    No jobs waiting or spooling on thermal printers.
                </div>`;
                return;
            }

            list.innerHTML = jobs.map((job) => `
                <div class="q-row">
                    <div class="q-info">
                        <div class="q-head">
                            <span class="q-id">${esc(job.id)}</span>
                            <span>${esc(job.printer)}</span>
                        </div>
                        <div class="q-sub">Size: ${esc(job.size)} · User: ${esc(job.user)} · ${esc(job.created_at)}</div>
                    </div>
                    <button type="button" class="btn sm danger" data-cancel-job="${esc(job.id)}" title="Cancel this job">
                        ${icon('trash')} Cancel
                    </button>
                </div>
            `).join('');

            // Bind individual cancel buttons
            list.querySelectorAll('[data-cancel-job]').forEach((btn) => {
                btn.addEventListener('click', async () => {
                    const id = btn.dataset.cancelJob;
                    btn.disabled = true;
                    btn.textContent = 'Cancelling…';
                    const res = await api(CFG.urls.cancelQueue, { job_id: id });
                    if (res.success) {
                        toast('Job cancelled', res.message, 'success');
                    } else {
                        toast('Failed to cancel', res.message, 'error');
                    }
                    await refreshQueueModal();
                    await refreshPrinterStatus();
                });
            });

            // Hydrate icons
            list.querySelectorAll('i[data-icon]').forEach((el) => { if (!el.firstChild) el.innerHTML = svg(el.dataset.icon); });
        } catch (e) {
            list.innerHTML = `<div class="empty" style="color:var(--bad);">Error loading queue: ${esc(e.message)}</div>`;
        }
    };

    $('#btnCancelAllJobs')?.addEventListener('click', async () => {
        if (!confirm('Are you sure you want to terminate ALL pending print jobs across all queues?')) return;
        const res = await api(CFG.urls.cancelQueue, { all: true });
        if (res.success) {
            toast('Queue cleared', res.message, 'success');
        } else {
            toast('Failed', res.message, 'error');
        }
        await refreshQueueModal();
        await refreshPrinterStatus();
    });

    /* ---------------------------------------------- WYSIWYG print pipeline */
    async function renderLabelCanvas() {
        const families = new Set();
        canvas.getObjects().forEach((o) => { if (isText(o)) families.add(o.fontFamily); });
        await Promise.all([...families].map(ensureFont));

        const json = canvas.toJSON(PROPS);
        const sc = new fabric.StaticCanvas(null, { width: W(), height: H(), enableRetinaScaling: false, renderOnAddRemove: false });
        await new Promise((res) => sc.loadFromJSON(json, res));
        sc.backgroundColor = '#ffffff';

        // Fill in variables; centre/right aligned text keeps its anchor
        const walk = (o) => {
            if (isText(o)) {
                const t = interpolate(o.text);
                if (t !== o.text) {
                    const anchor = o.textAlign === 'center' ? 'center' : o.textAlign === 'right' ? 'right' : 'left';
                    const p = o.getPointByOrigin(anchor, 'top');
                    o.set('text', t);
                    o.setPositionByOrigin(p, anchor, 'top');
                }
            }
            if (o._objects) o._objects.forEach(walk);
        };
        sc.getObjects().forEach(walk);
        await Promise.all(sc.getObjects().filter(isDynamic).map((o) => new Promise((r) => o.setSrc(dynamicURL(o), () => { o.set({ imageSmoothing: false }); r(); }))));
        sc.renderAll();

        const out = document.createElement('canvas');
        out.width = W(); out.height = H();
        const x = out.getContext('2d');
        x.fillStyle = '#fff'; x.fillRect(0, 0, out.width, out.height);
        x.drawImage(sc.getElement(), 0, 0);
        sc.dispose();
        return out;
    }

    function toMono(src, threshold, dither) {
        const c = document.createElement('canvas');
        c.width = src.width; c.height = src.height;
        const x = c.getContext('2d');
        x.fillStyle = '#fff'; x.fillRect(0, 0, c.width, c.height);
        x.drawImage(src, 0, 0);
        const img = x.getImageData(0, 0, c.width, c.height);
        const d = img.data, w = c.width, h = c.height;
        const g = new Float32Array(w * h);
        for (let i = 0; i < w * h; i++) { const j = i * 4; g[i] = d[j] * 0.299 + d[j + 1] * 0.587 + d[j + 2] * 0.114; }
        let black = 0;
        for (let y = 0; y < h; y++) {
            for (let xx = 0; xx < w; xx++) {
                const i = y * w + xx;
                const old = g[i];
                const v = old < threshold ? 0 : 255;
                if (dither) {
                    const err = old - v;
                    if (xx + 1 < w) g[i + 1] += err * 7 / 16;
                    if (y + 1 < h) {
                        if (xx > 0) g[i + w - 1] += err * 3 / 16;
                        g[i + w] += err * 5 / 16;
                        if (xx + 1 < w) g[i + w + 1] += err / 16;
                    }
                }
                const j = i * 4;
                d[j] = d[j + 1] = d[j + 2] = v; d[j + 3] = 255;
                if (!v) black++;
            }
        }
        x.putImageData(img, 0, 0);
        c.blackRatio = black / (w * h);
        return c;
    }

    let previewSrc = null, previewMono = null;
    function paintPreview() {
        if (!previewSrc) return;
        previewMono = toMono(previewSrc, state.threshold, state.dither);
        const pc = $('#previewCanvas');
        pc.width = previewMono.width; pc.height = previewMono.height;
        pc.getContext('2d').drawImage(previewMono, 0, 0);
        let s = Math.min(560 / pc.width, 420 / pc.height);
        if (s >= 1) s = Math.floor(s);
        pc.style.width = Math.round(pc.width * s) + 'px';
        pc.style.height = Math.round(pc.height * s) + 'px';
        $('#printInfo').textContent = `${pc.width} × ${pc.height} dots · ${state.widthMm} × ${state.heightMm} mm · ${Math.round(previewMono.blackRatio * 100)}% ink coverage`;
        $('#downloadPng').href = previewMono.toDataURL('image/png');
        $('#downloadPng').download = (state.name || 'label').replace(/[^\w\- ]+/g, '') + '.png';
        $('#thresholdVal').textContent = Math.round(((state.threshold - 60) / 170) * 100) + '%';
    }

    async function openPrint() {
        const a = canvas.getActiveObject();
        if (a && a.isEditing) a.exitEditing();
        const sel = $('#printPrinter');
        sel.innerHTML = (CFG.printers || []).map((p) => `<option value="${p.id}" ${p.id == state.printerId ? 'selected' : ''}>${esc(p.name)}</option>`).join('')
            || '<option value="">No active printers — add one in the panel</option>';
        $('#printCopies').value = state.copies;
        $('#printThreshold').value = state.threshold;
        $('#printDither').checked = state.dither;
        $('#printInfo').textContent = 'Rendering…';
        $('#printDialog').showModal();
        refreshPrinterStatus();
        previewSrc = await renderLabelCanvas();
        paintPreview();
    }

    async function doPrint() {
        if (!state.printerId) { toast('No printer selected', 'Add or activate a printer under Printing & Labels → Printers.', 'error'); return; }
        if (!previewMono) return;
        const btn = $('#btnDoPrint');
        btn.disabled = true;
        const res = await api(CFG.urls.print, {
            printer_id: state.printerId,
            copies: clamp(parseInt(state.copies, 10) || 1, 1, 500),
            width_mm: state.widthMm, height_mm: state.heightMm,
            gap_mm: state.gapMm, direction: state.direction,
            image_data: previewMono.toDataURL('image/png'),
        });
        btn.disabled = false;
        if (res.success) {
            toast('Sent to printer', res.message, 'success');
            $('#printDialog').close();
        } else {
            toast('Print failed', res.message || 'Unknown error', 'error');
        }
        refreshPrinterStatus();
    }

    /* ------------------------------------------- Client-Side WebUSB Direct Print */
    async function doUsbPrint() {
        if (!navigator.usb) {
            toast('WebUSB not supported', 'Please use Brave or Chrome on Mac/Windows. If using Brave, verify WebUSB permissions in brave://settings/content/usb.', 'error');
            return;
        }
        if (!previewMono) return;

        const btn = $('#btnUsbPrint');
        btn.disabled = true;
        btn.textContent = 'Selecting USB…';

        let device = null;
        try {
            // Check for previously paired USB devices first
            const paired = await navigator.usb.getDevices();
            if (paired && paired.length > 0) {
                // Look for Deli or USB printer
                device = paired.find(d => (d.vendorId === 0x0471 || d.vendorId === 0x1137 || d.productName?.toLowerCase().includes('printer') || d.manufacturerName?.toLowerCase().includes('deli'))) || paired[0];
            }

            // Prompt user if no matching paired device found
            if (!device) {
                device = await navigator.usb.requestDevice({
                    filters: [
                        { vendorId: 0x1137 }, // Deli Printer vendor ID
                        { vendorId: 0x0471 },
                        { classCode: 0x07 },  // USB Printer Class
                    ],
                }).catch(async () => {
                    // Fallback: allow selecting from any connected USB device
                    return await navigator.usb.requestDevice({ filters: [] });
                });
            }

            if (!device) {
                btn.disabled = false;
                btn.innerHTML = `${icon('usb')} Direct USB Print`;
                return;
            }

            btn.textContent = 'Connecting…';
            await device.open();

            // Select configuration #1 if not selected
            if (device.configuration === null) {
                await device.selectConfiguration(1);
            }

            // Find USB printer interface and bulk OUT endpoint
            let printerInterface = null;
            let outEndpoint = null;

            for (const iface of device.configuration.interfaces) {
                for (const alt of iface.alternates) {
                    for (const ep of alt.endpoints) {
                        if (ep.direction === 'out' && ep.type === 'bulk') {
                            printerInterface = iface;
                            outEndpoint = ep;
                            break;
                        }
                    }
                    if (outEndpoint) break;
                }
                if (outEndpoint) break;
            }

            if (!printerInterface || !outEndpoint) {
                throw new Error('No writable USB bulk endpoint found on ' + (device.productName || 'printer'));
            }

            // Claim interface
            await device.claimInterface(printerInterface.interfaceNumber);

            btn.textContent = 'Sending to printer…';

            // Generate TSPL binary raster
            const wDots = previewMono.width;
            const hDots = previewMono.height;
            const wBytes = Math.ceil(wDots / 8);
            const ctx = previewMono.getContext('2d');
            const imgData = ctx.getImageData(0, 0, wDots, hDots).data;

            const header = `SIZE ${state.widthMm} mm, ${state.heightMm} mm\nGAP ${state.gapMm} mm, 0 mm\nDIRECTION ${state.direction}\nCLS\nBITMAP 0,0,${wBytes},${hDots},0,`;
            const footer = `\nPRINT ${clamp(parseInt(state.copies, 10) || 1, 1, 500)}\n`;

            const encoder = new TextEncoder();
            const headerBytes = encoder.encode(header);
            const footerBytes = encoder.encode(footer);

            const bitmapBytes = new Uint8Array(wBytes * hDots);
            for (let y = 0; y < hDots; y++) {
                for (let xByte = 0; xByte < wBytes; xByte++) {
                    let byte = 0;
                    for (let bit = 0; bit < 8; bit++) {
                        const xx = xByte * 8 + bit;
                        if (xx < wDots) {
                            const idx = (y * wDots + xx) * 4;
                            if (imgData[idx] > 128) {
                                byte |= (1 << (7 - bit));
                            }
                        } else {
                            byte |= (1 << (7 - bit));
                        }
                    }
                    bitmapBytes[y * wBytes + xByte] = byte;
                }
            }

            const total = new Uint8Array(headerBytes.length + bitmapBytes.length + footerBytes.length);
            total.set(headerBytes, 0);
            total.set(bitmapBytes, headerBytes.length);
            total.set(footerBytes, headerBytes.length + bitmapBytes.length);

            // Transfer in chunks of 512 bytes (standard USB packet size)
            const CHUNK_SIZE = 512;
            for (let offset = 0; offset < total.length; offset += CHUNK_SIZE) {
                const chunk = total.slice(offset, offset + CHUNK_SIZE);
                await device.transferOut(outEndpoint.endpointNumber, chunk);
            }

            toast('Direct USB Print Dispatched', `Label sent directly via USB to ${device.productName || 'Deli Printer'}!`, 'success');
            $('#printDialog').close();

            // Release interface
            await device.releaseInterface(printerInterface.interfaceNumber);
            await device.close();
        } catch (err) {
            toast('USB Print Error', err.message || 'Device communication failed', 'error');
            if (device && device.opened) {
                try { await device.close(); } catch (_) {}
            }
        } finally {
            btn.disabled = false;
            btn.innerHTML = `${icon('usb')} Direct USB Print`;
        }
    }

    /* ------------------------------------------- Wireless Web Bluetooth Print */
    async function doBluetoothPrint() {
        if (!navigator.bluetooth) {
            const dlg = $('#bluetoothHelpDialog');
            if (dlg) {
                dlg.showModal();
            } else {
                toast('Brave Flag Required', 'Open brave://flags/#enable-web-bluetooth in a new tab, set to Enabled, and restart Brave.', 'error');
            }
            return;
        }
        if (!previewMono) return;

        const btn = $('#btnBluetoothPrint');
        btn.disabled = true;
        btn.textContent = 'Searching…';

        try {
            // Request Bluetooth device with standard printer services or accept all
            const device = await navigator.bluetooth.requestDevice({
                filters: [
                    { namePrefix: 'Deli' },
                    { namePrefix: '886' },
                    { namePrefix: 'Printer' },
                ],
                optionalServices: [
                    '000018f0-0000-1000-8000-00805f9b34fb', // Standard Print service
                    '49535343-fe7d-4ae5-8fa9-9fafd205e455', // ISSC transparent UART
                    '0000e0ff-0000-1000-8000-00805f9b34fb',
                    '0000ff00-0000-1000-8000-00805f9b34fb',
                ],
                acceptAllDevices: false,
            }).catch(async () => {
                // Fallback: prompt for any paired Bluetooth device
                return await navigator.bluetooth.requestDevice({
                    acceptAllDevices: true,
                    optionalServices: [
                        '000018f0-0000-1000-8000-00805f9b34fb',
                        '49535343-fe7d-4ae5-8fa9-9fafd205e455',
                        '0000e0ff-0000-1000-8000-00805f9b34fb',
                        '0000ff00-0000-1000-8000-00805f9b34fb',
                    ],
                });
            });

            if (!device) {
                btn.disabled = false;
                btn.innerHTML = `${icon('bluetooth')} Bluetooth Print`;
                return;
            }

            btn.textContent = 'Connecting…';
            const server = await device.gatt.connect();

            // Find first writable characteristic
            let writeChar = null;
            const services = await server.getPrimaryServices().catch(() => []);
            for (const s of services) {
                const chars = await s.getCharacteristics().catch(() => []);
                for (const c of chars) {
                    if (c.properties.write || c.properties.writeWithoutResponse) {
                        writeChar = c;
                        break;
                    }
                }
                if (writeChar) break;
            }

            if (!writeChar) {
                throw new Error('Could not find a writable Bluetooth printing characteristic on ' + device.name);
            }

            btn.textContent = 'Sending dots…';

            // Generate TSPL raster binary
            const wDots = previewMono.width;
            const hDots = previewMono.height;
            const wBytes = Math.ceil(wDots / 8);
            const ctx = previewMono.getContext('2d');
            const imgData = ctx.getImageData(0, 0, wDots, hDots).data;

            const header = `SIZE ${state.widthMm} mm, ${state.heightMm} mm\nGAP ${state.gapMm} mm, 0 mm\nDIRECTION ${state.direction}\nCLS\nBITMAP 0,0,${wBytes},${hDots},0,`;
            const footer = `\nPRINT ${clamp(parseInt(state.copies, 10) || 1, 1, 500)}\n`;

            const encoder = new TextEncoder();
            const headerBytes = encoder.encode(header);
            const footerBytes = encoder.encode(footer);

            const bitmapBytes = new Uint8Array(wBytes * hDots);
            for (let y = 0; y < hDots; y++) {
                for (let xByte = 0; xByte < wBytes; xByte++) {
                    let byte = 0;
                    for (let bit = 0; bit < 8; bit++) {
                        const xx = xByte * 8 + bit;
                        if (xx < wDots) {
                            const idx = (y * wDots + xx) * 4;
                            if (imgData[idx] > 128) {
                                byte |= (1 << (7 - bit));
                            }
                        } else {
                            byte |= (1 << (7 - bit));
                        }
                    }
                    bitmapBytes[y * wBytes + xByte] = byte;
                }
            }

            const total = new Uint8Array(headerBytes.length + bitmapBytes.length + footerBytes.length);
            total.set(headerBytes, 0);
            total.set(bitmapBytes, headerBytes.length);
            total.set(footerBytes, headerBytes.length + bitmapBytes.length);

            // Chunk transfer over Bluetooth BLE MTU (typically 128 to 512 bytes)
            const CHUNK_SIZE = 120;
            for (let offset = 0; offset < total.length; offset += CHUNK_SIZE) {
                const chunk = total.slice(offset, offset + CHUNK_SIZE);
                if (writeChar.writeValueWithoutResponse) {
                    await writeChar.writeValueWithoutResponse(chunk);
                } else {
                    await writeChar.writeValue(chunk);
                }
                // Short throttle to prevent buffer overrun on thermal microcontroller
                await new Promise((r) => setTimeout(r, 15));
            }

            toast('Bluetooth print dispatched', `Sent to ${device.name || 'Deli Printer'}!`, 'success');
            $('#printDialog').close();
            setTimeout(() => device.gatt?.disconnect(), 2000);
        } catch (err) {
            toast('Bluetooth error', err.message || 'Connection cancelled', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = `${icon('bluetooth')} Bluetooth Print`;
        }
    }

    function bindPrintDialog() {
        $('#printPrinter').addEventListener('change', (e) => { state.printerId = e.target.value ? +e.target.value : null; refreshPrinterStatus(); });
        $('#printCopies').addEventListener('input', (e) => { state.copies = e.target.value; });
        $('#printThreshold').addEventListener('input', (e) => { state.threshold = +e.target.value; paintPreview(); savePrefs(); });
        $('#printDither').addEventListener('change', (e) => { state.dither = e.target.checked; paintPreview(); savePrefs(); });
        $('#btnDoPrint').addEventListener('click', doPrint);
        $('#btnBluetoothPrint')?.addEventListener('click', doBluetoothPrint);
        $('#btnUsbPrint')?.addEventListener('click', doUsbPrint);

        // Copy button in Bluetooth helper dialog
        const copyBtn = $('#btnCopyFlagUrl');
        const copiedBtn = $('#btnCopiedAndClose');
        const input = $('#flagUrlInput');
        const doCopy = () => {
            if (input) {
                input.select();
                navigator.clipboard?.writeText(input.value);
                toast('URL Copied!', 'Paste brave://flags/#enable-web-bluetooth into a new tab in Brave.', 'success');
            }
        };
        copyBtn?.addEventListener('click', doCopy);
        input?.addEventListener('click', doCopy);
        copiedBtn?.addEventListener('click', () => {
            doCopy();
            $('#bluetoothHelpDialog')?.close();
        });
    }

    /* --------------------------------------------------------- Save / open */
    function renderTemplatePicker() {
        const sel = $('#templatePicker');
        sel.innerHTML = '<option value="">Open template…</option>'
            + (CFG.templates || []).map((t) => `<option value="${t.id}" ${t.id === state.id ? 'selected' : ''}>${esc(t.name)} · ${esc(t.size)}</option>`).join('');
    }
    async function save(asNew = false) {
        const a = canvas.getActiveObject();
        if (a && a.isEditing) a.exitEditing();
        let name = ($('#docName').value || '').trim() || 'Untitled label';
        if (asNew) {
            const n = prompt('Save as a new template — name:', name + ' copy');
            if (!n) return;
            name = n.trim();
        }
        const res = await api(CFG.urls.save, {
            id: asNew ? null : state.id,
            name,
            category: state.category,
            width_mm: state.widthMm,
            height_mm: state.heightMm,
            gap_mm: state.gapMm,
            direction: state.direction,
            elements: { ...canvas.toJSON(PROPS), studio: { version: 1, vars: state.vars } },
        });
        if (!res.success) { toast('Save failed', res.message, 'error'); return; }
        state.id = res.template.id;
        state.name = res.template.name;
        $('#docName').value = state.name;
        document.title = `${state.name} · Label Studio`;
        const list = CFG.templates || (CFG.templates = []);
        const idx = list.findIndex((t) => t.id === res.template.id);
        if (idx >= 0) list[idx] = res.template; else list.push(res.template);
        list.sort((x, y) => x.name.localeCompare(y.name));
        renderTemplatePicker();
        history.replaceState(null, '', `${CFG.urls.studio}/${state.id}`);
        setDirty(false);
        toast('Saved', res.message, 'success');
    }
    const confirmLeave = () => !state.dirty || confirm('You have unsaved changes. Leave anyway?');

    /* ---------------------------------------------------- Legacy designs */
    async function fromLegacy(elements) {
        const f = 8 / 5.5; // old preview used 5.5 px per mm
        for (const el of elements || []) {
            const x = (el.x || 0) * f, y = (el.y || 0) * f;
            let o = null;
            if (el.type === 'text') {
                o = new fabric.IText(el.content || '', { left: x, top: y, fontSize: (el.fontSize || 14) * f, fontWeight: el.bold ? 'bold' : 'normal', fontFamily: 'Arial', fill: '#000000', elType: 'text' });
            } else if (el.type === 'line') {
                o = new fabric.Rect({ left: x, top: y, width: (el.width || 100) * f, height: Math.max(2, (el.height || 2) * f), fill: '#000000', strokeWidth: 0, elType: 'rect', layerName: 'Line' });
            } else if (el.type === 'box') {
                o = new fabric.Rect({ left: x, top: y, width: (el.width || 80) * f, height: (el.height || 40) * f, fill: '', stroke: '#000000', strokeWidth: (el.thickness || 2) * f, strokeUniform: true, elType: 'rect' });
            } else if (el.type === 'barcode') {
                o = await createDynamic('barcode', { barcodeValue: el.content || '{{barcode}}', barcodeFormat: 'CODE128', barcodeModule: 2, barcodeHeight: Math.round((el.height || 40) * f), barcodeShowText: true });
                o.set({ left: x, top: y });
            } else if (el.type === 'qrcode') {
                o = await createDynamic('qr', { qrValue: el.content || '', qrCell: 4, qrEcc: 'M' });
                o.set({ left: x, top: y });
            }
            if (o) canvas.add(o);
        }
    }

    /* ------------------------------------------------------------- Actions */
    const actions = {
        new: () => { if (confirmLeave()) { state.dirty = false; location.href = `${CFG.urls.studio}?new=1`; } },
        save: () => save(false),
        saveAs: () => save(true),
        undo, redo,
        openQueue: openQueueModal,
        preview: openPrint,
        zoomIn: () => setZoom(state.zoom * 1.25),
        zoomOut: () => setZoom(state.zoom / 1.25),
        zoomFit,
        zoomActual: () => setZoom(1),
        duplicate,
        delete: deleteSel,
        bringFront: () => { const o = canvas.getActiveObject(); if (o) { canvas.bringToFront(o); renderLayers(); pushHistory(); } },
        sendBack: () => { const o = canvas.getActiveObject(); if (o) { canvas.sendToBack(o); renderLayers(); pushHistory(); } },
        align: (el) => align(el.dataset.dir),
        insertVar: (el) => insertVar(el.dataset.var),
        upload: () => { pendingImagePoint = null; $('#imageInput').click(); },
        addAsset: (el) => addImageFromURL(el.dataset.url, el.title),
        addSymbol: (el) => addSymbol(+el.dataset.i),
        addVar: () => {
            const k = (prompt('Variable name (letters, numbers, underscore):') || '').trim().replace(/[^a-zA-Z0-9_]/g, '_');
            if (!k) return;
            if (!(k in state.vars)) state.vars[k] = '';
            renderVars(); renderProps(); setDirty(true);
        },
        preset: (el) => setSize(el.dataset.w, el.dataset.h),
        toggleInk: () => { state.ink = state.ink === '#000000' ? '#ffffff' : '#000000'; renderInk(); applyToolMode(); },
        toggleOpt: (el) => { const k = el.dataset.opt; state[k] = !state[k]; savePrefs(); renderOptions(); canvas.requestRenderAll(); },
        togglePanels: () => $('#panels').classList.toggle('open'),
        fitImage: () => {
            const o = canvas.getActiveObject();
            if (!o) return;
            const s = Math.min(W() / o.width, H() / o.height);
            o.set({ scaleX: s, scaleY: s });
            o.setCoords();
            align('c'); align('m');
            renderProps();
        },
        reconvertBW: () => {
            const o = canvas.getActiveObject();
            if (!o || o.elType !== 'image') return;
            o.imgThreshold = 140;
            o.imgDither = false;
            o.imgInvert = false;
            o.imgRemoveWhite = false;
            updateImageMonochrome(o);
            renderProps();
        },
    };
    document.addEventListener('click', (e) => {
        const a = e.target.closest('[data-action]');
        if (!a || !actions[a.dataset.action]) return;
        e.preventDefault();
        actions[a.dataset.action](a, e);
    });

    /* ------------------------------------------------------------ Keyboard */
    document.addEventListener('keydown', (e) => {
        const tag = (e.target.tagName || '').toLowerCase();
        const typing = ['input', 'textarea', 'select'].includes(tag) || e.target.isContentEditable;
        const active = canvas.getActiveObject();
        const editing = active && active.isEditing;
        const mod = e.metaKey || e.ctrlKey;
        const k = e.key.toLowerCase();
        if ($('#printDialog').open) return;

        if (mod && k === 's') { e.preventDefault(); save(e.shiftKey); return; }
        if (mod && k === 'p') { e.preventDefault(); openPrint(); return; }
        if (typing || editing) return;

        if (mod && k === 'z') { e.preventDefault(); e.shiftKey ? redo() : undo(); return; }
        if (mod && k === 'y') { e.preventDefault(); redo(); return; }
        if (mod && k === 'c') { copySel(); return; }
        if (mod && k === 'v') { e.preventDefault(); paste(); return; }
        if (mod && k === 'd') { e.preventDefault(); duplicate(); return; }
        if (mod && k === 'a') { e.preventDefault(); selectAll(); return; }
        if (mod && (k === '=' || k === '+')) { e.preventDefault(); setZoom(state.zoom * 1.25); return; }
        if (mod && k === '-') { e.preventDefault(); setZoom(state.zoom / 1.25); return; }
        if (mod && k === '0') { e.preventDefault(); zoomFit(); return; }
        if (mod && k === '1') { e.preventDefault(); setZoom(1); return; }
        if (k === 'delete' || k === 'backspace') { e.preventDefault(); deleteSel(); return; }
        if (k === ' ') {
            e.preventDefault();
            if (!spaceDown) { spaceDown = true; applyToolMode(); }
            return;
        }
        if (k.startsWith('arrow') && active) {
            e.preventDefault();
            const step = e.shiftKey ? DPMM : 1;
            if (k === 'arrowleft') active.left -= step;
            if (k === 'arrowright') active.left += step;
            if (k === 'arrowup') active.top -= step;
            if (k === 'arrowdown') active.top += step;
            active.setCoords();
            canvas.requestRenderAll();
            updateGeomFields();
            scheduleHistory(300);
            return;
        }
        if (k === 'escape') { canvas.discardActiveObject(); setTool('select'); canvas.requestRenderAll(); return; }
        if (mod || e.altKey) return;
        if (k === 'x') { actions.toggleInk(); return; }
        const t = TOOLS.find((tt) => tt !== 'sep' && tt.key.toLowerCase() === k);
        if (t) setTool(t.id);
    });
    document.addEventListener('keyup', (e) => {
        if (e.key === ' ' && spaceDown) { spaceDown = false; applyToolMode(); }
    });
    window.addEventListener('beforeunload', (e) => {
        if (state.dirty) { e.preventDefault(); e.returnValue = ''; }
    });

    /* ---------------------------------------------------------------- Boot */
    function bindChrome() {
        $('#docName').value = state.name;
        $('#docName').addEventListener('input', (e) => { state.name = e.target.value; setDirty(true); });
        $('#docName').addEventListener('keydown', (e) => { if (e.key === 'Enter') e.target.blur(); });
        $('#templatePicker').addEventListener('change', (e) => {
            const id = e.target.value;
            if (!id) return;
            if (!confirmLeave()) { renderTemplatePicker(); return; }
            state.dirty = false;
            location.href = `${CFG.urls.studio}/${id}`;
        });
        $$('.panel-head').forEach((h) => h.addEventListener('click', () => { h.parentElement.classList.toggle('collapsed'); savePrefs(); }));
        (prefs.collapsed || ['assets', 'variables']).forEach((p) => $(`.panel[data-panel="${p}"]`)?.classList.add('collapsed'));
        // Hydrate static icons in the Blade markup
        $$('i[data-icon]').forEach((el) => { if (!el.firstChild) el.innerHTML = svg(el.dataset.icon); });

        // Drag & drop images directly onto the workspace
        const ws = $('#workspace');
        if (ws) {
            ws.addEventListener('dragover', (e) => {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'copy';
            });
            ws.addEventListener('drop', (e) => {
                e.preventDefault();
                const files = e.dataTransfer?.files;
                if (!files || !files.length) return;
                const file = files[0];
                if (!file.type.startsWith('image/')) return;
                const pointer = canvas.getPointer(e);
                const reader = new FileReader();
                reader.onload = () => {
                    addImageFromURL(reader.result, file.name.replace(/\.[^.]+$/, ''), pointer);
                };
                reader.readAsDataURL(file);
            });
        }
    }

    async function boot() {
        buildToolbar();
        buildLayersPanel();
        bindPropsPanel();
        bindOptionsBar();
        bindDocPanel();
        bindVarsPanel();
        bindPrintDialog();
        bindChrome();
        renderTemplatePicker();
        renderAssets();
        renderDoc();

        await loadFonts();

        state.muted++;
        let legacy = false;
        const els = T?.elements;
        if (els && !Array.isArray(els) && Array.isArray(els.objects)) {
            const { studio, ...json } = els;
            if (studio?.vars) state.vars = { ...DEFAULT_VARS, ...studio.vars };
            await new Promise((r) => canvas.loadFromJSON(json, r));
            canvas.forEachObject(applyLock);
            await refreshAllDynamic();
        } else if (Array.isArray(els) && els.length) {
            await fromLegacy(els);
            legacy = true;
        }
        canvas.backgroundColor = '#ffffff';

        // Automatically convert any existing image layer to thermal black & white
        for (const o of canvas.getObjects()) {
            if (o.elType === 'image') {
                if (!o.origSrc) o.origSrc = o.getSrc();
                if (o.imgThreshold === undefined) {
                    o.imgThreshold = 140;
                    o.imgDither = false;
                    o.imgInvert = false;
                    o.imgRemoveWhite = false;
                    await updateImageMonochrome(o);
                }
            }
        }

        state.muted--;

        renderVars();
        setZoom(1);
        zoomFit();
        setTool('select');
        renderLayers();
        renderProps();
        state.history = [snapshot()];
        updateUndoButtons();
        setDirty(legacy);
        if (legacy) toast('Design converted', 'This template was upgraded to the new studio format. Save to keep it.', 'info');
        document.title = `${state.name} · Label Studio`;

        refreshPrinterStatus();
        setInterval(refreshPrinterStatus, 15000);
        window.addEventListener('resize', debounce(drawRulers, 150));
    }

    boot();
})();


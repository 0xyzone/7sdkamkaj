@php
    $elements = $template->elements ?? [];
    $rawJson = json_encode($elements);
    // Find all {{variable}} patterns in elements
    preg_match_all('/\{\{([a-zA-Z0-9_\-]+)\}\}/', $rawJson, $matches);
    $usedVars = array_values(array_unique($matches[1] ?? []));
@endphp

<div
    x-data="quickPrintComponent_{{ $template->id }}()"
    x-init="init()"
    class="flex flex-col gap-5 text-sm"
    style="min-height: 480px;"
>
    {{-- Main Container (2-col desktop, 1-col mobile) --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
        {{-- Left: Live 1-bit Thermal Preview Canvas --}}
        <div class="lg:col-span-7 flex flex-col gap-3">
            <div
                class="rounded-xl border border-gray-800 p-4 flex flex-col items-center justify-center min-h-[300px] max-h-[460px] overflow-auto relative"
                style="background: repeating-conic-gradient(#18191d 0% 25%, #121316 0% 50%) 50% / 16px 16px;"
            >
                <div x-show="loading" class="absolute inset-0 bg-black/60 backdrop-blur-xs flex items-center justify-center z-10 text-xs text-gray-300">
                    <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Rendering thermal dots…
                </div>

                <canvas
                    id="qpCanvas_{{ $template->id }}"
                    class="rounded shadow-2xl bg-white max-w-full"
                    style="image-rendering: pixelated; box-shadow: 0 10px 35px rgba(0,0,0,0.6);"
                ></canvas>
            </div>

            {{-- Label Statistics Pill --}}
            <div class="flex items-center justify-between text-xs px-2 text-gray-400">
                <span x-text="statsText">Calculating…</span>
                <a
                    :href="downloadDataUrl"
                    :download="'{{ Str::slug($template->name) }}.png'"
                    class="text-red-400 hover:text-red-300 font-medium inline-flex items-center gap-1 transition-colors"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Download PNG
                </a>
            </div>
        </div>

        {{-- Right: Variable Inputs & Print Controls --}}
        <div class="lg:col-span-5 flex flex-col gap-4">
            {{-- Dynamic Variables Box (Only shows variables used in this design) --}}
            <div class="rounded-xl border border-gray-800 bg-[#0d0f14] p-3.5 flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-300 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        Design Variables
                    </span>
                    <span class="text-[10px] font-bold text-emerald-400 bg-emerald-950/60 border border-emerald-800/40 px-1.5 py-0.5 rounded">
                        Live Preview
                    </span>
                </div>

                <div class="flex flex-col gap-2.5 max-h-[220px] overflow-y-auto pr-1">
                    @forelse($usedVars as $var)
                        <div class="flex flex-col gap-1">
                            <label class="flex items-center justify-between text-xs font-medium text-gray-300">
                                <span>{{ ucwords(str_replace(['_', '-'], ' ', $var)) }}</span>
                                <code class="text-[10px] text-blue-400 font-mono bg-gray-900 px-1.5 py-0.5 rounded border border-gray-800">&#123;&#123;{{ $var }}&#125;&#125;</code>
                            </label>
                            <input
                                type="text"
                                x-model="vars['{{ $var }}']"
                                @input.debounce.70ms="renderPreview()"
                                class="w-full bg-[#13151b] border border-gray-700/80 rounded-lg px-2.5 py-1.5 text-xs text-white placeholder-gray-500 focus:border-red-500 focus:outline-none transition-colors"
                                placeholder="{{ ucwords(str_replace(['_', '-'], ' ', $var)) }}…"
                            >
                        </div>
                    @empty
                        <p class="text-xs text-gray-500 italic py-2">
                            This template contains static artwork only (no &#123;&#123;variables&#125;&#125; detected).
                        </p>
                    @endforelse
                </div>
            </div>

            {{-- Thermal Darkness & Print Options --}}
            <div class="rounded-xl border border-gray-800 bg-[#0d0f14] p-3.5 flex flex-col gap-3">
                <div class="grid grid-cols-2 gap-3">
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-medium text-gray-400">Copies</label>
                        <input
                            type="number"
                            min="1"
                            max="500"
                            x-model.number="copies"
                            class="w-full bg-[#13151b] border border-gray-700/80 rounded-lg px-2.5 py-1.5 text-xs text-white focus:border-red-500 focus:outline-none"
                        >
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="flex items-center justify-between text-xs font-medium text-gray-400">
                            <span>Darkness</span>
                            <span class="text-gray-300 font-mono text-[11px]" x-text="Math.round(((threshold - 60) / 170) * 100) + '%'"></span>
                        </label>
                        <input
                            type="range"
                            min="60"
                            max="230"
                            step="5"
                            x-model.number="threshold"
                            @input="renderPreview()"
                            class="w-full accent-red-500 cursor-pointer mt-1"
                        >
                    </div>
                </div>

                <label class="flex items-center gap-2 text-xs text-gray-300 cursor-pointer select-none">
                    <input
                        type="checkbox"
                        x-model="dither"
                        @change="renderPreview()"
                        class="rounded border-gray-700 bg-gray-900 text-red-600 focus:ring-0"
                    >
                    <span>Dither photos / gradients (Floyd-Steinberg)</span>
                </label>

                {{-- Server Printer Selector --}}
                <div class="flex flex-col gap-1 pt-1 border-t border-gray-800/80">
                    <label class="text-xs font-medium text-gray-400">Server Spool Printer</label>
                    <select
                        x-model="selectedPrinterId"
                        class="w-full bg-[#13151b] border border-gray-700/80 rounded-lg px-2.5 py-1.5 text-xs text-white focus:border-red-500 focus:outline-none"
                    >
                        @forelse($printers as $p)
                            <option value="{{ $p->id }}">{{ $p->name }} ({{ strtoupper($p->type) }})</option>
                        @empty
                            <option value="">No server printers registered</option>
                        @endforelse
                    </select>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex flex-col gap-2 pt-1">
                {{-- Direct USB Print (WebUSB) --}}
                <button
                    type="button"
                    @click="printUsb()"
                    :disabled="printingUsb"
                    class="w-full py-2.5 px-4 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-medium text-xs flex items-center justify-center gap-2 transition shadow-md disabled:opacity-50 cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4M12 5v14"/>
                    </svg>
                    <span x-text="printingUsb ? 'Sending via USB…' : 'Direct USB Print'"></span>
                </button>

                {{-- Wireless Bluetooth Print --}}
                <button
                    type="button"
                    @click="printBluetooth()"
                    :disabled="printingBt"
                    class="w-full py-2.5 px-4 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-medium text-xs flex items-center justify-center gap-2 transition shadow-md disabled:opacity-50 cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19l6-6-6-6M12 5v14"/>
                    </svg>
                    <span x-text="printingBt ? 'Connecting Bluetooth…' : 'Bluetooth Print (Deli / BLE)'"></span>
                </button>

                {{-- Server Spool & Studio Links --}}
                <div class="grid grid-cols-2 gap-2">
                    <button
                        type="button"
                        @click="printServer()"
                        :disabled="printingServer || !selectedPrinterId"
                        class="py-2 px-3 rounded-lg bg-gray-800 hover:bg-gray-700 text-gray-200 font-medium text-xs flex items-center justify-center gap-1.5 transition disabled:opacity-50 cursor-pointer border border-gray-700"
                    >
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        <span x-text="printingServer ? 'Spooling…' : 'Server Spool'"></span>
                    </button>

                    <a
                        href="{{ url('/label-studio/' . $template->id) }}"
                        target="_blank"
                        class="py-2 px-3 rounded-lg bg-red-600/20 hover:bg-red-600/30 text-red-400 border border-red-500/30 font-medium text-xs flex items-center justify-center gap-1.5 transition text-center"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                        </svg>
                        Edit in Studio
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function quickPrintComponent_{{ $template->id }}() {
    return {
        templateId: {{ $template->id }},
        widthMm: {{ (float) $template->width_mm }},
        heightMm: {{ (float) $template->height_mm }},
        gapMm: {{ (float) ($template->gap_mm ?? 2.0) }},
        direction: {{ (int) ($template->direction ?? 1) }},
        elements: @json($template->elements ?? []),
        copies: 1,
        threshold: 140,
        dither: false,
        selectedPrinterId: '{{ \App\Models\Printer::where('is_active', true)->where('is_default', true)->first()?->id ?? (\App\Models\Printer::where('is_active', true)->first()?->id ?? '') }}',
        loading: true,
        printingUsb: false,
        printingBt: false,
        printingServer: false,
        statsText: '',
        downloadDataUrl: '',
        monoCanvas: null,

        vars: {
            brand: 'SEVEN SERIES',
            item_name: 'Authentic Denim Pant',
            article: 'SSD-701',
            size: '32',
            price: 'Rs. 2,499',
            mrp: 'Rs. 2,499',
            color: 'Indigo Blue',
            barcode: '890123456789',
            qr: 'https://7series.com.np',
        },

        async init() {
            await this.ensureDependencies();
            // Load saved studio vars if present in template
            if (this.elements && this.elements.studio && this.elements.studio.vars) {
                this.vars = { ...this.vars, ...this.elements.studio.vars };
            }
            await this.renderPreview();
        },

        async ensureDependencies() {
            const loadScript = (src) => new Promise((resolve) => {
                if (document.querySelector(`script[src="${src}"]`)) return resolve();
                const s = document.createElement('script');
                s.src = src;
                s.onload = resolve;
                document.head.appendChild(s);
            });

            if (!window.fabric) await loadScript('{{ asset('vendor/label-studio/fabric.min.js') }}');
            if (!window.JsBarcode) await loadScript('{{ asset('vendor/label-studio/JsBarcode.all.min.js') }}');
            if (!window.qrcode) await loadScript('{{ asset('vendor/label-studio/qrcode.min.js') }}');
        },

        interpolate(text) {
            if (typeof text !== 'string') return text;
            return text.replace(/\{\{([a-zA-Z0-9_\-]+)\}\}/g, (_, k) => this.vars[k] ?? '');
        },

        async renderPreview() {
            this.loading = true;
            try {
                const DPMM = 8; // 203 DPI
                const w = Math.round(this.widthMm * DPMM);
                const h = Math.round(this.heightMm * DPMM);

                // Create offscreen Fabric canvas
                const el = document.createElement('canvas');
                el.width = w; el.height = h;
                const fc = new fabric.StaticCanvas(el, { width: w, height: h, backgroundColor: '#ffffff' });

                if (this.elements && this.elements.objects) {
                    await new Promise((r) => fc.loadFromJSON(this.elements, r));
                }

                // Interpolate variables into text and barcode layers
                for (const o of fc.getObjects()) {
                    if (o.type === 'i-text' || o.type === 'text') {
                        o.set('text', this.interpolate(o.text));
                    } else if (o.elType === 'barcode' && window.JsBarcode) {
                        try {
                            const bcCanvas = document.createElement('canvas');
                            window.JsBarcode(bcCanvas, this.interpolate(o.barcodeValue || '890123456789'), {
                                format: o.barcodeFormat || 'CODE128',
                                width: o.barcodeModule || 2,
                                height: o.barcodeHeight || 80,
                                displayValue: !!o.barcodeShowText,
                                font: 'Arial',
                                fontSize: 18,
                                background: '#ffffff',
                                lineColor: '#000000',
                            });
                            o.setElement(bcCanvas);
                        } catch (_) {}
                    } else if (o.elType === 'qr' && window.qrcode) {
                        try {
                            const qr = window.qrcode(0, 'M');
                            qr.addData(this.interpolate(o.qrValue || 'https://7series.com.np'));
                            qr.make();
                            const qrCanvas = document.createElement('canvas');
                            const cell = o.qrCell || 4;
                            const n = qr.getModuleCount();
                            qrCanvas.width = qrCanvas.height = n * cell;
                            const qctx = qrCanvas.getContext('2d');
                            qctx.fillStyle = '#fff'; qctx.fillRect(0, 0, qrCanvas.width, qrCanvas.height);
                            qctx.fillStyle = '#000';
                            for (let r = 0; r < n; r++) {
                                for (let c = 0; c < n; c++) {
                                    if (qr.isDark(r, c)) qctx.fillRect(c * cell, r * cell, cell, cell);
                                }
                            }
                            o.setElement(qrCanvas);
                        } catch (_) {}
                    }
                }
                fc.renderAll();

                // Convert to 1-bit monochrome bitmap
                this.monoCanvas = this.toMono(fc.getElement(), this.threshold, this.dither);
                fc.dispose();

                // Paint onto preview canvas
                const target = document.getElementById('qpCanvas_{{ $template->id }}');
                if (target) {
                    target.width = this.monoCanvas.width;
                    target.height = this.monoCanvas.height;
                    const tctx = target.getContext('2d');
                    tctx.drawImage(this.monoCanvas, 0, 0);

                    // Responsive scale
                    const maxW = 460, maxH = 340;
                    let scale = Math.min(maxW / target.width, maxH / target.height);
                    if (scale >= 1) scale = Math.floor(scale);
                    target.style.width = Math.round(target.width * scale) + 'px';
                    target.style.height = Math.round(target.height * scale) + 'px';
                }

                this.downloadDataUrl = this.monoCanvas.toDataURL('image/png');
                this.statsText = `${w} × ${h} dots · ${this.widthMm} × ${this.heightMm} mm · ${Math.round((this.monoCanvas.blackRatio || 0) * 100)}% ink`;
            } catch (err) {
                console.error('Quick print preview error:', err);
            } finally {
                this.loading = false;
            }
        },

        toMono(src, threshold, dither) {
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
        },

        buildTsplData() {
            const wDots = this.monoCanvas.width;
            const hDots = this.monoCanvas.height;
            const wBytes = Math.ceil(wDots / 8);
            const ctx = this.monoCanvas.getContext('2d');
            const imgData = ctx.getImageData(0, 0, wDots, hDots).data;

            const header = `SIZE ${this.widthMm} mm, ${this.heightMm} mm\nGAP ${this.gapMm} mm, 0 mm\nDIRECTION ${this.direction}\nCLS\nBITMAP 0,0,${wBytes},${hDots},0,`;
            const footer = `\nPRINT ${Math.max(1, parseInt(this.copies, 10) || 1)}\n`;

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
                            if (imgData[idx] > 128) byte |= (1 << (7 - bit));
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
            return total;
        },

        async printUsb() {
            if (!navigator.usb) {
                alert('WebUSB is not supported in this browser. Please use Brave or Chrome on Mac/Windows.');
                return;
            }
            if (!this.monoCanvas) return;

            this.printingUsb = true;
            let device = null;
            try {
                const paired = await navigator.usb.getDevices();
                if (paired && paired.length > 0) {
                    device = paired.find((d) => (d.vendorId === 0x1137 || d.vendorId === 0x0471 || d.productName?.toLowerCase().includes('printer') || d.manufacturerName?.toLowerCase().includes('deli'))) || paired[0];
                }
                if (!device) {
                    device = await navigator.usb.requestDevice({
                        filters: [
                            { vendorId: 0x1137 },
                            { vendorId: 0x0471 },
                            { classCode: 0x07 },
                        ],
                    }).catch(() => navigator.usb.requestDevice({ filters: [] }));
                }

                if (!device) return;

                await device.open();
                if (device.configuration === null) await device.selectConfiguration(1);

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
                    throw new Error('No writable USB bulk printer endpoint found.');
                }

                await device.claimInterface(printerInterface.interfaceNumber);
                const total = this.buildTsplData();
                const CHUNK_SIZE = 512;
                for (let offset = 0; offset < total.length; offset += CHUNK_SIZE) {
                    const chunk = total.slice(offset, offset + CHUNK_SIZE);
                    await device.transferOut(outEndpoint.endpointNumber, chunk);
                }

                await device.releaseInterface(printerInterface.interfaceNumber);
                await device.close();
                alert(`Direct USB Print Dispatched to ${device.productName || 'Deli Printer'}!`);
            } catch (err) {
                alert('USB Print Error: ' + (err.message || 'Communication failed'));
                if (device && device.opened) {
                    try { await device.close(); } catch (_) {}
                }
            } finally {
                this.printingUsb = false;
            }
        },

        async printBluetooth() {
            const THERMAL_BLE_SERVICES = [
                '0000ffe0-0000-1000-8000-00805f9b34fb', // Deli & HM-10 UART Service
                '0000ff00-0000-1000-8000-00805f9b34fb', // Deli secondary UART
                '49535343-fe7d-4ae5-8fa9-9fafd205e455', // ISSC transparent UART (Deli/Xprinter)
                '6e400001-b5a3-f393-e0a9-e50e24dcca9e', // Nordic Semiconductor NUS
                '0000fee7-0000-1000-8000-00805f9b34fb', // Tencent WeChat IoT BLE thermal
                '0000ae00-0000-1000-8000-00805f9b34fb', // JL BLE UART
                '0000af00-0000-1000-8000-00805f9b34fb', // JL alternate
                '000018f0-0000-1000-8000-00805f9b34fb', // Standard Print Service
                '0000fff0-0000-1000-8000-00805f9b34fb', // General POS/Label BLE
                'e7810a71-73ae-499d-8c15-faa9aef0c3f2', // Vendor BLE
            ];

            if (!navigator.bluetooth) {
                const choice = confirm('Web Bluetooth is blocked by Brave default privacy settings.\n\nTo enable it:\n1. Open brave://flags/#enable-web-bluetooth in a new tab\n2. Set to Enabled & click Relaunch.\n\nClick OK to try paired Bluetooth Serial Port instead (no flags needed!), or Cancel to copy the flag URL.');
                if (choice) {
                    await this.printSerial();
                } else {
                    navigator.clipboard?.writeText('brave://flags/#enable-web-bluetooth');
                    alert('Copied "brave://flags/#enable-web-bluetooth" to clipboard! Paste into a new Brave tab, enable it, and restart Brave.');
                }
                return;
            }

            if (!this.monoCanvas) return;
            this.printingBt = true;

            try {
                const device = await navigator.bluetooth.requestDevice({
                    acceptAllDevices: true,
                    optionalServices: THERMAL_BLE_SERVICES,
                });

                if (!device) return;

                const server = await device.gatt.connect();

                let writeChar = null;
                for (const svcUuid of THERMAL_BLE_SERVICES) {
                    try {
                        const svc = await server.getPrimaryService(svcUuid);
                        if (svc) {
                            const chars = await svc.getCharacteristics();
                            for (const c of chars) {
                                if (c.properties.write || c.properties.writeWithoutResponse) {
                                    writeChar = c;
                                    break;
                                }
                            }
                        }
                    } catch (_) {}
                    if (writeChar) break;
                }

                if (!writeChar) {
                    try {
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
                    } catch (_) {}
                }

                if (!writeChar) {
                    throw new Error('Could not find writable BLE printing service on ' + (device.name || 'Bluetooth device'));
                }

                const total = this.buildTsplData();
                const CHUNK_SIZE = 120;
                for (let offset = 0; offset < total.length; offset += CHUNK_SIZE) {
                    const chunk = total.slice(offset, offset + CHUNK_SIZE);
                    if (writeChar.writeValueWithoutResponse) {
                        await writeChar.writeValueWithoutResponse(chunk);
                    } else {
                        await writeChar.writeValue(chunk);
                    }
                    await new Promise((r) => setTimeout(r, 15));
                }

                alert(`Bluetooth Print Dispatched to ${device.name || 'Deli Printer'}!`);
                setTimeout(() => device.gatt?.disconnect(), 2000);
            } catch (err) {
                if (err.name !== 'NotFoundError') {
                    alert('Bluetooth error: ' + (err.message || 'Connection cancelled'));
                }
            } finally {
                this.printingBt = false;
            }
        },

        async printSerial() {
            if (!navigator.serial) {
                alert('Web Serial is supported in Brave, Chrome, and Edge on Mac/Windows. Pair your printer in macOS Bluetooth settings first.');
                return;
            }
            if (!this.monoCanvas) return;

            try {
                const port = await navigator.serial.requestPort();
                await port.open({ baudRate: 9600 });
                const writer = port.writable.getWriter();
                const total = this.buildTsplData();
                await writer.write(total);
                writer.releaseLock();
                await port.close();
                alert('Bluetooth Serial Print Dispatched!');
            } catch (err) {
                if (err.name !== 'NotFoundError') {
                    alert('Serial Error: ' + (err.message || 'Communication failed'));
                }
            }
        },

        async printServer() {
            if (!this.selectedPrinterId) {
                alert('Please select a server printer.');
                return;
            }
            if (!this.monoCanvas) return;

            this.printingServer = true;
            try {
                const res = await fetch('{{ route('label-studio.print') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    body: JSON.stringify({
                        printer_id: parseInt(this.selectedPrinterId, 10),
                        copies: Math.max(1, parseInt(this.copies, 10) || 1),
                        width_mm: this.widthMm,
                        height_mm: this.heightMm,
                        gap_mm: this.gapMm,
                        direction: this.direction,
                        image_data: this.monoCanvas.toDataURL('image/png'),
                    }),
                });

                const data = await res.json();
                if (data.success) {
                    alert('Sent to printer: ' + data.message);
                } else {
                    alert('Print failed: ' + (data.message || 'Unknown error'));
                }
            } catch (err) {
                alert('Server print error: ' + err.message);
            } finally {
                this.printingServer = false;
            }
        },
    };
}
</script>

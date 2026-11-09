@php
    $elements = $template->elements ?? [];
    $rawJson = json_encode($elements);
    // Find all {{variable}} patterns in elements
    preg_match_all('/\{\{([a-zA-Z0-9_\-]+)\}\}/', $rawJson, $matches);
    $usedVars = array_values(array_unique($matches[1] ?? []));

    $config = [
        'templateId' => $template->id,
        'name' => $template->name,
        'widthMm' => (float) $template->width_mm,
        'heightMm' => (float) $template->height_mm,
        'gapMm' => (float) ($template->gap_mm ?? 2.0),
        'direction' => (int) ($template->direction ?? 1),
        'elements' => $template->elements ?? [],
        'selectedPrinterId' => (string) ($printers->where('is_default', true)->first()?->id ?? ($printers->first()?->id ?? '')),
    ];
@endphp

<script>
    if (typeof window.quickPrintComponent === 'function') {
        window.quickPrintComponent_{{ $template->id }} = function() {
            return window.quickPrintComponent(@js($config));
        };
    }
</script>

<div
    x-data="window.quickPrintComponent ? window.quickPrintComponent(@js($config)) : (window.quickPrintFallback ? window.quickPrintFallback(@js($config)) : { loading: true, printingBt: false, printingServer: false, printingUsb: false, vars: {}, statsText: '', copies: 1, threshold: 140, darknessLabel() { return '50%'; }, init() {}, renderPreview() {} })"
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
                    <div>
                        <label class="text-xs font-medium text-gray-400">Copies</label>
                        <input
                            type="number"
                            min="1"
                            max="999"
                            x-model.number="copies"
                            value="1"
                            class="w-full bg-[#13151b] border border-gray-700/80 rounded-lg px-2.5 py-1.5 text-xs text-white focus:border-red-500 focus:outline-none mt-1"
                        >
                    </div>

                    <div>
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-medium text-gray-400">Darkness</label>
                            <span class="text-[10px] text-gray-400 font-mono" x-text="darknessLabel()"></span>
                        </div>
                        <input
                            type="range"
                            min="60"
                            max="230"
                            step="5"
                            x-model.number="threshold"
                            value="140"
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
                {{-- Share Directly to Deli App / RawBT (iOS / Android) --}}
                <button
                    type="button"
                    @click="sharePrint()"
                    class="w-full py-2.5 px-4 rounded-lg bg-teal-600 hover:bg-teal-500 text-white font-medium text-xs flex items-center justify-center gap-2 transition shadow-md cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/>
                        <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
                    </svg>
                    <span>Share to Deli App (Mobile 1-Tap)</span>
                </button>

                {{-- AirPrint / System Print Spooler --}}
                <button
                    type="button"
                    @click="systemPrint()"
                    class="w-full py-2 px-4 rounded-lg bg-slate-700 hover:bg-slate-600 text-gray-200 font-medium text-xs flex items-center justify-center gap-2 transition shadow-md cursor-pointer border border-slate-600"
                >
                    <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    <span>AirPrint / System Print</span>
                </button>

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
                    <span x-show="!printingUsb">Direct USB Print</span>
                    <span x-show="printingUsb" style="display:none;">Sending via USB…</span>
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
                    <span x-show="!printingBt">Bluetooth Print (Deli / BLE)</span>
                    <span x-show="printingBt" style="display:none;">Connecting Bluetooth…</span>
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
                        <span x-show="!printingServer">Server Spool</span>
                        <span x-show="printingServer" style="display:none;">Spooling…</span>
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
    if (typeof window.quickPrintComponent === 'function') {
        window.quickPrintComponent_{{ $template->id }} = function() {
            return window.quickPrintComponent(@js($config));
        };
    }
</script>

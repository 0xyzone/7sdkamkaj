<x-filament-panels::page>
    <div
        x-data="labelStudio({
            widthMm: @entangle('width_mm'),
            heightMm: @entangle('height_mm'),
            elements: @js($elements)
        })"
        x-init="initStudio()"
        @elements-reloaded.window="reloadElements($event.detail.elements)"
        class="w-full flex flex-col gap-6"
    >
        <!-- Top Control Header Bar -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-2xl flex flex-wrap items-center justify-between gap-4">
            <!-- Left: Template Selector & Name -->
            <div class="flex items-center gap-4 flex-wrap">
                <div class="flex items-center gap-2">
                    <span class="p-2.5 rounded-xl bg-red-600/10 text-red-500 border border-red-500/20">
                        <x-heroicon-o-paint-brush class="w-6 h-6" />
                    </span>
                    <div>
                        <h2 class="text-lg font-bold text-white tracking-tight">Thermal Label Studio</h2>
                        <p class="text-xs text-slate-400">Interactive live garment tag design & direct hardware spooler</p>
                    </div>
                </div>

                <div class="h-8 w-px bg-slate-800 hidden md:block"></div>

                <!-- Template Loader Dropdown -->
                <div class="flex items-center gap-2">
                    <label class="text-xs font-semibold text-slate-400">Template:</label>
                    <select
                        wire:change="selectTemplate($event.target.value)"
                        class="bg-slate-950 border border-slate-700 text-white rounded-xl px-3 py-1.5 text-xs focus:ring-1 focus:ring-red-500 focus:outline-none"
                    >
                        @foreach ($this->getAvailableTemplates() as $tpl)
                            <option value="{{ $tpl->id }}" @selected($this->templateId === $tpl->id)>
                                {{ $tpl->name }} ({{ $tpl->width_mm }}×{{ $tpl->height_mm }}mm)
                            </option>
                        @endforeach
                    </select>

                    <button
                        type="button"
                        wire:click="resetToDefaultTemplate"
                        class="p-2 text-slate-400 hover:text-white bg-slate-800 hover:bg-slate-700 rounded-xl transition"
                        title="New Blank Design"
                    >
                        <x-heroicon-o-document-plus class="w-4 h-4" />
                    </button>
                </div>
            </div>

            <!-- Right: Actions (Save Template, Send Direct Print) -->
            <div class="flex items-center gap-3 flex-wrap">
                <!-- Save Template Button -->
                <button
                    type="button"
                    @click="$wire.saveTemplate(getSerializedElements())"
                    class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold rounded-xl border border-slate-700 transition flex items-center gap-2 shadow"
                >
                    <x-heroicon-o-bookmark class="w-4 h-4 text-emerald-400" />
                    <span>Save Template</span>
                </button>

                <!-- Direct Print Action Pill -->
                <div class="flex items-center gap-2 bg-slate-950 border border-slate-800 p-1 rounded-xl">
                    <select
                        wire:model="selectedPrinterId"
                        class="bg-transparent text-xs text-slate-200 border-none focus:ring-0 py-1 pl-2 pr-6"
                    >
                        @foreach ($this->getAvailablePrinters() as $prn)
                            <option value="{{ $prn->id }}" class="bg-slate-900 text-white">
                                {{ $prn->name }}
                            </option>
                        @endforeach
                    </select>

                    <input
                        type="number"
                        wire:model="copies"
                        min="1"
                        max="500"
                        class="w-14 bg-slate-900 text-center text-xs text-white border border-slate-800 rounded-lg py-1 px-1 focus:ring-red-500"
                        title="Copies"
                    />

                    <button
                        type="button"
                        @click="$wire.printDirect(getSerializedElements())"
                        class="px-4 py-1.5 bg-red-600 hover:bg-red-500 text-white text-xs font-bold rounded-lg transition flex items-center gap-1.5 shadow-lg shadow-red-600/30"
                    >
                        <x-heroicon-o-printer class="w-4 h-4" />
                        <span>Print</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Studio Main Workspace -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- Left Sidebar: Dimensions, Presets & Variable Simulation (3 Cols) -->
            <div class="lg:col-span-3 flex flex-col gap-5">
                <!-- Label Dimension Card -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-xl flex flex-col gap-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2">
                        <x-heroicon-o-adjustments-horizontal class="w-4 h-4 text-red-500" />
                        Label Size & Paper
                    </h3>

                    <!-- Standard Size Presets -->
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1.5">Standard Presets</label>
                        <div class="grid grid-cols-2 gap-1.5 text-xs">
                            <button type="button" @click="$wire.setPreset('50x30')" class="px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-left transition font-mono">
                                50 × 30 mm
                            </button>
                            <button type="button" @click="$wire.setPreset('50x70')" class="px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-left transition font-mono">
                                50 × 70 mm
                            </button>
                            <button type="button" @click="$wire.setPreset('40x30')" class="px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-left transition font-mono">
                                40 × 30 mm
                            </button>
                            <button type="button" @click="$wire.setPreset('60x40')" class="px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-left transition font-mono">
                                60 × 40 mm
                            </button>
                            <button type="button" @click="$wire.setPreset('80x50')" class="px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-left transition font-mono">
                                80 × 50 mm
                            </button>
                            <button type="button" @click="$wire.setPreset('100x150')" class="px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-left transition font-mono">
                                100 × 150 mm
                            </button>
                        </div>
                    </div>

                    <!-- Custom Dimensions -->
                    <div class="grid grid-cols-2 gap-3 pt-2 border-t border-slate-800">
                        <div>
                            <label class="block text-xs font-medium text-slate-300 mb-1">Width (mm)</label>
                            <input
                                type="number"
                                wire:model.live="width_mm"
                                min="20"
                                max="108"
                                class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-1.5 text-xs text-white font-mono focus:ring-1 focus:ring-red-500"
                            />
                            <span class="text-[10px] text-slate-500">Max nozzle: 108mm</span>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-300 mb-1">Height (mm)</label>
                            <input
                                type="number"
                                wire:model.live="height_mm"
                                min="15"
                                max="300"
                                class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-1.5 text-xs text-white font-mono focus:ring-1 focus:ring-red-500"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Template Title</label>
                        <input
                            type="text"
                            wire:model="name"
                            class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-1.5 text-xs text-white focus:ring-1 focus:ring-red-500"
                        />
                    </div>
                </div>

                <!-- Live Garment Simulation Variables Card -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-xl flex flex-col gap-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2">
                        <x-heroicon-o-variable class="w-4 h-4 text-amber-500" />
                        Garment Print Variables
                    </h3>
                    <p class="text-[11px] text-slate-400">Values interpolated during test and production printing:</p>

                    <div class="flex flex-col gap-2.5 text-xs">
                        <div>
                            <span class="text-[10px] font-mono text-slate-400 block mb-0.5">&#123;&#123;brand&#125;&#125;</span>
                            <input type="text" wire:model.live="sampleBrand" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-2.5 py-1 text-slate-200 text-xs" />
                        </div>
                        <div>
                            <span class="text-[10px] font-mono text-slate-400 block mb-0.5">&#123;&#123;item_name&#125;&#125;</span>
                            <input type="text" wire:model.live="sampleItemName" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-2.5 py-1 text-slate-200 text-xs" />
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <span class="text-[10px] font-mono text-slate-400 block mb-0.5">&#123;&#123;size&#125;&#125;</span>
                                <input type="text" wire:model.live="sampleSize" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-2.5 py-1 text-slate-200 text-xs" />
                            </div>
                            <div>
                                <span class="text-[10px] font-mono text-slate-400 block mb-0.5">&#123;&#123;price&#125;&#125;</span>
                                <input type="text" wire:model.live="samplePrice" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-2.5 py-1 text-slate-200 text-xs" />
                            </div>
                        </div>
                        <div>
                            <span class="text-[10px] font-mono text-slate-400 block mb-0.5">&#123;&#123;barcode&#125;&#125;</span>
                            <input type="text" wire:model.live="sampleBarcode" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-2.5 py-1 text-slate-200 text-xs font-mono" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Center: Interactive Canvas Studio (6 Cols) -->
            <div class="lg:col-span-6 flex flex-col gap-4">
                <!-- Floating Canvas Toolbar -->
                <div class="bg-slate-900 border border-slate-800 p-2.5 rounded-2xl shadow-xl flex flex-wrap items-center justify-between gap-2">
                    <!-- Tool Selectors -->
                    <div class="flex items-center gap-1">
                        <button
                            type="button"
                            @click="setTool('select')"
                            :class="currentTool === 'select' ? 'bg-red-600 text-white shadow-md shadow-red-600/30 ring-1 ring-red-400' : 'bg-slate-800 text-slate-300 hover:bg-slate-700'"
                            class="px-2.5 py-1.5 rounded-lg text-xs font-medium transition flex items-center gap-1.5"
                        >
                            <x-heroicon-o-cursor-arrow-rays class="w-3.5 h-3.5" />
                            <span>Select</span>
                        </button>

                        <button
                            type="button"
                            @click="setTool('pen')"
                            :class="currentTool === 'pen' ? 'bg-red-600 text-white shadow-md shadow-red-600/30 ring-1 ring-red-400' : 'bg-slate-800 text-slate-300 hover:bg-slate-700'"
                            class="px-2.5 py-1.5 rounded-lg text-xs font-medium transition flex items-center gap-1.5"
                        >
                            <x-heroicon-o-pencil class="w-3.5 h-3.5" />
                            <span>Pen</span>
                        </button>

                        <button
                            type="button"
                            @click="setTool('eraser')"
                            :class="currentTool === 'eraser' ? 'bg-red-600 text-white shadow-md shadow-red-600/30 ring-1 ring-red-400' : 'bg-slate-800 text-slate-300 hover:bg-slate-700'"
                            class="px-2.5 py-1.5 rounded-lg text-xs font-medium transition flex items-center gap-1.5"
                        >
                            <x-heroicon-o-backspace class="w-3.5 h-3.5" />
                            <span>Eraser</span>
                        </button>
                    </div>

                    <!-- Insert Elements -->
                    <div class="flex items-center gap-1 flex-wrap">
                        <button type="button" @click="addText('Seven Series')" class="px-2 py-1.5 bg-slate-800 hover:bg-slate-700 text-white rounded-lg text-xs font-medium transition flex items-center gap-1">
                            <span class="font-bold text-red-400">T</span> Text
                        </button>
                        <button type="button" @click="addBarcode('7S-DENIM-001')" class="px-2 py-1.5 bg-slate-800 hover:bg-slate-700 text-white rounded-lg text-xs font-medium transition flex items-center gap-1">
                            <x-heroicon-o-qr-code class="w-3.5 h-3.5 text-blue-400" /> Barcode
                        </button>
                        <button type="button" @click="addQRCode('https://sevenseries.com')" class="px-2 py-1.5 bg-slate-800 hover:bg-slate-700 text-white rounded-lg text-xs font-medium transition flex items-center gap-1">
                            <x-heroicon-o-view-columns class="w-3.5 h-3.5 text-emerald-400" /> QR
                        </button>
                        <button type="button" @click="addLine()" class="px-2 py-1.5 bg-slate-800 hover:bg-slate-700 text-white rounded-lg text-xs font-medium transition flex items-center gap-1">
                            <x-heroicon-o-minus class="w-3.5 h-3.5 text-amber-400" /> Line
                        </button>
                        <button type="button" @click="addBox()" class="px-2 py-1.5 bg-slate-800 hover:bg-slate-700 text-white rounded-lg text-xs font-medium transition flex items-center gap-1">
                            <x-heroicon-o-stop class="w-3.5 h-3.5 text-indigo-400" /> Box
                        </button>
                    </div>
                </div>

                <!-- Canvas Board Container -->
                <div class="bg-slate-950 rounded-2xl border border-slate-800 p-8 flex flex-col items-center justify-center min-h-[520px] overflow-auto shadow-2xl relative">
                    <div class="text-[11px] text-slate-500 mb-4 flex items-center justify-between w-full max-w-[480px]">
                        <span class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            203 DPI Thermal Head Grid
                        </span>
                        <span class="font-mono bg-slate-900 border border-slate-800 px-2 py-0.5 rounded text-slate-400">
                            <span x-text="widthMm"></span>mm × <span x-text="heightMm"></span>mm
                        </span>
                    </div>

                    <!-- Label Sticker Surface (Exact physical millimeter scale rendering) -->
                    <div
                        id="studio-sticker-surface"
                        class="relative bg-white rounded-md shadow-2xl border-2 border-slate-300 transition-all select-none overflow-hidden"
                        :style="`width: ${Math.round(widthMm * 5.6)}px; height: ${Math.round(heightMm * 5.6)}px;`"
                        @mousedown="handleCanvasMouseDown($event)"
                        @mousemove="handleCanvasMouseMove($event)"
                        @mouseup="handleCanvasMouseUp($event)"
                        @mouseleave="handleCanvasMouseUp($event)"
                    >
                        <!-- Precision Alignment Grid -->
                        <div class="absolute inset-0 bg-[radial-gradient(#d1d5db_1px,transparent_1px)] [background-size:12px_12px] opacity-60 pointer-events-none"></div>

                        <!-- Freehand Pen / Eraser Drawing HTML5 Canvas Layer -->
                        <canvas
                            id="studio-drawing-layer"
                            class="absolute inset-0 w-full h-full pointer-events-none"
                            :width="Math.round(widthMm * 8)"
                            :height="Math.round(heightMm * 8)"
                        ></canvas>

                        <!-- Elements Layer -->
                        <template x-for="(element, index) in elements" :key="element.id">
                            <div
                                class="absolute text-black font-sans cursor-move border transition-all"
                                :class="selectedElementIndex === index ? 'border-red-500 ring-2 ring-red-400/50 bg-red-50/20' : 'border-transparent hover:border-slate-400/60'"
                                :style="`left: ${element.x}px; top: ${element.y}px;`"
                                @mousedown.stop="selectElement(index, $event)"
                            >
                                <!-- Text Rendering with Variable Interpolation Preview -->
                                <template x-if="element.type === 'text'">
                                    <span
                                        :style="`font-size: ${element.fontSize || 14}px; font-weight: ${element.bold ? 'bold' : 'normal'};`"
                                        x-text="interpolateVariables(element.content)"
                                    ></span>
                                </template>

                                <!-- Barcode Rendering Preview -->
                                <template x-if="element.type === 'barcode'">
                                    <div class="flex flex-col items-center">
                                        <div class="flex items-center gap-[2px] h-9 px-1 bg-white">
                                            <div class="w-[2px] h-full bg-black"></div>
                                            <div class="w-[1px] h-full bg-black"></div>
                                            <div class="w-[3px] h-full bg-black"></div>
                                            <div class="w-[1px] h-full bg-black"></div>
                                            <div class="w-[2px] h-full bg-black"></div>
                                            <div class="w-[4px] h-full bg-black"></div>
                                            <div class="w-[2px] h-full bg-black"></div>
                                            <div class="w-[1px] h-full bg-black"></div>
                                            <div class="w-[3px] h-full bg-black"></div>
                                            <div class="w-[2px] h-full bg-black"></div>
                                        </div>
                                        <span class="text-[9px] font-mono tracking-widest mt-0.5" x-text="interpolateVariables(element.content)"></span>
                                    </div>
                                </template>

                                <!-- QR Code Rendering Preview -->
                                <template x-if="element.type === 'qrcode'">
                                    <div class="w-11 h-11 bg-white border border-black p-0.5 flex flex-col justify-between">
                                        <div class="flex justify-between">
                                            <div class="w-3 h-3 border-2 border-black p-0.5"><div class="w-full h-full bg-black"></div></div>
                                            <div class="w-3 h-3 border-2 border-black p-0.5"><div class="w-full h-full bg-black"></div></div>
                                        </div>
                                        <div class="text-[7px] text-center font-bold">QR</div>
                                        <div class="flex justify-between">
                                            <div class="w-3 h-3 border-2 border-black p-0.5"><div class="w-full h-full bg-black"></div></div>
                                            <div class="w-1.5 h-1.5 bg-black"></div>
                                        </div>
                                    </div>
                                </template>

                                <!-- Dividing Line -->
                                <template x-if="element.type === 'line'">
                                    <div :style="`width: ${element.width || 100}px; height: ${element.height || 2}px; background-color: black;`"></div>
                                </template>

                                <!-- Bounding Box -->
                                <template x-if="element.type === 'box'">
                                    <div :style="`width: ${element.width || 80}px; height: ${element.height || 40}px; border: ${element.thickness || 2}px solid black;`"></div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Right Sidebar: Active Element Customizer & Inspector (3 Cols) -->
            <div class="lg:col-span-3 flex flex-col gap-4">
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-xl flex flex-col gap-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-300 pb-2 border-b border-slate-800 flex items-center justify-between">
                        <span>Inspector & Style</span>
                        <span x-show="selectedElementIndex !== null" class="text-[10px] font-mono text-red-400 bg-red-950/60 px-2 py-0.5 rounded border border-red-800/40">
                            #<span x-text="selectedElementIndex + 1"></span>
                        </span>
                    </h3>

                    <template x-if="selectedElementIndex !== null">
                        <div class="flex flex-col gap-3 text-xs">
                            <div>
                                <label class="block font-medium text-slate-300 mb-1">Content / Variable</label>
                                <input
                                    type="text"
                                    x-model="elements[selectedElementIndex].content"
                                    class="w-full bg-slate-950 border border-slate-700 rounded-xl px-2.5 py-1.5 text-white focus:ring-1 focus:ring-red-500 focus:outline-none"
                                />
                                <div class="text-[10px] text-slate-400 mt-1">
                                    Use <code class="text-red-400">&#123;&#123;size&#125;&#125;</code>, <code class="text-red-400">&#123;&#123;price&#125;&#125;</code>, <code class="text-red-400">&#123;&#123;barcode&#125;&#125;</code>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block font-medium text-slate-300 mb-1">X Position (px)</label>
                                    <input
                                        type="number"
                                        x-model.number="elements[selectedElementIndex].x"
                                        class="w-full bg-slate-950 border border-slate-700 rounded-xl px-2.5 py-1.5 text-white"
                                    />
                                </div>
                                <div>
                                    <label class="block font-medium text-slate-300 mb-1">Y Position (px)</label>
                                    <input
                                        type="number"
                                        x-model.number="elements[selectedElementIndex].y"
                                        class="w-full bg-slate-950 border border-slate-700 rounded-xl px-2.5 py-1.5 text-white"
                                    />
                                </div>
                            </div>

                            <template x-if="elements[selectedElementIndex].type === 'text'">
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block font-medium text-slate-300 mb-1">Font Size</label>
                                        <input
                                            type="number"
                                            x-model.number="elements[selectedElementIndex].fontSize"
                                            class="w-full bg-slate-950 border border-slate-700 rounded-xl px-2.5 py-1.5 text-white"
                                            min="8"
                                            max="72"
                                        />
                                    </div>
                                    <div class="flex items-center gap-2 pt-5">
                                        <label class="flex items-center gap-1.5 cursor-pointer text-slate-200">
                                            <input
                                                type="checkbox"
                                                x-model="elements[selectedElementIndex].bold"
                                                class="rounded bg-slate-950 border-slate-700 text-red-600 focus:ring-0"
                                            />
                                            Bold
                                        </label>
                                    </div>
                                </div>
                            </template>

                            <template x-if="elements[selectedElementIndex].type === 'line' || elements[selectedElementIndex].type === 'box'">
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block font-medium text-slate-300 mb-1">Width (px)</label>
                                        <input
                                            type="number"
                                            x-model.number="elements[selectedElementIndex].width"
                                            class="w-full bg-slate-950 border border-slate-700 rounded-xl px-2.5 py-1.5 text-white"
                                        />
                                    </div>
                                    <div>
                                        <label class="block font-medium text-slate-300 mb-1">Height (px)</label>
                                        <input
                                            type="number"
                                            x-model.number="elements[selectedElementIndex].height"
                                            class="w-full bg-slate-950 border border-slate-700 rounded-xl px-2.5 py-1.5 text-white"
                                        />
                                    </div>
                                </div>
                            </template>

                            <div class="pt-3 border-t border-slate-800 flex gap-2">
                                <button
                                    type="button"
                                    @click="deleteSelectedElement()"
                                    class="w-full px-3 py-2 bg-red-600/80 hover:bg-red-500 text-white rounded-xl text-xs font-semibold transition"
                                >
                                    Delete Element
                                </button>
                            </div>
                        </div>
                    </template>

                    <template x-if="selectedElementIndex === null">
                        <div class="text-xs text-slate-500 py-10 text-center italic">
                            Select any element on the canvas to inspect and edit its properties.
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('labelStudio', (config) => ({
            widthMm: config.widthMm || 50,
            heightMm: config.heightMm || 30,
            elements: config.elements || [],
            currentTool: 'select',
            selectedElementIndex: null,
            isDrawing: false,
            isDragging: false,
            dragOffsetX: 0,
            dragOffsetY: 0,
            drawingCanvas: null,
            drawingCtx: null,

            initStudio() {
                this.$nextTick(() => {
                    this.initDrawingCanvas();
                });
            },

            reloadElements(newElements) {
                this.elements = Array.isArray(newElements) ? newElements : [];
                this.selectedElementIndex = null;
            },

            initDrawingCanvas() {
                this.drawingCanvas = document.getElementById('studio-drawing-layer');
                if (this.drawingCanvas) {
                    this.drawingCtx = this.drawingCanvas.getContext('2d');
                }
            },

            setTool(tool) {
                this.currentTool = tool;
                if (tool !== 'select') {
                    this.selectedElementIndex = null;
                }
            },

            selectElement(index, e) {
                if (this.currentTool !== 'select') return;
                this.selectedElementIndex = index;
                this.isDragging = true;
                const el = this.elements[index];
                const sticker = document.getElementById('studio-sticker-surface').getBoundingClientRect();
                this.dragOffsetX = (e.clientX - sticker.left) - el.x;
                this.dragOffsetY = (e.clientY - sticker.top) - el.y;
            },

            handleCanvasMouseDown(e) {
                if (this.currentTool === 'pen' || this.currentTool === 'eraser') {
                    this.isDrawing = true;
                    const rect = e.target.getBoundingClientRect();
                    const x = (e.clientX - rect.left) * (this.drawingCanvas.width / rect.width);
                    const y = (e.clientY - rect.top) * (this.drawingCanvas.height / rect.height);
                    this.drawingCtx.beginPath();
                    this.drawingCtx.moveTo(x, y);
                    this.drawingCtx.lineWidth = this.currentTool === 'pen' ? 3 : 16;
                    this.drawingCtx.lineCap = 'round';
                    this.drawingCtx.strokeStyle = this.currentTool === 'pen' ? '#000000' : '#ffffff';
                }
            },

            handleCanvasMouseMove(e) {
                if (this.isDrawing && (this.currentTool === 'pen' || this.currentTool === 'eraser')) {
                    const wrapper = document.getElementById('studio-sticker-surface');
                    const rect = wrapper.getBoundingClientRect();
                    const x = (e.clientX - rect.left) * (this.drawingCanvas.width / rect.width);
                    const y = (e.clientY - rect.top) * (this.drawingCanvas.height / rect.height);
                    this.drawingCtx.lineTo(x, y);
                    this.drawingCtx.stroke();
                } else if (this.isDragging && this.selectedElementIndex !== null) {
                    const wrapper = document.getElementById('studio-sticker-surface');
                    const rect = wrapper.getBoundingClientRect();
                    let x = (e.clientX - rect.left) - this.dragOffsetX;
                    let y = (e.clientY - rect.top) - this.dragOffsetY;
                    x = Math.max(0, Math.min(x, rect.width - 20));
                    y = Math.max(0, Math.min(y, rect.height - 10));
                    this.elements[this.selectedElementIndex].x = Math.round(x);
                    this.elements[this.selectedElementIndex].y = Math.round(y);
                }
            },

            handleCanvasMouseUp() {
                this.isDrawing = false;
                this.isDragging = false;
            },

            addText(initialText) {
                this.elements.push({
                    id: 'txt_' + Date.now(),
                    type: 'text',
                    x: 20,
                    y: 20,
                    content: initialText,
                    fontSize: 14,
                    bold: true,
                });
                this.selectedElementIndex = this.elements.length - 1;
                this.setTool('select');
            },

            addBarcode(code) {
                this.elements.push({
                    id: 'bar_' + Date.now(),
                    type: 'barcode',
                    x: 20,
                    y: 60,
                    content: code,
                    height: 38,
                });
                this.selectedElementIndex = this.elements.length - 1;
                this.setTool('select');
            },

            addQRCode(link) {
                this.elements.push({
                    id: 'qr_' + Date.now(),
                    type: 'qrcode',
                    x: 20,
                    y: 60,
                    content: link,
                });
                this.selectedElementIndex = this.elements.length - 1;
                this.setTool('select');
            },

            addLine() {
                this.elements.push({
                    id: 'line_' + Date.now(),
                    type: 'line',
                    x: 10,
                    y: 40,
                    width: 180,
                    height: 2,
                });
                this.selectedElementIndex = this.elements.length - 1;
                this.setTool('select');
            },

            addBox() {
                this.elements.push({
                    id: 'box_' + Date.now(),
                    type: 'box',
                    x: 10,
                    y: 10,
                    width: 180,
                    height: 80,
                    thickness: 2,
                });
                this.selectedElementIndex = this.elements.length - 1;
                this.setTool('select');
            },

            deleteSelectedElement() {
                if (this.selectedElementIndex !== null) {
                    this.elements.splice(this.selectedElementIndex, 1);
                    this.selectedElementIndex = null;
                }
            },

            interpolateVariables(text) {
                if (!text) return '';
                return text
                    .replace(/\{\{brand\}\}/g, this.$wire.sampleBrand || 'SEVEN SERIES')
                    .replace(/\{\{item_name\}\}/g, this.$wire.sampleItemName || 'Authentic Jeans')
                    .replace(/\{\{size\}\}/g, this.$wire.sampleSize || '32')
                    .replace(/\{\{price\}\}/g, this.$wire.samplePrice || 'Rs. 2,999')
                    .replace(/\{\{barcode\}\}/g, this.$wire.sampleBarcode || '7S-DENIM-001');
            },

            getSerializedElements() {
                return JSON.stringify(this.elements);
            }
        }));
    });
    </script>
</x-filament-panels::page>


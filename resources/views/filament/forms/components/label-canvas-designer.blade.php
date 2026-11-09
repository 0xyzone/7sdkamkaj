<div
    x-data="labelCanvasDesigner({
        state: $wire.$entangle('{{ $getStatePath() }}'),
        widthMm: $wire.$entangle('width_mm'),
        heightMm: $wire.$entangle('height_mm')
    })"
    x-init="initDesigner()"
    class="w-full bg-slate-900/90 border border-slate-700 rounded-2xl p-4 shadow-xl text-slate-100 select-none flex flex-col gap-4"
>
    <!-- Top Toolbar -->
    <div class="flex flex-wrap items-center justify-between gap-3 bg-slate-800/80 p-3 rounded-xl border border-slate-700/60 backdrop-blur-md">
        <!-- Tools Palette -->
        <div class="flex items-center gap-1.5 flex-wrap">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 mr-2 flex items-center gap-1">
                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                Tools
            </span>

            <button type="button" @click="setTool('select')" :class="currentTool === 'select' ? 'bg-red-600 text-white shadow-lg shadow-red-600/30 ring-2 ring-red-400' : 'bg-slate-700/70 text-slate-200 hover:bg-slate-600'" class="px-3 py-1.5 rounded-lg text-xs font-medium transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"></path></svg>
                Select / Move
            </button>

            <button type="button" @click="setTool('pen')" :class="currentTool === 'pen' ? 'bg-red-600 text-white shadow-lg shadow-red-600/30 ring-2 ring-red-400' : 'bg-slate-700/70 text-slate-200 hover:bg-slate-600'" class="px-3 py-1.5 rounded-lg text-xs font-medium transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                Pen (Draw)
            </button>

            <button type="button" @click="setTool('eraser')" :class="currentTool === 'eraser' ? 'bg-red-600 text-white shadow-lg shadow-red-600/30 ring-2 ring-red-400' : 'bg-slate-700/70 text-slate-200 hover:bg-slate-600'" class="px-3 py-1.5 rounded-lg text-xs font-medium transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                Eraser
            </button>
        </div>

        <!-- Add Elements -->
        <div class="flex items-center gap-1.5 flex-wrap">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 mr-1 flex items-center gap-1">
                Insert:
            </span>

            <button type="button" @click="addText('Seven Series')" class="px-2.5 py-1.5 bg-slate-700/90 hover:bg-slate-600 text-white rounded-lg text-xs font-medium transition flex items-center gap-1">
                <span class="font-bold text-red-400 text-sm">T</span> Text
            </button>

            <button type="button" @click="addBarcode('7S-DENIM-001')" class="px-2.5 py-1.5 bg-slate-700/90 hover:bg-slate-600 text-white rounded-lg text-xs font-medium transition flex items-center gap-1">
                <svg class="w-3.5 h-3.5 text-blue-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 4a1 1 0 011-1h1a1 1 0 011 1v12a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm4 0a1 1 0 011-1h1a1 1 0 011 1v12a1 1 0 01-1 1H8a1 1 0 01-1-1V4zm5 0a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4zm5 0a1 1 0 011-1h1a1 1 0 011 1v12a1 1 0 01-1 1h-1a1 1 0 01-1-1V4z" clip-rule="evenodd"></path></svg>
                Barcode
            </button>

            <button type="button" @click="addQRCode('https://sevenseries.com')" class="px-2.5 py-1.5 bg-slate-700/90 hover:bg-slate-600 text-white rounded-lg text-xs font-medium transition flex items-center gap-1">
                <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                QR Code
            </button>

            <button type="button" @click="addLine()" class="px-2.5 py-1.5 bg-slate-700/90 hover:bg-slate-600 text-white rounded-lg text-xs font-medium transition flex items-center gap-1">
                <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                Line
            </button>

            <button type="button" @click="addBox()" class="px-2.5 py-1.5 bg-slate-700/90 hover:bg-slate-600 text-white rounded-lg text-xs font-medium transition flex items-center gap-1">
                <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6z"></path></svg>
                Box / Border
            </button>

            <button type="button" @click="clearCanvas()" class="px-2.5 py-1.5 bg-red-950/60 hover:bg-red-800 text-red-200 border border-red-800/60 rounded-lg text-xs font-medium transition flex items-center gap-1 ml-auto">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                Clear All
            </button>
        </div>
    </div>

    <!-- Main Workspace (Canvas Area + Selected Object Properties) -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 items-start">
        <!-- Interactive Label Canvas Stage -->
        <div class="lg:col-span-3 bg-slate-950 rounded-2xl border border-slate-800 p-6 flex flex-col items-center justify-center min-h-[460px] overflow-auto shadow-inner relative">
            <div class="text-xs text-slate-400 mb-3 flex items-center justify-between w-full max-w-[500px]">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="font-medium text-slate-300">Live Thermal Label Preview</span>
                </div>
                <span class="bg-slate-800 px-2.5 py-1 rounded-md text-[11px] font-mono text-slate-300 border border-slate-700">
                    <span x-text="widthMm"></span>mm × <span x-text="heightMm"></span>mm (203 DPI)
                </span>
            </div>

            <!-- The Sticker representation (White background mimicking physical thermal paper) -->
            <div
                id="label-sticker-wrapper"
                class="relative bg-white rounded-lg shadow-2xl border-2 border-slate-300 transition-all overflow-hidden cursor-crosshair"
                :style="`width: ${Math.round(widthMm * 5.5)}px; height: ${Math.round(heightMm * 5.5)}px;`"
                @mousedown="handleCanvasMouseDown($event)"
                @mousemove="handleCanvasMouseMove($event)"
                @mouseup="handleCanvasMouseUp($event)"
                @mouseleave="handleCanvasMouseUp($event)"
            >
                <!-- Background grid lines for precision alignment -->
                <div class="absolute inset-0 bg-[radial-gradient(#e5e7eb_1px,transparent_1px)] [background-size:12px_12px] opacity-70 pointer-events-none"></div>

                <!-- Drawing layer (HTML5 Canvas for Pen / Eraser freehand strokes) -->
                <canvas
                    id="drawing-canvas-layer"
                    class="absolute inset-0 w-full h-full pointer-events-none"
                    :width="Math.round(widthMm * 8)"
                    :height="Math.round(heightMm * 8)"
                ></canvas>

                <!-- Draggable / Scalable Elements Layer -->
                <template x-for="(element, index) in elements" :key="element.id">
                    <div
                        class="absolute text-slate-950 font-sans cursor-move border transition-all"
                        :class="selectedElementIndex === index ? 'border-red-500 ring-2 ring-red-400/50 bg-red-50/20' : 'border-transparent hover:border-slate-400/60'"
                        :style="`left: ${element.x}px; top: ${element.y}px; ${getElementStyle(element)}`"
                        @mousedown.stop="selectElement(index, $event)"
                    >
                        <!-- Render text -->
                        <template x-if="element.type === 'text'">
                            <span :style="`font-size: ${element.fontSize || 14}px; font-weight: ${element.bold ? 'bold' : 'normal'};`" x-text="element.content"></span>
                        </template>

                        <!-- Render Barcode placeholder -->
                        <template x-if="element.type === 'barcode'">
                            <div class="flex flex-col items-center">
                                <div class="flex items-center gap-[2px] h-10 px-1 bg-white">
                                    <div class="w-[2px] h-full bg-black"></div>
                                    <div class="w-[1px] h-full bg-black"></div>
                                    <div class="w-[3px] h-full bg-black"></div>
                                    <div class="w-[1px] h-full bg-black"></div>
                                    <div class="w-[2px] h-full bg-black"></div>
                                    <div class="w-[4px] h-full bg-black"></div>
                                    <div class="w-[1px] h-full bg-black"></div>
                                    <div class="w-[2px] h-full bg-black"></div>
                                    <div class="w-[3px] h-full bg-black"></div>
                                    <div class="w-[2px] h-full bg-black"></div>
                                    <div class="w-[1px] h-full bg-black"></div>
                                    <div class="w-[3px] h-full bg-black"></div>
                                    <div class="w-[2px] h-full bg-black"></div>
                                </div>
                                <span class="text-[10px] font-mono tracking-widest mt-0.5" x-text="element.content"></span>
                            </div>
                        </template>

                        <!-- Render QR Code placeholder -->
                        <template x-if="element.type === 'qrcode'">
                            <div class="w-12 h-12 bg-white border border-black p-0.5 flex flex-col justify-between">
                                <div class="flex justify-between">
                                    <div class="w-3.5 h-3.5 border-2 border-black p-0.5"><div class="w-full h-full bg-black"></div></div>
                                    <div class="w-3.5 h-3.5 border-2 border-black p-0.5"><div class="w-full h-full bg-black"></div></div>
                                </div>
                                <div class="text-[8px] text-center font-bold">QR</div>
                                <div class="flex justify-between">
                                    <div class="w-3.5 h-3.5 border-2 border-black p-0.5"><div class="w-full h-full bg-black"></div></div>
                                    <div class="w-2 h-2 bg-black"></div>
                                </div>
                            </div>
                        </template>

                        <!-- Render Horizontal Line -->
                        <template x-if="element.type === 'line'">
                            <div :style="`width: ${element.width || 100}px; height: ${element.height || 2}px; background-color: black;`"></div>
                        </template>

                        <!-- Render Box -->
                        <template x-if="element.type === 'box'">
                            <div :style="`width: ${element.width || 80}px; height: ${element.height || 40}px; border: ${element.thickness || 2}px solid black;`"></div>
                        </template>
                    </div>
                </template>
            </div>
        </div>

        <!-- Selected Element Properties Panel -->
        <div class="bg-slate-800/90 rounded-2xl border border-slate-700/80 p-4 flex flex-col gap-3 shadow-lg">
            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-300 pb-2 border-b border-slate-700 flex items-center justify-between">
                <span>Element Properties</span>
                <span x-show="selectedElementIndex !== null" class="text-[10px] font-mono text-red-400 bg-red-950/60 px-2 py-0.5 rounded border border-red-800/40">
                    Active #<span x-text="selectedElementIndex + 1"></span>
                </span>
            </h4>

            <template x-if="selectedElementIndex !== null">
                <div class="flex flex-col gap-3 text-xs">
                    <div>
                        <label class="block font-medium text-slate-300 mb-1">Content / Value</label>
                        <input
                            type="text"
                            x-model="elements[selectedElementIndex].content"
                            @input="updateState()"
                            class="w-full bg-slate-900 border border-slate-700 rounded-lg px-2.5 py-1.5 text-white focus:ring-1 focus:ring-red-500 focus:outline-none"
                            placeholder="Text or variable e.g. @{{size}}"
                        />
                        <div class="text-[10px] text-slate-400 mt-1">
                            Available: <code class="text-red-400">&#123;&#123;item_name&#125;&#125;</code>, <code class="text-red-400">&#123;&#123;size&#125;&#125;</code>, <code class="text-red-400">&#123;&#123;price&#125;&#125;</code>, <code class="text-red-400">&#123;&#123;barcode&#125;&#125;</code>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-medium text-slate-300 mb-1">X Position (px)</label>
                            <input
                                type="number"
                                x-model.number="elements[selectedElementIndex].x"
                                @input="updateState()"
                                class="w-full bg-slate-900 border border-slate-700 rounded-lg px-2.5 py-1.5 text-white"
                            />
                        </div>
                        <div>
                            <label class="block font-medium text-slate-300 mb-1">Y Position (px)</label>
                            <input
                                type="number"
                                x-model.number="elements[selectedElementIndex].y"
                                @input="updateState()"
                                class="w-full bg-slate-900 border border-slate-700 rounded-lg px-2.5 py-1.5 text-white"
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
                                    @input="updateState()"
                                    class="w-full bg-slate-900 border border-slate-700 rounded-lg px-2.5 py-1.5 text-white"
                                    min="8"
                                    max="64"
                                />
                            </div>
                            <div class="flex items-center gap-2 pt-5">
                                <label class="flex items-center gap-1.5 cursor-pointer text-slate-200">
                                    <input
                                        type="checkbox"
                                        x-model="elements[selectedElementIndex].bold"
                                        @change="updateState()"
                                        class="rounded bg-slate-900 border-slate-700 text-red-600 focus:ring-0"
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
                                    @input="updateState()"
                                    class="w-full bg-slate-900 border border-slate-700 rounded-lg px-2.5 py-1.5 text-white"
                                />
                            </div>
                            <div>
                                <label class="block font-medium text-slate-300 mb-1">Height (px)</label>
                                <input
                                    type="number"
                                    x-model.number="elements[selectedElementIndex].height"
                                    @input="updateState()"
                                    class="w-full bg-slate-900 border border-slate-700 rounded-lg px-2.5 py-1.5 text-white"
                                />
                            </div>
                        </div>
                    </template>

                    <div class="pt-2 border-t border-slate-700 flex gap-2">
                        <button
                            type="button"
                            @click="deleteSelectedElement()"
                            class="w-full px-3 py-1.5 bg-red-600/80 hover:bg-red-500 text-white rounded-lg text-xs font-semibold transition"
                        >
                            Delete Element
                        </button>
                    </div>
                </div>
            </template>

            <template x-if="selectedElementIndex === null">
                <div class="text-xs text-slate-400 py-6 text-center italic">
                    Click any element or insert a new one to edit its properties.
                </div>
            </template>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('labelCanvasDesigner', (config) => ({
        state: config.state,
        widthMm: config.widthMm || 50,
        heightMm: config.heightMm || 30,
        elements: [],
        currentTool: 'select', // 'select', 'pen', 'eraser'
        selectedElementIndex: null,
        isDrawing: false,
        isDragging: false,
        dragOffsetX: 0,
        dragOffsetY: 0,
        drawingCanvas: null,
        drawingCtx: null,

        initDesigner() {
            try {
                this.elements = Array.isArray(this.state) ? this.state : (JSON.parse(this.state || '[]') || []);
            } catch(e) {
                this.elements = [];
            }
            this.$nextTick(() => {
                this.initDrawingCanvas();
            });
        },

        initDrawingCanvas() {
            this.drawingCanvas = document.getElementById('drawing-canvas-layer');
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
            const sticker = document.getElementById('label-sticker-wrapper').getBoundingClientRect();
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
                const wrapper = document.getElementById('label-sticker-wrapper');
                const rect = wrapper.getBoundingClientRect();
                const x = (e.clientX - rect.left) * (this.drawingCanvas.width / rect.width);
                const y = (e.clientY - rect.top) * (this.drawingCanvas.height / rect.height);
                this.drawingCtx.lineTo(x, y);
                this.drawingCtx.stroke();
            } else if (this.isDragging && this.selectedElementIndex !== null) {
                const wrapper = document.getElementById('label-sticker-wrapper');
                const rect = wrapper.getBoundingClientRect();
                let x = (e.clientX - rect.left) - this.dragOffsetX;
                let y = (e.clientY - rect.top) - this.dragOffsetY;
                x = Math.max(0, Math.min(x, rect.width - 20));
                y = Math.max(0, Math.min(y, rect.height - 10));
                this.elements[this.selectedElementIndex].x = Math.round(x);
                this.elements[this.selectedElementIndex].y = Math.round(y);
                this.updateState();
            }
        },

        handleCanvasMouseUp(e) {
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
            this.updateState();
        },

        addBarcode(code) {
            this.elements.push({
                id: 'bar_' + Date.now(),
                type: 'barcode',
                x: 20,
                y: 60,
                content: code,
                height: 40,
            });
            this.selectedElementIndex = this.elements.length - 1;
            this.setTool('select');
            this.updateState();
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
            this.updateState();
        },

        addLine() {
            this.elements.push({
                id: 'line_' + Date.now(),
                type: 'line',
                x: 10,
                y: 50,
                width: 180,
                height: 2,
            });
            this.selectedElementIndex = this.elements.length - 1;
            this.setTool('select');
            this.updateState();
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
            this.updateState();
        },

        deleteSelectedElement() {
            if (this.selectedElementIndex !== null) {
                this.elements.splice(this.selectedElementIndex, 1);
                this.selectedElementIndex = null;
                this.updateState();
            }
        },

        clearCanvas() {
            this.elements = [];
            this.selectedElementIndex = null;
            if (this.drawingCtx && this.drawingCanvas) {
                this.drawingCtx.clearRect(0, 0, this.drawingCanvas.width, this.drawingCanvas.height);
            }
            this.updateState();
        },

        getElementStyle(element) {
            return '';
        },

        updateState() {
            this.state = JSON.stringify(this.elements);
        }
    }));
});
</script>


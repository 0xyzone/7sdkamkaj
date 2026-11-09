/**
 * 7 Series Kamkaj - Quick Print Slide-Over Component
 * Handles client-side thermal 1-bit WYSIWYG rendering, dynamic variable interpolation,
 * WebUSB, Web Bluetooth, System AirPrint, and Server Spooling.
 */
(function() {
    'use strict';

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

    window.quickPrintComponent = function(cfg) {
        cfg = cfg || {};
        return {
            templateId: cfg.templateId || 0,
            name: cfg.name || 'label',
            widthMm: parseFloat(cfg.widthMm) || 50,
            heightMm: parseFloat(cfg.heightMm) || 25,
            gapMm: parseFloat(cfg.gapMm) || 2.0,
            direction: parseInt(cfg.direction, 10) || 1,
            elements: cfg.elements || [],
            copies: 1,
            threshold: 140,
            dither: false,
            selectedPrinterId: cfg.selectedPrinterId || '',
            loading: true,
            printingUsb: false,
            printingBt: false,
            printingServer: false,
            statsText: 'Loading…',
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
                ...(cfg.elements?.studio?.vars || {}),
            },

            darknessLabel() {
                return Math.round(((this.threshold - 60) / 170) * 100) + '%';
            },

            async init() {
                await this.ensureDependencies();
                if (this.elements && this.elements.studio && this.elements.studio.vars) {
                    this.vars = { ...this.vars, ...this.elements.studio.vars };
                }
                await this.renderPreview();
            },

            async ensureDependencies() {
                const isFabricReady = () => typeof window.fabric !== 'undefined' && typeof window.fabric.StaticCanvas !== 'undefined';
                if (isFabricReady() && typeof window.JsBarcode !== 'undefined' && typeof window.qrcode !== 'undefined') {
                    return;
                }

                const loadScript = (src, isReady) => new Promise((resolve) => {
                    if (isReady()) return resolve();
                    let existing = document.querySelector(`script[src="${src}"]`);
                    if (!existing) {
                        existing = document.createElement('script');
                        existing.src = src;
                        document.head.appendChild(existing);
                    }
                    const start = Date.now();
                    const poll = setInterval(() => {
                        if (isReady() || Date.now() - start > 8000) {
                            clearInterval(poll);
                            resolve();
                        }
                    }, 40);
                });

                await Promise.all([
                    loadScript('/vendor/label-studio/fabric.min.js', isFabricReady),
                    loadScript('/vendor/label-studio/JsBarcode.all.min.js', () => typeof window.JsBarcode !== 'undefined'),
                    loadScript('/vendor/label-studio/qrcode.min.js', () => typeof window.qrcode !== 'undefined'),
                ]);
            },

            interpolate(text) {
                if (typeof text !== 'string') return text;
                return text.replace(/\{\{([a-zA-Z0-9_\-]+)\}\}/g, (_, k) => this.vars[k] ?? '');
            },

            async renderPreview() {
                this.loading = true;
                try {
                    await this.ensureDependencies();
                    if (typeof window.fabric === 'undefined' || !window.fabric.StaticCanvas) {
                        throw new Error('Canvas engine initializing…');
                    }

                    const DPMM = 8; // 203 DPI (8 dots per mm)
                    const w = Math.round(this.widthMm * DPMM);
                    const h = Math.round(this.heightMm * DPMM);

                    // Create offscreen StaticCanvas with enableRetinaScaling disabled to prevent pixel doubling
                    const sc = new fabric.StaticCanvas(null, {
                        width: w,
                        height: h,
                        enableRetinaScaling: false,
                        renderOnAddRemove: false,
                        backgroundColor: '#ffffff',
                    });

                    let els = this.elements;
                    if (typeof els === 'string') {
                        try { els = JSON.parse(els); } catch (_) {}
                    }

                    if (els && !Array.isArray(els) && Array.isArray(els.objects)) {
                        // Studio JSON format - extract canvas elements
                        const { studio, ...json } = els;
                        await new Promise((r) => sc.loadFromJSON(json, r));
                    } else if (Array.isArray(els) && els.length) {
                        // Legacy array elements format
                        const f = 8 / 5.5; // Scale from legacy 5.5 dpmm to 8 dpmm
                        for (const el of els) {
                            const x = (el.x || 0) * f, y = (el.y || 0) * f;
                            let o = null;
                            if (el.type === 'text') {
                                o = new fabric.IText(el.content || '', {
                                    left: x,
                                    top: y,
                                    fontSize: (el.fontSize || 14) * f,
                                    fontWeight: el.bold ? 'bold' : 'normal',
                                    fontFamily: el.fontFamily || 'Arial',
                                    fill: '#000000',
                                    elType: 'text',
                                });
                            } else if (el.type === 'line') {
                                o = new fabric.Rect({
                                    left: x,
                                    top: y,
                                    width: (el.width || 100) * f,
                                    height: Math.max(2, (el.height || 2) * f),
                                    fill: '#000000',
                                    strokeWidth: 0,
                                    elType: 'rect',
                                });
                            } else if (el.type === 'box' || el.type === 'rect') {
                                o = new fabric.Rect({
                                    left: x,
                                    top: y,
                                    width: (el.width || 80) * f,
                                    height: (el.height || 40) * f,
                                    fill: el.fill || '',
                                    stroke: '#000000',
                                    strokeWidth: (el.thickness || 2) * f,
                                    strokeUniform: true,
                                    rx: (el.rx || 0) * f,
                                    ry: (el.ry || 0) * f,
                                    elType: 'rect',
                                });
                            } else if (el.type === 'barcode') {
                                o = new fabric.Image(document.createElement('canvas'), {
                                    left: x,
                                    top: y,
                                    elType: 'barcode',
                                    barcodeValue: el.content || '890123456789',
                                    barcodeFormat: el.format || 'CODE128',
                                    barcodeModule: el.module || 2,
                                    barcodeHeight: Math.round((el.height || 40) * f),
                                    barcodeShowText: el.showText !== false,
                                });
                            } else if (el.type === 'qrcode') {
                                o = new fabric.Image(document.createElement('canvas'), {
                                    left: x,
                                    top: y,
                                    elType: 'qr',
                                    qrValue: el.content || 'https://7series.com.np',
                                    qrCell: 4,
                                });
                            }
                            if (o) sc.add(o);
                        }
                    }

                    // Wait for any embedded images to complete loading
                    const imgObjs = sc.getObjects().filter((o) => o.type === 'image' && o.getElement());
                    if (imgObjs.length) {
                        await Promise.all(imgObjs.map((img) => new Promise((res) => {
                            const el = img.getElement();
                            if (!el || el.complete) return res();
                            el.onload = () => res();
                            el.onerror = () => res();
                            setTimeout(res, 2000);
                        })));
                    }

                    // Interpolate dynamic template text variables while keeping anchor alignment
                    const walk = (o) => {
                        if (o.type === 'i-text' || o.type === 'text') {
                            if (!o.origTemplateText) o.origTemplateText = o.text;
                            const t = this.interpolate(o.origTemplateText);
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

                    // Render dynamic barcode & QR layers
                    for (const o of sc.getObjects()) {
                        if (o.elType === 'barcode' && window.JsBarcode) {
                            try {
                                const bcCanvas = document.createElement('canvas');
                                const val = this.interpolate(o.barcodeValue || '890123456789');
                                window.JsBarcode(bcCanvas, val, {
                                    format: o.barcodeFormat || 'CODE128',
                                    width: o.barcodeModule || 2,
                                    height: o.barcodeHeight || 80,
                                    displayValue: o.barcodeShowText !== false,
                                    font: 'Arial',
                                    fontSize: 16,
                                    background: '#ffffff',
                                    lineColor: '#000000',
                                });
                                o.setElement(bcCanvas);
                                o.set({ width: bcCanvas.width, height: bcCanvas.height });
                            } catch (_) {}
                        } else if (o.elType === 'qr' && window.qrcode) {
                            try {
                                const val = this.interpolate(o.qrValue || 'https://7series.com.np');
                                const qr = window.qrcode(0, 'M');
                                qr.addData(val);
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
                                o.set({ width: qrCanvas.width, height: qrCanvas.height });
                            } catch (_) {}
                        }
                    }

                    sc.renderAll();

                    // Convert to 1-bit monochrome thermal bitmap
                    this.monoCanvas = this.toMono(sc.getElement(), this.threshold, this.dither);
                    sc.dispose();

                    // Paint onto preview canvas
                    let target = document.getElementById(`qpCanvas_${this.templateId}`);
                    if (!target) {
                        await new Promise((r) => setTimeout(r, 80));
                        target = document.getElementById(`qpCanvas_${this.templateId}`);
                    }
                    if (target) {
                        target.width = this.monoCanvas.width;
                        target.height = this.monoCanvas.height;
                        const tctx = target.getContext('2d');
                        tctx.drawImage(this.monoCanvas, 0, 0);

                        // Responsive scale inside preview box
                        const maxW = 460, maxH = 340;
                        let scale = Math.min(maxW / target.width, maxH / target.height);
                        if (scale >= 1) scale = Math.floor(scale);
                        target.style.width = Math.round(target.width * scale) + 'px';
                        target.style.height = Math.round(target.height * scale) + 'px';
                    }

                    this.downloadDataUrl = this.monoCanvas.toDataURL('image/png');
                    const inkCoverage = Math.round((this.monoCanvas.blackRatio || 0) * 100);
                    this.statsText = `${w} × ${h} dots · ${this.widthMm} × ${this.heightMm} mm · ${inkCoverage}% ink`;
                } catch (err) {
                    console.error('Quick print preview error:', err);
                    this.statsText = 'Preview calculating…';
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
                for (let i = 0; i < w * h; i++) {
                    const j = i * 4;
                    g[i] = d[j] * 0.299 + d[j + 1] * 0.587 + d[j + 2] * 0.114;
                }
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
                    alert('WebUSB is not supported in this browser. Please use Chrome, Brave, or Edge on Mac or Windows.');
                    return;
                }
                if (!this.monoCanvas) return;
                this.printingUsb = true;

                let device = null;
                try {
                    device = await navigator.usb.requestDevice({ filters: [] });
                    if (!device) return;
                    await device.open();
                    await device.selectConfiguration(1);

                    let outEndpoint = null;
                    let ifaceNumber = 0;
                    for (const iface of device.configuration.interfaces) {
                        for (const alt of iface.alternates) {
                            for (const ep of alt.endpoints) {
                                if (ep.direction === 'out') {
                                    outEndpoint = ep.endpointNumber;
                                    ifaceNumber = iface.interfaceNumber;
                                    break;
                                }
                            }
                            if (outEndpoint) break;
                        }
                        if (outEndpoint) break;
                    }

                    if (!outEndpoint) throw new Error('Could not find USB OUT bulk endpoint on ' + (device.productName || 'USB printer'));

                    await device.claimInterface(ifaceNumber);
                    const total = this.buildTsplData();

                    const CHUNK_SIZE = 1024;
                    for (let offset = 0; offset < total.length; offset += CHUNK_SIZE) {
                        const chunk = total.slice(offset, offset + CHUNK_SIZE);
                        await device.transferOut(outEndpoint, chunk);
                    }

                    alert(`Direct USB Print Dispatched to ${device.productName || 'Printer'}!`);
                    await device.releaseInterface(ifaceNumber);
                    await device.close();
                } catch (err) {
                    if (err.name !== 'NotFoundError') {
                        alert('USB Print Error: ' + (err.message || 'Connection failed'));
                    }
                    if (device && device.opened) {
                        try { await device.close(); } catch (_) {}
                    }
                } finally {
                    this.printingUsb = false;
                }
            },

            async printBluetooth() {
                const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
                const isAndroid = /Android/i.test(navigator.userAgent);

                if (!navigator.bluetooth) {
                    if (isIOS) {
                        const choice = confirm('Apple iOS restricts Web Bluetooth in all mobile browsers (Brave, Chrome, Safari).\n\nTap OK to Share directly to the Deli Label app with 1 tap (Recommended!), or Cancel to use AirPrint.');
                        if (choice) {
                            await this.sharePrint();
                        } else {
                            this.systemPrint();
                        }
                        return;
                    }

                    if (isAndroid) {
                        const choice = confirm('Web Bluetooth is blocked by Brave privacy settings on Android.\n\nTap OK to Share directly to the Deli Label / RawBT app (Fastest!), or Cancel to copy the Brave flag URL.');
                        if (choice) {
                            await this.sharePrint();
                        } else {
                            navigator.clipboard?.writeText('chrome://flags/#enable-web-bluetooth');
                            alert('Copied "chrome://flags/#enable-web-bluetooth" to clipboard! Paste into a new tab, set to Enabled, and Relaunch Brave.');
                        }
                        return;
                    }

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
                    const res = await fetch('/label-studio/print', {
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

            async sharePrint() {
                if (!this.monoCanvas) return;
                try {
                    const blob = await new Promise((resolve) => this.monoCanvas.toBlob(resolve, 'image/png'));
                    if (!blob) throw new Error('Could not render label bitmap');
                    const safeName = (this.name || 'label').replace(/[^\w\- ]+/g, '').trim() || 'label';
                    const file = new File([blob], `${safeName}.png`, { type: 'image/png' });

                    if (navigator.canShare && navigator.canShare({ files: [file] })) {
                        await navigator.share({
                            title: this.name,
                            text: 'Print thermal sticker via Deli Label or RawBT',
                            files: [file],
                        });
                    } else if (navigator.share) {
                        await navigator.share({
                            title: this.name,
                            url: this.monoCanvas.toDataURL('image/png'),
                        });
                    } else {
                        const a = document.createElement('a');
                        a.href = this.monoCanvas.toDataURL('image/png');
                        a.download = `${safeName}.png`;
                        a.click();
                        alert('Label image downloaded! Open with your Deli Label app to print.');
                    }
                } catch (err) {
                    if (err.name !== 'AbortError') {
                        alert('Share error: ' + (err.message || 'Operation failed'));
                    }
                }
            },

            systemPrint() {
                if (!this.monoCanvas) return;
                const dataUrl = this.monoCanvas.toDataURL('image/png');
                const wMm = this.widthMm;
                const hMm = this.heightMm;
                const printWin = window.open('', '_blank', 'width=600,height=600');
                if (printWin) {
                    printWin.document.write(`
                        <html>
                        <head>
                            <title>Print Label</title>
                            <style>
                                @page { margin: 0; size: ${wMm}mm ${hMm}mm; }
                                body { margin: 0; padding: 0; display: flex; align-items: center; justify-content: center; background: #fff; }
                                img { width: ${wMm}mm; height: ${hMm}mm; image-rendering: pixelated; object-fit: contain; }
                            </style>
                        </head>
                        <body onload="window.focus(); window.print(); window.close();">
                            <img src="${dataUrl}" alt="Label">
                        </body>
                        </html>
                    `);
                    printWin.document.close();
                } else {
                    window.print();
                }
            },
        };
    };

    window.quickPrintFallback = function(cfg) {
        return {
            loading: true,
            printingUsb: false,
            printingBt: false,
            printingServer: false,
            statsText: 'Loading…',
            downloadDataUrl: '',
            copies: 1,
            threshold: 140,
            selectedPrinterId: '',
            vars: {},
            darknessLabel() { return '47%'; },
            init() {},
            renderPreview() {},
        };
    };

    if (window.Alpine) {
        window.Alpine.data('quickPrintComponent', window.quickPrintComponent);
    }
})();

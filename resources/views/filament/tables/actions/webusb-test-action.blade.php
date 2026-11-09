<div
    x-data="{
        async printUsb() {
            if (!navigator.usb) {
                alert('WebUSB is not supported in this browser. Please use Brave or Chrome on Mac/Windows.');
                return;
            }

            try {
                let device = null;
                const paired = await navigator.usb.getDevices();
                if (paired && paired.length > 0) {
                    device = paired.find(d => (d.vendorId === 0x1137 || d.vendorId === 0x0471 || d.productName?.toLowerCase().includes('printer') || d.manufacturerName?.toLowerCase().includes('deli'))) || paired[0];
                }

                if (!device) {
                    device = await navigator.usb.requestDevice({
                        filters: [
                            { vendorId: 0x1137 },
                            { vendorId: 0x0471 },
                            { classCode: 0x07 },
                        ]
                    }).catch(async () => {
                        return await navigator.usb.requestDevice({ filters: [] });
                    });
                }

                if (!device) return;

                await device.open();
                if (device.configuration === null) {
                    await device.selectConfiguration(1);
                }

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

                const tspl = 'SIZE 50 mm, 30 mm\nGAP 2 mm, 0 mm\nDIRECTION 1\nCLS\nTEXT 40,40,\'3\',0,1,1,\'7 SERIES KAMKAJ\'\nTEXT 40,80,\'2\',0,1,1,\'WebUSB Direct Test\'\nTEXT 40,120,\'2\',0,1,1,\'Status: CONNECTED OK!\'\nBAR 40,160,280,2\nPRINT 1\n';
                const encoder = new TextEncoder();
                const data = encoder.encode(tspl);

                const CHUNK_SIZE = 512;
                for (let offset = 0; offset < data.length; offset += CHUNK_SIZE) {
                    await device.transferOut(outEndpoint.endpointNumber, data.slice(offset, offset + CHUNK_SIZE));
                }

                await device.releaseInterface(printerInterface.interfaceNumber);
                await device.close();

                new FilamentNotification()
                    .title('Test Print Dispatched!')
                    .body('Test label sent directly via USB cable to printer.')
                    .success()
                    .send();
            } catch (err) {
                alert('USB Test Print Error: ' + (err.message || 'Device communication failed'));
            }
        }
    }"
    class="inline-block"
>
    <button
        type="button"
        @click="printUsb()"
        class="fi-btn fi-btn-size-sm fi-color-success fi-btn-color-success fi-color-custom relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg gap-1.5 px-2.5 py-1.5 text-xs inline-grid shadow-sm bg-emerald-600 hover:bg-emerald-500 text-white"
        title="Send direct USB test print via WebUSB in Brave"
    >
        <svg class="fi-btn-icon transition duration-75 h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
        </svg>
        <span class="fi-btn-label">USB Test</span>
    </button>
</div>

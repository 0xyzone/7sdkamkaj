<div 
    x-data="{
        deferredPrompt: null,
        showPrompt: false,
        isIOS: false,
        init() {
            // Check if already in standalone / PWA mode
            const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
            if (isStandalone) {
                return;
            }

            // Check dismissal cooldown (7 days)
            const dismissedAt = localStorage.getItem('pwa_prompt_dismissed');
            if (dismissedAt && (Date.now() - parseInt(dismissedAt)) < 7 * 24 * 60 * 60 * 1000) {
                return;
            }

            // Check iOS
            const userAgent = window.navigator.userAgent.toLowerCase();
            this.isIOS = /iphone|ipad|ipod/.test(userAgent);

            // Listen for beforeinstallprompt (Chrome / Android / Desktop)
            window.addEventListener('beforeinstallprompt', (e) => {
                e.preventDefault();
                this.deferredPrompt = e;
                this.showPrompt = true;
            });

            // If iOS Safari and not standalone, show prompt after 3s
            if (this.isIOS && !isStandalone) {
                setTimeout(() => {
                    this.showPrompt = true;
                }, 3000);
            }
        },
        async installPWA() {
            if (this.deferredPrompt) {
                this.deferredPrompt.prompt();
                const { outcome } = await this.deferredPrompt.userChoice;
                this.deferredPrompt = null;
                this.showPrompt = false;
            }
        },
        dismissPrompt() {
            this.showPrompt = false;
            localStorage.setItem('pwa_prompt_dismissed', Date.now().toString());
        }
    }"
    x-show="showPrompt"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-8"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-8"
    class="fixed bottom-4 left-4 right-4 sm:left-auto sm:right-6 sm:bottom-6 sm:max-w-md z-50 bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl shadow-2xl p-4 md:p-5"
    x-cloak
>
    <div class="flex items-start gap-3.5">
        <div class="h-12 w-12 rounded-xl bg-red-50 dark:bg-red-950/40 p-2 flex items-center justify-center shrink-0 border border-red-200/60 dark:border-red-900/60 shadow-xs">
            <img src="{{ asset('images/symbol.png') }}" alt="7" class="h-8 w-auto object-contain" />
        </div>
        
        <div class="flex-1 min-w-0">
            <h4 class="text-sm font-semibold text-slate-900 dark:text-white flex items-center gap-1.5">
                Install 7 Series Kamkaj
                <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold bg-red-100 dark:bg-red-950 text-red-600 dark:text-red-400">
                    APP
                </span>
            </h4>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 leading-relaxed">
                Add to your home screen for quick mobile access, native feel, and faster load times.
            </p>

            <template x-if="isIOS">
                <div class="mt-2.5 p-2 rounded-lg bg-slate-50 dark:bg-slate-800/60 text-[11px] text-slate-600 dark:text-slate-300 border border-slate-200/50 dark:border-slate-700/50">
                    Tap <span class="font-bold inline-flex items-center">Share <svg class="inline h-3.5 w-3.5 mx-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path></svg></span> then select <span class="font-bold underline">"Add to Home Screen"</span>.
                </div>
            </template>
        </div>

        <button 
            type="button" 
            x-on:click="dismissPrompt"
            class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg"
            aria-label="Close"
        >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>

    <div class="mt-3.5 flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800/80">
        <button 
            type="button"
            x-on:click="dismissPrompt"
            class="px-3 py-1.5 text-xs font-medium text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200"
        >
            Not Now
        </button>

        <template x-if="!isIOS">
            <button 
                type="button"
                x-on:click="installPWA"
                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold rounded-lg bg-[#ed1c24] hover:bg-[#d6141b] text-white shadow-xs transition-colors"
            >
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                Install App
            </button>
        </template>
    </div>
</div>

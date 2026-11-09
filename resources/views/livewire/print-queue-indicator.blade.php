<div wire:poll.5s="refreshQueue" class="flex items-center">
    <!-- Topbar Trigger Button -->
    <button
        type="button"
        wire:click="openModal"
        class="relative flex items-center justify-center w-9 h-9 rounded-xl text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800/60 transition-colors focus:outline-none"
        title="Print Queue"
    >
        <svg class="w-5 h-5 {{ $jobCount > 0 ? 'text-primary-600 dark:text-primary-500 animate-bounce' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 9V3h12v6M3 9h18a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2v-6a2 2 0 012-2zm3 5h12v7H6v-7z"></path>
        </svg>

        @if ($jobCount > 0)
            <span class="absolute -top-0.5 -right-0.5 flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-primary-600 px-1 text-[10px] font-bold text-white shadow-sm ring-2 ring-white dark:ring-slate-900 animate-pulse">
                {{ $jobCount }}
            </span>
        @endif
    </button>

    <!-- Slide-over / Modal for Print Jobs -->
    @if ($isOpen)
        <div class="fixed inset-0 z-50 overflow-hidden" aria-labelledby="print-queue-title" role="dialog" aria-modal="true">
            <!-- Backdrop -->
            <div
                wire:click="closeModal"
                class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm transition-opacity"
            ></div>

            <div class="fixed inset-y-0 right-0 max-w-full flex pl-10">
                <div class="w-screen max-w-md bg-white dark:bg-slate-900 shadow-2xl border-l border-slate-200 dark:border-slate-800 flex flex-col">
                    <!-- Header -->
                    <div class="p-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-950/50">
                        <div class="flex items-center gap-3">
                            <span class="p-2.5 rounded-xl bg-primary-50 text-primary-600 dark:bg-primary-950/60 dark:text-primary-400 border border-primary-200/50 dark:border-primary-800/40">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                            </span>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white" id="print-queue-title">
                                    Active Print Queue
                                </h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    {{ $jobCount }} pending or processing {{ str('job')->plural($jobCount) }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-1.5">
                            <button
                                type="button"
                                wire:click="refreshQueue"
                                class="p-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-lg transition"
                                title="Refresh"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                            </button>
                            <button
                                type="button"
                                wire:click="closeModal"
                                class="p-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-lg transition"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Job List -->
                    <div class="flex-1 overflow-y-auto p-5 divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($jobs as $job)
                            <div class="py-3.5 first:pt-0 last:pb-0 flex items-center justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-bold bg-primary-100 text-primary-800 dark:bg-primary-950/80 dark:text-primary-300">
                                            {{ $job['id'] }}
                                        </span>
                                        <span class="text-xs font-semibold text-slate-700 dark:text-slate-200 truncate">
                                            {{ $job['printer'] }}
                                        </span>
                                    </div>
                                    <div class="mt-1 flex items-center gap-3 text-[11px] text-slate-500 dark:text-slate-400">
                                        <span>Size: {{ $job['size'] }}</span>
                                        <span>•</span>
                                        <span>User: {{ $job['user'] }}</span>
                                    </div>
                                    <div class="mt-0.5 text-[10px] font-mono text-slate-400">
                                        {{ $job['created_at'] }}
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    wire:click="cancelJob('{{ $job['id'] }}')"
                                    wire:loading.attr="disabled"
                                    class="px-2.5 py-1.5 text-xs font-medium text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 bg-red-50 hover:bg-red-100 dark:bg-red-950/40 dark:hover:bg-red-950/80 rounded-lg transition flex items-center gap-1 border border-red-200/50 dark:border-red-800/40"
                                    title="Cancel job"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    <span>Cancel</span>
                                </button>
                            </div>
                        @empty
                            <div class="py-16 text-center">
                                <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400 mb-3 border border-emerald-200/40 dark:border-emerald-800/40">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <h4 class="text-sm font-semibold text-slate-800 dark:text-slate-200">
                                    Queue is Empty
                                </h4>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-xs mx-auto">
                                    No active jobs waiting or processing on thermal printers. All spoolers idle.
                                </p>
                            </div>
                        @endforelse
                    </div>

                    <!-- Footer / Bulk Actions -->
                    @if ($jobCount > 0)
                        <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50 flex items-center justify-between">
                            <span class="text-xs text-slate-500 dark:text-slate-400">
                                Danger zone:
                            </span>
                            <button
                                type="button"
                                wire:click="cancelAllJobs"
                                wire:confirm="Are you sure you want to terminate ALL pending print jobs across all queues?"
                                class="px-3 py-1.5 text-xs font-semibold text-white bg-red-600 hover:bg-red-500 rounded-xl transition shadow-sm flex items-center gap-1.5"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                <span>Terminate All Jobs</span>
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>

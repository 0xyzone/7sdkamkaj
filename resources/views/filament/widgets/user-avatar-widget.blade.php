@php
    $user = filament()->auth()->user();
    $avatarUrl = $user?->getFilamentAvatarUrl();
@endphp

<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="relative group">
                    @if ($avatarUrl)
                        <img 
                            src="{{ $avatarUrl }}" 
                            alt="{{ $user?->name }}" 
                            class="h-14 w-14 rounded-full object-cover ring-2 ring-primary-500/30 shadow-sm"
                        />
                    @else
                        <div class="h-14 w-14 rounded-full bg-primary-50 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400 flex items-center justify-center font-bold text-lg ring-2 ring-primary-500/20 shadow-sm">
                            {{ strtoupper(substr($user?->name ?? 'U', 0, 2)) }}
                        </div>
                    @endif
                </div>

                <div class="space-y-0.5">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-semibold text-gray-950 dark:text-white">
                            {{ $user?->name }}
                        </span>
                        @if ($user?->roles?->first())
                            <x-filament::badge size="sm" color="primary">
                                {{ $user->roles->first()->name }}
                            </x-filament::badge>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $user?->email }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                {{ $this->updateAvatarAction }}

                @if ($avatarUrl)
                    {{ $this->removeAvatarAction }}
                @endif
            </div>
        </div>

        <x-filament-actions::modals />
    </x-filament::section>
</x-filament-widgets::widget>

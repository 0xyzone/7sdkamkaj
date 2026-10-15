<div class="flex items-center gap-2.5 py-1">
    {{-- Mobile Logo: Crimson 7 Symbol --}}
    <img 
        src="{{ asset('images/symbol.png') }}" 
        alt="7 Series" 
        class="h-8 w-auto md:hidden object-contain" 
    />
    <span class="font-bold text-base tracking-tight text-slate-900 dark:text-white md:hidden">
        Kamkaj
    </span>

    {{-- Desktop Logo: Full 7 Series Authentic Denim Logo --}}
    <div class="hidden md:flex items-center">
        {{-- Light Theme: Black Text Logo --}}
        <img 
            src="{{ asset('images/logo.png') }}" 
            alt="7 Series Authentic Denim" 
            class="h-8 w-auto object-contain dark:hidden" 
        />

        {{-- Dark Theme: White Text Logo --}}
        <img 
            src="{{ asset('images/logo-dark.png') }}" 
            srcset="{{ asset('images/logo-dark.png') }} 1x, {{ asset('images/logo-dark@2x.png') }} 2x"
            alt="7 Series Authentic Denim" 
            class="h-8 w-auto object-contain hidden dark:block" 
        />
    </div>
</div>

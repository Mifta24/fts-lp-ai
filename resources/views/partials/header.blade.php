@php
    $links = [
        'home' => '#home',
        'about' => '#about',
        'services' => '#services',
        'pricing' => '#pricing',
        'team' => '#team',
        'contact' => '#contact',
    ];
    $wa = 'https://wa.me/'.config('fts.whatsapp');
@endphp

<header class="fixed inset-x-0 top-0 z-50 border-b border-line bg-white/80 backdrop-blur">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-3 px-4 sm:px-6 lg:px-8">
        <a href="#home" class="flex items-center gap-2" aria-label="Fujiyama Technology Solutions">
            <img src="{{ asset('images/logo.webp') }}" alt="" width="32" height="32" class="h-8 w-8 rounded-lg">
            <span class="text-lg font-bold text-ink">Fujiyama</span>
        </a>

        <nav class="hidden items-center gap-7 lg:flex" aria-label="Main">
            @foreach ($links as $key => $href)
                <a href="{{ $href }}" @class([
                    'border-b-2 py-1 text-sm font-medium transition-colors hover:text-primary',
                    'border-primary text-primary' => $key === 'home',
                    'border-transparent text-ink' => $key !== 'home',
                ])>{{ __('landing.nav.'.$key) }}</a>
            @endforeach
        </nav>

        <div class="flex items-center gap-2 sm:gap-3">
            <div class="flex rounded-full border border-line bg-white p-0.5 text-xs font-semibold" role="group" aria-label="{{ __('landing.nav.language') }}">
                @foreach (config('fts.languages') as $code)
                    <a href="{{ route('home', ['lang' => $code]) }}" hreflang="{{ $code }}" @class([
                        'rounded-full px-2.5 py-1.5 transition-colors',
                        'bg-primary text-white' => app()->getLocale() === $code,
                        'text-muted hover:text-ink' => app()->getLocale() !== $code,
                    ]) @if (app()->getLocale() === $code) aria-current="true" @endif>{{ strtoupper($code) }}</a>
                @endforeach
            </div>
            <a href="{{ $wa }}" target="_blank" rel="noopener" class="hidden rounded-full bg-primary px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-dark sm:inline-flex">{{ __('landing.nav.wa') }}</a>
            <button type="button" data-nav-toggle aria-controls="mobile-menu" aria-expanded="false" aria-label="{{ __('landing.nav.menu') }}" class="rounded-lg p-2 text-ink hover:bg-surface lg:hidden">
                <x-icon name="menu" />
            </button>
        </div>
    </div>

    <div id="mobile-menu" class="hidden border-t border-line bg-white lg:hidden">
        <nav class="mx-auto flex max-w-7xl flex-col px-4 py-3 sm:px-6" aria-label="Mobile">
            @foreach ($links as $key => $href)
                <a href="{{ $href }}" class="rounded-lg px-3 py-3 text-sm font-medium text-ink hover:bg-surface">{{ __('landing.nav.'.$key) }}</a>
            @endforeach
            <a href="{{ $wa }}" target="_blank" rel="noopener" class="mt-2 rounded-full bg-primary px-4 py-3 text-center text-sm font-semibold text-white">{{ __('landing.nav.wa') }}</a>
        </nav>
    </div>
</header>

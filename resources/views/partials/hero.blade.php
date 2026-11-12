@php
    $tiles = [
        ['code', 'tile_web', '#svc-web'],
        ['sparkles', 'tile_ai', '#svc-ai'],
        ['zap', 'tile_agent', '#svc-agent'],
        ['cloud', 'tile_offshore', '#svc-offshore'],
        ['shield', 'tile_quality', '#about'],
        ['trending', 'tile_automation', '#svc-ai'],
    ];
@endphp

<section id="home" class="hero relative isolate flex min-h-screen overflow-hidden pt-16 text-white">
    <canvas id="starfield" class="absolute inset-0 -z-10 h-full w-full" aria-hidden="true"></canvas>

    <div class="mx-auto grid w-full max-w-7xl items-end gap-4 px-4 sm:px-6 lg:grid-cols-[1.15fr_0.85fr] lg:gap-8 lg:px-8">
        <div class="pt-10 text-center lg:pb-20 lg:pt-16 lg:text-left">
            <p class="glass-dark mx-auto inline-flex max-w-full items-center gap-2 rounded-full px-4 py-2 text-xs font-semibold sm:text-sm lg:mx-0">
                <x-icon name="sparkles" class="h-4 w-4 shrink-0 text-amber-300" />
                <span>{{ __('landing.hero.badge') }}</span>
            </p>

            <h1 class="mt-6 text-4xl font-bold leading-[1.1] tracking-tight sm:text-5xl xl:text-6xl">
                <span class="block">{{ __('landing.hero.title_1') }}</span>
                <span class="gradient-text block">{{ __('landing.hero.title_2') }}</span>
            </h1>

            <p class="mx-auto mt-5 max-w-xl text-base leading-relaxed text-slate-300 sm:text-lg lg:mx-0">{{ __('landing.hero.intro') }}</p>

            <ul class="mx-auto mt-8 grid max-w-xl grid-cols-3 gap-3 lg:mx-0">
                @foreach ($tiles as [$icon, $key, $href])
                    <li>
                        <a href="{{ $href }}" class="glass-dark group flex h-full flex-col items-center gap-2 rounded-xl px-2 py-4 text-center text-xs font-semibold transition hover:border-primary/60 hover:bg-white/10 sm:text-sm">
                            <x-icon :name="$icon" class="h-6 w-6 text-white/90 transition group-hover:text-primary" />
                            <span>{{ __('landing.hero.'.$key) }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>

            <div class="mt-8 flex flex-col items-center gap-3 sm:flex-row sm:justify-center lg:justify-start">
                <a href="#contact" class="inline-flex items-center gap-2 rounded-full bg-white px-7 py-3.5 text-base font-semibold text-primary shadow-lg transition hover:bg-slate-100">
                    {{ __('landing.hero.cta_primary') }}
                    <x-icon name="arrow" class="h-4 w-4" />
                </a>
                <a href="#services" class="inline-flex items-center rounded-full bg-slate-200 px-7 py-3.5 text-base font-semibold text-ink transition hover:bg-white">
                    {{ __('landing.hero.cta_secondary') }}
                </a>
            </div>
        </div>

        <div class="relative mx-auto flex w-full max-w-sm items-end justify-center self-end lg:max-w-none">
            <div class="avatar-glow pointer-events-none absolute inset-x-0 bottom-0 h-3/4" aria-hidden="true"></div>
            <img
                src="{{ asset('images/avatar.webp') }}"
                alt="{{ __('landing.hero.avatar_alt') }}"
                width="1024" height="1536"
                fetchpriority="high"
                class="avatar relative z-10 h-auto max-h-[480px] w-auto object-contain lg:max-h-[82vh]"
            >
            <div class="glass-dark absolute bottom-6 left-0 z-20 hidden max-w-[16rem] rounded-2xl p-4 text-sm sm:block lg:-left-6">
                <p class="text-xs font-bold uppercase tracking-widest text-primary">{{ __('landing.hero.greeting_label') }}</p>
                <p class="mt-1 leading-snug text-slate-100">{{ __('landing.hero.greeting') }}</p>
            </div>
        </div>
    </div>
</section>

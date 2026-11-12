@php
    $points = [['SEO', 'seo_text'], ['AI', 'ai_text'], ['WA', 'wa_text'], ['JP', 'jp_text']];
@endphp

<section id="about" class="bg-white py-20 sm:py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-12 lg:grid-cols-2 lg:items-center">
            <div>
                <p class="text-sm font-semibold uppercase tracking-widest text-primary">{{ __('landing.about.eyebrow') }}</p>
                <h2 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">{{ __('landing.about.title') }}</h2>
                <p class="mt-5 text-lg text-muted">{{ __('landing.about.p1') }}</p>
                <p class="mt-4 text-muted">{{ __('landing.about.p2') }}</p>
            </div>

            <ul class="grid grid-cols-2 gap-4">
                @foreach ($points as [$badge, $key])
                    <li class="card p-5">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10 text-sm font-bold text-primary">{{ $badge }}</span>
                        <p class="mt-3 font-semibold">{{ __('landing.about.'.$key) }}</p>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</section>

@php
    $plans = [
        ['p5', 700000, false],
        ['p10', 1200000, true],
        ['p15', 1600000, false],
    ];
@endphp

<section id="pricing" class="bg-tint py-20 sm:py-24" data-usd-rate="{{ config('fts.usd_rate') }}">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <x-section-heading
            :eyebrow="__('landing.pricing.eyebrow')"
            :title="__('landing.pricing.title')"
            :sub="__('landing.pricing.sub')"
        />

        <div class="mb-10 flex justify-center">
            <div class="inline-flex rounded-full border border-line bg-white p-1 text-sm font-semibold" role="group" aria-label="Currency">
                <button type="button" data-currency="IDR" aria-pressed="true" class="rounded-full px-5 py-1.5 transition aria-pressed:bg-primary aria-pressed:text-white">IDR</button>
                <button type="button" data-currency="USD" aria-pressed="false" class="rounded-full px-5 py-1.5 transition aria-pressed:bg-primary aria-pressed:text-white">USD</button>
            </div>
        </div>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($plans as [$key, $idr, $featured])
                <article @class(['card relative flex flex-col p-6', 'ring-2 ring-primary' => $featured])>
                    @if ($featured)
                        <span class="absolute -top-3 left-6 rounded-full bg-primary px-3 py-1 text-xs font-bold text-white">{{ __('landing.pricing.popular') }}</span>
                    @endif
                    <h3 class="text-lg font-bold">{{ __('landing.pricing.'.$key) }}</h3>
                    <p class="mt-4">
                        <span class="price text-3xl font-extrabold" data-idr="{{ $idr }}">Rp {{ number_format($idr, 0, ',', '.') }}</span>
                        <span class="text-sm text-muted">{{ __('landing.pricing.month') }}</span>
                    </p>
                    <p class="mt-4 text-sm font-semibold text-primary-dark">{{ __('landing.pricing.free') }}</p>
                    <p class="mt-2 text-sm text-muted">{{ __('landing.pricing.contract') }}</p>
                    <a href="#contact" @class([
                        'mt-auto inline-flex justify-center rounded-full px-5 py-2.5 text-sm font-semibold transition',
                        'bg-primary text-white hover:bg-primary-dark' => $featured,
                        'border border-line bg-white text-ink hover:border-primary hover:text-primary' => ! $featured,
                    ])>{{ __('landing.pricing.cta') }}</a>
                </article>
            @endforeach

            <article class="card flex flex-col p-6">
                <h3 class="text-lg font-bold">{{ __('landing.pricing.quote_name') }}</h3>
                <p class="mt-4 text-sm text-muted">{{ __('landing.pricing.quote_desc') }}</p>
                <a href="#contact" class="mt-auto inline-flex justify-center rounded-full border border-line bg-white px-5 py-2.5 text-sm font-semibold text-ink transition hover:border-primary hover:text-primary">{{ __('landing.pricing.quote_cta') }}</a>
            </article>
        </div>
    </div>
</section>

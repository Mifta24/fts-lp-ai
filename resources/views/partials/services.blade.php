@php
    $services = [
        ['svc-web', 'web', 'code', true],
        ['svc-ai', 'ai', 'sparkles', false],
        ['svc-agent', 'agent', 'zap', false],
        ['svc-offshore', 'offshore', 'cloud', false],
    ];
@endphp

<section id="services" class="bg-tint py-20 sm:py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <x-section-heading
            :eyebrow="__('landing.services.eyebrow')"
            :title="__('landing.services.title')"
            :sub="__('landing.services.sub')"
        />

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($services as [$id, $key, $icon, $hasCase])
                <article id="{{ $id }}" class="card flex flex-col p-6">
                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-primary text-white">
                        <x-icon :name="$icon" class="h-6 w-6" />
                    </span>
                    <h3 class="mt-5 text-xl font-bold">{{ __('landing.services.'.$key.'.title') }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-muted">{{ __('landing.services.'.$key.'.desc') }}</p>

                    <ul class="mt-4 space-y-2 text-sm">
                        @foreach (['b1', 'b2', 'b3'] as $bullet)
                            <li class="flex gap-2">
                                <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-primary" aria-hidden="true"></span>
                                <span>{{ __('landing.services.'.$key.'.'.$bullet) }}</span>
                            </li>
                        @endforeach
                    </ul>

                    @if ($hasCase)
                        <p class="mt-4 border-t border-line pt-4 text-xs leading-relaxed text-muted">{{ __('landing.services.'.$key.'.case') }}</p>
                    @endif

                    <a href="#contact" class="mt-auto inline-flex items-center gap-1 pt-5 text-sm font-semibold text-primary hover:text-primary-dark">
                        {{ __('landing.services.cta') }}
                        <x-icon name="arrow" class="h-4 w-4" />
                    </a>
                </article>
            @endforeach
        </div>
    </div>
</section>

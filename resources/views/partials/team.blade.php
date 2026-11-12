@php
    $members = [
        ['n1', 'Yoshihiro Nakagawa', 'YN'],
        ['n2', 'Takakazu Kaburaki', 'TK'],
        ['n3', 'Naoki Yoshida', 'NY'],
        ['n4', 'Takaki Morita', 'TM'],
    ];
    $values = ['innovation', 'quality', 'transparency', 'growth'];
@endphp

<section id="team" class="bg-white py-20 sm:py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <x-section-heading
            :eyebrow="__('landing.team.eyebrow')"
            :title="__('landing.team.title')"
            :sub="__('landing.team.sub')"
        />

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($members as [$key, $name, $initials])
                <article class="card p-6">
                    <span class="inline-flex h-14 w-14 items-center justify-center rounded-full bg-primary/10 text-lg font-bold text-primary ring-1 ring-primary/30" aria-hidden="true">{{ $initials }}</span>
                    <h3 class="mt-4 text-lg font-bold">{{ $name }}</h3>
                    <p class="text-sm font-semibold text-primary">{{ __('landing.team.'.$key.'.role') }}</p>
                    <p class="mt-3 text-sm leading-relaxed text-muted">{{ __('landing.team.'.$key.'.bio') }}</p>
                </article>
            @endforeach
        </div>

        <h3 class="mb-6 mt-16 text-center text-2xl font-bold">{{ __('landing.values.title') }}</h3>
        <ul class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($values as $value)
                <li class="card p-5 text-center">
                    <p class="font-bold">{{ __('landing.values.'.$value) }}</p>
                    <p class="mt-1 text-sm text-muted">{{ __('landing.values.'.$value.'_desc') }}</p>
                </li>
            @endforeach
        </ul>
    </div>
</section>

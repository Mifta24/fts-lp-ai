<section id="how" class="bg-white py-20 sm:py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <x-section-heading :eyebrow="__('landing.how.eyebrow')" :title="__('landing.how.title')" />

        <ol class="grid gap-6 md:grid-cols-3">
            @foreach ([1, 2, 3] as $step)
                <li class="card p-6">
                    <span class="gradient-text text-5xl font-extrabold">{{ $step }}</span>
                    <h3 class="mt-3 text-xl font-bold">{{ __('landing.how.s'.$step.'_title') }}</h3>
                    <p class="mt-2 text-muted">{{ __('landing.how.s'.$step.'_desc') }}</p>
                </li>
            @endforeach
        </ol>
    </div>
</section>

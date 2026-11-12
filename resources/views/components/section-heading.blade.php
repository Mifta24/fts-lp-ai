@props(['eyebrow' => null, 'title', 'sub' => null, 'center' => true])

<header @class(['mb-12 max-w-2xl', 'mx-auto text-center' => $center])>
    @if ($eyebrow)
        <p class="text-sm font-semibold uppercase tracking-widest text-primary">{{ $eyebrow }}</p>
    @endif
    <h2 class="mt-3 text-3xl font-bold tracking-tight text-ink sm:text-4xl">{{ $title }}</h2>
    @if ($sub)
        <p class="mt-4 text-lg text-muted">{{ $sub }}</p>
    @endif
</header>

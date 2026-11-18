@props(['logo' => null, 'initial' => 'F', 'label'])
{{-- The "AI portal" shown while a scene is being prepared. stage.js only opens it when loading really takes a while. --}}
<div class="stage-loader" data-stage-loader role="status" aria-live="polite">
    <div class="portal" aria-hidden="true">
        <span class="portal-glow"></span>
        <span class="portal-wave"></span>
        <span class="portal-wave portal-wave--late"></span>
        <span class="portal-ring portal-ring--outer"></span>
        <span class="portal-ring portal-ring--dash"></span>
        <span class="portal-ring portal-ring--arc"></span>
        <span class="portal-ring portal-ring--orbit"><i></i></span>
        <span class="portal-particles">
            @foreach (range(0, 9) as $particle)
                <i style="--a: {{ $particle * 36 }}deg; --d: {{ round(($particle * 7 % 10) * 0.24, 2) }}s"></i>
            @endforeach
        </span>
        <span class="portal-core">@if ($logo)<img src="{{ $logo }}" alt="">@else{{ $initial }}@endif</span>
    </div>
    <p class="portal-label">
        <span class="portal-eyebrow">AI Concierge</span>
        <span class="portal-text">{{ $label }}</span>
    </p>
</div>

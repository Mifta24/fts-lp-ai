@props(['name' => 'tour'])
<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" {{ $attributes }}>
    @switch($name)
        @case('rooms')
            <rect x="3" y="4" width="18" height="16" rx="2.5"/><path d="M3 9h18M6.5 6.5h.01M9.5 6.5h.01M8 13h8M8 16h5"/>
            @break
        @case('facilities')
            <path d="m12 3 9 5-9 5-9-5 9-5Z"/><path d="m3 12.5 9 5 9-5"/><path d="m3 16.5 9 5 9-5"/>
            @break
        @case('solutions')
            <path d="M11 3.5 12.9 8l4.6 1.9-4.6 1.9L11 16.3l-1.9-4.5L4.5 9.9 9.1 8z"/><path d="m18.5 14 .9 2.1 2.1.9-2.1.9-.9 2.1-.9-2.1-2.1-.9 2.1-.9z"/>
            @break
        @case('portfolio')
            <rect x="3" y="7" width="18" height="13" rx="2.5"/><path d="M8.5 7V5.5A2 2 0 0 1 10.5 3.5h3a2 2 0 0 1 2 2V7M3 13h18M11 13v1.5h2V13"/>
            @break
        @case('info')
            <circle cx="12" cy="12" r="9"/><path d="M12 11v5.5M12 7.5h.01"/>
            @break
        @case('reservation')
            <path d="M5.5 15.5c-1.6 1.3-2 4.5-2 4.5s3.2-.4 4.5-2"/><path d="m9.5 14.5-2-2c1-4.5 4.8-8.3 12-9-.7 7.2-4.5 11-9 12z"/><circle cx="15" cy="9" r="1.6"/>
            @break
        @case('staff')
            <path d="M20.5 12a8.5 8.5 0 0 1-12.4 7.5L3.5 20.5l1-4.4A8.5 8.5 0 1 1 20.5 12Z"/><path d="M8.5 12h.01M12 12h.01M15.5 12h.01"/>
            @break
        @default
            <circle cx="12" cy="12" r="9"/><path d="m15.8 8.2-2.1 5.5-5.5 2.1 2.1-5.5z"/>
    @endswitch
</svg>

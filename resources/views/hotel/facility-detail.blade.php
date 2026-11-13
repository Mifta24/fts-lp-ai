<x-hotel-stage :prefill="$prefill" :hotel="$hotel" :locale="$locale" :supported-locales="$supportedLocales" :labels="$labels" :lobby="$lobby" :narration="$narration" :scene="$scene" :backdrop="$backdrop" :menu-items="$menuItems" :wizard="$wizard" :room-terms="$roomTerms" :today="$today" :tour-stops="$tourStops" :menu="false" :title="$facility->translatedTitle($locale).' · '.$hotel->name">
    @php
        $title = $facility->translatedTitle($locale);
        $facilityUrl = fn ($item) => route('hotel.facility', ['hotelSlug' => $hotel->slug, 'facilityId' => $item->id, 'lang' => $locale]);
    @endphp

    <div class="stage-panels">
        <section class="lobby-content stage-panel-right @container" aria-label="{{ $title }}">
            <a href="{{ route($section['route'], ['hotelSlug' => $hotel->slug, 'lang' => $locale]) }}" data-stage-exit class="panel-close" aria-label="{{ $section['back'] }}"><span aria-hidden="true">×</span></a>
            <article class="room-scene">
                <div class="flex flex-wrap items-center justify-between gap-3 pr-12">
                    <p class="lobby-eyebrow">{{ $section['counter'] }} {{ $facilityIndex + 1 }} / {{ $facilities->count() }}</p>
                    @if ($facilities->count() > 1)
                        <nav class="flex gap-2" aria-label="{{ $section['heading'] }}">
                            <a href="{{ $facilityUrl($previousFacility) }}" data-stage-exit class="room-scene-step" rel="prev"><span aria-hidden="true">←</span> {{ $lobby['prev_facility'] }}</a>
                            <a href="{{ $facilityUrl($nextFacility) }}" data-stage-exit class="room-scene-step" rel="next">{{ $lobby['next_facility'] }} <span aria-hidden="true">→</span></a>
                        </nav>
                    @endif
                </div>
                @if ($facility->image_url)
                    <div class="facility-hero mt-5">
                        <img src="{{ $facility->image_url }}" alt="{{ $title }}">
                        <span class="facility-card-icon"><x-facility-icon :name="$facility->iconName()" /></span>
                    </div>
                @endif
                @if (count($facility->photoUrls()) > 1)
                    <div class="facility-gallery mt-3">
                        @foreach (array_slice($facility->photoUrls(), 1) as $photoUrl)
                            <img src="{{ $photoUrl }}" alt="{{ $title }}" loading="lazy">
                        @endforeach
                    </div>
                @endif
                <h2>{{ $title }}</h2>
                @if ($facility->openingHours())
                    <p class="facility-hours">{{ $labels['chat_hours'] }}: {{ $facility->openingHours() }}</p>
                @endif
                @include('hotel.narrator', ['text' => $facility->translatedBody($locale), 'key' => 'facility-'.$facility->id, 'inline' => true])
                @if ($facility->menuEntries() !== [])
                    <ul class="rc-menu facility-menu mt-5">
                        @foreach ($facility->menuEntries() as $dish)
                            <li>
                                <span class="rc-menu-name">{{ $dish['name'] }}</span>
                                @if ($dish['price'])<span class="rc-menu-price">{{ $dish['price'] }}</span>@endif
                                @if ($dish['description'])<span class="rc-menu-desc">{{ $dish['description'] }}</span>@endif
                            </li>
                        @endforeach
                    </ul>
                @endif
                <div class="mt-8 flex flex-wrap gap-3">
                    <button type="button" class="lobby-action" data-hero-quick-message="{{ str_replace(':name', $title, $section['ask_q']) }}">{{ $section['ask'] }} <span aria-hidden="true">↗</span></button>
                    <a href="{{ route($section['route'], ['hotelSlug' => $hotel->slug, 'lang' => $locale]) }}" data-stage-exit class="room-scene-step">{{ $section['back'] }}</a>
                </div>
            </article>
        </section>
    </div>
</x-hotel-stage>

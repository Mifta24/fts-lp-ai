<x-hotel-stage :prefill="$prefill" :hotel="$hotel" :locale="$locale" :supported-locales="$supportedLocales" :labels="$labels" :lobby="$lobby" :narration="$narration" :scene="$scene" :backdrop="$backdrop" :menu-items="$menuItems" :wizard="$wizard" :room-terms="$roomTerms" :today="$today" :tour-stops="$tourStops" :menu="false" :title="$labels['rooms_heading'].' · '.$hotel->name">
    @if ($roomNarrations['rooms'])
        <x-slot:welcome>
            <div class="stage-welcome stage-welcome-scene" data-anchor="{{ $backdrop['text'] }}">
                @include('hotel.narrator', ['text' => $roomNarrations['rooms'], 'key' => 'rooms', 'onStage' => true])
            </div>
        </x-slot:welcome>
    @endif

    {{-- The plans are the focus of this scene: one large glass card in the middle, the concierge on the left and the chat on the right. --}}
    <section class="plan-board" data-plan-board aria-labelledby="plan-board-title">
        <a href="{{ route('hotel.show', ['hotelSlug' => $hotel->slug, 'lang' => $locale]) }}" data-stage-exit data-tour-line="{{ $narration['tour_lobby'] }}" class="panel-close" aria-label="{{ $lobby['back'] }}"><span aria-hidden="true">×</span></a>
        <header class="plan-board-head">
            <p class="lobby-eyebrow">{{ $lobby['explore'] }}</p>
            <h1 id="plan-board-title">{{ $labels['rooms_heading'] }}</h1>
            @if ($roomTypes->isNotEmpty())
                <p class="plan-board-note">{{ $lobby['availability_note'] }}</p>
            @endif
        </header>

        @if ($roomTypes->isNotEmpty())
            <ol class="plan-grid">
                @foreach ($roomTypes as $plan)
                    @php
                        $planUrl = route('hotel.room', ['hotelSlug' => $hotel->slug, 'roomSlug' => $plan->slug, 'lang' => $locale]);
                        $photo = $plan->images->first();
                    @endphp
                    <li class="plan-card" data-plan="{{ $plan->slug }}" style="--i: {{ $loop->index }}">
                        <a href="{{ $planUrl }}" data-stage-exit class="plan-card-photo" tabindex="-1" aria-hidden="true">
                            @if ($photo)
                                <img src="{{ $photo->image_source }}" alt="" loading="lazy">
                            @endif
                            <span class="plan-card-index">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        </a>
                        <div class="plan-card-body">
                            <h2 class="plan-card-name">{{ $plan->translatedName($locale) }}</h2>
                            <p class="plan-card-price">
                                @if (filled($labels['from']))<span>{{ $labels['from'] }}</span>@endif
                                <span>{{ $hotel->currency }}</span>
                                <strong>{{ number_format((float) $plan->base_price, 0, ',', '.') }}</strong>
                                <span>{{ $labels['per_night'] }}</span>
                            </p>
                            @if (filled($plan->translatedDescription($locale)))
                                <p class="plan-card-desc">{{ $plan->translatedDescription($locale) }}</p>
                            @endif
                            <div class="plan-card-actions">
                                <a href="{{ route('hotel.reservation', ['hotelSlug' => $hotel->slug, 'lang' => $locale, 'room' => $plan->slug]) }}" data-stage-exit data-tour-line="{{ $narration['tour_reservation'] }}" class="plan-card-cta">{{ $lobby['reserve_room'] }} <span aria-hidden="true">↗</span></a>
                                <a href="{{ $planUrl }}" data-stage-exit class="plan-card-more">{{ $labels['view_details'] }} <span aria-hidden="true">→</span></a>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ol>
        @else
            <p class="plan-board-empty">{{ $lobby['rooms_empty'] }}</p>
        @endif
    </section>
</x-hotel-stage>

<x-hotel-stage :prefill="$prefill" :hotel="$hotel" :locale="$locale" :supported-locales="$supportedLocales" :labels="$labels" :lobby="$lobby" :narration="$narration" :scene="$scene" :backdrop="$backdrop" :menu-items="$menuItems" :wizard="$wizard" :room-terms="$roomTerms" :today="$today" :tour-stops="$tourStops" :menu="false" :title="$section['heading'].' · '.$hotel->name">
    <div class="stage-panels">
        <section class="lobby-content stage-panel-right float-field @container" data-float-field aria-label="{{ $section['heading'] }}">
            <a href="{{ route('hotel.show', ['hotelSlug' => $hotel->slug, 'lang' => $locale]) }}" data-stage-exit data-tour-line="{{ $narration['tour_lobby'] }}" class="panel-close" aria-label="{{ $lobby['back'] }}"><span aria-hidden="true">×</span></a>
            <p class="lobby-eyebrow">{{ $labels['chat_heading'] }}</p>
            <h2>{{ $section['heading'] }}</h2>
            @if ($sceneNarrations['facilities'])
                @include('hotel.narrator', ['text' => $sceneNarrations['facilities'], 'key' => 'facilities', 'inline' => true, 'onStage' => true])
            @endif

            <div class="facility-grid">
                @forelse ($facilities as $item)
                    @php
                        $title = $item->translatedTitle($locale);
                        // Each card drifts at its own depth and pace, so the field never moves in lockstep.
                        $index = $loop->index;
                        $motion = sprintf(
                            '--i: %d; --depth: %s; --float-duration: %ss; --float-delay: -%ss; --sway: %sdeg',
                            $index,
                            [0.6, 1, 1.4][$index % 3],
                            round(5 + ($index * 7 % 5) * 0.6, 2),
                            round(($index * 13 % 9) * 0.7, 2),
                            $index % 2 === 0 ? 0.4 : -0.4,
                        );
                    @endphp
                    @php($role = $item->demoRole($locale))
                    <a href="{{ route('hotel.facility', ['hotelSlug' => $hotel->slug, 'facilityId' => $item->id, 'lang' => $locale]) }}" data-stage-exit class="facility-card" style="{{ $motion }}">
                        @if ($role)
                            {{-- A live demo's cover: its screenshot (or a tinted ground) with a little chat from the AI staff member it showcases. --}}
                            <span class="facility-card-photo facility-card-cover @if($item->image_url) has-photo @endif" style="--hue: {{ [205, 222, 246, 192, 262][$index % 5] }}" aria-hidden="true">
                                @if ($item->image_url)
                                    <img src="{{ $item->image_url }}" alt="" loading="lazy">
                                @endif
                                <span class="cover-chat">
                                    <span class="cover-avatar"><x-menu-icon name="solutions" /></span>
                                    <span class="cover-bubble"><span class="cover-role">{{ $role }}</span><span class="cover-typing"><i></i><i></i><i></i></span></span>
                                </span>
                            </span>
                        @else
                            <span class="facility-card-photo">
                                @if ($item->image_url)
                                    <img src="{{ $item->image_url }}" alt="" loading="lazy">
                                @else
                                    <span class="facility-card-icon"><x-facility-icon :name="$item->iconName()" /></span>
                                @endif
                            </span>
                        @endif
                        <span class="facility-card-body">
                            @if ($role)
                                <span class="card-badge">{{ $lobby['live_demo'] }}</span>
                            @endif
                            <span class="facility-card-title">{{ $title }}</span>
                            <span class="facility-card-text">{{ $item->translatedBody($locale) }}</span>
                            <span class="facility-card-more">{{ $labels['view_details'] }} <span class="facility-card-go" aria-hidden="true">→</span></span>
                        </span>
                    </a>
                @empty
                    <p class="text-stone-500">{{ $lobby['empty'] }}</p>
                @endforelse
            </div>
        </section>
    </div>
</x-hotel-stage>

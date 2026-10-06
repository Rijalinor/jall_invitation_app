<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $title }}">
    <title>{{ $title }}</title>
    @include('invitations.shared.og')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,600;0,9..144,700;1,9..144,400&family=Karla:wght@400;500;600;700&display=swap" rel="stylesheet">
    {{-- Marks that scripting is available before the first paint. --}}
    <script>document.documentElement.classList.add('js-ready');</script>
    @vite(['resources/css/invitations.css', 'resources/js/invitation-hosts.js', 'resources/js/invitation-forms.js', 'resources/js/invitation-lightbox.js', 'resources/invitation-templates/coastal-vow/assets/theme.css', 'resources/invitation-templates/coastal-vow/assets/theme.js'])
</head>
<body class="coastal-vow" style="--cv-accent: {{ $theme['accent_color'] }}; --cv-focal-x: {{ $theme['cover_focal_x'] }}%; --cv-focal-y: {{ $theme['cover_focal_y'] }}%; --cv-overlay: {{ $theme['cover_overlay_opacity'] / 100 }}" data-motion="{{ $theme['motion'] }}">
    @php
        $navItems = [
            'hosts' => ['id' => 'hosts', 'icon' => '❦', 'label' => 'Mempelai'],
            'events' => ['id' => 'events', 'icon' => '☼', 'label' => 'Acara'],
            'story' => ['id' => 'story', 'icon' => '❧', 'label' => 'Cerita'],
            'gallery' => ['id' => 'gallery', 'icon' => '▤', 'label' => 'Galeri'],
            'rsvp' => ['id' => 'rsvp', 'icon' => '✎', 'label' => 'RSVP'],
        ];
        // A dock link only earns its place when the section it points at will
        // actually render, so an empty invitation never shows a dead anchor.
        $hasContent = ['hosts' => count($hosts), 'events' => count($events), 'story' => count($stories), 'gallery' => count($gallery), 'rsvp' => true];
        $visibleNav = collect($sections)->filter(fn ($section) => isset($navItems[$section]) && ($hasContent[$section] ?? true))->mapWithKeys(fn ($section) => [$section => $navItems[$section]])->all();
        $groomName = collect($hosts)->firstWhere('role', 'groom')['name'] ?? ($hosts[0]['name'] ?? null);
        $brideName = collect($hosts)->firstWhere('role', 'bride')['name'] ?? ($hosts[1]['name'] ?? null);
        $coverImage = $theme['cover_poster_image'] ?: ($gallery[0]['url'] ?? ($hosts[0]['photo_url'] ?? null));
        $coverDesktop = $theme['cover_video_enabled'] ? $theme['cover_video_desktop'] : null;
        $coverMobile = $theme['cover_video_enabled'] ? ($theme['cover_video_mobile'] ?: $coverDesktop) : null;
        // The opening section reuses the cover video whenever one is uploaded.
        $openingVideo = $coverDesktop;
        $openingVideoMobile = $coverMobile;
    @endphp
    <div class="cv-cover" id="opening-cover">
        @if ($coverImage)<img class="cv-cover__poster" src="{{ $coverImage }}" alt="" aria-hidden="true">@endif
        @if ($coverDesktop)
            <video class="cv-cover__video cv-cover__video--desktop" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video>
                <source src="{{ $coverDesktop }}">
            </video>
        @endif
        @if ($coverMobile && $coverMobile !== $coverDesktop)
            <video class="cv-cover__video cv-cover__video--mobile" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video>
                <source src="{{ $coverMobile }}">
            </video>
        @endif
        <span class="cv-cover__sun" aria-hidden="true"></span>
        <div class="cv-cover__shade" aria-hidden="true"></div>
        <div class="cv-cover__waves" aria-hidden="true">
            <svg viewBox="0 0 1440 320" preserveAspectRatio="none" focusable="false">
                <path fill="currentColor" opacity=".22" d="M0,176 C240,240 480,112 720,144 C960,176 1210,272 1440,208 L1440,320 L0,320 Z"></path>
                <path fill="currentColor" opacity=".4" d="M0,224 C260,272 520,160 780,192 C1040,224 1230,304 1440,256 L1440,320 L0,320 Z"></path>
                <path fill="currentColor" opacity=".85" d="M0,272 C300,304 560,224 840,248 C1120,272 1290,312 1440,288 L1440,320 L0,320 Z"></path>
            </svg>
        </div>
        <div class="cv-cover__body">
            <span class="invitation-eyebrow">{{ $labels['cover_eyebrow'] }}</span>
            @if ($groomName && $brideName)
                <h1 class="cv-couple"><span>{{ $groomName }}</span><i>&amp;</i><span>{{ $brideName }}</span></h1>
            @else
                <h1>{{ $title }}</h1>
            @endif
            @if ($primary_event)<span class="cv-cover__date">{{ $primary_event['date'] }}</span>@endif
            @if ($primary_event && $primary_event['timestamp'])
                <div class="cv-cover__countdown" data-countdown="{{ $primary_event['timestamp'] }}" aria-label="Hitung mundur menuju acara">
                    <span class="cv-cover__countdown-label">{{ $labels['cover_countdown_label'] }}</span>
                    <div data-countdown-output>
                        @foreach (['days' => 'Hari', 'hours' => 'Jam', 'minutes' => 'Menit', 'seconds' => 'Detik'] as $unit => $label)
                            <span><strong data-countdown-unit="{{ $unit }}">00</strong><small>{{ $label }}</small></span>
                        @endforeach
                    </div>
                </div>
            @endif
            @if ($recipient)
                <div class="cv-cover__recipient">
                    <span>{{ $labels['cover_recipient_label'] }}</span>
                    <strong>{{ $recipient }}</strong>
                </div>
            @endif
            <a class="cv-cover__cta" href="#coastal-content" data-open-invitation>{{ $labels['cover_cta'] }}</a>
        </div>
    </div>

    <nav class="cv-dock" aria-label="Navigasi undangan">
        <a href="#coastal-content" class="cv-dock__home"><span class="cv-dock__icon" aria-hidden="true">≈</span><span class="cv-dock__label">Awal</span></a>
        @foreach ($visibleNav as $item)
            <a href="#{{ $item['id'] }}" data-dock-link="{{ $item['id'] }}"><span class="cv-dock__icon" aria-hidden="true">{{ $item['icon'] }}</span><span class="cv-dock__label">{{ $item['label'] }}</span></a>
        @endforeach
    </nav>

    <span class="cv-tide" aria-hidden="true"></span>

    <main id="coastal-content" tabindex="-1" data-gate>
        @foreach ($sections as $section)
            @if ($section === 'opening')
                <section class="invitation-section cv-hero" data-height="{{ $section_heights['opening'] ?? 'full' }}" aria-labelledby="cv-opening-title">
                    @if ($openingVideo)
                        @if ($openingVideoMobile !== $openingVideo)
                            <video class="cv-hero__video cv-hero__video--desktop" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video><source src="{{ $openingVideo }}"></video>
                            <video class="cv-hero__video cv-hero__video--mobile" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video><source src="{{ $openingVideoMobile }}"></video>
                        @else
                            <video class="cv-hero__video" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video><source src="{{ $openingVideo }}"></video>
                        @endif
                    @endif
                    <span class="invitation-eyebrow">{{ $labels['opening_eyebrow'] }}</span>
                    @if (count($hosts) >= 2)
                        <h2 id="cv-opening-title" class="cv-couple"><span>{{ $hosts[0]['name'] }}</span><i>&amp;</i><span>{{ $hosts[1]['name'] }}</span></h2>
                    @else
                        <h2 id="cv-opening-title">{{ $title }}</h2>
                    @endif
                    @if ($opening_text)<p data-reveal>{!! nl2br(e($opening_text)) !!}</p>@endif
                    @if ($primary_event)<p class="cv-hero__date" data-reveal>{{ $primary_event['date'] }}</p>@endif
                </section>
            @elseif ($section === 'events' && count($events))
                <section class="invitation-section cv-events" id="events" data-height="{{ $section_heights['events'] ?? 'full' }}" aria-labelledby="cv-events-title">
                    <span class="invitation-eyebrow">{{ $labels['events_eyebrow'] }}</span>
                    <h2 id="cv-events-title">{{ $labels['events_title'] }}</h2>
                    <div class="cv-events__list">
                        @foreach ($events as $event)
                            <article class="cv-event" data-reveal>
                                <div class="cv-event__head">
                                    <h3>{{ $event['label'] }}</h3>
                                    <p class="cv-event__date">{{ $event['date'] }}</p>
                                    @if ($event['start_time'])<p class="cv-event__time">{{ $event['start_time'] }}{{ $event['end_time'] ? ' – '.$event['end_time'] : ' s/d Selesai' }}{{ ($theme['hide_timezone'] ?? false) ? '' : ' '.$event['timezone_label'] }}</p>@endif
                                </div>
                                @if ($event['venue'] || $event['address'])
                                    <div class="cv-event__venue">
                                        @if ($event['venue'])<strong>{{ $event['venue'] }}</strong>@endif
                                        @if ($event['address'])<p>{{ $event['address'] }}</p>@endif
                                    </div>
                                @endif
                                <div class="cv-actions">
                                    @if (in_array('map', $sections) && $event['directions_url'])<a href="{{ $event['directions_url'] }}" target="_blank" rel="noopener noreferrer">Petunjuk Arah</a>@endif
                                    @if (in_array('map', $sections) && $event['address'])<button type="button" data-copy="{{ $event['address'] }}">Salin Alamat</button>@endif
                                </div>
                                @if (count($event['notes']))<div class="cv-event__notes">@foreach ($event['notes'] as $note)<small>{!! nl2br(e($note)) !!}</small>@endforeach</div>@endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @elseif ($section === 'countdown' && $primary_event && $primary_event['timestamp'])
                <section class="invitation-section cv-countdown" data-height="{{ $section_heights['countdown'] ?? 'full' }}" data-countdown="{{ $primary_event['timestamp'] }}" aria-labelledby="cv-countdown-title">
                    <span class="invitation-eyebrow">{{ $labels['countdown_eyebrow'] }}</span>
                    <h2 id="cv-countdown-title">{{ $labels['countdown_title'] }}</h2>
                    <div class="cv-countdown__units" data-countdown-output>
                        @foreach (['days' => 'Hari', 'hours' => 'Jam', 'minutes' => 'Menit', 'seconds' => 'Detik'] as $unit => $label)
                            <span><strong data-countdown-unit="{{ $unit }}">00</strong><small>{{ $label }}</small></span>
                        @endforeach
                    </div>
                </section>
            @elseif ($section === 'hosts' && count($hosts))
                <section class="invitation-section invitation-section--tint cv-hosts" id="hosts" data-height="{{ $section_heights['hosts'] ?? 'full' }}" aria-labelledby="cv-hosts-title">
                    <span class="invitation-eyebrow">{{ $labels['hosts_eyebrow'] }}</span>
                    <h2 id="cv-hosts-title">{{ $labels['hosts_title'] }}</h2>
                    <div class="cv-hosts__grid" data-count="{{ count($hosts) }}">
                        @foreach ($hosts as $host)
                            <article class="cv-host" data-host data-reveal>
                                <button type="button" class="host-card__trigger cv-host__trigger" data-host-open data-host-name="{{ $host['name'] }}" data-host-role="{{ match ($host['role']) { 'groom' => 'Mempelai Pria', 'bride' => 'Mempelai Wanita', default => 'Mempelai' } }}" aria-haspopup="dialog" aria-label="Lihat profil {{ $host['name'] }}">
                                    <div class="cv-host__portrait">
                                        @if ($host['photo_url'])<img src="{{ $host['photo_url'] }}" alt="Foto {{ $host['name'] }}" loading="lazy" decoding="async">@else<span aria-hidden="true">{{ mb_substr($host['name'], 0, 1) }}</span>@endif
                                    </div>
                                </button>
                                <small class="cv-host__role">{{ match ($host['role']) { 'groom' => 'Mempelai Pria', 'bride' => 'Mempelai Wanita', default => 'Mempelai' } }}</small>
                                <h3>{{ $host['name'] }}</h3>
                                @if ($host['family'])<p>{{ $host['family'] }}</p>@endif
                                @if ($host['instagram'])<a class="cv-host__social" href="{{ $host['instagram'] }}" target="_blank" rel="noopener noreferrer">Instagram</a>@endif
                                <div class="host-card__details cv-host__details" data-host-details>
                                    @if ($host['birth_order'])<p>{{ $host['birth_order'] }}</p>@endif
                                    @if ($host['bio'])<p>{!! nl2br(e($host['bio'])) !!}</p>@endif
                                </div>
                            </article>
                            @if (count($hosts) === 2 && ! $loop->last)<span class="cv-hosts__and" aria-hidden="true">&amp;</span>@endif
                        @endforeach
                    </div>
                </section>
            @elseif ($section === 'story' && count($stories))
                <section class="invitation-section cv-story" id="story" data-height="{{ $section_heights['story'] ?? 'full' }}" aria-labelledby="cv-story-title">
                    <span class="invitation-eyebrow">{{ $labels['story_eyebrow'] }}</span>
                    <h2 id="cv-story-title">{{ $labels['story_title'] }}</h2>
                    <div class="cv-story__timeline">
                        @foreach ($stories as $story)
                            <article data-reveal>
                                @if ($story['image_url'])<img src="{{ $story['image_url'] }}" alt="{{ $story['title'] }}" loading="lazy" decoding="async">@endif
                                <small>{{ $story['date'] }}</small>
                                <h3>{{ $story['title'] }}</h3>
                                @if ($story['body'])<p>{!! nl2br(e($story['body'])) !!}</p>@endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @elseif ($section === 'gallery' && count($gallery))
                <section class="invitation-section invitation-section--tint cv-gallery" id="gallery" data-height="{{ $section_heights['gallery'] ?? 'full' }}" aria-labelledby="cv-gallery-title">
                    <span class="invitation-eyebrow">{{ $labels['gallery_eyebrow'] }}</span>
                    <h2 id="cv-gallery-title">{{ $labels['gallery_title'] }}</h2>
                    <div class="cv-gallery__rail">
                        @foreach (array_chunk($gallery, 3) as $page)
                            <div class="cv-gallery__page">
                                @foreach ($page as $image)
                                    <figure>
                                        <button type="button" data-lightbox-src="{{ $image['url'] }}" data-lightbox-alt="{{ $image['alt'] }}"><img src="{{ $image['url'] }}" alt="{{ $image['alt'] }}" loading="lazy" decoding="async"></button>
                                        @if ($image['caption'])<figcaption>{{ $image['caption'] }}</figcaption>@endif
                                    </figure>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </section>
            @elseif ($section === 'map' && $primary_event && $primary_event['map_embed_url'])
                <section class="invitation-section cv-map" id="map" data-height="{{ $section_heights['map'] ?? 'full' }}" aria-labelledby="cv-map-title">
                    <span class="invitation-eyebrow">{{ $labels['map_eyebrow'] }}</span>
                    <h2 id="cv-map-title">{{ $labels['map_title'] }}</h2>
                    <div class="cv-map__frame"><iframe src="{{ $primary_event['map_embed_url'] }}" title="Peta {{ $primary_event['venue'] ?: $primary_event['label'] }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe></div>
                    @if ($primary_event['address'])<p class="cv-map__address">{{ $primary_event['address'] }}</p>@endif
                    <div class="cv-actions cv-actions--center">
                        @if ($primary_event['directions_url'])<a href="{{ $primary_event['directions_url'] }}" target="_blank" rel="noopener noreferrer">Buka Google Maps</a>@endif
                        @if ($primary_event['address'])<button type="button" data-copy="{{ $primary_event['address'] }}">Salin Alamat</button>@endif
                    </div>
                </section>
            @elseif (str_starts_with($section, 'blocks') && ! empty($block_sections[$section] ?? $blocks))
                <div class="cv-shared">@include('invitations.shared.blocks', ['blocks' => $block_sections[$section] ?? $blocks])</div>
            @elseif ($section === 'rsvp')
                @if ($theme['merge_rsvp_guestbook'] ?? false)
                    <div class="cv-shared">@include('invitations.shared.confirmation')</div>
                @else
                    <div class="cv-shared" id="rsvp">@include('invitations.shared.rsvp')</div>
                @endif
            @elseif ($section === 'guestbook')
                @unless ($theme['merge_rsvp_guestbook'] ?? false)
                    <div class="cv-shared">@include('invitations.shared.guestbook')</div>
                @endunless
            @elseif ($section === 'gifts' && count($gifts))
                <section class="invitation-section cv-gifts" data-height="{{ $section_heights['gifts'] ?? 'full' }}" aria-labelledby="cv-gifts-title">
                    <span class="invitation-eyebrow">{{ $labels['gifts_eyebrow'] }}</span>
                    <h2 id="cv-gifts-title">{{ $labels['gifts_title'] }}</h2>
                    <p>{!! nl2br(e($labels['gifts_intro'])) !!}</p>
                    @include('invitations.shared.gifts')
                </section>
            @elseif ($section === 'contacts' && count($contacts))
                <section class="invitation-section invitation-section--tint cv-contacts" data-height="{{ $section_heights['contacts'] ?? 'full' }}" aria-labelledby="cv-contacts-title">
                    <span class="invitation-eyebrow">{{ $labels['contacts_eyebrow'] }}</span>
                    <h2 id="cv-contacts-title">{{ $labels['contacts_title'] }}</h2>
                    <p>{!! nl2br(e($labels['contacts_intro'])) !!}</p>
                    <div class="cv-contacts__grid">
                        @foreach ($contacts as $contact)
                            <article class="cv-contact" data-reveal>
                                <small>{{ $contact['label'] }}</small>
                                <h3>{{ $contact['name'] }}</h3>
                                <div class="cv-actions cv-actions--center">
                                    <a href="{{ $contact['whatsapp_url'] }}" target="_blank" rel="noopener noreferrer">WhatsApp</a>
                                    <a href="{{ $contact['phone_url'] }}">Telepon</a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @elseif ($section === 'livestream' && $livestream_url)
                <section class="invitation-section cv-livestream" data-height="{{ $section_heights['livestream'] ?? 'full' }}">
                    <h2>{{ $livestream_label }}</h2>
                    <div class="cv-actions cv-actions--center"><a href="{{ $livestream_url }}" target="_blank" rel="noopener noreferrer">{{ $livestream_label }}</a></div>
                </section>
            @elseif ($section === 'sharing')
                <section class="invitation-section cv-sharing" data-height="{{ $section_heights['sharing'] ?? 'full' }}" aria-labelledby="cv-sharing-title">
                    <span class="invitation-eyebrow">{{ $labels['sharing_eyebrow'] }}</span>
                    <h2 id="cv-sharing-title">{{ $labels['sharing_title'] }}</h2>
                    <p>{!! nl2br(e($labels['sharing_intro'])) !!}</p>
                    <div class="cv-actions cv-actions--center">
                        <button type="button" data-share data-share-url="{{ $share_url }}">Bagikan Sekarang</button>
                        <a href="{{ $whatsapp_url }}" target="_blank" rel="noopener noreferrer">Kirim via WhatsApp</a>
                    </div>
                </section>
            @elseif ($section === 'closing')
                <section class="invitation-section cv-closing" data-height="{{ $section_heights['closing'] ?? 'full' }}">
                    <span class="invitation-eyebrow">{{ $labels['closing_eyebrow'] }}</span>
                    <h2>{{ $couple_title }}</h2>
                    @if ($closing_message)<p>{!! nl2br(e($closing_message)) !!}</p>@endif
                    @include('invitations.shared.closing-families')
                    @if ($closing_footer)<p>{!! nl2br(e($closing_footer)) !!}</p>@endif
                    <p class="cv-closing__kicker">{{ $labels['closing_kicker'] }}</p>
                </section>
            @endif
        @endforeach
    </main>

    @if ($music_url)
        <audio data-music loop preload="none" src="{{ $music_url }}"></audio>
        <button class="cv-music" type="button" data-music-toggle aria-label="Putar musik"><span aria-hidden="true">♪</span></button>
    @endif
    <dialog class="cv-lightbox" data-lightbox><button type="button" data-lightbox-close aria-label="Tutup galeri">Tutup</button><img data-lightbox-image alt=""></dialog>
    @include('invitations.shared.host-dialog')
</body>
</html>

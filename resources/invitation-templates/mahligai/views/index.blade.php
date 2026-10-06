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
    <link href="https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400&family=Jost:wght@400;500;600&display=swap" rel="stylesheet">
    <script>document.documentElement.classList.add('js-ready');</script>
    @vite(['resources/css/invitations.css', 'resources/js/invitation-hosts.js', 'resources/js/invitation-forms.js', 'resources/js/invitation-lightbox.js', 'resources/invitation-templates/mahligai/assets/theme.css', 'resources/invitation-templates/mahligai/assets/theme.js'])
</head>
<body class="mahligai" style="--mh-accent: {{ $theme['accent_color'] }}; --mh-focal-x: {{ $theme['cover_focal_x'] }}%; --mh-focal-y: {{ $theme['cover_focal_y'] }}%; --mh-overlay: {{ $theme['cover_overlay_opacity'] / 100 }}" data-motion="{{ $theme['motion'] }}" data-auto-scroll="{{ ($theme['auto_scroll'] ?? false) ? '1' : '0' }}">
    @php
        $navItems = [
            'hosts' => ['id' => 'hosts', 'label' => 'Mempelai'],
            'events' => ['id' => 'events', 'label' => 'Acara'],
            'story' => ['id' => 'story', 'label' => 'Cerita'],
            'gallery' => ['id' => 'gallery', 'label' => 'Galeri'],
            'rsvp' => ['id' => 'rsvp', 'label' => 'Konfirmasi'],
        ];
        $hasContent = ['hosts' => count($hosts), 'events' => count($events), 'story' => count($stories), 'gallery' => count($gallery), 'rsvp' => true];
        $visibleNav = collect($sections)->filter(fn ($section) => isset($navItems[$section]) && ($hasContent[$section] ?? true))->mapWithKeys(fn ($section) => [$section => $navItems[$section]])->all();
        $groomName = collect($hosts)->firstWhere('role', 'groom')['name'] ?? ($hosts[0]['name'] ?? null);
        $brideName = collect($hosts)->firstWhere('role', 'bride')['name'] ?? ($hosts[1]['name'] ?? null);
        $monogram = collect([$groomName, $brideName])->filter()->map(fn ($name) => mb_strtoupper(mb_substr(trim($name), 0, 1)))->implode(' · ');
        $coverImage = $theme['cover_poster_image'] ?: ($gallery[0]['url'] ?? ($hosts[0]['photo_url'] ?? null));
        $coverDesktop = $theme['cover_video_enabled'] ? $theme['cover_video_desktop'] : null;
        $coverMobile = $theme['cover_video_enabled'] ? ($theme['cover_video_mobile'] ?: $coverDesktop) : null;
        $openingVideo = $coverDesktop;
        $openingVideoMobile = $coverMobile;
    @endphp

    <svg class="mh-sprite" width="0" height="0" focusable="false" aria-hidden="true">
        <defs>
            <symbol id="mh-arch" viewBox="0 0 32 32">
                <path d="M6 28V15a10 10 0 0 1 20 0v13" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                <path d="M11.5 28v-8.5a4.5 4.5 0 0 1 9 0V28" fill="none" stroke="currentColor" stroke-width="1.1" stroke-linecap="round"/>
            </symbol>
            <symbol id="mh-star" viewBox="0 0 32 32">
                <path d="M16 1.8 19.4 11 29 14l-9.6 3L16 30.2 12.6 17 3 14l9.6-3z" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linejoin="round"/>
                <circle cx="16" cy="15" r="2.3" fill="currentColor"/>
            </symbol>
        </defs>
    </svg>

    <div class="mh-cover" id="opening-cover">
        <div class="mh-cover__media">
            @if ($coverImage)<img class="mh-cover__poster" src="{{ $coverImage }}" alt="" aria-hidden="true">@endif
            @if ($coverDesktop)<video class="mh-cover__video mh-cover__video--desktop" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video><source src="{{ $coverDesktop }}"></video>@endif
            @if ($coverMobile && $coverMobile !== $coverDesktop)<video class="mh-cover__video mh-cover__video--mobile" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video><source src="{{ $coverMobile }}"></video>@endif
            <div class="mh-cover__shade" aria-hidden="true"></div>
            <div class="mh-lattice" aria-hidden="true">
                <svg viewBox="0 0 200 200" preserveAspectRatio="xMidYMid slice" focusable="false">
                    <g fill="none" stroke="currentColor" stroke-width="1">
                        <path d="M0 0h200v200H0z"/>
                        <path d="M100 0v200M0 100h200"/>
                        <path d="M0 0l200 200M200 0L0 200"/>
                        <path d="M100 0 200 100 100 200 0 100Z"/>
                        <path d="M100 20 180 100 100 180 20 100Z"/>
                    </g>
                </svg>
            </div>
        </div>
        <div class="mh-cover__panel">
            @if ($monogram)<span class="mh-seal" aria-hidden="true">{{ $monogram }}</span>@endif
            <span class="mh-kicker">{{ $labels['cover_eyebrow'] }}</span>
            @if ($groomName && $brideName)<h1 class="mh-couple"><span>{{ $groomName }}</span><i>&amp;</i><span>{{ $brideName }}</span></h1>@else<h1>{{ $title }}</h1>@endif
            @if ($primary_event)<p class="mh-cover__date"><strong>{{ $primary_event['date'] }}</strong>@if ($primary_event['venue'])<span>{{ $primary_event['venue'] }}</span>@endif</p>@endif
            @if ($recipient)<p class="mh-cover__recipient"><span>{{ $labels['cover_recipient_label'] }}</span><strong>{{ $recipient }}</strong></p>@endif
            <a class="mh-btn mh-btn--solid" href="#mahligai-content" data-open-invitation>{{ $labels['cover_cta'] }}</a>
            @if ($primary_event && $primary_event['timestamp'])
                <div class="mh-cover__countdown" data-countdown="{{ $primary_event['timestamp'] }}" aria-label="Hitung mundur menuju acara">
                    <span>{{ $labels['cover_countdown_label'] }}</span>
                    <div data-countdown-output>@foreach (['days' => 'Hari', 'hours' => 'Jam', 'minutes' => 'Menit', 'seconds' => 'Detik'] as $unit => $label)<span><strong data-countdown-unit="{{ $unit }}">00</strong><small>{{ $label }}</small></span>@endforeach</div>
                </div>
            @endif
        </div>
    </div>

    <nav class="mh-dock" aria-label="Navigasi undangan">
        <a class="mh-dock__home" href="#mahligai-content" aria-label="Awal"><svg class="mh-dock__icon" viewBox="0 0 32 32" aria-hidden="true"><use href="#mh-star"/></svg></a>
        @foreach ($visibleNav as $item)
            <a href="#{{ $item['id'] }}" data-dock-link="{{ $item['id'] }}"><svg class="mh-dock__icon" viewBox="0 0 32 32" aria-hidden="true"><use href="#mh-arch"/></svg><span class="mh-dock__label">{{ $item['label'] }}</span></a>
        @endforeach
    </nav>

    <div class="mh-glow" aria-hidden="true"><span></span><span></span><span></span></div>

    <main id="mahligai-content" tabindex="-1" data-gate>
        @foreach ($sections as $section)
            @if ($section === 'opening')
                <section class="invitation-section mh-hero" data-height="{{ $section_heights['opening'] ?? 'full' }}" aria-labelledby="mh-opening-title">
                    @if ($openingVideo)
                        <div class="mh-hero__frame" data-reveal="zoom">
                            @if ($openingVideoMobile !== $openingVideo)<video class="mh-hero__video mh-hero__video--desktop" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video><source src="{{ $openingVideo }}"></video><video class="mh-hero__video mh-hero__video--mobile" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video><source src="{{ $openingVideoMobile }}"></video>@else<video class="mh-hero__video" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video><source src="{{ $openingVideo }}"></video>@endif
                        </div>
                    @else
                        <span class="mh-ornament" data-reveal aria-hidden="true"><svg viewBox="0 0 48 48" fill="none"><use href="#mh-star"/></svg></span>
                    @endif
                    <span class="invitation-eyebrow" data-reveal="down">{{ $labels['opening_eyebrow'] }}</span>
                    @if (count($hosts) >= 2)<h2 id="mh-opening-title" class="mh-couple mh-couple--opening" data-reveal="zoom"><span>{{ $hosts[0]['name'] }}</span><i>&amp;</i><span>{{ $hosts[1]['name'] }}</span></h2>@else<h2 id="mh-opening-title" data-reveal="zoom">{{ $title }}</h2>@endif
                    @if ($opening_text)<p class="mh-hero__text" data-reveal>{!! nl2br(e($opening_text)) !!}</p>@endif
                    @if ($primary_event)<p class="mh-hero__date" data-reveal>{{ $primary_event['date'] }}</p>@endif
                </section>
            @elseif ($section === 'hosts' && count($hosts))
                <section class="invitation-section invitation-section--tint mh-hosts" id="hosts" data-height="{{ $section_heights['hosts'] ?? 'full' }}" aria-labelledby="mh-hosts-title">
                    <div class="mh-heading" data-reveal="zoom"><span class="invitation-eyebrow">{{ $labels['hosts_eyebrow'] }}</span><h2 id="mh-hosts-title">{{ $labels['hosts_title'] }}</h2></div>
                    <div class="mh-hosts__grid" data-count="{{ count($hosts) }}">
                        @foreach ($hosts as $host)
                            <article class="mh-host" data-host data-reveal>
                                <button type="button" class="host-card__trigger mh-host__trigger" data-host-open data-host-name="{{ $host['name'] }}" data-host-role="{{ match ($host['role']) { 'groom' => 'Mempelai Pria', 'bride' => 'Mempelai Wanita', default => 'Mempelai' } }}" aria-haspopup="dialog" aria-label="Lihat profil {{ $host['name'] }}">
                                    <div class="mh-host__portrait">@if ($host['photo_url'])<img src="{{ $host['photo_url'] }}" alt="Foto {{ $host['name'] }}" loading="lazy" decoding="async">@else<span aria-hidden="true">{{ mb_substr($host['name'], 0, 1) }}</span>@endif</div>
                                </button>
                                <small class="mh-host__role">{{ match ($host['role']) { 'groom' => 'Mempelai pria', 'bride' => 'Mempelai wanita', default => 'Mempelai' } }}</small>
                                <h3>{{ $host['name'] }}</h3>
                                @if ($host['family'])<p class="mh-host__family"><small>{{ match ($host['role']) { 'groom' => 'Putra dari', 'bride' => 'Putri dari', default => 'Putra/putri dari' } }}</small><span>{{ $host['family'] }}</span></p>@endif
                                @if ($host['instagram'])<a class="mh-host__social" href="{{ $host['instagram'] }}" target="_blank" rel="noopener noreferrer">Instagram</a>@endif
                                <div class="host-card__details mh-host__details" data-host-details>@if ($host['birth_order'])<p>{{ $host['birth_order'] }}</p>@endif @if ($host['bio'])<p>{!! nl2br(e($host['bio'])) !!}</p>@endif</div>
                            </article>
                            @if (count($hosts) === 2 && ! $loop->last)<span class="mh-hosts__and" aria-hidden="true">&amp;</span>@endif
                        @endforeach
                    </div>
                </section>
            @elseif ($section === 'events' && count($events))
                <section class="invitation-section mh-events" id="events" data-height="{{ $section_heights['events'] ?? 'full' }}" aria-labelledby="mh-events-title">
                    <div class="mh-heading" data-reveal="zoom"><span class="invitation-eyebrow">{{ $labels['events_eyebrow'] }}</span><h2 id="mh-events-title">{{ $labels['events_title'] }}</h2></div>
                    <div class="mh-events__list">
                        @foreach ($events as $event)
                            <article class="mh-event" data-reveal>
                                <div class="mh-event__date"><strong>{{ $event['date'] }}</strong>@if ($event['start_time'])<span>{{ $event['start_time'] }}{{ $event['end_time'] ? ' – '.$event['end_time'] : ' s/d Selesai' }}{{ ($theme['hide_timezone'] ?? false) ? '' : ($event['timezone_label'] ? ' '.$event['timezone_label'] : '') }}</span>@endif</div>
                                <div class="mh-event__details">
                                    <h3>{{ $event['label'] }}</h3>
                                    @if ($event['venue'])<p class="mh-event__venue"><strong>{{ $event['venue'] }}</strong></p>@endif
                                    @if ($event['address'])<p>{{ $event['address'] }}</p>@endif
                                    @if (count($event['notes']))<div class="mh-event__notes">@foreach ($event['notes'] as $note)<small>{!! nl2br(e($note)) !!}</small>@endforeach</div>@endif
                                    <div class="mh-actions">
                                        @if (in_array('map', $sections) && $event['directions_url'])<a href="{{ $event['directions_url'] }}" target="_blank" rel="noopener noreferrer">Petunjuk arah</a>@endif
                                        @if (in_array('map', $sections) && $event['address'])<button type="button" data-copy="{{ $event['address'] }}">Salin alamat</button>@endif
                                        @if ($event['calendar_url'])<a href="{{ $event['calendar_url'] }}" target="_blank" rel="noopener noreferrer">Tambah ke kalender</a>@endif
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @elseif ($section === 'countdown' && $primary_event && $primary_event['timestamp'])
                <section class="invitation-section mh-countdown" data-height="{{ $section_heights['countdown'] ?? 'full' }}" data-countdown="{{ $primary_event['timestamp'] }}" aria-labelledby="mh-countdown-title">
                    <div class="mh-heading" data-reveal="zoom"><span class="invitation-eyebrow">{{ $labels['countdown_eyebrow'] }}</span><h2 id="mh-countdown-title">{{ $labels['countdown_title'] }}</h2></div>
                    <div class="mh-countdown__units" data-countdown-output data-reveal>@foreach (['days' => 'Hari', 'hours' => 'Jam', 'minutes' => 'Menit', 'seconds' => 'Detik'] as $unit => $label)<span><strong data-countdown-unit="{{ $unit }}">00</strong><small>{{ $label }}</small></span>@endforeach</div>
                </section>
            @elseif ($section === 'map' && $primary_event && $primary_event['map_embed_url'])
                <section class="invitation-section mh-map" id="map" data-height="{{ $section_heights['map'] ?? 'full' }}" aria-labelledby="mh-map-title">
                    <div class="mh-heading" data-reveal="zoom"><span class="invitation-eyebrow">{{ $labels['map_eyebrow'] }}</span><h2 id="mh-map-title">{{ $labels['map_title'] }}</h2></div>
                    <div class="mh-map__frame" data-reveal="zoom"><iframe src="{{ $primary_event['map_embed_url'] }}" title="Peta {{ $primary_event['venue'] ?: $primary_event['label'] }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe></div>
                    @if ($primary_event['address'])<p class="mh-map__address" data-reveal>{{ $primary_event['address'] }}</p>@endif
                    <div class="mh-actions mh-actions--center" data-reveal>@if ($primary_event['directions_url'])<a class="mh-btn mh-btn--solid" href="{{ $primary_event['directions_url'] }}" target="_blank" rel="noopener noreferrer">Buka Google Maps</a>@endif @if ($primary_event['address'])<button type="button" data-copy="{{ $primary_event['address'] }}">Salin alamat</button>@endif</div>
                </section>
            @elseif ($section === 'story' && count($stories))
                <section class="invitation-section invitation-section--tint mh-story" id="story" data-height="{{ $section_heights['story'] ?? 'full' }}" aria-labelledby="mh-story-title">
                    <div class="mh-heading" data-reveal="zoom"><span class="invitation-eyebrow">{{ $labels['story_eyebrow'] }}</span><h2 id="mh-story-title">{{ $labels['story_title'] }}</h2></div>
                    <ol class="mh-story__list">@foreach ($stories as $story)<li data-reveal>@if ($story['image_url'])<img src="{{ $story['image_url'] }}" alt="{{ $story['title'] }}" loading="lazy" decoding="async">@endif<div class="mh-story__body"><small>{{ $story['date'] }}</small><h3>{{ $story['title'] }}</h3>@if ($story['body'])<p>{!! nl2br(e($story['body'])) !!}</p>@endif</div></li>@endforeach</ol>
                </section>
            @elseif ($section === 'gallery' && count($gallery))
                <section class="invitation-section mh-gallery" id="gallery" data-height="{{ $section_heights['gallery'] ?? 'full' }}" aria-labelledby="mh-gallery-title">
                    <div class="mh-heading" data-reveal="zoom"><span class="invitation-eyebrow">{{ $labels['gallery_eyebrow'] }}</span><h2 id="mh-gallery-title">{{ $labels['gallery_title'] }}</h2></div>
                    <div class="mh-gallery__grid">@foreach ($gallery as $image)<figure data-reveal="zoom"><button type="button" data-lightbox-src="{{ $image['url'] }}" data-lightbox-alt="{{ $image['alt'] }}"><img src="{{ $image['url'] }}" alt="{{ $image['alt'] }}" loading="lazy" decoding="async"></button>@if ($image['caption'])<figcaption>{{ $image['caption'] }}</figcaption>@endif</figure>@endforeach</div>
                </section>
            @elseif (str_starts_with($section, 'blocks') && ! empty($block_sections[$section] ?? $blocks))
                <div class="mh-shared">@include('invitations.shared.blocks', ['blocks' => $block_sections[$section] ?? $blocks])</div>
            @elseif ($section === 'rsvp')
                @if ($theme['merge_rsvp_guestbook'] ?? false)<div class="mh-shared" id="rsvp">@include('invitations.shared.confirmation')</div>@else<div class="mh-shared" id="rsvp">@include('invitations.shared.rsvp')</div>@endif
            @elseif ($section === 'guestbook')
                @unless ($theme['merge_rsvp_guestbook'] ?? false)<div class="mh-shared">@include('invitations.shared.guestbook')</div>@endunless
            @elseif ($section === 'gifts' && count($gifts))
                <section class="invitation-section invitation-section--tint mh-gifts" data-height="{{ $section_heights['gifts'] ?? 'full' }}" aria-labelledby="mh-gifts-title">
                    <div class="mh-heading" data-reveal="zoom"><span class="invitation-eyebrow">{{ $labels['gifts_eyebrow'] }}</span><h2 id="mh-gifts-title">{{ $labels['gifts_title'] }}</h2></div>
                    <p class="mh-gifts__intro" data-reveal>{!! nl2br(e($labels['gifts_intro'])) !!}</p>
                    @include('invitations.shared.gifts')
                </section>
            @elseif ($section === 'contacts' && count($contacts))
                <section class="invitation-section mh-contacts" data-height="{{ $section_heights['contacts'] ?? 'full' }}" aria-labelledby="mh-contacts-title">
                    <div class="mh-heading" data-reveal="zoom"><span class="invitation-eyebrow">{{ $labels['contacts_eyebrow'] }}</span><h2 id="mh-contacts-title">{{ $labels['contacts_title'] }}</h2></div>
                    <p data-reveal>{{ $labels['contacts_intro'] }}</p>
                    <div class="mh-contacts__grid">@foreach ($contacts as $contact)<article data-reveal><small>{{ $contact['label'] }}</small><h3>{{ $contact['name'] }}</h3><div class="mh-actions mh-actions--center"><a href="{{ $contact['whatsapp_url'] }}" target="_blank" rel="noopener noreferrer">WhatsApp</a><a href="{{ $contact['phone_url'] }}">Telepon</a></div></article>@endforeach</div>
                </section>
            @elseif ($section === 'livestream' && $livestream_url)
                <section class="invitation-section mh-livestream" data-height="{{ $section_heights['livestream'] ?? 'full' }}" aria-labelledby="mh-livestream-title">
                    <div class="mh-heading" data-reveal="zoom"><span class="invitation-eyebrow">{{ $labels['livestream_eyebrow'] }}</span><h2 id="mh-livestream-title">{{ $livestream_label }}</h2></div>
                    <a class="mh-btn mh-btn--solid" href="{{ $livestream_url }}" target="_blank" rel="noopener noreferrer">{{ $livestream_label }}</a>
                </section>
            @elseif ($section === 'sharing')
                <section class="invitation-section mh-sharing" data-height="{{ $section_heights['sharing'] ?? 'full' }}" aria-labelledby="mh-sharing-title">
                    <div class="mh-heading" data-reveal="zoom"><span class="invitation-eyebrow">{{ $labels['sharing_eyebrow'] }}</span><h2 id="mh-sharing-title">{{ $labels['sharing_title'] }}</h2></div>
                    <p data-reveal>{{ $labels['sharing_intro'] }}</p>
                    <div class="mh-actions mh-actions--center" data-reveal><button class="mh-btn mh-btn--solid" type="button" data-share data-share-url="{{ $share_url }}">Bagikan sekarang</button><a href="{{ $whatsapp_url }}" target="_blank" rel="noopener noreferrer">Kirim via WhatsApp</a></div>
                </section>
            @elseif ($section === 'closing')
                <section class="invitation-section mh-closing" data-height="{{ $section_heights['closing'] ?? 'full' }}">
                    <span class="mh-closing__mark" aria-hidden="true"><svg viewBox="0 0 48 48" fill="none"><use href="#mh-star"/></svg></span>
                    <span class="invitation-eyebrow" data-reveal="down">{{ $labels['closing_eyebrow'] }}</span>
                    <h2 data-reveal="zoom">{{ $couple_title }}</h2>
                    @if ($closing_message)<p class="mh-closing__message" data-reveal>{!! nl2br(e($closing_message)) !!}</p>@endif
                    @include('invitations.shared.closing-families')
                    @include('invitations.shared.closing-social')
                    @if ($closing_footer)<p class="mh-closing__footer" data-reveal>{!! nl2br(e($closing_footer)) !!}</p>@endif
                    <p class="mh-closing__kicker" data-reveal>{{ $labels['closing_kicker'] }}</p>
                </section>
            @endif
        @endforeach
    </main>

    @if ($music_url)<audio data-music loop preload="none" src="{{ $music_url }}"></audio><button class="mh-music" type="button" data-music-toggle aria-label="Putar musik"><span aria-hidden="true">♪</span></button>@endif
    <dialog class="mh-lightbox" data-lightbox><button type="button" data-lightbox-close aria-label="Tutup galeri">Tutup</button><img data-lightbox-image alt=""></dialog>
    @include('invitations.shared.host-dialog')
</body>
</html>

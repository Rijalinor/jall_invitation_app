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
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;1,500&family=Jost:wght@400;500;600&display=swap" rel="stylesheet">
    <script>document.documentElement.classList.add('js-ready');</script>
    @vite(['resources/css/invitations.css', 'resources/js/invitation-hosts.js', 'resources/js/invitation-forms.js', 'resources/js/invitation-lightbox.js', 'resources/invitation-templates/sari-pura/assets/theme.css', 'resources/invitation-templates/sari-pura/assets/theme.js'])
</head>
<body class="sari-pura" style="--sp-accent: {{ $theme['accent_color'] }}; --sp-focal-x: {{ $theme['cover_focal_x'] }}%; --sp-focal-y: {{ $theme['cover_focal_y'] }}%; --sp-overlay: {{ $theme['cover_overlay_opacity'] / 100 }}" data-motion="{{ $theme['motion'] }}">
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

    <div class="sp-cover" id="opening-cover">
        <div class="sp-cover__image">
            @if ($coverImage)<img class="sp-cover__poster" src="{{ $coverImage }}" alt="" aria-hidden="true">@endif
            @if ($coverDesktop)<video class="sp-cover__video sp-cover__video--desktop" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video><source src="{{ $coverDesktop }}"></video>@endif
            @if ($coverMobile && $coverMobile !== $coverDesktop)<video class="sp-cover__video sp-cover__video--mobile" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video><source src="{{ $coverMobile }}"></video>@endif
            <div class="sp-cover__shade" aria-hidden="true"></div>
            <svg class="sp-cover__engraving" viewBox="0 0 240 520" fill="none" aria-hidden="true"><path d="M120 12C34 68 34 150 120 202s86 134 0 186-86 87 0 120M120 12c86 56 86 138 0 190s-86 134 0 186 86 87 0 120M120 35v450M70 94c34 10 46 28 50 58m50-58c-34 10-46 28-50 58M70 295c34 10 46 28 50 58m50-58c-34 10-46 28-50 58" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
        </div>
        <div class="sp-cover__paper">
            @if ($monogram)<span class="sp-seal" aria-hidden="true">{{ $monogram }}</span>@endif
            <span class="sp-kicker">{{ $labels['cover_eyebrow'] }}</span>
            @if ($groomName && $brideName)<h1 class="sp-couple"><span>{{ $groomName }}</span><i>&amp;</i><span>{{ $brideName }}</span></h1>@else<h1>{{ $title }}</h1>@endif
            @if ($primary_event)<p class="sp-cover__date"><strong>{{ $primary_event['date'] }}</strong>@if ($primary_event['venue'])<span>{{ $primary_event['venue'] }}</span>@endif</p>@endif
            @if ($recipient)<p class="sp-cover__recipient"><span>{{ $labels['cover_recipient_label'] }}</span><strong>{{ $recipient }}</strong></p>@endif
            <a class="sp-button sp-button--solid" href="#sari-content" data-open-invitation>{{ $labels['cover_cta'] }}</a>
            @if ($primary_event && $primary_event['timestamp'])
                <div class="sp-cover__countdown" data-countdown="{{ $primary_event['timestamp'] }}" aria-label="Hitung mundur menuju acara">
                    <span>{{ $labels['cover_countdown_label'] }}</span><div data-countdown-output>@foreach (['days' => 'Hari', 'hours' => 'Jam', 'minutes' => 'Menit', 'seconds' => 'Detik'] as $unit => $label)<span><strong data-countdown-unit="{{ $unit }}">00</strong><small>{{ $label }}</small></span>@endforeach</div>
                </div>
            @endif
        </div>
    </div>

    <nav class="sp-index" aria-label="Navigasi undangan">
        <a class="sp-index__home" href="#sari-content" aria-label="Awal"><span aria-hidden="true">SP</span></a>
        @foreach ($visibleNav as $item)<a href="#{{ $item['id'] }}" data-dock-link="{{ $item['id'] }}"><span class="sp-index__num" aria-hidden="true">{{ sprintf('%02d', $loop->iteration) }}</span><span class="sp-index__label">{{ $item['label'] }}</span></a>@endforeach
    </nav>

    <div class="sp-atmosphere" aria-hidden="true">
        <span class="sp-petal sp-petal--a"></span>
        <span class="sp-petal sp-petal--b"></span>
        <span class="sp-petal sp-petal--c"></span>
        <span class="sp-petal sp-petal--d"></span>
    </div>
    <div class="sp-atmosphere sp-atmosphere--front" aria-hidden="true">
        <span class="sp-petal sp-petal--e"></span>
        <span class="sp-petal sp-petal--f"></span>
    </div>

    <main id="sari-content" tabindex="-1" data-gate>
        @foreach ($sections as $section)
            @if ($section === 'opening')
                <section class="invitation-section sp-opening" data-height="{{ $section_heights['opening'] ?? 'full' }}" aria-labelledby="sp-opening-title">
                    @if ($openingVideo)
                        <div class="sp-opening__frame" data-reveal>
                            @if ($openingVideoMobile !== $openingVideo)<video class="sp-opening__video sp-opening__video--desktop" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video><source src="{{ $openingVideo }}"></video><video class="sp-opening__video sp-opening__video--mobile" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video><source src="{{ $openingVideoMobile }}"></video>@else<video class="sp-opening__video" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video><source src="{{ $openingVideo }}"></video>@endif
                        </div>
                    @else
                        <span class="sp-motif" data-reveal aria-hidden="true"><svg viewBox="0 0 120 44" fill="none"><circle cx="60" cy="22" r="7"/><path d="M60 15c-7-6-7-13 0-13s7 7 0 13z"/><path d="M8 22h42m20 0h42" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/><circle cx="53" cy="28" r="2.4"/><circle cx="67" cy="28" r="2.4"/></svg></span>
                    @endif
                    <span class="sp-kicker" data-reveal>{{ $labels['opening_eyebrow'] }}</span>
                    @if (count($hosts) >= 2)<h2 id="sp-opening-title" class="sp-couple sp-couple--opening" data-reveal><span>{{ $hosts[0]['name'] }}</span><i>&amp;</i><span>{{ $hosts[1]['name'] }}</span></h2>@else<h2 id="sp-opening-title" data-reveal>{{ $title }}</h2>@endif
                    @if ($opening_text)<p class="sp-opening__text" data-reveal>{!! nl2br(e($opening_text)) !!}</p>@endif
                    @if ($primary_event)<p class="sp-opening__date" data-reveal>{{ $primary_event['date'] }}</p>@endif
                </section>
            @elseif ($section === 'hosts' && count($hosts))
                <section class="invitation-section invitation-section--tint sp-hosts" id="hosts" data-height="{{ $section_heights['hosts'] ?? 'full' }}" aria-labelledby="sp-hosts-title">
                    <div class="sp-section-heading" data-reveal><span class="sp-kicker">{{ $labels['hosts_eyebrow'] }}</span><h2 id="sp-hosts-title">{{ $labels['hosts_title'] }}</h2></div>
                    <div class="sp-hosts__grid" data-count="{{ count($hosts) }}">
                        @foreach ($hosts as $host)
                            <article class="sp-host" data-host data-reveal>
                                <button type="button" class="host-card__trigger sp-host__trigger" data-host-open data-host-name="{{ $host['name'] }}" data-host-role="{{ match ($host['role']) { 'groom' => 'Mempelai Pria', 'bride' => 'Mempelai Wanita', default => 'Mempelai' } }}" aria-haspopup="dialog" aria-label="Lihat profil {{ $host['name'] }}">
                                    <div class="sp-host__portrait">@if ($host['photo_url'])<img src="{{ $host['photo_url'] }}" alt="Foto {{ $host['name'] }}" loading="lazy" decoding="async">@else<span aria-hidden="true">{{ mb_substr($host['name'], 0, 1) }}</span>@endif</div>
                                </button>
                                <small class="sp-host__role">{{ match ($host['role']) { 'groom' => 'Mempelai pria', 'bride' => 'Mempelai wanita', default => 'Mempelai' } }}</small>
                                <h3>{{ $host['name'] }}</h3>
                                @if ($host['family'])<p class="sp-host__family"><small>{{ match ($host['role']) { 'groom' => 'Putra dari', 'bride' => 'Putri dari', default => 'Putra/putri dari' } }}</small><span>{{ $host['family'] }}</span></p>@endif
                                @if ($host['instagram'])<a class="sp-host__social" href="{{ $host['instagram'] }}" target="_blank" rel="noopener noreferrer">Instagram</a>@endif
                                <div class="host-card__details sp-host__details" data-host-details>@if ($host['birth_order'])<p>{{ $host['birth_order'] }}</p>@endif @if ($host['bio'])<p>{!! nl2br(e($host['bio'])) !!}</p>@endif</div>
                            </article>
                            @if (count($hosts) === 2 && ! $loop->last)<span class="sp-hosts__and" aria-hidden="true">&amp;</span>@endif
                        @endforeach
                    </div>
                </section>
            @elseif ($section === 'events' && count($events))
                <section class="invitation-section sp-events" id="events" data-height="{{ $section_heights['events'] ?? 'full' }}" aria-labelledby="sp-events-title">
                    <div class="sp-section-heading" data-reveal><span class="sp-kicker">{{ $labels['events_eyebrow'] }}</span><h2 id="sp-events-title">{{ $labels['events_title'] }}</h2></div>
                    <div class="sp-events__list">
                        @foreach ($events as $event)
                            <article class="sp-event" data-reveal>
                                <div class="sp-event__date"><strong>{{ $event['date'] }}</strong>@if ($event['start_time'])<span>{{ $event['start_time'] }}{{ $event['end_time'] ? ' – '.$event['end_time'] : ' s/d Selesai' }}{{ ($theme['hide_timezone'] ?? false) ? '' : ($event['timezone_label'] ? ' '.$event['timezone_label'] : '') }}</span>@endif</div>
                                <div class="sp-event__details">
                                    <h3>{{ $event['label'] }}</h3>
                                    @if ($event['venue'])<p class="sp-event__venue"><strong>{{ $event['venue'] }}</strong></p>@endif
                                    @if ($event['address'])<p>{{ $event['address'] }}</p>@endif
                                    @if (count($event['notes']))<div class="sp-event__notes">@foreach ($event['notes'] as $note)<small>{!! nl2br(e($note)) !!}</small>@endforeach</div>@endif
                                    <div class="sp-actions">
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
                <section class="invitation-section sp-countdown" data-height="{{ $section_heights['countdown'] ?? 'full' }}" data-countdown="{{ $primary_event['timestamp'] }}" aria-labelledby="sp-countdown-title">
                    <div class="sp-section-heading" data-reveal><span class="sp-kicker">{{ $labels['countdown_eyebrow'] }}</span><h2 id="sp-countdown-title">{{ $labels['countdown_title'] }}</h2></div>
                    <div class="sp-countdown__units" data-countdown-output data-reveal>@foreach (['days' => 'Hari', 'hours' => 'Jam', 'minutes' => 'Menit', 'seconds' => 'Detik'] as $unit => $label)<span><strong data-countdown-unit="{{ $unit }}">00</strong><small>{{ $label }}</small></span>@endforeach</div>
                </section>
            @elseif ($section === 'map' && $primary_event && $primary_event['map_embed_url'])
                <section class="invitation-section sp-map" id="map" data-height="{{ $section_heights['map'] ?? 'full' }}" aria-labelledby="sp-map-title">
                    <div class="sp-section-heading" data-reveal><span class="sp-kicker">{{ $labels['map_eyebrow'] }}</span><h2 id="sp-map-title">{{ $labels['map_title'] }}</h2></div>
                    <div class="sp-map__frame" data-reveal><iframe src="{{ $primary_event['map_embed_url'] }}" title="Peta {{ $primary_event['venue'] ?: $primary_event['label'] }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe></div>
                    @if ($primary_event['address'])<p class="sp-map__address" data-reveal>{{ $primary_event['address'] }}</p>@endif
                    <div class="sp-actions sp-actions--center" data-reveal>@if ($primary_event['directions_url'])<a class="sp-button sp-button--solid" href="{{ $primary_event['directions_url'] }}" target="_blank" rel="noopener noreferrer">Buka Google Maps</a>@endif @if ($primary_event['address'])<button type="button" data-copy="{{ $primary_event['address'] }}">Salin alamat</button>@endif</div>
                </section>
            @elseif ($section === 'story' && count($stories))
                <section class="invitation-section sp-story" id="story" data-height="{{ $section_heights['story'] ?? 'full' }}" aria-labelledby="sp-story-title">
                    <div class="sp-section-heading" data-reveal><span class="sp-kicker">{{ $labels['story_eyebrow'] }}</span><h2 id="sp-story-title">{{ $labels['story_title'] }}</h2></div>
                    <ol class="sp-story__list">@foreach ($stories as $story)<li data-reveal>@if ($story['image_url'])<img src="{{ $story['image_url'] }}" alt="{{ $story['title'] }}" loading="lazy" decoding="async">@endif<div class="sp-story__body"><small>{{ $story['date'] }}</small><h3>{{ $story['title'] }}</h3>@if ($story['body'])<p>{!! nl2br(e($story['body'])) !!}</p>@endif</div></li>@endforeach</ol>
                </section>
            @elseif ($section === 'gallery' && count($gallery))
                <section class="invitation-section invitation-section--tint sp-gallery" id="gallery" data-height="{{ $section_heights['gallery'] ?? 'full' }}" aria-labelledby="sp-gallery-title">
                    <div class="sp-section-heading" data-reveal><span class="sp-kicker">{{ $labels['gallery_eyebrow'] }}</span><h2 id="sp-gallery-title">{{ $labels['gallery_title'] }}</h2></div>
                    <div class="sp-gallery__grid">@foreach ($gallery as $image)<figure data-reveal><button type="button" data-lightbox-src="{{ $image['url'] }}" data-lightbox-alt="{{ $image['alt'] }}"><img src="{{ $image['url'] }}" alt="{{ $image['alt'] }}" loading="lazy" decoding="async"></button>@if ($image['caption'])<figcaption>{{ $image['caption'] }}</figcaption>@endif</figure>@endforeach</div>
                </section>
            @elseif (str_starts_with($section, 'blocks') && ! empty($block_sections[$section] ?? $blocks))
                <div class="sp-shared">@include('invitations.shared.blocks', ['blocks' => $block_sections[$section] ?? $blocks])</div>
            @elseif ($section === 'rsvp')
                @if ($theme['merge_rsvp_guestbook'] ?? false)<div class="sp-shared" id="rsvp">@include('invitations.shared.confirmation')</div>@else<div class="sp-shared" id="rsvp">@include('invitations.shared.rsvp')</div>@endif
            @elseif ($section === 'guestbook')
                @unless ($theme['merge_rsvp_guestbook'] ?? false)<div class="sp-shared">@include('invitations.shared.guestbook')</div>@endunless
            @elseif ($section === 'gifts' && count($gifts))
                <section class="invitation-section invitation-section--tint sp-gifts" data-height="{{ $section_heights['gifts'] ?? 'full' }}" aria-labelledby="sp-gifts-title">
                    <div class="sp-section-heading" data-reveal><span class="sp-kicker">{{ $labels['gifts_eyebrow'] }}</span><h2 id="sp-gifts-title">{{ $labels['gifts_title'] }}</h2></div>
                    <p class="sp-gifts__intro" data-reveal>{!! nl2br(e($labels['gifts_intro'])) !!}</p>
                    @include('invitations.shared.gifts')
                </section>
            @elseif ($section === 'contacts' && count($contacts))
                <section class="invitation-section sp-contacts" data-height="{{ $section_heights['contacts'] ?? 'full' }}" aria-labelledby="sp-contacts-title">
                    <div class="sp-section-heading" data-reveal><span class="sp-kicker">{{ $labels['contacts_eyebrow'] }}</span><h2 id="sp-contacts-title">{{ $labels['contacts_title'] }}</h2></div>
                    <p data-reveal>{{ $labels['contacts_intro'] }}</p>
                    <div class="sp-contacts__grid">@foreach ($contacts as $contact)<article data-reveal><small>{{ $contact['label'] }}</small><h3>{{ $contact['name'] }}</h3><div class="sp-actions sp-actions--center"><a href="{{ $contact['whatsapp_url'] }}" target="_blank" rel="noopener noreferrer">WhatsApp</a><a href="{{ $contact['phone_url'] }}">Telepon</a></div></article>@endforeach</div>
                </section>
            @elseif ($section === 'livestream' && $livestream_url)
                <section class="invitation-section sp-livestream" data-height="{{ $section_heights['livestream'] ?? 'full' }}" aria-labelledby="sp-livestream-title">
                    <div class="sp-section-heading" data-reveal><span class="sp-kicker">{{ $labels['livestream_eyebrow'] }}</span><h2 id="sp-livestream-title">{{ $livestream_label }}</h2></div>
                    <a class="sp-button sp-button--solid" href="{{ $livestream_url }}" target="_blank" rel="noopener noreferrer">{{ $livestream_label }}</a>
                </section>
            @elseif ($section === 'sharing')
                <section class="invitation-section sp-sharing" data-height="{{ $section_heights['sharing'] ?? 'full' }}" aria-labelledby="sp-sharing-title">
                    <div class="sp-section-heading" data-reveal><span class="sp-kicker">{{ $labels['sharing_eyebrow'] }}</span><h2 id="sp-sharing-title">{{ $labels['sharing_title'] }}</h2></div>
                    <p data-reveal>{{ $labels['sharing_intro'] }}</p>
                    <div class="sp-actions sp-actions--center" data-reveal><button class="sp-button sp-button--solid" type="button" data-share data-share-url="{{ $share_url }}">Bagikan sekarang</button><a href="{{ $whatsapp_url }}" target="_blank" rel="noopener noreferrer">Kirim via WhatsApp</a></div>
                </section>
            @elseif ($section === 'closing')
                <section class="invitation-section sp-closing" data-height="{{ $section_heights['closing'] ?? 'full' }}">
                    <span class="sp-closing__mark" aria-hidden="true"><svg viewBox="0 0 48 48" fill="none"><path d="M24 4c6 8 14 14 20 20-6 6-14 12-20 20-6-8-14-14-20-20 6-6 14-12 20-20z" stroke="currentColor" stroke-width="1.4"/><circle cx="24" cy="24" r="3.2"/></svg></span>
                    <span class="sp-kicker" data-reveal>{{ $labels['closing_eyebrow'] }}</span>
                    <h2 data-reveal>{{ $couple_title }}</h2>
                    @if ($closing_message)<p class="sp-closing__message" data-reveal>{!! nl2br(e($closing_message)) !!}</p>@endif
                    @include('invitations.shared.closing-families')
                    @if ($closing_footer)<p class="sp-closing__footer" data-reveal>{!! nl2br(e($closing_footer)) !!}</p>@endif
                    <p class="sp-closing__kicker" data-reveal>{{ $labels['closing_kicker'] }}</p>
                </section>
            @endif
        @endforeach
    </main>

    @if ($music_url)<audio data-music loop preload="none" src="{{ $music_url }}"></audio><button class="sp-music" type="button" data-music-toggle aria-label="Putar musik"><span aria-hidden="true">♪</span></button>@endif
    <dialog class="sp-lightbox" data-lightbox><button type="button" data-lightbox-close aria-label="Tutup galeri">Tutup</button><img data-lightbox-image alt=""></dialog>
    @include('invitations.shared.host-dialog')
</body>
</html>

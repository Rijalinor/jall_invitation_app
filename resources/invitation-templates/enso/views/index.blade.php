<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $title }}">
    <title>{{ $title }}</title>
    @include('invitations.shared.og')
    {{-- Marks that scripting is available before the first paint. --}}
    <script>document.documentElement.classList.add('js-ready');</script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Shippori+Mincho:wght@400;500;600&family=Zen+Kaku+Gothic+New:wght@300;400;500&display=swap">
    @vite(['resources/css/invitations.css', 'resources/js/invitation-hosts.js', 'resources/js/invitation-forms.js', 'resources/js/invitation-lightbox.js', 'resources/invitation-templates/enso/assets/theme.css', 'resources/invitation-templates/enso/assets/theme.js'])
</head>
<body class="enso" style="--en-accent: {{ $theme['accent_color'] }}; --en-cover-veil: {{ ($theme['cover_overlay_opacity'] ?? 46) / 100 }};" data-motion="{{ $theme['motion'] }}">
    @php
        $navItems = [
            'hosts' => ['id' => 'hosts', 'label' => 'Mempelai'],
            'events' => ['id' => 'events', 'label' => 'Acara'],
            'story' => ['id' => 'story', 'label' => 'Kisah'],
            'gallery' => ['id' => 'gallery', 'label' => 'Galeri'],
            'rsvp' => ['id' => 'rsvp', 'label' => 'RSVP'],
        ];
        $hasContent = ['hosts' => count($hosts), 'events' => count($events), 'story' => count($stories), 'gallery' => count($gallery), 'rsvp' => true];
        $visibleNav = collect($sections)->filter(fn ($section) => isset($navItems[$section]) && ($hasContent[$section] ?? true))->mapWithKeys(fn ($section) => [$section => $navItems[$section]])->all();
        $groomName = collect($hosts)->firstWhere('role', 'groom')['name'] ?? ($hosts[0]['name'] ?? null);
        $brideName = collect($hosts)->firstWhere('role', 'bride')['name'] ?? ($hosts[1]['name'] ?? null);
        $monogram = $groomName && $brideName
            ? mb_strtoupper(mb_substr($groomName, 0, 1).mb_substr($brideName, 0, 1))
            : mb_strtoupper(mb_substr($title, 0, 2));
        $coverImage = $theme['cover_poster_image'] ?: ($gallery[0]['url'] ?? ($hosts[0]['photo_url'] ?? null));
        $coverDesktop = $theme['cover_video_enabled'] ? $theme['cover_video_desktop'] : null;
        $coverMobile = $theme['cover_video_enabled'] ? ($theme['cover_video_mobile'] ?: $coverDesktop) : null;
        $openingVideo = $coverDesktop;
        $openingVideoMobile = $coverMobile;
        $coverFocus = 'object-position: '.($theme['cover_focal_x'] ?? 50).'% '.($theme['cover_focal_y'] ?? 45).'%;';
    @endphp

    {{-- Back layer: soft sumi ink washes drifting with the scroll, and the ensō
         that draws itself once the invitation opens, turning very slowly after. --}}
    <div class="en-wash" aria-hidden="true">
        <span class="en-wash__blot en-wash__blot--1"></span>
        <span class="en-wash__blot en-wash__blot--2"></span>
        <span class="en-wash__blot en-wash__blot--3"></span>
    </div>

    <div class="en-enso" aria-hidden="true">
        <svg viewBox="0 0 200 200" fill="none" focusable="false">
            <circle class="en-enso__ring" cx="100" cy="100" r="80" pathLength="1"></circle>
        </svg>
    </div>

    {{-- Front layer: sakura petals falling across the whole viewport on a loop,
         softly blurred, so the garden reads as depth. --}}
    <svg class="en-petals__sprite" aria-hidden="true" width="0" height="0" focusable="false">
        <symbol id="en-blossom" viewBox="0 0 24 32"><path d="M12 30 C6 25 3 18 3 12 C3 7 5 4 7 3 C9 6 10 8 12 8 C14 8 15 6 17 3 C19 4 21 7 21 12 C21 18 18 25 12 30 Z"/></symbol>
    </svg>
    <div class="en-petals" aria-hidden="true">
        <span class="en-petal en-petal--1"><svg class="en-petal__blossom" viewBox="0 0 24 32"><use href="#en-blossom"/></svg></span>
        <span class="en-petal en-petal--2"><svg class="en-petal__blossom" viewBox="0 0 24 32"><use href="#en-blossom"/></svg></span>
        <span class="en-petal en-petal--3"><svg class="en-petal__blossom" viewBox="0 0 24 32"><use href="#en-blossom"/></svg></span>
        <span class="en-petal en-petal--4"><svg class="en-petal__blossom" viewBox="0 0 24 32"><use href="#en-blossom"/></svg></span>
        <span class="en-petal en-petal--5"><svg class="en-petal__blossom" viewBox="0 0 24 32"><use href="#en-blossom"/></svg></span>
    </div>

    {{-- A fine ink rule down the left edge that draws with the page progress. --}}
    <div class="en-brush" aria-hidden="true"><span class="en-brush__ink"></span></div>

    <div class="en-cover" id="opening-cover" style="{{ $coverFocus }}">
        @if ($coverImage)<img class="en-cover__poster" src="{{ $coverImage }}" alt="" aria-hidden="true" style="{{ $coverFocus }}">@endif
        @if ($coverDesktop)
            <video class="en-cover__video en-cover__video--desktop" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video>
                <source src="{{ $coverDesktop }}">
            </video>
        @endif
        @if ($coverMobile)
            <video class="en-cover__video en-cover__video--mobile" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video>
                <source src="{{ $coverMobile }}">
            </video>
        @endif
        <div class="en-cover__panel">
            <span class="en-hanko" aria-hidden="true">{{ $monogram }}</span>
            <span class="invitation-eyebrow">{{ $labels['cover_eyebrow'] }}</span>
            @if ($groomName && $brideName)
                <h1 class="en-couple"><span>{{ $groomName }}</span><i>&amp;</i><span>{{ $brideName }}</span></h1>
            @else
                <h1>{{ $title }}</h1>
            @endif
            @if ($primary_event)<span class="en-cover__date">{{ $primary_event['date'] }}</span>@endif
            @if ($primary_event && $primary_event['timestamp'])
                <div class="en-cover__countdown" data-countdown="{{ $primary_event['timestamp'] }}" aria-label="Hitung mundur menuju acara">
                    <span class="en-cover__countdown-label">{{ $labels['cover_countdown_label'] }}</span>
                    <div data-countdown-output>
                        @foreach (['days' => 'Hari', 'hours' => 'Jam', 'minutes' => 'Menit', 'seconds' => 'Detik'] as $unit => $label)
                            <span><strong data-countdown-unit="{{ $unit }}">00</strong><small>{{ $label }}</small></span>
                        @endforeach
                    </div>
                </div>
            @endif
            @if ($recipient)
                <p class="en-cover__to">{{ $labels['cover_recipient_label'] }}</p>
                <strong class="en-cover__name">{{ $recipient }}</strong>
            @endif
            <a class="en-cover__cta" href="#invitation-content" data-open-invitation>{{ $labels['cover_cta'] }}</a>
        </div>
    </div>

    <nav class="en-nav" aria-label="Navigasi undangan">
        <div class="en-nav__inner">
            <a href="#invitation-content">Awal</a>
            @foreach ($visibleNav as $item)<a href="#{{ $item['id'] }}" data-nav-link="{{ $item['id'] }}">{{ $item['label'] }}</a>@endforeach
        </div>
    </nav>

    <main id="invitation-content" tabindex="-1" data-gate>
        @foreach ($sections as $section)
            @if ($section === 'opening')
                <section class="invitation-section en-hero" id="opening" data-height="{{ $section_heights['opening'] ?? 'full' }}">
                    @if ($openingVideo)
                        @if ($openingVideoMobile !== $openingVideo)
                            <video class="en-hero__video en-hero__video--desktop" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video><source src="{{ $openingVideo }}"></video>
                            <video class="en-hero__video en-hero__video--mobile" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video><source src="{{ $openingVideoMobile }}"></video>
                        @else
                            <video class="en-hero__video" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video><source src="{{ $openingVideo }}"></video>
                        @endif
                    @endif
                    <div class="en-hero__inner">
                        <span class="invitation-eyebrow" data-reveal="down">{{ $labels['opening_eyebrow'] }}</span>
                        @if (count($hosts) >= 2)
                            <h2 class="en-couple en-couple--lg" data-reveal="settle"><span>{{ $hosts[0]['name'] }}</span><i>&amp;</i><span>{{ $hosts[1]['name'] }}</span></h2>
                        @else
                            <h2 data-reveal="settle">{{ $title }}</h2>
                        @endif
                        @if ($opening_text)<p data-reveal>{!! nl2br(e($opening_text)) !!}</p>@endif
                        @if ($primary_event)<p class="en-date" data-reveal>{{ $primary_event['date'] }}</p>@endif
                    </div>
                </section>
            @elseif ($section === 'hosts' && count($hosts))
                <section class="invitation-section" data-height="{{ $section_heights['hosts'] ?? 'full' }}" id="hosts" aria-labelledby="hosts-title">
                    <span class="invitation-eyebrow" data-reveal="down">{{ $labels['hosts_eyebrow'] }}</span>
                    <h2 id="hosts-title" data-reveal="settle">{{ $labels['hosts_title'] }}</h2>
                    <div class="en-hosts" data-count="{{ count($hosts) }}">
                        @foreach ($hosts as $host)
                            <article class="en-host" data-host data-reveal="settle">
                                <button type="button" class="host-card__trigger en-host__trigger" data-host-open data-host-name="{{ $host['name'] }}" data-host-role="{{ match ($host['role']) { 'groom' => 'Mempelai Pria', 'bride' => 'Mempelai Wanita', default => 'Mempelai' } }}" aria-haspopup="dialog" aria-label="Lihat profil {{ $host['name'] }}">
                                    <span class="en-host__portrait">
                                        @if ($host['photo_url'])<img src="{{ $host['photo_url'] }}" alt="Foto {{ $host['name'] }}" loading="lazy" decoding="async">@else<span aria-hidden="true">{{ mb_substr($host['name'], 0, 1) }}</span>@endif
                                    </span>
                                </button>
                                <h3>{{ $host['name'] }}</h3>
                                @if ($host['family'])
                                    <p class="en-host__parents">
                                        <small>{{ match ($host['role']) { 'groom' => 'Putra', 'bride' => 'Putri', default => 'Putra/putri' } }} dari</small>
                                        <span>{{ $host['family'] }}</span>
                                    </p>
                                @endif
                                <div class="host-card__details en-host__details" data-host-details>
                                    @if ($host['birth_order'])<p>{{ $host['birth_order'] }}</p>@endif
                                    @if ($host['bio'])<p>{!! nl2br(e($host['bio'])) !!}</p>@endif
                                </div>
                            </article>
                            @if (count($hosts) === 2 && ! $loop->last)<span class="en-hosts__and" aria-hidden="true">&amp;</span>@endif
                        @endforeach
                    </div>
                </section>
            @elseif ($section === 'events' && count($events))
                <section class="invitation-section invitation-section--tint" data-height="{{ $section_heights['events'] ?? 'full' }}" id="events" aria-labelledby="events-title">
                    <span class="invitation-eyebrow" data-reveal="down">{{ $labels['events_eyebrow'] }}</span>
                    <h2 id="events-title" data-reveal="settle">{{ $labels['events_title'] }}</h2>
                    <div class="en-events">
                        @foreach ($events as $event)
                            <article class="en-event" data-reveal="settle">
                                <div class="en-event__when">
                                    <strong>{{ $event['date'] }}</strong>
                                    @if ($event['start_time'])
                                        <span>{{ $event['start_time'] }}{{ $event['end_time'] ? ' – '.$event['end_time'] : ' s/d Selesai' }}</span>
                                        @if (! ($theme['hide_timezone'] ?? false) && $event['timezone_label'])<small>{{ $event['timezone_label'] }}</small>@endif
                                    @endif
                                </div>
                                <div class="en-event__what">
                                    <h3>{{ $event['label'] }}</h3>
                                    @if ($event['venue'])<p><strong>{{ $event['venue'] }}</strong></p>@endif
                                    @if ($event['address'])<p>{{ $event['address'] }}</p>@endif
                                    <div class="en-actions">
                                        @if (in_array('map', $sections) && $event['directions_url'])<a href="{{ $event['directions_url'] }}" target="_blank" rel="noopener noreferrer">Petunjuk Arah</a>@endif
                                        @if ($event['address'])<button type="button" data-copy="{{ $event['address'] }}">Salin Alamat</button>@endif
                                    </div>
                                    @if (count($event['notes']))<div class="en-event__notes">@foreach ($event['notes'] as $note)<small>{!! nl2br(e($note)) !!}</small>@endforeach</div>@endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @elseif ($section === 'map' && $primary_event && $primary_event['map_embed_url'])
                <section class="invitation-section" data-height="{{ $section_heights['map'] ?? 'full' }}" aria-labelledby="map-title">
                    <span class="invitation-eyebrow" data-reveal="down">{{ $labels['map_eyebrow'] }}</span>
                    <h2 id="map-title" data-reveal="settle">{{ $labels['map_title'] }}</h2>
                    <div class="en-map" data-reveal="settle"><iframe src="{{ $primary_event['map_embed_url'] }}" title="Peta {{ $primary_event['venue'] ?: $primary_event['label'] }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe></div>
                    @if ($primary_event['address'])<p>{{ $primary_event['address'] }}</p>@endif
                    <div class="en-actions en-actions--center">
                        @if ($primary_event['directions_url'])<a href="{{ $primary_event['directions_url'] }}" target="_blank" rel="noopener noreferrer">Buka Google Maps</a>@endif
                        @if ($primary_event['address'])<button type="button" data-copy="{{ $primary_event['address'] }}">Salin Alamat</button>@endif
                    </div>
                </section>
            @elseif ($section === 'countdown' && $primary_event && $primary_event['timestamp'])
                <section class="invitation-section" data-height="{{ $section_heights['countdown'] ?? 'full' }}" data-countdown="{{ $primary_event['timestamp'] }}" aria-label="Hitung mundur menuju hari bahagia">
                    <span class="invitation-eyebrow" data-reveal="down">{{ $labels['countdown_eyebrow'] }}</span>
                    <h2 data-reveal="settle">{{ $labels['countdown_title'] }}</h2>
                    <div class="en-countdown" data-countdown-output>
                        @foreach (['days' => 'Hari', 'hours' => 'Jam', 'minutes' => 'Menit', 'seconds' => 'Detik'] as $unit => $label)
                            <span><strong data-countdown-unit="{{ $unit }}">00</strong><small>{{ $label }}</small></span>
                        @endforeach
                    </div>
                </section>
            @elseif ($section === 'story' && count($stories))
                <section class="invitation-section" data-height="{{ $section_heights['story'] ?? 'full' }}" id="story" aria-labelledby="story-title">
                    <span class="invitation-eyebrow" data-reveal="down">{{ $labels['story_eyebrow'] }}</span>
                    <h2 id="story-title" data-reveal="settle">{{ $labels['story_title'] }}</h2>
                    <div class="en-path">
                        @foreach ($stories as $story)
                            <article class="en-step" data-reveal="settle">
                                @if ($story['image_url'])<img src="{{ $story['image_url'] }}" alt="{{ $story['title'] }}" loading="lazy">@endif
                                <small>{{ $story['date'] }}</small>
                                <h3>{{ $story['title'] }}</h3>
                                @if ($story['body'])<p>{!! nl2br(e($story['body'])) !!}</p>@endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @elseif ($section === 'gallery' && count($gallery))
                <section class="invitation-section invitation-section--tint" data-height="{{ $section_heights['gallery'] ?? 'full' }}" id="gallery" aria-labelledby="gallery-title">
                    <span class="invitation-eyebrow" data-reveal="down">{{ $labels['gallery_eyebrow'] }}</span>
                    <h2 id="gallery-title" data-reveal="settle">{{ $labels['gallery_title'] }}</h2>
                    <div class="en-gallery">
                        @foreach ($gallery as $image)
                            <figure data-reveal="settle">
                                <button type="button" data-lightbox-src="{{ $image['url'] }}" data-lightbox-alt="{{ $image['alt'] }}"><img src="{{ $image['url'] }}" alt="{{ $image['alt'] }}" loading="lazy" decoding="async"></button>
                                @if ($image['caption'])<figcaption>{{ $image['caption'] }}</figcaption>@endif
                            </figure>
                        @endforeach
                    </div>
                </section>
            @elseif (str_starts_with($section, 'blocks') && ! empty($block_sections[$section] ?? $blocks))
                @include('invitations.shared.blocks', ['blocks' => $block_sections[$section] ?? $blocks])
            @elseif ($section === 'rsvp')
                @if ($theme['merge_rsvp_guestbook'] ?? false)
                    @include('invitations.shared.confirmation')
                @else
                    <div id="rsvp">@include('invitations.shared.rsvp')</div>
                @endif
            @elseif ($section === 'guestbook')
                @unless ($theme['merge_rsvp_guestbook'] ?? false)
                    <div id="guestbook">@include('invitations.shared.guestbook')</div>
                @endunless
            @elseif ($section === 'gifts' && count($gifts))
                <section class="invitation-section" data-height="{{ $section_heights['gifts'] ?? 'full' }}" aria-labelledby="gifts-title">
                    <span class="invitation-eyebrow" data-reveal="down">{{ $labels['gifts_eyebrow'] }}</span>
                    <h2 id="gifts-title" data-reveal="settle">{{ $labels['gifts_title'] }}</h2>
                    <p data-reveal>{!! nl2br(e($labels['gifts_intro'])) !!}</p>
                    @include('invitations.shared.gifts')
                </section>
            @elseif ($section === 'livestream' && $livestream_url)
                <section class="invitation-section" data-height="{{ $section_heights['livestream'] ?? 'full' }}">
                    <span class="invitation-eyebrow" data-reveal="down">{{ $labels['livestream_eyebrow'] }}</span>
                    <h2 data-reveal="settle">Live Streaming</h2>
                    <div class="en-actions en-actions--center" data-reveal><a href="{{ $livestream_url }}" target="_blank" rel="noopener noreferrer">{{ $livestream_label }}</a></div>
                </section>
            @elseif ($section === 'contacts' && count($contacts))
                <section class="invitation-section" data-height="{{ $section_heights['contacts'] ?? 'full' }}" aria-labelledby="contacts-title">
                    <span class="invitation-eyebrow" data-reveal="down">{{ $labels['contacts_eyebrow'] }}</span>
                    <h2 id="contacts-title" data-reveal="settle">{{ $labels['contacts_title'] }}</h2>
                    <p data-reveal>{!! nl2br(e($labels['contacts_intro'])) !!}</p>
                    <div class="en-contacts">
                        @foreach ($contacts as $contact)
                            <article class="en-contact" data-reveal="settle">
                                <small>{{ $contact['label'] }}</small>
                                <h3>{{ $contact['name'] }}</h3>
                                <div class="en-actions en-actions--center">
                                    <a href="{{ $contact['whatsapp_url'] }}" target="_blank" rel="noopener noreferrer">WhatsApp</a>
                                    <a href="{{ $contact['phone_url'] }}">Telepon</a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @elseif ($section === 'sharing')
                <section class="invitation-section" data-height="{{ $section_heights['sharing'] ?? 'full' }}">
                    <span class="invitation-eyebrow" data-reveal="down">{{ $labels['sharing_eyebrow'] }}</span>
                    <h2 data-reveal="settle">{{ $labels['sharing_title'] }}</h2>
                    <p data-reveal>{!! nl2br(e($labels['sharing_intro'])) !!}</p>
                    <div class="en-actions en-actions--center" data-reveal>
                        <button type="button" data-share data-share-url="{{ $share_url }}">Bagikan Sekarang</button>
                        <a href="{{ $whatsapp_url }}" target="_blank" rel="noopener noreferrer">Kirim via WhatsApp</a>
                    </div>
                </section>
            @elseif ($section === 'closing')
                <section class="invitation-section en-closing" data-height="{{ $section_heights['closing'] ?? 'full' }}">
                    <span class="invitation-eyebrow" data-reveal="down">{{ $labels['closing_eyebrow'] }}</span>
                    @if (count($hosts) >= 2)
                        <h2 class="en-couple" data-reveal="settle"><span>{{ $hosts[0]['name'] }}</span><i>&amp;</i><span>{{ $hosts[1]['name'] }}</span></h2>
                    @else
                        <h2 data-reveal="settle">{{ $couple_title }}</h2>
                    @endif
                    @if ($closing_message)<p data-reveal>{!! nl2br(e($closing_message)) !!}</p>@endif
                    @include('invitations.shared.closing-families')
                    @if ($closing_footer)<p data-reveal>{!! nl2br(e($closing_footer)) !!}</p>@endif
                    @if ($labels['closing_kicker'] ?? null)<p class="en-kicker" data-reveal>{{ $labels['closing_kicker'] }}</p>@endif
                </section>
            @endif
        @endforeach
    </main>

    @if ($music_url)
        <audio data-music loop preload="none" src="{{ $music_url }}"></audio>
        <button class="en-music" type="button" data-music-toggle aria-label="Putar musik"><span aria-hidden="true">♪</span></button>
    @endif
    <dialog class="en-lightbox" data-lightbox><button type="button" data-lightbox-close aria-label="Tutup galeri">Tutup</button><img data-lightbox-image alt=""></dialog>
    @include('invitations.shared.host-dialog')
</body>
</html>

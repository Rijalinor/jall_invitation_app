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
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Marcellus&family=Work+Sans:wght@300;400;600;700&display=swap">
    @vite(['resources/css/invitations.css', 'resources/js/invitation-hosts.js', 'resources/js/invitation-forms.js', 'resources/js/invitation-lightbox.js', 'resources/invitation-templates/jejak-perjalanan/assets/theme.css', 'resources/invitation-templates/jejak-perjalanan/assets/theme.js'])
</head>
<body class="jejak-perjalanan" style="--jp-accent: {{ $theme['accent_color'] }}; --jp-cover-veil: {{ ($theme['cover_overlay_opacity'] ?? 46) / 100 }};" data-motion="{{ $theme['motion'] }}">
    @php
        $navItems = [
            'hosts' => ['id' => 'hosts', 'label' => 'Mempelai'],
            'events' => ['id' => 'events', 'label' => 'Acara'],
            'story' => ['id' => 'story', 'label' => 'Cerita'],
            'gallery' => ['id' => 'gallery', 'label' => 'Galeri'],
            'rsvp' => ['id' => 'rsvp', 'label' => 'RSVP'],
        ];
        $visibleNav = collect($sections)->filter(fn ($section) => isset($navItems[$section]))->mapWithKeys(fn ($section) => [$section => $navItems[$section]])->all();
        $groomName = collect($hosts)->firstWhere('role', 'groom')['name'] ?? ($hosts[0]['name'] ?? null);
        $brideName = collect($hosts)->firstWhere('role', 'bride')['name'] ?? ($hosts[1]['name'] ?? null);
        $coverImage = $theme['cover_poster_image'] ?: ($gallery[0]['url'] ?? ($hosts[0]['photo_url'] ?? null));
        $coverDesktop = $theme['cover_video_enabled'] ? $theme['cover_video_desktop'] : null;
        $coverMobile = $theme['cover_video_enabled'] ? ($theme['cover_video_mobile'] ?: $coverDesktop) : null;
        $openingVideo = $coverDesktop;
        $openingVideoMobile = $coverMobile;
        $coverFocus = 'object-position: '.($theme['cover_focal_x'] ?? 50).'% '.($theme['cover_focal_y'] ?? 45).'%;';
    @endphp

    {{-- Back layer: soft map contours and little postcards drifting behind the
         sections. The postcards loop across the whole viewport, so they are present
         at every scroll position; the scroll adds only a small, bounded drift.
         This template keeps its ambient motion behind the content only. --}}
    <div class="jp-bg" aria-hidden="true">
        <span class="jp-contour jp-contour--1"></span>
        <span class="jp-contour jp-contour--2"></span>
        <span class="jp-contour jp-contour--3"></span>
        <span class="jp-contour jp-contour--4"></span>
        <span class="jp-postcard jp-postcard--1"></span>
        <span class="jp-postcard jp-postcard--2"></span>
        <span class="jp-postcard jp-postcard--3"></span>
        <span class="jp-postcard jp-postcard--4"></span>
        <span class="jp-postcard jp-postcard--5"></span>
    </div>

    {{-- The traveller's route: a dashed line down the left edge with a marker that
         travels the whole journey as the guest scrolls. --}}
    <div class="jp-route" aria-hidden="true">
        <span class="jp-route__line"></span>
        <span class="jp-route__dot"></span>
    </div>

    {{-- A compass whose ring keeps turning on its own while the needle answers the
         scroll position. --}}
    <div class="jp-compass" aria-hidden="true">
        <span class="jp-compass__ring"></span>
        <span class="jp-compass__needle"></span>
    </div>

    <div class="jp-cover" id="opening-cover" style="{{ $coverFocus }}">
        @if ($coverImage)<img class="jp-cover__poster" src="{{ $coverImage }}" alt="" aria-hidden="true" style="{{ $coverFocus }}">@endif
        @if ($coverDesktop)
            <video class="jp-cover__video jp-cover__video--desktop" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video>
                <source src="{{ $coverDesktop }}">
            </video>
        @endif
        @if ($coverMobile)
            <video class="jp-cover__video jp-cover__video--mobile" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video>
                <source src="{{ $coverMobile }}">
            </video>
        @endif
        <div class="jp-ticket jp-ticket--cover">
            <div class="jp-ticket__stub">
                <span class="jp-ticket__mark" aria-hidden="true">✦</span>
                <span class="jp-ticket__class">Perjalanan</span>
            </div>
            <div class="jp-ticket__body">
                <span class="invitation-eyebrow">{{ $labels['cover_eyebrow'] }}</span>
                @if ($groomName && $brideName)
                    <h1 class="jp-couple-title"><span>{{ $groomName }}</span><i>&amp;</i><span>{{ $brideName }}</span></h1>
                @else
                    <h1>{{ $title }}</h1>
                @endif
                @if ($primary_event)<span class="jp-cover__date">{{ $primary_event['date'] }}</span>@endif
                @if ($primary_event && $primary_event['timestamp'])
                    <div class="jp-cover__countdown" data-countdown="{{ $primary_event['timestamp'] }}" aria-label="Hitung mundur menuju acara">
                        <span class="jp-cover__countdown-label">{{ $labels['cover_countdown_label'] }}</span>
                        <div data-countdown-output>
                            @foreach (['days' => 'Hari', 'hours' => 'Jam', 'minutes' => 'Menit', 'seconds' => 'Detik'] as $unit => $label)
                                <span><strong data-countdown-unit="{{ $unit }}">00</strong><small>{{ $label }}</small></span>
                            @endforeach
                        </div>
                    </div>
                @endif
                @if ($recipient)
                    <p class="jp-cover__to">{{ $labels['cover_recipient_label'] }}</p>
                    <strong class="jp-cover__name">{{ $recipient }}</strong>
                @endif
                <a class="jp-cover__cta" href="#invitation-content" data-open-invitation>{{ $labels['cover_cta'] }}</a>
            </div>
        </div>
    </div>

    <nav class="jp-nav" aria-label="Navigasi undangan">
        <div class="jp-nav__inner">
            <a href="#invitation-content">Awal</a>
            @foreach ($visibleNav as $item)<a href="#{{ $item['id'] }}">{{ $item['label'] }}</a>@endforeach
        </div>
    </nav>

    <main id="invitation-content" tabindex="-1" data-gate>
        @foreach ($sections as $section)
            @if ($section === 'opening')
                <section class="invitation-section jp-hero" id="opening" data-height="{{ $section_heights['opening'] ?? 'full' }}">
                    @if ($openingVideo)
                        @if ($openingVideoMobile !== $openingVideo)
                            <video class="jp-hero__video jp-hero__video--desktop" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video><source src="{{ $openingVideo }}"></video>
                            <video class="jp-hero__video jp-hero__video--mobile" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video><source src="{{ $openingVideoMobile }}"></video>
                        @else
                            <video class="jp-hero__video" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video><source src="{{ $openingVideo }}"></video>
                        @endif
                    @endif
                    <div class="jp-hero__inner">
                        <span class="invitation-eyebrow" data-reveal="down">{{ $labels['opening_eyebrow'] }}</span>
                        @if (count($hosts) >= 2)
                            <h2 class="jp-couple-title jp-couple-title--lg" data-reveal="settle"><span>{{ $hosts[0]['name'] }}</span><i>&amp;</i><span>{{ $hosts[1]['name'] }}</span></h2>
                        @else
                            <h2 data-reveal="settle">{{ $title }}</h2>
                        @endif
                        @if ($opening_text)<p data-reveal>{!! nl2br(e($opening_text)) !!}</p>@endif
                        @if ($primary_event)<p class="jp-date" data-reveal>{{ $primary_event['date'] }}</p>@endif
                    </div>
                </section>
            @elseif ($section === 'hosts' && count($hosts))
                <section class="invitation-section" data-height="{{ $section_heights['hosts'] ?? 'full' }}" id="hosts" aria-labelledby="hosts-title">
                    <span class="invitation-eyebrow" data-reveal="down">{{ $labels['hosts_eyebrow'] }}</span>
                    <h2 id="hosts-title" data-reveal="settle">{{ $labels['hosts_title'] }}</h2>
                    <div class="jp-hosts" data-count="{{ count($hosts) }}">
                        @foreach ($hosts as $host)
                            <article class="jp-host" data-host data-reveal="settle">
                                <button type="button" class="host-card__trigger jp-host__trigger" data-host-open data-host-name="{{ $host['name'] }}" data-host-role="{{ match ($host['role']) { 'groom' => 'Mempelai Pria', 'bride' => 'Mempelai Wanita', default => 'Mempelai' } }}" aria-haspopup="dialog" aria-label="Lihat profil {{ $host['name'] }}">
                                    <span class="jp-host__photo">
                                        @if ($host['photo_url'])<img src="{{ $host['photo_url'] }}" alt="Foto {{ $host['name'] }}" loading="lazy" decoding="async">@else<span aria-hidden="true">{{ mb_substr($host['name'], 0, 1) }}</span>@endif
                                    </span>
                                </button>
                                <h3>{{ $host['name'] }}</h3>
                                @if ($host['family'])
                                    <p class="jp-host__parents">
                                        <small>{{ match ($host['role']) { 'groom' => 'Putra', 'bride' => 'Putri', default => 'Putra/putri' } }} dari</small>
                                        <span>{{ $host['family'] }}</span>
                                    </p>
                                @endif
                                @if ($host['instagram'])<a href="{{ $host['instagram'] }}" rel="noopener noreferrer" target="_blank">Instagram</a>@endif
                                <div class="host-card__details jp-host__details" data-host-details>
                                    @if ($host['birth_order'])<p>{{ $host['birth_order'] }}</p>@endif
                                    @if ($host['bio'])<p>{!! nl2br(e($host['bio'])) !!}</p>@endif
                                </div>
                            </article>
                            @if (count($hosts) === 2 && ! $loop->last)<span class="jp-hosts__and" aria-hidden="true">&amp;</span>@endif
                        @endforeach
                    </div>
                </section>
            @elseif ($section === 'events' && count($events))
                <section class="invitation-section invitation-section--tint" data-height="{{ $section_heights['events'] ?? 'full' }}" id="events" aria-labelledby="events-title">
                    <span class="invitation-eyebrow" data-reveal="down">{{ $labels['events_eyebrow'] }}</span>
                    <h2 id="events-title" data-reveal="settle">{{ $labels['events_title'] }}</h2>
                    <div class="jp-events">
                        @foreach ($events as $event)
                            <article class="jp-ticket" data-reveal="settle">
                                <div class="jp-ticket__stub">
                                    <span class="jp-ticket__mark" aria-hidden="true">✈</span>
                                    <span class="jp-ticket__class">{{ $loop->iteration < 10 ? '0' : '' }}{{ $loop->iteration }}</span>
                                </div>
                                <div class="jp-ticket__body">
                                    <h3>{{ $event['label'] }}</h3>
                                    <div class="jp-ticket__fields">
                                        <div class="jp-ticket__field">
                                            <small>Tanggal</small>
                                            <strong>{{ $event['date'] }}</strong>
                                        </div>
                                        @if ($event['start_time'])
                                            <div class="jp-ticket__field">
                                                <small>Waktu</small>
                                                <strong>{{ $event['start_time'] }}{{ $event['end_time'] ? ' – '.$event['end_time'] : ' s/d Selesai' }}</strong>
                                                @if (! ($theme['hide_timezone'] ?? false) && $event['timezone_label'])<small>{{ $event['timezone_label'] }}</small>@endif
                                            </div>
                                        @endif
                                        @if ($event['venue'] || $event['address'])
                                            <div class="jp-ticket__field jp-ticket__field--wide">
                                                <small>Tempat</small>
                                                <strong>{{ $event['venue'] ?: 'Lokasi Acara' }}</strong>
                                                @if ($event['address'])<small>{{ $event['address'] }}</small>@endif
                                            </div>
                                        @endif
                                    </div>
                                    <div class="jp-actions jp-actions--center">
                                        @if (in_array('map', $sections) && $event['directions_url'])<a href="{{ $event['directions_url'] }}" target="_blank" rel="noopener noreferrer">Petunjuk Arah</a>@endif
                                        @if ($event['address'])<button type="button" data-copy="{{ $event['address'] }}">Salin Alamat</button>@endif
                                    </div>
                                    @if (count($event['notes']))<div class="jp-event__notes">@foreach ($event['notes'] as $note)<small>{!! nl2br(e($note)) !!}</small>@endforeach</div>@endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @elseif ($section === 'map' && $primary_event && $primary_event['map_embed_url'])
                <section class="invitation-section" data-height="{{ $section_heights['map'] ?? 'full' }}" aria-labelledby="map-title">
                    <span class="invitation-eyebrow" data-reveal="down">{{ $labels['map_eyebrow'] }}</span>
                    <h2 id="map-title" data-reveal="settle">{{ $labels['map_title'] }}</h2>
                    <div class="jp-map" data-reveal="settle"><iframe src="{{ $primary_event['map_embed_url'] }}" title="Peta {{ $primary_event['venue'] ?: $primary_event['label'] }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe></div>
                    @if ($primary_event['address'])<p>{{ $primary_event['address'] }}</p>@endif
                    <div class="jp-actions jp-actions--center">
                        @if ($primary_event['directions_url'])<a href="{{ $primary_event['directions_url'] }}" target="_blank" rel="noopener noreferrer">Buka Google Maps</a>@endif
                        @if ($primary_event['address'])<button type="button" data-copy="{{ $primary_event['address'] }}">Salin Alamat</button>@endif
                    </div>
                </section>
            @elseif ($section === 'countdown' && $primary_event && $primary_event['timestamp'])
                <section class="invitation-section" data-height="{{ $section_heights['countdown'] ?? 'full' }}" data-countdown="{{ $primary_event['timestamp'] }}" aria-label="Hitung mundur menuju hari bahagia">
                    <span class="invitation-eyebrow" data-reveal="down">{{ $labels['countdown_eyebrow'] }}</span>
                    <h2 data-reveal="settle">{{ $labels['countdown_title'] }}</h2>
                    <div class="jp-countdown" data-countdown-output>
                        @foreach (['days' => 'Hari', 'hours' => 'Jam', 'minutes' => 'Menit', 'seconds' => 'Detik'] as $unit => $label)
                            <span><strong data-countdown-unit="{{ $unit }}">00</strong><small>{{ $label }}</small></span>
                        @endforeach
                    </div>
                </section>
            @elseif ($section === 'story' && count($stories))
                <section class="invitation-section" data-height="{{ $section_heights['story'] ?? 'full' }}" id="story" aria-labelledby="story-title">
                    <span class="invitation-eyebrow" data-reveal="down">{{ $labels['story_eyebrow'] }}</span>
                    <h2 id="story-title" data-reveal="settle">{{ $labels['story_title'] }}</h2>
                    <div class="jp-log">
                        @foreach ($stories as $story)
                            <article class="jp-stop" data-reveal="settle">
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
                    <div class="jp-postcards">
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
                    <div class="jp-actions jp-actions--center" data-reveal><a href="{{ $livestream_url }}" target="_blank" rel="noopener noreferrer">{{ $livestream_label }}</a></div>
                </section>
            @elseif ($section === 'contacts' && count($contacts))
                <section class="invitation-section" data-height="{{ $section_heights['contacts'] ?? 'full' }}" aria-labelledby="contacts-title">
                    <span class="invitation-eyebrow" data-reveal="down">{{ $labels['contacts_eyebrow'] }}</span>
                    <h2 id="contacts-title" data-reveal="settle">{{ $labels['contacts_title'] }}</h2>
                    <p data-reveal>{!! nl2br(e($labels['contacts_intro'])) !!}</p>
                    <div class="jp-contacts">
                        @foreach ($contacts as $contact)
                            <article class="jp-contact" data-reveal="settle">
                                <small>{{ $contact['label'] }}</small>
                                <h3>{{ $contact['name'] }}</h3>
                                <div class="jp-actions jp-actions--center">
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
                    <div class="jp-actions jp-actions--center" data-reveal>
                        <button type="button" data-share data-share-url="{{ $share_url }}">Bagikan Sekarang</button>
                        <a href="{{ $whatsapp_url }}" target="_blank" rel="noopener noreferrer">Kirim via WhatsApp</a>
                    </div>
                </section>
            @elseif ($section === 'closing')
                <section class="invitation-section jp-closing" data-height="{{ $section_heights['closing'] ?? 'full' }}">
                    <span class="invitation-eyebrow" data-reveal="down">{{ $labels['closing_eyebrow'] }}</span>
                    @if (count($hosts) >= 2)
                        <h2 class="jp-couple-title" data-reveal="settle"><span>{{ $hosts[0]['name'] }}</span><i>&amp;</i><span>{{ $hosts[1]['name'] }}</span></h2>
                    @else
                        <h2 data-reveal="settle">{{ $couple_title }}</h2>
                    @endif
                    @if ($closing_message)<p data-reveal>{!! nl2br(e($closing_message)) !!}</p>@endif
                    @include('invitations.shared.closing-families')
                    @if ($closing_footer)<p data-reveal>{!! nl2br(e($closing_footer)) !!}</p>@endif
                    @if ($labels['closing_kicker'] ?? null)<p class="jp-kicker" data-reveal>{{ $labels['closing_kicker'] }}</p>@endif
                </section>
            @endif
        @endforeach
    </main>

    @if ($music_url)
        <audio data-music loop preload="none" src="{{ $music_url }}"></audio>
        <button class="jp-music" type="button" data-music-toggle aria-label="Putar musik"><span aria-hidden="true">♪</span></button>
    @endif
    <dialog class="jp-lightbox" data-lightbox><button type="button" data-lightbox-close aria-label="Tutup galeri">Tutup</button><img data-lightbox-image alt=""></dialog>
    @include('invitations.shared.host-dialog')
</body>
</html>

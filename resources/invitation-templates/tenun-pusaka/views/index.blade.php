<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $title }}">
    <title>{{ $title }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Karla:wght@400;500;600&family=Marcellus&display=swap" rel="stylesheet">
    {{-- Marks that scripting is available before the first paint, so the cover can
         be promoted to a full overlay without a flash of it in normal flow. --}}
    <script>document.documentElement.classList.add('js-ready');</script>
    @vite(['resources/invitation-templates/tenun-pusaka/assets/theme.css', 'resources/invitation-templates/tenun-pusaka/assets/theme.js'])
</head>
<body class="tenun-pusaka" style="--tp-accent: {{ $theme['accent_color'] }}; --tp-focal-x: {{ $theme['cover_focal_x'] ?? 50 }}%; --tp-focal-y: {{ $theme['cover_focal_y'] ?? 45 }}%; --tp-overlay: {{ ($theme['cover_overlay_opacity'] ?? 52) / 100 }};" data-motion="{{ $theme['motion'] }}">
    @php
        $groom = collect($hosts)->firstWhere('role', 'groom')['name'] ?? ($hosts[0]['name'] ?? null);
        $bride = collect($hosts)->firstWhere('role', 'bride')['name'] ?? ($hosts[1]['name'] ?? null);
        $couple = $groom && $bride ? [$groom, $bride] : [$title];
        $coverImage = $theme['cover_poster_image'] ?: ($gallery[0]['url'] ?? ($hosts[0]['photo_url'] ?? null));
        $navItems = ['hosts' => 'Mempelai', 'events' => 'Acara', 'story' => 'Kisah', 'gallery' => 'Galeri', 'rsvp' => 'RSVP'];
        $visibleNav = array_values(array_intersect($sections, array_keys($navItems)));
    @endphp

    {{-- The cover is two woven panels that part left and right, like threads
         separating on the loom. --}}
    <div class="tp-cover" id="tp-cover">
        @if ($coverImage)<img class="tp-cover__photo" src="{{ $coverImage }}" alt="" fetchpriority="high" decoding="async">@endif
        <div class="tp-cover__veil" aria-hidden="true"></div>
        <div class="tp-cover__panel tp-cover__panel--left" aria-hidden="true"></div>
        <div class="tp-cover__panel tp-cover__panel--right" aria-hidden="true"></div>

        <div class="tp-cover__content">
            <p class="tp-kicker">Undangan pernikahan</p>
            <h1 class="tp-cover__names">@foreach ($couple as $name)<span class="tp-cover__name">{{ $name }}</span>@if (! $loop->last)<i class="tp-amp" aria-hidden="true">&amp;</i>@endif@endforeach</h1>
            @if ($primary_event)<p class="tp-cover__date">{{ $primary_event['date'] }}</p>@endif
            <p class="tp-cover__to">Kepada <strong class="tp-cover__guest">{{ $recipient }}</strong></p>
            <a class="tp-open" href="#tp-content" data-open-invitation>Buka undangan</a>
        </div>
    </div>

    @if ($visibleNav)
        <nav class="tp-nav" aria-label="Navigasi undangan" data-gate>
            <span class="tp-nav__knot" aria-hidden="true"></span>
            @foreach ($visibleNav as $key)<a href="#{{ $key }}">{{ $navItems[$key] }}</a>@endforeach
        </nav>
    @endif

    <main id="tp-content" tabindex="-1" data-gate>
        @foreach ($sections as $section)
            @if ($section === 'opening')
                <section class="tp-stage tp-opening">
                    <div class="tp-stage__inner">
                        <p class="tp-kicker">Dengan hormat</p>
                        <p class="tp-opening__text" data-reveal>{{ $opening_text ?: 'Dengan menyebut nama Tuhan, kami bermaksud menyelenggarakan pernikahan putra-putri kami. Merupakan suatu kehormatan apabila Bapak/Ibu/Saudara/i berkenan hadir.' }}</p>
                        <dl class="tp-facts" data-reveal>
                            <div><dt>Mempelai</dt><dd>{{ implode(' & ', $couple) }}</dd></div>
                            @if ($primary_event)<div><dt>Hari</dt><dd>{{ $primary_event['date'] }}</dd></div>@endif
                            @if ($primary_event && $primary_event['venue'])<div><dt>Tempat</dt><dd>{{ $primary_event['venue'] }}</dd></div>@endif
                        </dl>
                    </div>
                </section>
            @elseif ($section === 'hosts' && count($hosts))
                <section class="tp-stage tp-couple" id="hosts" aria-labelledby="hosts-title">
                    <div class="tp-stage__inner">
                        <header class="tp-head" data-reveal>
                            <p class="tp-kicker">Dua helai benang</p>
                            <h2 class="tp-head__title" id="hosts-title">Yang bertemu, lalu menjadi satu</h2>
                        </header>
                        <div class="tp-couple__grid">
                            @foreach ($hosts as $host)
                                <article class="tp-host" data-reveal>
                                    <div class="tp-host__frame">
                                        @if ($host['photo_url'])
                                            <img src="{{ $host['photo_url'] }}" alt="Foto {{ $host['name'] }}" loading="lazy" decoding="async">
                                        @else
                                            <span class="tp-host__initial" aria-hidden="true">{{ mb_substr($host['name'], 0, 1) }}</span>
                                        @endif
                                    </div>
                                    <p class="tp-host__role">{{ match ($host['role']) { 'groom' => 'Mempelai pria', 'bride' => 'Mempelai wanita', default => 'Tuan rumah' } }}</p>
                                    <h3 class="tp-host__name">{{ $host['name'] }}</h3>
                                    @if ($host['birth_order'])<p class="tp-host__order">{{ $host['birth_order'] }}</p>@endif
                                    @if ($host['family'])<p class="tp-host__family">{{ $host['family'] }}</p>@endif
                                    @if ($host['bio'])<p class="tp-host__bio">{{ $host['bio'] }}</p>@endif
                                    @if ($host['instagram'])<a class="tp-host__link" href="{{ $host['instagram'] }}" target="_blank" rel="noopener noreferrer">Instagram</a>@endif
                                </article>
                            @endforeach
                        </div>
                    </div>
                </section>
            @elseif ($section === 'story' && count($stories))
                <section class="tp-stage tp-story" id="story" aria-labelledby="story-title">
                    <div class="tp-stage__inner">
                        <header class="tp-head" data-reveal>
                            <p class="tp-kicker">Helai demi helai</p>
                            <h2 class="tp-head__title" id="story-title">Kisah kami</h2>
                        </header>
                        <ol class="tp-timeline">
                            @foreach ($stories as $story)
                                <li class="tp-timeline__item" data-reveal>
                                    <span class="tp-timeline__date">{{ $story['date'] }}</span>
                                    <div class="tp-timeline__body">
                                        @if ($story['image_url'])<img class="tp-timeline__image" src="{{ $story['image_url'] }}" alt="{{ $story['title'] }}" loading="lazy" decoding="async">@endif
                                        <h3 class="tp-timeline__title">{{ $story['title'] }}</h3>
                                        @if ($story['body'])<p class="tp-timeline__text">{{ $story['body'] }}</p>@endif
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                </section>
            @elseif ($section === 'events' && count($events))
                <section class="tp-stage tp-events" id="events" aria-labelledby="events-title">
                    <div class="tp-stage__inner">
                        <header class="tp-head" data-reveal>
                            <p class="tp-kicker">Rangkaian acara</p>
                            <h2 class="tp-head__title" id="events-title">Waktu dan tempat</h2>
                        </header>
                        <div class="tp-events__list">
                            @foreach ($events as $event)
                                <article class="tp-event" data-reveal>
                                    <p class="tp-event__index">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</p>
                                    <div class="tp-event__detail">
                                        <h3 class="tp-event__label">{{ $event['label'] }}</h3>
                                        <dl class="tp-event__meta">
                                            <div><dt>Tanggal</dt><dd>{{ $event['date'] }}</dd></div>
                                            <div><dt>Waktu</dt><dd>{{ $event['start_time'] }}@if ($event['end_time']) &ndash; {{ $event['end_time'] }}@endif <span class="tp-event__zone">{{ $event['timezone'] }}</span></dd></div>
                                            @if ($event['venue'])<div><dt>Tempat</dt><dd>{{ $event['venue'] }}@if ($event['address']) <span class="tp-event__address">{{ $event['address'] }}</span>@endif</dd></div>@endif
                                        </dl>
                                        <div class="tp-actions">
                                            @if ($event['directions_url'])<a class="tp-action" href="{{ $event['directions_url'] }}" target="_blank" rel="noopener noreferrer">Petunjuk arah</a>@endif
                                            @if ($event['calendar_url'])<a class="tp-action" href="{{ $event['calendar_url'] }}" target="_blank" rel="noopener noreferrer">Simpan tanggal</a>@endif
                                            @if ($event['ics_url'])<a class="tp-action" href="{{ $event['ics_url'] }}">Unduh kalender</a>@endif
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                </section>
            @elseif ($section === 'countdown' && $primary_event && $primary_event['timestamp'])
                <section class="tp-stage tp-countdown" aria-label="Hitung mundur menuju hari bahagia">
                    <div class="tp-stage__inner">
                        <p class="tp-kicker" data-reveal>Menuju hari bahagia</p>
                        <div class="tp-countdown" data-countdown="{{ $primary_event['timestamp'] }}" data-countdown-output data-reveal>
                            @foreach (['days' => 'Hari', 'hours' => 'Jam', 'minutes' => 'Menit', 'seconds' => 'Detik'] as $unit => $label)
                                <div class="tp-countdown__cell"><strong class="tp-countdown__num" data-countdown-unit="{{ $unit }}">00</strong><span class="tp-countdown__label">{{ $label }}</span></div>
                            @endforeach
                        </div>
                    </div>
                </section>
            @elseif ($section === 'map' && $primary_event && $primary_event['map_embed_url'])
                <section class="tp-stage tp-map" aria-labelledby="map-title">
                    <div class="tp-stage__inner">
                        <header class="tp-head" data-reveal>
                            <p class="tp-kicker">Menemukan kami</p>
                            <h2 class="tp-head__title" id="map-title">Peta lokasi</h2>
                        </header>
                        <div class="tp-map__frame" data-reveal>
                            <iframe src="{{ $primary_event['map_embed_url'] }}" title="Peta {{ $primary_event['venue'] ?: $primary_event['label'] }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                        @if ($primary_event['address'])
                            <p class="tp-map__address" data-reveal>{{ $primary_event['address'] }}</p>
                        @endif
                        <div class="tp-actions" data-reveal>
                            @if ($primary_event['directions_url'])<a class="tp-action" href="{{ $primary_event['directions_url'] }}" target="_blank" rel="noopener noreferrer">Buka Google Maps</a>@endif
                            @if ($primary_event['address'])<button class="tp-action" type="button" data-copy="{{ $primary_event['address'] }}">Salin alamat</button>@endif
                        </div>
                    </div>
                </section>
            @elseif ($section === 'gallery' && count($gallery))
                <section class="tp-stage tp-gallery" id="gallery" aria-labelledby="gallery-title">
                    <div class="tp-stage__inner">
                        <header class="tp-head" data-reveal>
                            <p class="tp-kicker">Rekaman hari</p>
                            <h2 class="tp-head__title" id="gallery-title">Galeri</h2>
                        </header>
                        <div class="tp-gallery__grid">
                            @foreach ($gallery as $image)
                                <figure class="tp-shot" data-reveal>
                                    <button class="tp-shot__open" type="button" data-lightbox-src="{{ $image['url'] }}" data-lightbox-alt="{{ $image['alt'] }}">
                                        <img src="{{ $image['url'] }}" alt="{{ $image['alt'] }}" loading="lazy" decoding="async">
                                    </button>
                                    @if ($image['caption'])<figcaption class="tp-shot__caption">{{ $image['caption'] }}</figcaption>@endif
                                </figure>
                            @endforeach
                        </div>
                    </div>
                </section>
            @elseif ($section === 'rsvp')
                <div class="tp-stage tp-shared" id="rsvp" data-gate>@include('invitations.shared.rsvp')</div>
            @elseif ($section === 'guestbook')
                <div class="tp-stage tp-shared" data-gate>@include('invitations.shared.guestbook')</div>
            @elseif ($section === 'gifts' && count($gifts))
                <section class="tp-stage tp-gifts" aria-labelledby="gifts-title">
                    <div class="tp-stage__inner">
                        <header class="tp-head" data-reveal>
                            <p class="tp-kicker">Tanda kasih</p>
                            <h2 class="tp-head__title" id="gifts-title">Hadiah digital</h2>
                        </header>
                        <div class="tp-gifts__grid">
                            @foreach ($gifts as $gift)
                                <article class="tp-gift" data-reveal>
                                    <p class="tp-gift__type">{{ $gift['type_label'] }}</p>
                                    <h3 class="tp-gift__provider">{{ $gift['provider'] }}</h3>
                                    @if ($gift['account_number'])
                                        <p class="tp-gift__number">{{ $gift['account_number'] }}</p>
                                        <button class="tp-action" type="button" data-copy="{{ $gift['account_number'] }}">Salin nomor</button>
                                    @endif
                                    @if ($gift['account_name'])<p class="tp-gift__name">a.n. {{ $gift['account_name'] }}</p>@endif
                                    @if ($gift['delivery_address'])
                                        <p class="tp-gift__address">{{ $gift['delivery_address'] }}</p>
                                        <button class="tp-action" type="button" data-copy="{{ $gift['delivery_address'] }}">Salin alamat</button>
                                    @endif
                                    @if ($gift['notes'])<p class="tp-gift__notes">{{ $gift['notes'] }}</p>@endif
                                </article>
                            @endforeach
                        </div>
                    </div>
                </section>
            @elseif ($section === 'contacts' && count($contacts))
                <section class="tp-stage tp-contacts" aria-labelledby="contacts-title">
                    <div class="tp-stage__inner">
                        <header class="tp-head" data-reveal>
                            <p class="tp-kicker">Menghubungi kami</p>
                            <h2 class="tp-head__title" id="contacts-title">Kontak penanggung jawab</h2>
                        </header>
                        <div class="tp-contacts__grid">
                            @foreach ($contacts as $contact)
                                <a class="tp-contact" data-reveal href="{{ $contact['whatsapp_url'] }}" target="_blank" rel="noopener noreferrer">
                                    <span class="tp-contact__label">{{ $contact['label'] }}</span>
                                    <strong class="tp-contact__name">{{ $contact['name'] }}</strong>
                                    <span class="tp-contact__phone">{{ $contact['phone'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </section>
            @elseif ($section === 'livestream' && $livestream_url)
                <section class="tp-stage tp-live">
                    <div class="tp-stage__inner">
                        <p class="tp-kicker" data-reveal>Hadir dari jauh</p>
                        <h2 class="tp-head__title" data-reveal>Ikuti acaranya secara langsung</h2>
                        <a class="tp-action tp-action--solid" data-reveal href="{{ $livestream_url }}" target="_blank" rel="noopener noreferrer">{{ $livestream_label }}</a>
                    </div>
                </section>
            @elseif ($section === 'sharing')
                <section class="tp-stage tp-share">
                    <div class="tp-stage__inner">
                        <p class="tp-kicker" data-reveal>Meneruskan kabar</p>
                        <h2 class="tp-head__title" data-reveal>Bagikan undangan ini</h2>
                        <div class="tp-share__actions" data-reveal>
                            <button class="tp-action tp-action--solid" type="button" data-share data-share-url="{{ $share_url }}"><span data-share-label>Bagikan undangan</span></button>
                            <a class="tp-action" href="{{ $whatsapp_url }}" target="_blank" rel="noopener noreferrer">Kirim lewat WhatsApp</a>
                        </div>
                    </div>
                </section>
            @elseif ($section === 'closing')
                <section class="tp-stage tp-closing">
                    @if ($coverImage)<img class="tp-closing__image" src="{{ $coverImage }}" alt="" aria-hidden="true" loading="lazy">@endif
                    <div class="tp-closing__veil" aria-hidden="true"></div>
                    <div class="tp-stage__inner">
                        <p class="tp-kicker" data-reveal>Terima kasih</p>
                        <h2 class="tp-closing__names" data-reveal>@foreach ($couple as $name)<span class="tp-closing__name">{{ $name }}</span>@endforeach</h2>
                        @if ($closing_message)<p class="tp-closing__text" data-reveal>{{ $closing_message }}</p>@endif
                    </div>
                </section>
            @endif
        @endforeach
    </main>

    @if ($music_url)
        <audio data-music loop preload="none" src="{{ $music_url }}"></audio>
        <button class="tp-music" type="button" data-music-toggle aria-pressed="false" aria-label="Putar musik"><span class="tp-music__glyph" aria-hidden="true">♪</span></button>
    @endif

    <dialog class="tp-lightbox" data-lightbox>
        <button class="tp-lightbox__close" type="button" data-lightbox-close aria-label="Tutup pratinjau">Tutup</button>
        <img data-lightbox-image alt="">
    </dialog>
</body>
</html>

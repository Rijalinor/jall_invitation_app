<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $title }}">
    <title>{{ $title }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Inter:wght@400;700&display=swap" rel="stylesheet">
    @vite(['resources/invitation-templates/midnight-ledger/assets/theme.css', 'resources/invitation-templates/midnight-ledger/assets/theme.js'])
</head>
<body class="midnight-ledger" style="--ml-accent: {{ $theme['accent_color'] }}; --ml-focal-x: {{ $theme['cover_focal_x'] }}%; --ml-focal-y: {{ $theme['cover_focal_y'] }}%; --ml-overlay: {{ $theme['cover_overlay_opacity'] / 100 }};" data-motion="{{ $theme['motion'] }}">
    @php
        $coverImage = $theme['cover_poster_image'] ?: ($gallery[0]['url'] ?? ($hosts[0]['photo_url'] ?? null));
        $coverDesktop = $theme['cover_video_enabled'] ? $theme['cover_video_desktop'] : null;
        $coverMobile = $theme['cover_video_enabled'] ? ($theme['cover_video_mobile'] ?: $coverDesktop) : null;
        $groomName = collect($hosts)->firstWhere('role', 'groom')['name'] ?? ($hosts[0]['name'] ?? null);
        $brideName = collect($hosts)->firstWhere('role', 'bride')['name'] ?? ($hosts[1]['name'] ?? null);
        $nav = [
            'events' => count($events) ? ['id' => 'agenda', 'icon' => '◈', 'label' => 'Acara'] : null,
            'hosts'  => count($hosts)  ? ['id' => 'people', 'icon' => '◎', 'label' => 'Mempelai'] : null,
            'gallery'=> count($gallery)? ['id' => 'frames', 'icon' => '▣', 'label' => 'Galeri'] : null,
            'rsvp'   => in_array('rsvp', $sections) ? ['id' => 'response', 'icon' => '✦', 'label' => 'RSVP'] : null,
        ];
        $navigation = collect($sections)->mapWithKeys(fn ($s) => isset($nav[$s]) ? [$s => $nav[$s]] : [])->all();
        foreach (['events' => 'Ⅰ', 'hosts' => 'Ⅱ', 'gallery' => 'Ⅲ', 'rsvp' => 'Ⅳ'] as $key => $icon) {
            if (isset($navigation[$key])) $navigation[$key]['icon'] = $icon;
        }
        $sectionNumbers = array_flip(array_keys($navigation));
        $showLivestreamNav = in_array('livestream', $sections) && $livestream_url;
        $showSharingNav = in_array('sharing', $sections);
    @endphp
    <div class="ml-cover" data-cover>
        @if ($coverImage)<img class="ml-cover__poster" src="{{ $coverImage }}" alt="" aria-hidden="true">@endif
        @if ($coverDesktop)
            <video class="ml-cover__video ml-cover__video--desktop" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video>
                <source src="{{ $coverDesktop }}">
            </video>
        @endif
        @if ($coverMobile)
            <video class="ml-cover__video ml-cover__video--mobile" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video>
                <source src="{{ $coverMobile }}">
            </video>
        @endif
        <div class="ml-cover__shade" aria-hidden="true"></div>
        <p class="ml-kicker">{{ $primary_event['date'] ?? 'Undangan Pernikahan' }}</p>
        <div class="ml-cover__recipient"><span>Kepada Yth.</span><strong>{{ $recipient }}</strong></div>
        <div class="ml-cover__footer"><span>Undangan Pernikahan</span><button type="button" data-open-invitation>Buka Undangan <i aria-hidden="true">↗</i></button></div>
    </div>

    <header class="ml-rail" aria-label="Navigasi undangan">
        <a href="#top" class="ml-mark" aria-label="Ke awal">ML</a>
        <nav>
            @foreach ($navigation as $item)<a href="#{{ $item['id'] }}">{{ sprintf('%02d', $loop->iteration) }} <span>{{ $item['label'] }}</span></a>@endforeach
            @if ($showLivestreamNav)<a href="{{ $livestream_url }}" target="_blank" rel="noopener noreferrer" class="ml-nav-action">Live <span>{{ $livestream_label }}</span></a>@endif
            @if ($showSharingNav)<button type="button" class="ml-nav-action" data-share data-share-url="{{ $share_url }}">Bagikan <span>Share</span></button>@endif
        </nav>
        <span class="ml-progress" aria-hidden="true"><i data-scroll-progress></i></span>
    </header>

    <nav class="ml-mobile-nav" aria-label="Navigasi cepat">
        @foreach ($navigation as $item)<a href="#{{ $item['id'] }}" aria-label="{{ $item['label'] }}"><span aria-hidden="true">{{ $item['icon'] }}</span>{{ $item['label'] }}</a>@endforeach
    </nav>
    @if ($showLivestreamNav || $showSharingNav)
        <div class="ml-quick-actions" aria-label="Aksi undangan">
            <button type="button" class="ml-quick-actions__toggle" data-quick-actions-toggle aria-expanded="false" aria-controls="ml-quick-actions-panel" aria-label="Buka aksi undangan">◇</button>
            <div class="ml-quick-actions__panel" id="ml-quick-actions-panel" hidden>
                @if ($showLivestreamNav)<a href="{{ $livestream_url }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $livestream_label }}">▷</a>@endif
                @if ($showSharingNav)<button type="button" data-share data-share-url="{{ $share_url }}" aria-label="Bagikan undangan">↗</button>@endif
            </div>
        </div>
    @endif

    <main id="top" tabindex="-1" inert>
        @foreach ($sections as $section)
            @if ($section === 'opening')
                <section class="ml-hero">
                    <div class="ml-index">{{ $primary_event['date'] ?? 'Undangan Pernikahan' }}</div>
                    <div class="ml-hero__title"><p>Dengan penuh kebahagiaan, kami mengundang Anda</p><h1 class="ml-couple-title">@if ($groomName && $brideName)<span>{{ $groomName }}</span><em>&amp;</em><span>{{ $brideName }}</span>@else{{ $title }}@endif</h1></div>
                    <div class="ml-hero__note">@if ($opening_text)<p>{{ $opening_text }}</p>@endif<span aria-hidden="true">↓</span></div>
                </section>
            @elseif ($section === 'events' && count($events))
                <section class="ml-section ml-agenda" id="agenda" aria-labelledby="agenda-title">
                    <header><span>{{ sprintf('%02d', $sectionNumbers['events'] + 1) }} · Acara</span><h2 id="agenda-title">Hari yang dinantikan</h2></header>
                    <div class="ml-event-list">
                        @foreach ($events as $event)
                            <article data-reveal>
                                <div class="ml-event__number">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</div>
                                <div><h3>{{ $event['label'] }}</h3><p class="ml-large">{{ $event['date'] }}</p>@if ($event['start_time'])<p>{{ $event['start_time'] }}{{ $event['end_time'] ? ' – '.$event['end_time'] : '' }} · {{ $event['timezone'] }}</p>@endif</div>
                                <div>@if ($event['venue'])<strong>{{ $event['venue'] }}</strong>@endif @if ($event['address'])<p>{{ $event['address'] }}</p>@endif
                                    <div class="ml-actions">@if (in_array('map', $sections) && $event['directions_url'])<a href="{{ $event['directions_url'] }}" target="_blank" rel="noopener noreferrer" aria-label="Petunjuk arah">⌖</a>@endif @if (in_array('calendar', $sections) && $event['calendar_url'])<a href="{{ $event['calendar_url'] }}" target="_blank" rel="noopener noreferrer" aria-label="Simpan ke kalender">□</a>@endif @if (in_array('map', $sections) && $event['address'])<button type="button" data-copy="{{ $event['address'] }}" aria-label="Salin alamat">∥</button>@endif</div>
                                    @foreach ($event['notes'] as $note)<small>{{ $note }}</small>@endforeach
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @elseif ($section === 'countdown' && $primary_event && $primary_event['timestamp'])
                <section class="ml-countdown" data-countdown="{{ $primary_event['timestamp'] }}">
                    <div class="ml-countdown__heading"><span>Menuju hari bahagia</span><h2>Waktu menuju perayaan.</h2></div>
                    <div class="ml-countdown__grid" data-countdown-output role="timer" aria-live="off">
                        @foreach (['days' => 'Hari', 'hours' => 'Jam', 'minutes' => 'Mnt', 'seconds' => 'Dtk'] as $unit => $label)
                            <div><b data-countdown-unit="{{ $unit }}">00</b><span>{{ $label }}</span></div>
                        @endforeach
                    </div>
                </section>
            @elseif ($section === 'hosts' && count($hosts))
                <section class="ml-section ml-people" id="people" aria-labelledby="people-title"><header><span>{{ sprintf('%02d', $sectionNumbers['hosts'] + 1) }} · Mempelai</span><h2 id="people-title">Dua hati, satu tujuan</h2></header><div class="ml-hosts">
                    @foreach ($hosts as $host)<article>@if ($host['photo_url'])<img src="{{ $host['photo_url'] }}" alt="Foto {{ $host['name'] }}" loading="lazy" decoding="async">@else<div class="ml-photo-fallback" aria-hidden="true">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</div>@endif<div class="ml-host__copy"><small>{{ match ($host['role']) { 'groom' => 'Mempelai Pria', 'bride' => 'Mempelai Wanita', default => 'Mempelai' } }}</small><h3>{{ $host['name'] }}</h3>@if ($host['birth_order'])<p>{{ $host['birth_order'] }}</p>@endif @if ($host['family'])<p>{{ $host['family'] }}</p>@endif @if ($host['bio'])<p>{{ $host['bio'] }}</p>@endif @if ($host['instagram'])<a href="{{ $host['instagram'] }}" target="_blank" rel="noopener noreferrer" aria-label="Instagram {{ $host['name'] }}">↗</a>@endif</div></article>@endforeach
                </div></section>
            @elseif ($section === 'story' && count($stories))
                <section class="ml-section ml-story" aria-labelledby="story-title"><header><span>Perjalanan</span><h2 id="story-title">Catatan kisah kami</h2></header><ol>@foreach ($stories as $story)<li>@if ($story['image_url'])<img src="{{ $story['image_url'] }}" alt="{{ $story['title'] }}" loading="lazy" decoding="async">@endif<div><small>{{ $story['date'] }}</small><h3>{{ $story['title'] }}</h3>@if ($story['body'])<p>{{ $story['body'] }}</p>@endif</div></li>@endforeach</ol></section>
            @elseif ($section === 'gallery' && count($gallery))
                <section class="ml-frames" id="frames" aria-labelledby="frames-title"><header><span>{{ sprintf('%02d', $sectionNumbers['gallery'] + 1) }} · Galeri</span><h2 id="frames-title">Momen yang tersimpan</h2></header><div class="ml-gallery">@foreach (array_chunk($gallery, 3) as $page)<div class="ml-gallery__page">@foreach ($page as $image)<figure><button type="button" data-lightbox-src="{{ $image['url'] }}" data-lightbox-alt="{{ $image['alt'] }}"><img src="{{ $image['url'] }}" alt="{{ $image['alt'] }}" loading="lazy" decoding="async"></button>@if ($image['caption'])<figcaption>{{ str_pad($loop->parent->iteration * 3 - 3 + $loop->iteration, 2, '0', STR_PAD_LEFT) }} · {{ $image['caption'] }}</figcaption>@endif</figure>@endforeach</div>@endforeach</div></section>
            @elseif ($section === 'map' && $primary_event && $primary_event['map_embed_url'])
                <section class="ml-section ml-location" aria-labelledby="location-title"><header><span>Lokasi</span><h2 id="location-title">Sampai jumpa di sana</h2></header><div class="ml-map"><iframe src="{{ $primary_event['map_embed_url'] }}" title="Peta {{ $primary_event['venue'] ?: $primary_event['label'] }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe></div>@if ($primary_event['address'])<p>{{ $primary_event['address'] }}</p>@endif<div class="ml-actions">@if ($primary_event['directions_url'])<a href="{{ $primary_event['directions_url'] }}" target="_blank" rel="noopener noreferrer" aria-label="Buka Google Maps">⌖</a>@endif @if ($primary_event['address'])<button type="button" data-copy="{{ $primary_event['address'] }}" aria-label="Salin alamat">∥</button>@endif</div></section>
            @elseif ($section === 'rsvp')
                <div class="ml-shared" id="response">@include('invitations.shared.rsvp')</div>
            @elseif ($section === 'guestbook')
                <div class="ml-shared">@include('invitations.shared.guestbook')</div>
            @elseif ($section === 'gifts' && count($gifts))
                <section class="ml-section ml-gifts" aria-labelledby="gifts-title"><header><span>Tanda Kasih</span><h2 id="gifts-title">Hadiah & Ucapan</h2></header><p>Kehadiran dan doa Anda adalah hadiah terindah.</p>@foreach ($gifts as $gift)<details><summary>{{ $gift['type_label'] }} · {{ $gift['provider'] }}</summary><div>@if ($gift['account_number'])<strong>{{ $gift['account_number'] }}</strong><button type="button" data-copy="{{ $gift['account_number'] }}" aria-label="Salin nomor">∥</button>@endif @if ($gift['account_name'])<p>a.n. {{ $gift['account_name'] }}</p>@endif @if ($gift['delivery_address'])<p>{{ $gift['delivery_address'] }}</p><button type="button" data-copy="{{ $gift['delivery_address'] }}" aria-label="Salin alamat">∥</button>@endif @if ($gift['notes'])<p>{{ $gift['notes'] }}</p>@endif</div></details>@endforeach</section>
            @elseif ($section === 'livestream' && $livestream_url)
            @elseif ($section === 'contacts' && count($contacts))
                <section class="ml-section ml-contacts"><header><span>Kontak</span><h2>Ada pertanyaan?</h2></header><div class="ml-actions">@foreach ($contacts as $contact)<a href="{{ $contact['whatsapp_url'] }}" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp {{ $contact['name'] }}">◌ {{ $contact['name'] }}</a><a href="{{ $contact['phone_url'] }}" aria-label="Telepon {{ $contact['name'] }}">☎</a>@endforeach</div></section>
            @elseif ($section === 'sharing')
            @elseif ($section === 'closing')
                <section class="ml-closing">
                    <span>{{ date('Y') }}</span>
                    <div><p class="ml-closing__script">Dengan cinta,</p><h2 class="ml-couple-title">@if ($groomName && $brideName)<span>{{ $groomName }}</span><em>&amp;</em><span>{{ $brideName }}</span>@else{{ $title }}@endif</h2></div>
                    <div class="ml-closing__message">@if ($closing_message)<p>{{ $closing_message }}</p>@endif<p class="ml-kicker">Terima kasih telah menjadi bagian dari cerita kami.</p></div>
                    <a href="#top" class="ml-back-to-top" aria-label="Kembali ke atas">↑</a>
                </section>
            @endif
        @endforeach
    </main>

    @if ($music_url)<audio data-music loop preload="none" src="{{ $music_url }}"></audio><button class="ml-music" type="button" data-music-toggle aria-label="Putar musik"><span aria-hidden="true">♪</span></button>@endif
    <dialog class="ml-lightbox" data-lightbox><button type="button" data-lightbox-close aria-label="Tutup galeri">✕</button><img data-lightbox-image alt=""></dialog>
</body>
</html>

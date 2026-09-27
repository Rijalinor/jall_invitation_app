<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $title }}">
    <title>{{ $title }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">
    @vite(['resources/invitation-templates/luminous-atelier/assets/theme.css', 'resources/invitation-templates/luminous-atelier/assets/theme.js'])
    <noscript><style>[data-reveal] { opacity: 1 !important; transform: none !important; }</style></noscript>
    {{-- Marks that scripting is available before the first paint, so the cover can
         be promoted to a full-screen overlay without a flash of it in normal flow. --}}
    <script>document.documentElement.classList.add('js-ready');</script>
</head>
<body class="luminous-atelier" style="--la-accent: {{ $theme['accent_color'] }}; --la-focal-x: {{ $theme['cover_focal_x'] }}%; --la-focal-y: {{ $theme['cover_focal_y'] }}%; --la-overlay: {{ $theme['cover_overlay_opacity'] / 100 }};" data-motion="{{ $theme['motion'] }}">
    @php
        $coverImage = $theme['cover_poster_image'] ?: ($gallery[0]['url'] ?? ($hosts[0]['photo_url'] ?? null));
        $coverDesktop = $theme['cover_video_enabled'] ? $theme['cover_video_desktop'] : null;
        $coverMobile = $theme['cover_video_enabled'] ? ($theme['cover_video_mobile'] ?: $coverDesktop) : null;
        $groom = collect($hosts)->firstWhere('role', 'groom')['name'] ?? ($hosts[0]['name'] ?? null);
        $bride = collect($hosts)->firstWhere('role', 'bride')['name'] ?? ($hosts[1]['name'] ?? null);
        $couple = $groom && $bride ? [$groom, $bride] : [$title];
        $nav = ['hosts' => 'Mempelai', 'story' => 'Cerita', 'events' => 'Acara', 'gallery' => 'Galeri', 'rsvp' => 'RSVP'];
    @endphp

    <div class="la-cover" data-cover>
        @if ($coverImage)<img class="la-cover__media" src="{{ $coverImage }}" alt="" aria-hidden="true" fetchpriority="high">@endif
        @if ($coverDesktop)<video class="la-cover__video la-cover__video--desktop" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video><source src="{{ $coverDesktop }}"></video>@endif
        @if ($coverMobile)<video class="la-cover__video la-cover__video--mobile" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video><source src="{{ $coverMobile }}"></video>@endif
        <div class="la-cover__shade"></div><div class="la-orbit la-orbit--cover" aria-hidden="true"></div>
        <div class="la-cover__content">
            <p>Undangan pernikahan</p><h1>@foreach ($couple as $name)<span>{{ $name }}</span>@if (!$loop->last)<b aria-hidden="true">&amp;</b>@endif @endforeach</h1>
            @if ($primary_event)<time datetime="{{ $primary_event['timestamp'] }}">{{ $primary_event['date'] }}</time>@endif
            <p class="la-cover__to">Untuk {{ $recipient }}</p>
            <a class="la-open" href="#top" data-open-invitation>Buka undangan <span aria-hidden="true">+</span></a>
        </div>
    </div>

    <header class="la-nav" aria-label="Navigasi undangan" data-gate><a href="#top" class="la-nav__monogram" aria-label="Kembali ke awal">LA</a><div>@foreach ($nav as $key => $label) @if (in_array($key, $sections, true))<a href="#{{ $key }}">{{ $label }}</a>@endif @endforeach</div></header>
    <main id="top" tabindex="-1" data-gate>
        @foreach ($sections as $section)
            @if ($section === 'opening')
                <section class="la-opening la-stage"><div class="la-opening__line" aria-hidden="true"></div><p data-reveal>Dengan segala sukacita, kami mengundang Anda untuk menjadi saksi sebuah hari yang kami nantikan.</p><div data-reveal><span>Hari yang kami pilih</span><strong>{{ $title }}</strong></div></section>
            @elseif ($section === 'hosts' && count($hosts))
                <section class="la-couple la-stage" id="hosts" aria-labelledby="couple-title"><header data-reveal><p>Mereka yang berbahagia</p><h2 id="couple-title">Two lives,<br>one horizon.</h2></header><div class="la-couple__grid">
                    @foreach ($hosts as $host)<article data-reveal><div class="la-portrait">@if ($host['photo_url'])<img src="{{ $host['photo_url'] }}" alt="Foto {{ $host['name'] }}" loading="lazy" decoding="async">@else<span>{{ $loop->iteration }}</span>@endif</div><p>{{ match ($host['role']) { 'groom' => 'Mempelai pria', 'bride' => 'Mempelai wanita', default => 'Tuan rumah' } }}</p><h3>{{ $host['name'] }}</h3>@if ($host['family'])<small>{{ $host['family'] }}</small>@endif @if ($host['bio'])<span class="la-bio">{{ $host['bio'] }}</span>@endif @if ($host['instagram'])<a href="{{ $host['instagram'] }}" target="_blank" rel="noopener noreferrer">Instagram</a>@endif</article>@endforeach
                </div></section>
            @elseif ($section === 'story' && count($stories))
                <section class="la-story la-stage" id="story" aria-labelledby="story-title"><header data-reveal><p>Kisah kami</p><h2 id="story-title">A sequence<br>of small miracles.</h2></header><ol>@foreach ($stories as $story)<li data-reveal><span>{{ $story['date'] }}</span><div>@if ($story['image_url'])<img src="{{ $story['image_url'] }}" alt="{{ $story['title'] }}" loading="lazy" decoding="async">@endif<h3>{{ $story['title'] }}</h3>@if ($story['body'])<p>{{ $story['body'] }}</p>@endif</div></li>@endforeach</ol></section>
            @elseif ($section === 'events' && count($events))
                <section class="la-events la-stage" id="events" aria-labelledby="events-title"><header data-reveal><p>Save the moment</p><h2 id="events-title">Meet us<br>in the light.</h2></header><div class="la-events__list">@foreach ($events as $event)<article data-reveal><div><span>{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><h3>{{ $event['label'] }}</h3></div><dl><div><dt>Tanggal</dt><dd>{{ $event['date'] }}</dd></div><div><dt>Waktu</dt><dd>{{ $event['start_time'] }}@if ($event['end_time']) - {{ $event['end_time'] }}@endif {{ $event['timezone'] }}</dd></div><div><dt>Tempat</dt><dd>{{ $event['venue'] }}@if ($event['address'])<br>{{ $event['address'] }}@endif</dd></div></dl><div class="la-actions">@if ($event['directions_url'])<a href="{{ $event['directions_url'] }}" target="_blank" rel="noopener noreferrer">Petunjuk arah</a>@endif @if ($event['calendar_url'])<a href="{{ $event['calendar_url'] }}" target="_blank" rel="noopener noreferrer">Google Calendar</a>@endif @if ($event['ics_url'])<a href="{{ $event['ics_url'] }}">Unduh ICS</a>@endif</div></article>@endforeach</div></section>
            @elseif ($section === 'countdown' && $primary_event && $primary_event['timestamp'])
                <section class="la-countdown la-stage" data-countdown="{{ $primary_event['timestamp'] }}" aria-label="Hitung mundur menuju hari bahagia"><div class="la-orbit" aria-hidden="true"></div><p data-reveal>Menuju hari yang kami pilih</p><div class="la-countdown__numbers" data-countdown-output data-reveal>@foreach (['days' => 'Hari', 'hours' => 'Jam', 'minutes' => 'Menit', 'seconds' => 'Detik'] as $unit => $label)<div><strong data-countdown-unit="{{ $unit }}">00</strong><span>{{ $label }}</span></div>@endforeach</div></section>
            @elseif ($section === 'gallery' && count($gallery))
                <section class="la-gallery la-stage" id="gallery" aria-labelledby="gallery-title"><header data-reveal><p>Frame by frame</p><h2 id="gallery-title">A few things<br>we will remember.</h2></header><div class="la-gallery__strip">@foreach ($gallery as $image)<figure data-reveal><button type="button" data-lightbox-src="{{ $image['url'] }}" data-lightbox-alt="{{ $image['alt'] }}"><img src="{{ $image['url'] }}" alt="{{ $image['alt'] }}" loading="lazy" decoding="async"></button>@if ($image['caption'])<figcaption>{{ $image['caption'] }}</figcaption>@endif</figure>@endforeach</div></section>
            @elseif ($section === 'map' && $primary_event && $primary_event['map_embed_url'])
                <section class="la-location la-stage" aria-labelledby="location-title"><header data-reveal><p>Lokasi</p><h2 id="location-title">The way<br>to us.</h2></header><div class="la-map" data-reveal><iframe src="{{ $primary_event['map_embed_url'] }}" title="Peta {{ $primary_event['venue'] ?: $primary_event['label'] }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe></div><div class="la-actions" data-reveal>@if ($primary_event['directions_url'])<a href="{{ $primary_event['directions_url'] }}" target="_blank" rel="noopener noreferrer">Buka Google Maps</a>@endif @if ($primary_event['address'])<button type="button" data-copy="{{ $primary_event['address'] }}">Salin alamat</button>@endif</div></section>
            @elseif ($section === 'rsvp')<div class="la-shared" id="rsvp">@include('invitations.shared.rsvp')</div>
            @elseif ($section === 'guestbook')<div class="la-shared">@include('invitations.shared.guestbook')</div>
            @elseif ($section === 'gifts' && count($gifts))
                <section class="la-gifts la-stage" aria-labelledby="gifts-title"><header data-reveal><p>Tanda kasih</p><h2 id="gifts-title">With thanks.</h2></header><div>@foreach ($gifts as $gift)<article data-reveal><h3>{{ $gift['type_label'] }} / {{ $gift['provider'] }}</h3>@if ($gift['account_number'])<strong>{{ $gift['account_number'] }}</strong><button type="button" data-copy="{{ $gift['account_number'] }}">Salin nomor</button>@endif @if ($gift['account_name'])<p>a.n. {{ $gift['account_name'] }}</p>@endif @if ($gift['delivery_address'])<p>{{ $gift['delivery_address'] }}</p><button type="button" data-copy="{{ $gift['delivery_address'] }}">Salin alamat</button>@endif</article>@endforeach</div></section>
            @elseif ($section === 'livestream' && $livestream_url)
                <section class="la-live la-stage"><p data-reveal>Hadir dari jauh</p><h2 data-reveal>Join the<br>celebration.</h2><a data-reveal href="{{ $livestream_url }}" target="_blank" rel="noopener noreferrer">{{ $livestream_label }}</a></section>
            @elseif ($section === 'contacts' && count($contacts))
                <section class="la-contacts la-stage"><header data-reveal><p>Butuh bantuan?</p><h2>We are close.</h2></header><div>@foreach ($contacts as $contact)<a data-reveal href="{{ $contact['whatsapp_url'] }}" target="_blank" rel="noopener noreferrer"><span>{{ $contact['label'] }}</span><strong>{{ $contact['name'] }}</strong></a>@endforeach</div></section>
            @elseif ($section === 'sharing')
                <section class="la-share la-stage"><p data-reveal>Sebarkan kabar baik ini</p><h2 data-reveal>Pass the<br>invitation on.</h2><div data-reveal><button type="button" data-share data-share-url="{{ $share_url }}"><span data-share-label>Bagikan undangan</span></button><a href="{{ $whatsapp_url }}" target="_blank" rel="noopener noreferrer">Kirim via WhatsApp</a></div></section>
            @elseif ($section === 'closing')
                <section class="la-closing la-stage">@if ($coverImage)<img src="{{ $coverImage }}" alt="" aria-hidden="true" loading="lazy">@endif<div></div><p data-reveal>Terima kasih</p><h2 data-reveal>@foreach ($couple as $name)<span>{{ $name }}</span>@endforeach</h2>@if ($closing_message)<p data-reveal>{{ $closing_message }}</p>@endif</section>
            @endif
        @endforeach
    </main>
    @if ($music_url)<audio data-music loop preload="none" src="{{ $music_url }}"></audio><button class="la-music" type="button" data-music-toggle aria-pressed="false" aria-label="Putar musik"><span aria-hidden="true">Music</span></button>@endif
    <dialog class="la-lightbox" data-lightbox><button type="button" data-lightbox-close aria-label="Tutup galeri">Close</button><img data-lightbox-image alt=""></dialog>
</body>
</html>

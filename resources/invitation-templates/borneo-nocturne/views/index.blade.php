<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $title }}">
    <title>{{ $title }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&amp;family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,500;1,6..72,400&amp;display=swap" rel="stylesheet">
    @vite(['resources/invitation-templates/borneo-nocturne/assets/theme.css', 'resources/invitation-templates/borneo-nocturne/assets/theme.js', 'resources/invitation-templates/borneo-nocturne/assets/gsap.js'])
</head>
<body class="borneo-nocturne" style="--bn-accent: {{ $theme['accent_color'] }}" data-motion="{{ $theme['motion'] }}">
    @php
        $navItems = [
            'hosts' => ['id' => 'couple', 'label' => 'Mempelai'],
            'story' => ['id' => 'journey', 'label' => 'Cerita'],
            'events' => ['id' => 'celebration', 'label' => 'Acara'],
            'gallery' => ['id' => 'memories', 'label' => 'Galeri'],
            'map' => ['id' => 'location', 'label' => 'Lokasi'],
            'rsvp' => ['id' => 'attendance', 'label' => 'RSVP'],
        ];
        $visibleNav = collect($sections)->filter(fn ($section) => isset($navItems[$section]))->mapWithKeys(fn ($section) => [$section => $navItems[$section]])->all();
    @endphp

    <div class="bn-cover" data-cover>
        <div class="bn-cover__gate" aria-hidden="true"><i></i><i></i></div>
        <div class="bn-cover__scene" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i></div>
        <div class="bn-cover__veil" aria-hidden="true"></div>
        <header><span>Borneo Royal Nocturne</span><span class="bn-cover__date" data-split>{{ $primary_event['date'] ?? 'Save the date' }}</span></header>
        <div class="bn-cover__content">
            <p class="bn-cover__intro" data-split>Undangan pernikahan untuk</p>
            <h1 data-split>{{ $recipient }}</h1>
            <span class="bn-cover__line" aria-hidden="true"></span>
            <p class="bn-cover__note" data-split>Sebuah perayaan agung untuk hari yang kami nantikan.</p>
        </div>
        <button type="button" data-open-invitation><span>Buka undangan</span><i aria-hidden="true">↓</i></button>
    </div>

    <nav class="bn-compass" aria-label="Navigasi undangan">
        <button type="button" data-nav-toggle aria-expanded="false" aria-controls="bn-compass-menu"><i aria-hidden="true"></i><span>Jelajah</span></button>
        <svg class="bn-compass__ring" viewBox="0 0 120 120" aria-hidden="true"><circle cx="60" cy="60" r="56" pathLength="100"></circle><circle class="bn-compass__ring-fill" cx="60" cy="60" r="56" pathLength="100"></circle></svg>
        <div id="bn-compass-menu">
            <a href="#top"><b>00</b><span>Awal</span></a>
            @foreach ($visibleNav as $item)<a href="#{{ $item['id'] }}"><b>{{ sprintf('%02d', $loop->iteration) }}</b><span>{{ $item['label'] }}</span></a>@endforeach
        </div>
    </nav>

    <div class="bn-grain" aria-hidden="true"></div>
    <div class="bn-global-fireflies" aria-hidden="true">
        <i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i>
    </div>

    <main id="top" tabindex="-1" inert>
        @foreach ($sections as $section)
            @if ($section === 'opening')
                <section class="bn-hero" data-hero>
                    <div class="bn-hero__mist" aria-hidden="true"></div>
                    <div class="bn-hero__scene" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i><i></i></div>
                    <div class="bn-hero__bank"><p class="bn-eyebrow">A royal evening of love</p><span>01 · Royal</span></div>
                    <div class="bn-hero__title"><span class="bn-hero__splash" data-split>Perayaan</span><h2 data-split>{{ $title }}</h2><span class="bn-hero__lead" data-split>yang kami muliakan</span></div>
                    <div class="bn-hero__bank">@if ($opening_text)<p class="bn-hero__copy bn-observe">{{ $opening_text }}</p>@endif<div class="bn-scroll-cue"><i></i><span>Lanjutkan undangan</span></div></div>
                </section>
            @elseif ($section === 'hosts' && count($hosts))
                <section class="bn-section bn-couple" id="couple">
                    <header class="bn-heading bn-observe"><p class="bn-eyebrow">Dua jiwa · Satu tujuan</p><h2 data-split>Berjumpa, lalu bertumbuh bersama.</h2></header>
                    <div class="bn-couple__grid">
                        @foreach ($hosts as $host)
                            <article class="bn-observe">
                                <div class="bn-portrait">@if ($host['photo_url'])<img src="{{ $host['photo_url'] }}" alt="Foto {{ $host['name'] }}" loading="lazy" decoding="async">@else<span aria-hidden="true">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>@endif</div>
                                <div><small>{{ match ($host['role']) { 'groom' => 'Mempelai Pria', 'bride' => 'Mempelai Wanita', default => $host['role'] ?: 'Mempelai' } }}</small><h3 data-split>{{ $host['name'] }}</h3>@if ($host['birth_order'])<p>{{ $host['birth_order'] }}</p>@endif @if ($host['family'])<p>Putra/putri dari {{ $host['family'] }}</p>@endif @if ($host['bio'])<p>{{ $host['bio'] }}</p>@endif @if ($host['instagram'])<a href="{{ $host['instagram'] }}" target="_blank" rel="noopener noreferrer">Instagram ↗</a>@endif</div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @elseif ($section === 'story' && count($stories))
                <section class="bn-section bn-story" id="journey">
                    <header class="bn-heading bn-observe"><p class="bn-eyebrow">Jejak waktu</p><h2 data-split>Kisah yang mengantar kami ke hari ini.</h2></header>
                    <svg class="bn-river-path" viewBox="0 0 400 1000" preserveAspectRatio="none" aria-hidden="true"><path class="bn-river-path__fade" d="M206 0C90 135 326 235 190 360S88 605 218 702s78 198-28 298"/><path class="bn-river-path__draw" d="M206 0C90 135 326 235 190 360S88 605 218 702s78 198-28 298"/></svg>
                    <span class="bn-boat" aria-hidden="true"><i></i></span>
                    <ol>
                        @foreach ($stories as $story)<li class="bn-observe"><span>{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>@if ($story['image_url'])<img src="{{ $story['image_url'] }}" alt="{{ $story['title'] }}" loading="lazy" decoding="async">@endif<div><small>{{ $story['date'] }}</small><h3 data-split>{{ $story['title'] }}</h3>@if ($story['body'])<p>{{ $story['body'] }}</p>@endif</div></li>@endforeach
                    </ol>
                </section>
            @elseif ($section === 'events' && count($events))
                <section class="bn-section bn-events" id="celebration">
                    <header class="bn-heading bn-observe"><p class="bn-eyebrow">Hari perayaan</p><h2 data-split>Temui kami dalam malam yang agung.</h2></header>
                    <div class="bn-events__grid" data-docks>
                        @foreach ($events as $event)<article class="bn-observe"><span>{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><div><small>{{ $event['label'] }}</small><h3 data-split>{{ $event['date'] }}</h3>@if ($event['start_time'])<p>{{ $event['start_time'] }}{{ $event['end_time'] ? ' – '.$event['end_time'] : '' }} · {{ $event['timezone'] }}</p>@endif</div><div>@if ($event['venue'])<strong>{{ $event['venue'] }}</strong>@endif @if ($event['address'])<p>{{ $event['address'] }}</p>@endif @foreach ($event['notes'] as $note)<small>{{ $note }}</small>@endforeach<div class="bn-actions">@if ($event['directions_url'])<a href="{{ $event['directions_url'] }}" target="_blank" rel="noopener noreferrer">Petunjuk arah</a>@endif @if ($event['calendar_url'])<a href="{{ $event['calendar_url'] }}" target="_blank" rel="noopener noreferrer">Tambah kalender</a>@endif @if ($event['ics_url'])<a href="{{ $event['ics_url'] }}">Unduh ICS</a>@endif @if ($event['address'])<button type="button" data-copy="{{ $event['address'] }}">Salin alamat</button>@endif</div></div></article>@endforeach
                    </div>
                </section>
            @elseif ($section === 'countdown' && $primary_event && $primary_event['timestamp'])
                <section class="bn-countdown" data-countdown="{{ $primary_event['timestamp'] }}">
                    <p class="bn-eyebrow">Menuju hari bahagia</p>
                    <div data-countdown-output role="timer" aria-live="off">@foreach (['days' => 'Hari', 'hours' => 'Jam', 'minutes' => 'Menit', 'seconds' => 'Detik'] as $unit => $label)<span><b data-countdown-unit="{{ $unit }}">00</b><small>{{ $label }}</small></span>@endforeach</div>
                </section>
            @elseif ($section === 'gallery' && count($gallery))
                <section class="bn-gallery" id="memories">
                    <header class="bn-heading bn-observe"><p class="bn-eyebrow">Fragmen kenangan</p><h2 data-split>Cahaya yang sempat kami simpan.</h2></header>
                    <div class="bn-gallery__mosaic">@foreach ($gallery as $image)<figure class="bn-observe"><button type="button" data-lightbox-src="{{ $image['url'] }}" data-lightbox-alt="{{ $image['alt'] }}"><img src="{{ $image['url'] }}" alt="{{ $image['alt'] }}" loading="lazy" decoding="async"></button><figcaption>{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}@if ($image['caption']) · {{ $image['caption'] }}@endif</figcaption></figure>@endforeach</div>
                </section>
            @elseif ($section === 'map' && $primary_event && $primary_event['map_embed_url'])
                <section class="bn-section bn-location" id="location"><header class="bn-heading bn-observe"><p class="bn-eyebrow">Titik pertemuan</p><h2 data-split>Di sinilah malam bahagia digelar.</h2></header><div class="bn-location__map bn-observe"><iframe src="{{ $primary_event['map_embed_url'] }}" title="Peta {{ $primary_event['venue'] ?: $primary_event['label'] }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe></div>@if ($primary_event['address'])<p>{{ $primary_event['address'] }}</p>@endif<div class="bn-actions">@if ($primary_event['directions_url'])<a href="{{ $primary_event['directions_url'] }}" target="_blank" rel="noopener noreferrer">Buka Google Maps</a>@endif @if ($primary_event['address'])<button type="button" data-copy="{{ $primary_event['address'] }}">Salin alamat</button>@endif</div></section>
            @elseif ($section === 'rsvp')
                <div class="bn-shared" id="attendance">@include('invitations.shared.rsvp')</div>
            @elseif ($section === 'guestbook')
                <div class="bn-shared" id="wishes">@include('invitations.shared.guestbook')</div>
            @elseif ($section === 'gifts' && count($gifts))
                <section class="bn-section bn-gifts"><header class="bn-heading bn-observe"><p class="bn-eyebrow">Tanda kasih</p><h2 data-split>Doa Anda adalah hadiah terindah.</h2></header>@foreach ($gifts as $gift)<details class="bn-observe"><summary><span>{{ $gift['type_label'] }}</span>{{ $gift['provider'] }}</summary><div>@if ($gift['account_number'])<strong>{{ $gift['account_number'] }}</strong><button type="button" data-copy="{{ $gift['account_number'] }}">Salin nomor</button>@endif @if ($gift['account_name'])<p>Atas nama {{ $gift['account_name'] }}</p>@endif @if ($gift['delivery_address'])<p>{{ $gift['delivery_address'] }}</p><button type="button" data-copy="{{ $gift['delivery_address'] }}">Salin alamat hadiah</button>@endif @if ($gift['notes'])<p>{{ $gift['notes'] }}</p>@endif</div></details>@endforeach</section>
            @elseif ($section === 'livestream' && $livestream_url)
                <section class="bn-ribbon"><span>Saksikan dari kejauhan</span><a href="{{ $livestream_url }}" target="_blank" rel="noopener noreferrer">{{ $livestream_label }} ↗</a></section>
            @elseif ($section === 'contacts' && count($contacts))
                <section class="bn-section bn-contacts"><header class="bn-heading"><p class="bn-eyebrow">Hubungi kami</p><h2 data-split>Ada yang ingin ditanyakan?</h2></header><div class="bn-actions">@foreach ($contacts as $contact)<a href="{{ $contact['whatsapp_url'] }}" target="_blank" rel="noopener noreferrer">WhatsApp {{ $contact['label'] }} · {{ $contact['name'] }}</a><a href="{{ $contact['phone_url'] }}">Telepon</a>@endforeach</div></section>
            @elseif ($section === 'sharing')
                <section class="bn-ribbon"><span>Bagikan kabar bahagia</span><div class="bn-actions"><button type="button" data-share data-share-url="{{ $share_url }}">Bagikan undangan</button><a href="{{ $whatsapp_url }}" target="_blank" rel="noopener noreferrer">WhatsApp ↗</a></div></section>
            @elseif ($section === 'closing')
                <section class="bn-closing"><p class="bn-eyebrow">Sampai bertemu</p><div><span>With love,</span><h2 data-split>{{ $title }}</h2></div>@if ($closing_message)<p>{{ $closing_message }}</p>@endif<a href="#top">Kembali ke awal</a></section>
            @endif
        @endforeach
    </main>

    @if ($music_url)<audio data-music loop preload="none" src="{{ $music_url }}"></audio><button class="bn-music" type="button" data-music-toggle aria-label="Putar musik"><i aria-hidden="true"></i><span>Musik</span></button>@endif
    <dialog class="bn-lightbox" data-lightbox><button type="button" data-lightbox-close aria-label="Tutup galeri">Tutup ×</button><img data-lightbox-image alt=""></dialog>
</body>
</html>

<!doctype html>
<html lang="id" class="cbg-page">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $title }}">
    <title>{{ $title }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&amp;family=Inter:wght@400;500;600;700&amp;family=Newsreader:opsz,wght@6..72,400;6..72,600&amp;display=swap" rel="stylesheet">
    @vite(['resources/invitation-templates/cinematic-botanical-gold/assets/theme.css', 'resources/invitation-templates/cinematic-botanical-gold/assets/theme.js'])
</head>
<body class="cbg" style="--cbg-accent: {{ $theme['accent_color'] }}; --cbg-focal-x: {{ $theme['cover_focal_x'] }}%; --cbg-focal-y: {{ $theme['cover_focal_y'] }}%; --cbg-overlay: {{ $theme['cover_overlay_opacity'] / 100 }};" data-motion="{{ $theme['motion'] }}" data-font="{{ $theme['font_pairing'] }}" data-cover-position="{{ $theme['cover_text_position'] }}" data-ornament="{{ $theme['ornament_style'] }}">
    @php
        $coverImage = $theme['cover_poster_image'] ?: ($gallery[0]['url'] ?? ($hosts[0]['photo_url'] ?? null));
        $coverDesktop = $theme['cover_video_enabled'] ? $theme['cover_video_desktop'] : null;
        $coverMobile = $theme['cover_video_enabled'] ? ($theme['cover_video_mobile'] ?: $coverDesktop) : null;
        $displayNames = collect($hosts)->pluck('nickname')->filter()->whenEmpty(fn ($c) => collect($hosts)->pluck('name'))->take(2)->join(' & ') ?: $title;
        $navItems = [
            'hosts' => ['id' => 'couple', 'label' => 'Mempelai'],
            'events' => ['id' => 'events', 'label' => 'Acara'],
            'story' => ['id' => 'story', 'label' => 'Cerita'],
            'gallery' => ['id' => 'gallery', 'label' => 'Galeri'],
            'map' => ['id' => 'location', 'label' => 'Lokasi'],
            'rsvp' => ['id' => 'rsvp', 'label' => 'RSVP'],
        ];
        $visibleNav = collect($sections)->filter(fn ($section) => isset($navItems[$section]))->mapWithKeys(fn ($section) => [$section => $navItems[$section]])->all();
    @endphp

    <div class="cbg-cover" data-cover>
        @if ($coverImage)<img class="cbg-cover__poster" src="{{ $coverImage }}" alt="" aria-hidden="true">@endif
        @if ($coverDesktop)
            <video class="cbg-cover__video cbg-cover__video--desktop" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video>
                <source src="{{ $coverDesktop }}">
            </video>
        @endif
        @if ($coverMobile)
            <video class="cbg-cover__video cbg-cover__video--mobile" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video>
                <source src="{{ $coverMobile }}">
            </video>
        @endif
        <div class="cbg-cover__shade" aria-hidden="true"></div>
        <div class="cbg-cover__content">
            <p>The Wedding of</p>
            <h1>{{ $displayNames }}</h1>
            <time>{{ $primary_event['date'] ?? 'Save the date' }}</time>
            <span>Kepada Yth. {{ $recipient }}</span>
            <button type="button" data-open-invitation>Buka Undangan</button>
        </div>
    </div>

    <nav class="cbg-nav" aria-label="Navigasi undangan">
        <a href="#top">Awal</a>
        @foreach ($visibleNav as $item)<a href="#{{ $item['id'] }}">{{ $item['label'] }}</a>@endforeach
    </nav>

    <main id="top" tabindex="-1" inert>
        @foreach ($sections as $section)
            @if ($section === 'opening')
                <section class="cbg-section cbg-opening" aria-labelledby="opening-title">
                    <span class="cbg-kicker">A golden vow</span>
                    <h2 id="opening-title">Dalam hangat cahaya, kami mengundang Anda hadir.</h2>
                    @if ($opening_text)<p>{{ $opening_text }}</p>@endif
                </section>
            @elseif ($section === 'hosts' && count($hosts))
                <section class="cbg-section cbg-hosts" id="couple" aria-labelledby="hosts-title">
                    <header class="cbg-hosts__header">
                        <span class="cbg-kicker">Mempelai</span>
                        <h2 id="hosts-title">Dua hati, satu janji.</h2>
                    </header>
                    <div class="cbg-hosts__pair">
                        @foreach ($hosts as $host)
                            <article class="cbg-host">
                                <figure class="cbg-host__figure">
                                    @if ($host['photo_url'])
                                        <img src="{{ $host['photo_url'] }}" alt="Foto {{ $host['name'] }}" loading="lazy" decoding="async">
                                    @else
                                        <span>{{ $host['nickname'] ?: $host['name'] }}</span>
                                    @endif
                                </figure>
                                <div class="cbg-host__copy">
                                    <small class="cbg-host__role">{{ match ($host['role']) { 'groom' => 'Mempelai Pria', 'bride' => 'Mempelai Wanita', default => $host['role'] ?: 'Mempelai' } }}</small>
                                    <h3 class="cbg-host__name">{{ $host['name'] }}</h3>
                                    @if ($host['birth_order'])<p class="cbg-host__order">{{ $host['birth_order'] }}</p>@endif
                                    @if ($host['family'])<p class="cbg-host__family">Putra/putri dari {{ $host['family'] }}</p>@endif
                                    @if ($host['bio'])<p class="cbg-host__bio">{{ $host['bio'] }}</p>@endif
                                    @if ($host['instagram'])
                                        <a class="cbg-host__ig" href="{{ $host['instagram'] }}" target="_blank" rel="noopener noreferrer">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>
                                            Instagram
                                        </a>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @elseif ($section === 'events' && count($events))
                <section class="cbg-section cbg-events" id="events" aria-labelledby="events-title">
                    <header><span class="cbg-kicker">Rangkaian acara</span><h2 id="events-title">Hari yang kami nantikan.</h2></header>
                    <div class="cbg-events__grid">
                        @foreach ($events as $event)
                            <article>
                                <small>{{ $event['label'] }}</small>
                                <h3>{{ $event['date'] }}</h3>
                                @if ($event['start_time'])<p>{{ $event['start_time'] }}{{ $event['end_time'] ? ' - '.$event['end_time'] : '' }} {{ $event['timezone'] }}</p>@endif
                                @if ($event['venue'])<strong>{{ $event['venue'] }}</strong>@endif
                                @if ($event['address'])<p>{{ $event['address'] }}</p>@endif
                                @foreach ($event['notes'] as $note)<p class="cbg-muted">{{ $note }}</p>@endforeach
                                <div class="cbg-actions">@if ($event['directions_url'])<a href="{{ $event['directions_url'] }}" target="_blank" rel="noopener noreferrer">Google Maps</a>@endif @if ($event['calendar_url'])<a href="{{ $event['calendar_url'] }}" target="_blank" rel="noopener noreferrer">Tambah Kalender</a>@endif @if ($event['ics_url'])<a href="{{ $event['ics_url'] }}">ICS</a>@endif @if ($event['address'])<button type="button" data-copy="{{ $event['address'] }}">Salin Alamat</button>@endif</div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @elseif ($section === 'countdown' && $primary_event && $primary_event['timestamp'])
                <section class="cbg-countdown" data-countdown="{{ $primary_event['timestamp'] }}" aria-label="Hitung mundur">
                    <span class="cbg-kicker">Menuju perayaan</span>
                    <div data-countdown-output role="timer">@foreach (['days' => 'Hari', 'hours' => 'Jam', 'minutes' => 'Menit', 'seconds' => 'Detik'] as $unit => $label)<span><b data-countdown-unit="{{ $unit }}">00</b><small>{{ $label }}</small></span>@endforeach</div>
                </section>
            @elseif ($section === 'story' && count($stories))
                <section class="cbg-section cbg-story" id="story" aria-labelledby="story-title">
                    <header><span class="cbg-kicker">Love story</span><h2 id="story-title">Potongan waktu yang membawa kami ke hari ini.</h2></header>
                    <ol>@foreach ($stories as $story)<li><span>{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><div>@if ($story['image_url'])<img src="{{ $story['image_url'] }}" alt="{{ $story['title'] }}" loading="lazy" decoding="async">@endif<small>{{ $story['date'] }}</small><h3>{{ $story['title'] }}</h3>@if ($story['body'])<p>{{ $story['body'] }}</p>@endif</div></li>@endforeach</ol>
                </section>
            @elseif ($section === 'gallery' && count($gallery))
                <section class="cbg-section cbg-gallery" id="gallery" aria-labelledby="gallery-title">
                    <header><span class="cbg-kicker">Gallery</span><h2 id="gallery-title">Cahaya yang tersimpan.</h2></header>
                    <div>
                        @foreach ($gallery as $image)
                            @if ($loop->iteration % 4 === 1)<div class="cbg-gallery__page">@endif
                            <figure><button type="button" data-lightbox-src="{{ $image['url'] }}" data-lightbox-alt="{{ $image['alt'] }}"><img src="{{ $image['url'] }}" alt="{{ $image['alt'] }}" loading="lazy" decoding="async"></button>@if ($image['caption'])<figcaption>{{ $image['caption'] }}</figcaption>@endif</figure>
                            @if ($loop->iteration % 4 === 0 || $loop->last)</div>@endif
                        @endforeach
                    </div>
                </section>
            @elseif ($section === 'map' && $primary_event && ($primary_event['map_embed_url'] || $primary_event['address']))
                <section class="cbg-section cbg-location" id="location" aria-labelledby="location-title">
                    <div><span class="cbg-kicker">Lokasi</span><h2 id="location-title">{{ $primary_event['venue'] ?: 'Tempat Acara' }}</h2>@if ($primary_event['address'])<p>{{ $primary_event['address'] }}</p>@endif<div class="cbg-actions">@if ($primary_event['directions_url'])<a href="{{ $primary_event['directions_url'] }}" target="_blank" rel="noopener noreferrer">Petunjuk Arah</a>@endif @if ($primary_event['address'])<button type="button" data-copy="{{ $primary_event['address'] }}">Salin Alamat</button>@endif</div></div>
                    @if ($primary_event['map_embed_url'])<iframe src="{{ $primary_event['map_embed_url'] }}" title="Peta {{ $primary_event['venue'] ?: $primary_event['label'] }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>@endif
                </section>
            @elseif ($section === 'rsvp')
                <div class="cbg-shared" id="rsvp">@include('invitations.shared.rsvp')</div>
            @elseif ($section === 'guestbook')
                <div class="cbg-shared" id="guestbook">@include('invitations.shared.guestbook')</div>
            @elseif ($section === 'gifts' && count($gifts))
                <section class="cbg-section cbg-gifts" aria-labelledby="gifts-title">
                    <header><span class="cbg-kicker">Amplop digital</span><h2 id="gifts-title">Doa adalah hadiah paling berarti.</h2><p>Untuk tanda kasih, detail hadiah dapat dibuka dengan tenang di bawah ini.</p></header>
                    @foreach ($gifts as $gift)<details><summary>{{ $gift['type_label'] }} @if ($gift['provider'])<span>{{ $gift['provider'] }}</span>@endif</summary><div>@if ($gift['account_number'])<strong>{{ $gift['account_number'] }}</strong><button type="button" data-copy="{{ $gift['account_number'] }}">Salin Nomor</button>@endif @if ($gift['account_name'])<p>Atas nama {{ $gift['account_name'] }}</p>@endif @if ($gift['delivery_address'])<p>{{ $gift['delivery_address'] }}</p><button type="button" data-copy="{{ $gift['delivery_address'] }}">Salin Alamat</button>@endif @if ($gift['notes'])<p>{{ $gift['notes'] }}</p>@endif</div></details>@endforeach
                </section>
            @elseif ($section === 'livestream' && $livestream_url)
                <section class="cbg-ribbon"><span>Saksikan dari kejauhan</span><p>Untuk keluarga dan sahabat yang belum dapat hadir langsung.</p><a href="{{ $livestream_url }}" target="_blank" rel="noopener noreferrer">{{ $livestream_label }}</a></section>
            @elseif ($section === 'contacts' && count($contacts))
                <section class="cbg-section cbg-contacts" aria-labelledby="contacts-title">
                    <header><span class="cbg-kicker">Kontak</span><h2 id="contacts-title">Hubungi keluarga.</h2><p>Jika ada pertanyaan seputar acara, silakan hubungi kontak berikut.</p></header>
                    <div class="cbg-actions">@foreach ($contacts as $contact)<a href="{{ $contact['whatsapp_url'] }}" target="_blank" rel="noopener noreferrer">WhatsApp {{ $contact['label'] }} - {{ $contact['name'] }}</a><a href="{{ $contact['phone_url'] }}">Telepon</a>@endforeach</div>
                </section>
            @elseif ($section === 'sharing')
                <section class="cbg-ribbon"><span>Bagikan undangan</span><p>Kirimkan tautan ini kepada keluarga atau sahabat yang ingin ikut menerima kabar bahagia.</p><div class="cbg-actions"><button type="button" data-share data-share-url="{{ $share_url }}">Bagikan</button><a href="{{ $whatsapp_url }}" target="_blank" rel="noopener noreferrer">WhatsApp</a></div></section>
            @elseif ($section === 'closing')
                <section class="cbg-closing">
                    @if ($coverImage)<img src="{{ $coverImage }}" alt="" aria-hidden="true" loading="lazy">@endif
                    <div><span class="cbg-kicker">Terima kasih</span><h2>{{ $displayNames }}</h2>@if ($closing_message)<p>{{ $closing_message }}</p>@endif<a href="#top">Kembali ke awal</a></div>
                </section>
            @endif
        @endforeach
    </main>

    @if ($music_url && $theme['music_enabled'])<audio data-music loop preload="none" src="{{ $music_url }}"></audio><button class="cbg-music" type="button" data-music-toggle aria-label="Putar musik">Musik</button>@endif
    <dialog class="cbg-lightbox" data-lightbox><button type="button" data-lightbox-close aria-label="Tutup galeri">Tutup</button><img data-lightbox-image alt=""></dialog>
</body>
</html>

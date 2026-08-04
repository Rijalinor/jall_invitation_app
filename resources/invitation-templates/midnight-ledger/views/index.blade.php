<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $title }}">
    <title>{{ $title }}</title>
    @vite(['resources/invitation-templates/midnight-ledger/assets/theme.css', 'resources/invitation-templates/midnight-ledger/assets/theme.js'])
</head>
<body class="midnight-ledger" style="--ml-accent: {{ $theme['accent_color'] }}" data-motion="{{ $theme['motion'] }}">
    @php
        $availableNavigation = [
            'events' => count($events) ? ['id' => 'agenda', 'label' => 'Agenda'] : null,
            'hosts' => count($hosts) ? ['id' => 'people', 'label' => 'Kisah'] : null,
            'gallery' => count($gallery) ? ['id' => 'frames', 'label' => 'Galeri'] : null,
            'rsvp' => in_array('rsvp', $sections) ? ['id' => 'response', 'label' => 'RSVP'] : null,
        ];
        $navigation = collect($sections)->mapWithKeys(fn ($section) => isset($availableNavigation[$section]) ? [$section => $availableNavigation[$section]] : [])->all();
        $sectionNumbers = array_flip(array_keys($navigation));
    @endphp
    <div class="ml-cover" data-cover>
        <p class="ml-kicker">Private celebration · {{ $primary_event['date'] ?? 'Save the date' }}</p>
        <div class="ml-cover__recipient"><span>Undangan untuk</span><strong>{{ $recipient }}</strong><em>We saved you a seat</em></div>
        <div class="ml-cover__footer"><span>Wedding journal · Vol. 01</span><button type="button" data-open-invitation>Buka cerita <i aria-hidden="true">↗</i></button></div>
    </div>

    <header class="ml-rail" aria-label="Navigasi undangan">
        <a href="#top" class="ml-mark" aria-label="Ke awal">ML</a>
        <nav>
            @foreach ($navigation as $item)<a href="#{{ $item['id'] }}">{{ sprintf('%02d', $loop->iteration) }} <span>{{ $item['label'] }}</span></a>@endforeach
        </nav>
        <span class="ml-progress" aria-hidden="true"><i data-scroll-progress></i></span>
    </header>

    <nav class="ml-mobile-nav" aria-label="Navigasi cepat">
        @foreach ($navigation as $item)<a href="#{{ $item['id'] }}"><span>{{ sprintf('%02d', $loop->iteration) }}</span>{{ $item['label'] }}</a>@endforeach
    </nav>

    <main id="top" tabindex="-1" inert>
        @foreach ($sections as $section)
            @if ($section === 'opening')
                <section class="ml-hero">
                    <div class="ml-index">Issue No. 01<br>{{ $primary_event['date'] ?? 'A celebration' }}</div>
                    <div class="ml-hero__title"><p>Dengan penuh kebahagiaan</p><h1>{{ $title }}</h1></div>
                    <div class="ml-hero__note">@if ($opening_text)<p>{{ $opening_text }}</p>@endif<span>Scroll untuk membaca</span></div>
                </section>
            @elseif ($section === 'events' && count($events))
                <section class="ml-section ml-agenda" id="agenda" aria-labelledby="agenda-title">
                    <header><span>{{ sprintf('%02d', $sectionNumbers['events'] + 1) }} / Agenda</span><h2 id="agenda-title">Hari yang kami nantikan.</h2></header>
                    <div class="ml-event-list">
                        @foreach ($events as $event)
                            <article data-reveal>
                                <div class="ml-event__number">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</div>
                                <div><h3>{{ $event['label'] }}</h3><p class="ml-large">{{ $event['date'] }}</p>@if ($event['start_time'])<p>{{ $event['start_time'] }}{{ $event['end_time'] ? ' – '.$event['end_time'] : '' }} · {{ $event['timezone'] }}</p>@endif</div>
                                <div>@if ($event['venue'])<strong>{{ $event['venue'] }}</strong>@endif @if ($event['address'])<p>{{ $event['address'] }}</p>@endif
                                    <div class="ml-actions">@if (in_array('map', $sections) && $event['directions_url'])<a href="{{ $event['directions_url'] }}" target="_blank" rel="noopener noreferrer">Arah</a>@endif @if (in_array('calendar', $sections) && $event['calendar_url'])<a href="{{ $event['calendar_url'] }}" target="_blank" rel="noopener noreferrer">Kalender</a><a href="{{ $event['ics_url'] }}">ICS</a>@endif @if (in_array('map', $sections) && $event['address'])<button type="button" data-copy="{{ $event['address'] }}">Salin alamat</button>@endif</div>
                                    @foreach ($event['notes'] as $note)<small>{{ $note }}</small>@endforeach
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @elseif ($section === 'countdown' && $primary_event && $primary_event['timestamp'])
                <section class="ml-countdown" data-countdown="{{ $primary_event['timestamp'] }}">
                    <div class="ml-countdown__heading"><span>Menuju perayaan</span><strong>Save the moment</strong></div>
                    <div class="ml-countdown__grid" data-countdown-output role="timer" aria-live="off">
                        @foreach (['days' => 'Hari', 'hours' => 'Jam', 'minutes' => 'Menit', 'seconds' => 'Detik'] as $unit => $label)
                            <div><b data-countdown-unit="{{ $unit }}">00</b><span>{{ $label }}</span></div>
                        @endforeach
                    </div>
                </section>
            @elseif ($section === 'hosts' && count($hosts))
                <section class="ml-section" id="people" aria-labelledby="people-title"><header><span>{{ sprintf('%02d', $sectionNumbers['hosts'] + 1) }} / The people</span><h2 id="people-title">Dua cerita, satu perjalanan.</h2></header><div class="ml-hosts">
                    @foreach ($hosts as $host)<article>@if ($host['photo_url'])<img src="{{ $host['photo_url'] }}" alt="Foto {{ $host['name'] }}" loading="lazy" decoding="async">@else<div class="ml-photo-fallback" aria-hidden="true">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</div>@endif<div><h3>{{ $host['name'] }}</h3>@if ($host['birth_order'])<p>{{ $host['birth_order'] }}</p>@endif @if ($host['family'])<p>Putra/putri dari {{ $host['family'] }}</p>@endif @if ($host['bio'])<p>{{ $host['bio'] }}</p>@endif @if ($host['instagram'])<a href="{{ $host['instagram'] }}" target="_blank" rel="noopener noreferrer">Instagram ↗</a>@endif</div></article>@endforeach
                </div></section>
            @elseif ($section === 'story' && count($stories))
                <section class="ml-section ml-story" aria-labelledby="story-title"><header><span>Chronology</span><h2 id="story-title">Catatan perjalanan.</h2></header><ol>@foreach ($stories as $story)<li>@if ($story['image_url'])<img src="{{ $story['image_url'] }}" alt="{{ $story['title'] }}" loading="lazy" decoding="async">@endif<div><small>{{ $story['date'] }}</small><h3>{{ $story['title'] }}</h3>@if ($story['body'])<p>{{ $story['body'] }}</p>@endif</div></li>@endforeach</ol></section>
            @elseif ($section === 'gallery' && count($gallery))
                <section class="ml-frames" id="frames" aria-labelledby="frames-title"><header><span>{{ sprintf('%02d', $sectionNumbers['gallery'] + 1) }} / Selected frames</span><h2 id="frames-title">Beberapa momen yang kami simpan.</h2></header><div class="ml-gallery">@foreach ($gallery as $image)<figure><button type="button" data-lightbox-src="{{ $image['url'] }}" data-lightbox-alt="{{ $image['alt'] }}"><img src="{{ $image['url'] }}" alt="{{ $image['alt'] }}" loading="lazy" decoding="async"></button>@if ($image['caption'])<figcaption>{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }} · {{ $image['caption'] }}</figcaption>@endif</figure>@endforeach</div></section>
            @elseif ($section === 'map' && $primary_event && $primary_event['map_embed_url'])
                <section class="ml-section ml-location" aria-labelledby="location-title"><header><span>Destination</span><h2 id="location-title">Sampai jumpa di sana.</h2></header><div class="ml-map"><iframe src="{{ $primary_event['map_embed_url'] }}" title="Peta {{ $primary_event['venue'] ?: $primary_event['label'] }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe></div>@if ($primary_event['address'])<p>{{ $primary_event['address'] }}</p>@endif<div class="ml-actions">@if ($primary_event['directions_url'])<a href="{{ $primary_event['directions_url'] }}" target="_blank" rel="noopener noreferrer">Google Maps ↗</a>@endif @if ($primary_event['address'])<button type="button" data-copy="{{ $primary_event['address'] }}">Salin alamat</button>@endif</div></section>
            @elseif ($section === 'rsvp')
                <div class="ml-shared" id="response">@include('invitations.shared.rsvp')</div>
            @elseif ($section === 'guestbook')
                <div class="ml-shared">@include('invitations.shared.guestbook')</div>
            @elseif ($section === 'gifts' && count($gifts))
                <section class="ml-section ml-gifts" aria-labelledby="gifts-title"><header><span>With love</span><h2 id="gifts-title">Tanda kasih.</h2></header><p>Kehadiran dan doa Anda adalah hadiah terindah.</p>@foreach ($gifts as $gift)<details><summary>{{ $gift['type_label'] }} · {{ $gift['provider'] }}</summary><div>@if ($gift['account_number'])<strong>{{ $gift['account_number'] }}</strong><button type="button" data-copy="{{ $gift['account_number'] }}">Salin nomor</button>@endif @if ($gift['account_name'])<p>Atas nama {{ $gift['account_name'] }}</p>@endif @if ($gift['delivery_address'])<p>{{ $gift['delivery_address'] }}</p><button type="button" data-copy="{{ $gift['delivery_address'] }}">Salin alamat hadiah</button>@endif @if ($gift['notes'])<p>{{ $gift['notes'] }}</p>@endif</div></details>@endforeach</section>
            @elseif ($section === 'livestream' && $livestream_url)
                <section class="ml-strip"><span>Live broadcast</span><a href="{{ $livestream_url }}" target="_blank" rel="noopener noreferrer">{{ $livestream_label }} ↗</a></section>
            @elseif ($section === 'contacts' && count($contacts))
                <section class="ml-section ml-contacts"><header><span>Contact desk</span><h2>Perlu bantuan?</h2></header><div class="ml-actions">@foreach ($contacts as $contact)<a href="{{ $contact['whatsapp_url'] }}" target="_blank" rel="noopener noreferrer">WhatsApp {{ $contact['label'] }} · {{ $contact['name'] }}</a><a href="{{ $contact['phone_url'] }}">Telepon</a>@endforeach</div></section>
            @elseif ($section === 'sharing')
                <section class="ml-strip"><span>Share the date</span><div class="ml-actions"><button type="button" data-share data-share-url="{{ $share_url }}">Bagikan undangan</button><a href="{{ $whatsapp_url }}" target="_blank" rel="noopener noreferrer">WhatsApp ↗</a></div></section>
            @elseif ($section === 'closing')
                <section class="ml-closing">
                    <span>End note · 2026</span>
                    <div><p class="ml-closing__script">With love,</p><h2>{{ $title }}</h2></div>
                    <div class="ml-closing__message">@if ($closing_message)<p>{{ $closing_message }}</p>@endif<p class="ml-kicker">Terima kasih telah menjadi bagian dari cerita kami.</p></div>
                    <a href="#top" class="ml-back-to-top">Kembali ke awal <span aria-hidden="true">↑</span></a>
                </section>
            @endif
        @endforeach
    </main>

    @if ($music_url)<audio data-music loop preload="none" src="{{ $music_url }}"></audio><button class="ml-music" type="button" data-music-toggle aria-label="Putar musik">Putar musik</button>@endif
    <dialog class="ml-lightbox" data-lightbox><button type="button" data-lightbox-close aria-label="Tutup galeri">Tutup ×</button><img data-lightbox-image alt=""></dialog>
</body>
</html>

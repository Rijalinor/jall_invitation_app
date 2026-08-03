<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $title }}">
    <title>{{ $title }}</title>
    @vite(['resources/invitation-templates/elegant-rose/assets/theme.css', 'resources/invitation-templates/elegant-rose/assets/theme.js'])
</head>
<body class="elegant-rose" style="--rose-accent: {{ $theme['accent_color'] }}" data-motion="{{ $theme['motion'] }}">
    <div class="er-cover" id="opening-cover">
        <div class="er-cover__paper">
            <span class="er-cover__ornament" aria-hidden="true">✦</span>
            <span class="er-eyebrow">Undangan</span>
            <h1>{{ $title }}</h1>
            @if ($primary_event)<span class="er-cover__date">{{ $primary_event['date'] }}</span>@endif
            <p>Kepada Yth.</p>
            <strong>{{ $recipient }}</strong>
            <button type="button" data-open-invitation>Buka Undangan</button>
        </div>
    </div>

    <main id="invitation-content" tabindex="-1" inert>
        @foreach ($sections as $section)
            @if ($section === 'opening')
                <section class="er-hero er-section">
                    <span class="er-eyebrow">Dengan penuh kebahagiaan</span>
                    @if (count($hosts) >= 2)
                        <h2 class="er-couple-title"><span>{{ $hosts[0]['name'] }}</span><i>&amp;</i><span>{{ $hosts[1]['name'] }}</span></h2>
                    @else
                        <h2>{{ $title }}</h2>
                    @endif
                    @if ($opening_text)<p>{{ $opening_text }}</p>@endif
                    @if ($primary_event)<p class="er-date">{{ $primary_event['date'] }}</p>@endif
                </section>
            @elseif ($section === 'hosts' && count($hosts))
                <section class="er-section" aria-labelledby="hosts-title">
                    <span class="er-eyebrow">Yang Berbahagia</span>
                    <h2 id="hosts-title">Mempelai &amp; Keluarga</h2>
                    <div class="er-hosts" data-count="{{ count($hosts) }}">
                        @foreach ($hosts as $host)
                            <article class="er-host">
                                <div class="er-host__portrait">
                                    @if ($host['photo_url'])<img src="{{ $host['photo_url'] }}" alt="Foto {{ $host['name'] }}" loading="lazy" decoding="async">@else<span aria-hidden="true">{{ mb_substr($host['name'], 0, 1) }}</span>@endif
                                </div>
                                <h3>{{ $host['name'] }}</h3>
                                @if ($host['birth_order'])<p>{{ $host['birth_order'] }}</p>@endif
                                @if ($host['family'])<p>Putra/putri dari {{ $host['family'] }}</p>@endif
                                @if ($host['bio'])<p>{{ $host['bio'] }}</p>@endif
                                @if ($host['instagram'])<a href="{{ $host['instagram'] }}" rel="noopener noreferrer" target="_blank">Instagram</a>@endif
                            </article>
                            @if (count($hosts) === 2 && ! $loop->last)<span class="er-hosts__and" aria-hidden="true">&amp;</span>@endif
                        @endforeach
                    </div>
                </section>
            @elseif ($section === 'events' && count($events))
                <section class="er-section er-section--tint" aria-labelledby="events-title">
                    <span class="er-eyebrow">Save the Date</span>
                    <h2 id="events-title">Rangkaian Acara</h2>
                    <div class="er-events">
                        @foreach ($events as $event)
                            <article class="er-event">
                                <span class="er-event__number">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <h3>{{ $event['label'] }}</h3>
                                <p><strong>{{ $event['date'] }}</strong></p>
                                @if ($event['start_time'])<p>{{ $event['start_time'] }}{{ $event['end_time'] ? ' – '.$event['end_time'] : '' }} {{ $event['timezone'] }}</p>@endif
                                @if ($event['venue'])<p>{{ $event['venue'] }}</p>@endif
                                @if ($event['address'])<p>{{ $event['address'] }}</p>@endif
                                <div class="er-actions">
                                    @if (in_array('map', $sections) && $event['directions_url'])<a href="{{ $event['directions_url'] }}" target="_blank" rel="noopener noreferrer">Petunjuk Arah</a>@endif
                                    @if (in_array('calendar', $sections) && $event['calendar_url'])<a href="{{ $event['calendar_url'] }}" target="_blank" rel="noopener noreferrer">Google Calendar</a>@endif
                                    @if (in_array('calendar', $sections))<a href="{{ $event['ics_url'] }}">Unduh ICS</a>@endif
                                    @if (in_array('map', $sections) && $event['address'])<button type="button" data-copy="{{ $event['address'] }}">Salin Alamat</button>@endif
                                </div>
                                @foreach ($event['notes'] as $note)<small>{{ $note }}</small>@endforeach
                            </article>
                        @endforeach
                    </div>
                </section>
            @elseif ($section === 'map' && $primary_event && $primary_event['map_embed_url'])
                <section class="er-section" aria-labelledby="map-title">
                    <span class="er-eyebrow">Lokasi Acara</span><h2 id="map-title">Petunjuk Lokasi</h2>
                    <div class="er-map"><iframe src="{{ $primary_event['map_embed_url'] }}" title="Peta {{ $primary_event['venue'] ?: $primary_event['label'] }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe></div>
                    @if ($primary_event['address'])<p>{{ $primary_event['address'] }}</p>@endif
                    <div class="er-actions er-actions--center">@if ($primary_event['directions_url'])<a href="{{ $primary_event['directions_url'] }}" target="_blank" rel="noopener noreferrer">Buka Google Maps</a>@endif @if ($primary_event['address'])<button type="button" data-copy="{{ $primary_event['address'] }}">Salin Alamat</button>@endif</div>
                </section>
            @elseif ($section === 'countdown' && $primary_event && $primary_event['timestamp'])
                <section class="er-section er-countdown" data-countdown="{{ $primary_event['timestamp'] }}" aria-live="polite">
                    <span class="er-eyebrow">Menuju Hari Bahagia</span>
                    <p data-countdown-output>Menghitung waktu…</p>
                </section>
            @elseif ($section === 'story' && count($stories))
                <section class="er-section" aria-labelledby="story-title">
                    <span class="er-eyebrow">Jejak Cerita</span><h2 id="story-title">Kisah Kami</h2>
                    <div class="er-timeline">
                        @foreach ($stories as $story)
                            <article>
                                @if ($story['image_url'])<img src="{{ $story['image_url'] }}" alt="{{ $story['title'] }}" loading="lazy">@endif
                                <small>{{ $story['date'] }}</small><h3>{{ $story['title'] }}</h3>
                                @if ($story['body'])<p>{{ $story['body'] }}</p>@endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @elseif ($section === 'gallery' && count($gallery))
                <section class="er-section er-section--tint" aria-labelledby="gallery-title">
                    <span class="er-eyebrow">Galeri</span><h2 id="gallery-title">Momen Pilihan</h2>
                    <div class="er-gallery">@foreach ($gallery as $image)<figure><button type="button" data-lightbox-src="{{ $image['url'] }}" data-lightbox-alt="{{ $image['alt'] }}"><img src="{{ $image['url'] }}" alt="{{ $image['alt'] }}" loading="lazy" decoding="async"></button>@if ($image['caption'])<figcaption>{{ $image['caption'] }}</figcaption>@endif</figure>@endforeach</div>
                </section>
            @elseif ($section === 'rsvp')
                @include('invitations.shared.rsvp')
            @elseif ($section === 'guestbook')
                @include('invitations.shared.guestbook')
            @elseif ($section === 'gifts' && count($gifts))
                <section class="er-section" aria-labelledby="gifts-title">
                    <span class="er-eyebrow">Tanda Kasih</span><h2 id="gifts-title">Hadiah Digital</h2>
                    <p>Doa dan kehadiran Anda adalah hadiah terindah. Detail berikut tersedia bila Anda ingin mengirim tanda kasih.</p>
                    <div class="er-gifts">
                        @foreach ($gifts as $gift)
                            <details class="er-gift"><summary>{{ $gift['type_label'] }} · {{ $gift['provider'] }}</summary><div>
                                @if ($gift['account_number'])<p><small>Nomor rekening / e-wallet</small><strong>{{ $gift['account_number'] }}</strong></p><button type="button" data-copy="{{ $gift['account_number'] }}">Salin Nomor</button>@endif
                                @if ($gift['account_name'])<p>Atas nama {{ $gift['account_name'] }}</p>@endif
                                @if ($gift['delivery_address'])<p>{{ $gift['delivery_address'] }}</p><button type="button" data-copy="{{ $gift['delivery_address'] }}">Salin Alamat Hadiah</button>@endif
                                @if ($gift['notes'])<p>{{ $gift['notes'] }}</p>@endif
                            </div></details>
                        @endforeach
                    </div>
                </section>
            @elseif ($section === 'livestream' && $livestream_url)
                <section class="er-section"><h2>Live Streaming</h2><a class="er-button" href="{{ $livestream_url }}" target="_blank" rel="noopener noreferrer">{{ $livestream_label }}</a></section>
            @elseif ($section === 'contacts' && count($contacts))
                <section class="er-section" aria-labelledby="contacts-title"><h2 id="contacts-title">Kontak</h2><div class="er-actions er-actions--center">@foreach ($contacts as $contact)<a href="{{ $contact['whatsapp_url'] }}" target="_blank" rel="noopener noreferrer">WhatsApp {{ $contact['label'] }} · {{ $contact['name'] }}</a><a href="{{ $contact['phone_url'] }}">Telepon</a>@endforeach</div></section>
            @elseif ($section === 'sharing')
                <section class="er-section er-section--compact"><div class="er-actions er-actions--center"><button type="button" data-share data-share-url="{{ $share_url }}">Bagikan Undangan</button><a href="{{ $whatsapp_url }}" target="_blank" rel="noopener noreferrer">Bagikan via WhatsApp</a></div></section>
            @elseif ($section === 'closing')
                <section class="er-section er-closing"><span class="er-eyebrow">Terima Kasih</span><h2>{{ $title }}</h2>@if ($closing_message)<p>{{ $closing_message }}</p>@endif</section>
            @endif
        @endforeach
    </main>

    @if ($music_url)
        <audio data-music loop preload="none" src="{{ $music_url }}"></audio>
        <button class="er-music" type="button" data-music-toggle aria-label="Putar musik">Musik</button>
    @endif
    <dialog class="er-lightbox" data-lightbox><button type="button" data-lightbox-close aria-label="Tutup galeri">Tutup</button><img data-lightbox-image alt=""></dialog>
</body>
</html>

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $title }}">
    <title>{{ $title }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400;1,600&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/invitation-templates/korean-aesthetic/assets/theme.css', 'resources/invitation-templates/korean-aesthetic/assets/theme.js'])
</head>
<body class="korean-aesthetic" style="--ka-accent: {{ $theme['accent_color'] ?? '#c4a482' }}; --ka-bg: {{ $theme['bg_color'] ?? '#f7f4ef' }};" data-motion="{{ $theme['motion'] ?? 'expressive' }}">
    @php
        $navItems = [
            'hosts' => ['id' => 'hosts', 'label' => 'Mempelai'],
            'events' => ['id' => 'events', 'label' => 'Acara'],
            'story' => ['id' => 'story', 'label' => 'Cerita Kita'],
            'gallery' => ['id' => 'gallery', 'label' => 'Galeri'],
            'rsvp' => ['id' => 'rsvp', 'label' => 'RSVP'],
        ];
        $visibleNav = collect($sections)->filter(fn ($section) => isset($navItems[$section]))->mapWithKeys(fn ($section) => [$section => $navItems[$section]])->all();
        $coverImage = ($theme['cover_poster_image'] ?? null) ?: ($gallery[0]['url'] ?? ($hosts[0]['photo_url'] ?? null));
        $displayNames = collect($hosts)->pluck('nickname')->filter()->whenEmpty(fn ($c) => collect($hosts)->pluck('name'))->take(2)->join(' & ') ?: $title;
    @endphp

    <!-- Cover Overlay -->
    <div class="ka-cover" id="opening-cover">
        @if ($coverImage)
            <img class="ka-cover__bg" src="{{ $coverImage }}" alt="" aria-hidden="true">
        @endif
        <div class="ka-cover__card">
            <span class="ka-sub-tag">THE WEDDING OF</span>
            <h1 class="ka-cover__title">{{ $displayNames }}</h1>
            @if ($primary_event)
                <p class="ka-cover__date">✦ {{ $primary_event['date'] }} ✦</p>
            @endif
            <div class="ka-recipient-box">
                <small>Dear Special Guest,</small>
                <strong>{{ $recipient }}</strong>
            </div>
            <button type="button" class="ka-btn ka-btn--primary ka-btn--lg" data-open-invitation>
                Open Invitation 💌
            </button>
        </div>
    </div>

    <!-- Sticky Navigation -->
    <nav class="ka-nav" aria-label="Navigasi undangan">
        <a href="#invitation-content">Awal</a>
        @foreach ($visibleNav as $item)
            <a href="#{{ $item['id'] }}">{{ $item['label'] }}</a>
        @endforeach
    </nav>

    <!-- Main Content -->
    <main id="invitation-content" tabindex="-1" inert>
        @foreach ($sections as $section)
            @if ($section === 'opening')
                <section class="ka-section ka-hero">
                    <span class="ka-eyebrow">Our Special Day</span>
                    @if (count($hosts) >= 2)
                        <h2 class="ka-couple-title">
                            <span>{{ $hosts[0]['nickname'] ?: $hosts[0]['name'] }}</span>
                            <span class="ka-ampersand">&amp;</span>
                            <span>{{ $hosts[1]['nickname'] ?: $hosts[1]['name'] }}</span>
                        </h2>
                    @else
                        <h2>{{ $title }}</h2>
                    @endif
                    <div class="ka-divider"><span>✦</span></div>
                    @if ($opening_text)
                        <p class="ka-hero__text">{{ $opening_text }}</p>
                    @else
                        <p class="ka-hero__text">Dengan memohon rahmat dan ridho Allah SWT, kami bermaksud menyelenggarakan syukuran pernikahan putra-putri kami.</p>
                    @endif
                    @if ($primary_event)
                        <div class="ka-date-badge">🗓️ {{ $primary_event['date'] }}</div>
                    @endif
                </section>

            @elseif ($section === 'hosts' && count($hosts))
                <section class="ka-section" id="hosts" aria-labelledby="hosts-title">
                    <div class="ka-section-header">
                        <span class="ka-eyebrow">GROOM & BRIDE</span>
                        <h2 id="hosts-title">Mempelai Indah</h2>
                        <div class="ka-divider"><span>✦</span></div>
                    </div>
                    <div class="ka-hosts" data-count="{{ count($hosts) }}">
                        @foreach ($hosts as $host)
                            <article class="ka-host ka-polaroid">
                                <div class="ka-polaroid__photo">
                                    @if ($host['photo_url'])
                                        <img src="{{ $host['photo_url'] }}" alt="Foto {{ $host['name'] }}" loading="lazy" decoding="async">
                                    @else
                                        <div class="ka-polaroid__placeholder">{{ mb_substr($host['name'], 0, 1) }}</div>
                                    @endif
                                    <span class="ka-role-tag">
                                        {{ match ($host['role']) { 'groom' => 'The Groom', 'bride' => 'The Bride', default => 'Mempelai' } }}
                                    </span>
                                </div>
                                <div class="ka-polaroid__caption">
                                    <h3 class="ka-host__name">{{ $host['name'] }}</h3>
                                    @if ($host['birth_order'])
                                        <span class="ka-host__order">{{ $host['birth_order'] }}</span>
                                    @endif
                                    @if ($host['family'])
                                        <p class="ka-host__family">{{ match ($host['role']) { 'groom' => 'Putra dari Bpk/Ibu', 'bride' => 'Putri dari Bpk/Ibu', default => 'Putra/i dari' } }}<br><strong>{{ $host['family'] }}</strong></p>
                                    @endif
                                    @if ($host['instagram'])
                                        <a class="ka-btn ka-btn--outline ka-btn--sm" href="{{ $host['instagram'] }}" target="_blank" rel="noopener noreferrer">
                                            @<span>{{ Str::after($host['instagram'], 'instagram.com/') ?: 'Instagram' }}</span>
                                        </a>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>

            @elseif ($section === 'events' && count($events))
                <section class="ka-section ka-section--card" id="events" aria-labelledby="events-title">
                    <div class="ka-section-header">
                        <span class="ka-eyebrow">SAVE THE DATE</span>
                        <h2 id="events-title">Rangkaian Acara</h2>
                        <div class="ka-divider"><span>✦</span></div>
                    </div>
                    <div class="ka-events">
                        @foreach ($events as $event)
                            <article class="ka-event-card">
                                <div class="ka-event-card__header">
                                    <span class="ka-badge">{{ $event['label'] }}</span>
                                    <h3>{{ $event['date'] }}</h3>
                                    @if ($event['start_time'])
                                        <p class="ka-event__time">⏰ {{ $event['start_time'] }}{{ $event['end_time'] ? ' - '.$event['end_time'] : '' }} {{ $event['timezone'] }}</p>
                                    @endif
                                </div>
                                @if ($event['venue'] || $event['address'])
                                    <div class="ka-event__venue">
                                        @if ($event['venue'])
                                            <strong>📍 {{ $event['venue'] }}</strong>
                                        @endif
                                        @if ($event['address'])
                                            <p>{{ $event['address'] }}</p>
                                        @endif
                                    </div>
                                @endif
                                <div class="ka-actions">
                                    @if (in_array('map', $sections) && $event['directions_url'])
                                        <a class="ka-btn ka-btn--primary" href="{{ $event['directions_url'] }}" target="_blank" rel="noopener noreferrer">🗺️ Petunjuk Lokasi</a>
                                    @endif
                                    @if (in_array('calendar', $sections) && $event['calendar_url'])
                                        <a class="ka-btn ka-btn--secondary" href="{{ $event['calendar_url'] }}" target="_blank" rel="noopener noreferrer">📅 Simpan Kalender</a>
                                    @endif
                                    @if ($event['address'])
                                        <button type="button" class="ka-btn ka-btn--outline" data-copy="{{ $event['address'] }}">📋 Salin Alamat</button>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>

            @elseif ($section === 'countdown' && $primary_event && $primary_event['timestamp'])
                <section class="ka-section ka-countdown" data-countdown="{{ $primary_event['timestamp'] }}" aria-label="Hitung mundur menuju hari bahagia">
                    <div class="ka-section-header">
                        <span class="ka-eyebrow">COUNTDOWN</span>
                        <h2>Menghitung Hari</h2>
                        <div class="ka-divider"><span>✦</span></div>
                    </div>
                    <div class="ka-countdown__grid" data-countdown-output>
                        @foreach (['days' => 'Hari', 'hours' => 'Jam', 'minutes' => 'Menit', 'seconds' => 'Detik'] as $unit => $label)
                            <div class="ka-countdown__card">
                                <span class="ka-countdown__num" data-countdown-unit="{{ $unit }}">00</span>
                                <small class="ka-countdown__label">{{ $label }}</small>
                            </div>
                        @endforeach
                    </div>
                </section>

            @elseif ($section === 'map' && $primary_event && $primary_event['map_embed_url'])
                <section class="ka-section" aria-labelledby="map-title">
                    <div class="ka-section-header">
                        <span class="ka-eyebrow">LOCATION</span>
                        <h2 id="map-title">Peta Denah Lokasi</h2>
                        <div class="ka-divider"><span>✦</span></div>
                    </div>
                    <div class="ka-map-card">
                        <iframe src="{{ $primary_event['map_embed_url'] }}" title="Peta {{ $primary_event['venue'] ?: $primary_event['label'] }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                    @if ($primary_event['address'])
                        <p class="ka-map__address">📍 {{ $primary_event['address'] }}</p>
                    @endif
                    <div class="ka-actions ka-actions--center">
                        @if ($primary_event['directions_url'])
                            <a class="ka-btn ka-btn--primary" href="{{ $primary_event['directions_url'] }}" target="_blank" rel="noopener noreferrer">🚀 Buka Google Maps</a>
                        @endif
                        @if ($primary_event['address'])
                            <button type="button" class="ka-btn ka-btn--outline" data-copy="{{ $primary_event['address'] }}">📋 Salin Alamat</button>
                        @endif
                    </div>
                </section>

            @elseif ($section === 'story' && count($stories))
                <section class="ka-section ka-section--card" id="story" aria-labelledby="story-title">
                    <div class="ka-section-header">
                        <span class="ka-eyebrow">LOVE STORY</span>
                        <h2 id="story-title">Kisah Perjalanan Kita</h2>
                        <div class="ka-divider"><span>✦</span></div>
                    </div>
                    <div class="ka-timeline">
                        @foreach ($stories as $story)
                            <article class="ka-story-card">
                                <span class="ka-story__chapter">Chapter {{ $loop->iteration }}</span>
                                @if ($story['image_url'])
                                    <img class="ka-story__img" src="{{ $story['image_url'] }}" alt="{{ $story['title'] }}" loading="lazy">
                                @endif
                                <small class="ka-story__date">🗓️ {{ $story['date'] }}</small>
                                <h3>{{ $story['title'] }}</h3>
                                @if ($story['body'])
                                    <p>{{ $story['body'] }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>

            @elseif ($section === 'gallery' && count($gallery))
                <section class="ka-section" id="gallery" aria-labelledby="gallery-title">
                    <div class="ka-section-header">
                        <span class="ka-eyebrow">OUR MEMORIES</span>
                        <h2 id="gallery-title">Galeri Foto</h2>
                        <div class="ka-divider"><span>✦</span></div>
                    </div>
                    <div class="ka-gallery">
                        @foreach (array_chunk($gallery, 4) as $page)
                            <div class="ka-gallery__page">
                                @foreach ($page as $image)
                                    <figure class="ka-gallery__item ka-polaroid--sm">
                                        <button type="button" data-lightbox-src="{{ $image['url'] }}" data-lightbox-alt="{{ $image['alt'] }}">
                                            <img src="{{ $image['url'] }}" alt="{{ $image['alt'] }}" loading="lazy" decoding="async">
                                        </button>
                                        @if ($image['caption'])
                                            <figcaption>{{ $image['caption'] }}</figcaption>
                                        @endif
                                    </figure>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </section>

            @elseif ($section === 'rsvp')
                <div class="ka-section-wrapper" id="rsvp">
                    @include('invitations.shared.rsvp')
                </div>

            @elseif ($section === 'guestbook')
                <div class="ka-section-wrapper" id="guestbook">
                    @include('invitations.shared.guestbook')
                </div>

            @elseif ($section === 'gifts' && count($gifts))
                <section class="ka-section ka-section--card" aria-labelledby="gifts-title">
                    <div class="ka-section-header">
                        <span class="ka-eyebrow">WEDDING GIFT</span>
                        <h2 id="gifts-title">Tanda Kasih</h2>
                        <p>Doa restu Anda merupakan karunia yang sangat berarti bagi kami. Namun jika ingin memberikan tanda kasih, dapat melalui berikut ini:</p>
                    </div>
                    <div class="ka-gifts">
                        @foreach ($gifts as $gift)
                            <details class="ka-gift-card">
                                <summary class="ka-gift__summary">
                                    <span>💳 {{ $gift['type_label'] }} · {{ $gift['provider'] }}</span>
                                    <span class="ka-gift__arrow">▼</span>
                                </summary>
                                <div class="ka-gift__body">
                                    @if ($gift['account_number'])
                                        <p><small>Nomor Rekening / E-Wallet:</small></p>
                                        <strong class="ka-gift__num">{{ $gift['account_number'] }}</strong>
                                        <button type="button" class="ka-btn ka-btn--sm ka-btn--primary" data-copy="{{ $gift['account_number'] }}">📋 Salin Nomor</button>
                                    @endif
                                    @if ($gift['account_name'])
                                        <p>Atas nama: <strong>{{ $gift['account_name'] }}</strong></p>
                                    @endif
                                    @if ($gift['delivery_address'])
                                        <p>Alamat Pengiriman Kado:</p>
                                        <p><strong>{{ $gift['delivery_address'] }}</strong></p>
                                        <button type="button" class="ka-btn ka-btn--sm ka-btn--outline" data-copy="{{ $gift['delivery_address'] }}">📋 Salin Alamat</button>
                                    @endif
                                    @if ($gift['notes'])
                                        <p class="ka-muted">{{ $gift['notes'] }}</p>
                                    @endif
                                </div>
                            </details>
                        @endforeach
                    </div>
                </section>

            @elseif ($section === 'contacts' && count($contacts))
                <section class="ka-section" aria-labelledby="contacts-title">
                    <div class="ka-section-header">
                        <span class="ka-eyebrow">CONTACT US</span>
                        <h2 id="contacts-title">Kontak Keluarga</h2>
                    </div>
                    <div class="ka-contacts">
                        @foreach ($contacts as $contact)
                            <article class="ka-contact-card">
                                <small>{{ $contact['label'] }}</small>
                                <h3>{{ $contact['name'] }}</h3>
                                <div class="ka-actions ka-actions--center">
                                    <a class="ka-btn ka-btn--primary ka-btn--sm" href="{{ $contact['whatsapp_url'] }}" target="_blank" rel="noopener noreferrer">💬 WhatsApp</a>
                                    <a class="ka-btn ka-btn--outline ka-btn--sm" href="{{ $contact['phone_url'] }}">📞 Telepon</a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>

            @elseif ($section === 'sharing')
                <section class="ka-section ka-section--card">
                    <div class="ka-section-header">
                        <span class="ka-eyebrow">SHARE THE LOVE</span>
                        <h2>Bagikan Undangan</h2>
                    </div>
                    <div class="ka-actions ka-actions--center">
                        <button type="button" class="ka-btn ka-btn--primary" data-share data-share-url="{{ $share_url }}">🚀 Bagikan Link</button>
                        <a class="ka-btn ka-btn--secondary" href="{{ $whatsapp_url }}" target="_blank" rel="noopener noreferrer">💬 Kirim via WhatsApp</a>
                    </div>
                </section>

            @elseif ($section === 'closing')
                <section class="ka-section ka-closing">
                    <span class="ka-eyebrow">THANK YOU</span>
                    <h2>{{ $displayNames }}</h2>
                    @if ($closing_message)
                        <p class="ka-closing__msg">{{ $closing_message }}</p>
                    @else
                        <p class="ka-closing__msg">Merupakan suatu kehormatan dan kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir untuk memberikan doa restu.</p>
                    @endif
                    <a class="ka-btn ka-btn--outline ka-btn--sm" href="#invitation-content">⬆️ Kembali Ke Atas</a>
                </section>
            @endif
        @endforeach
    </main>

    @if ($music_url)
        <audio data-music loop preload="none" src="{{ $music_url }}"></audio>
        <button class="ka-music" type="button" data-music-toggle aria-label="Putar musik">
            <span aria-hidden="true">🎵</span>
        </button>
    @endif

    <dialog class="ka-lightbox" data-lightbox>
        <button type="button" data-lightbox-close aria-label="Tutup galeri">✕ Tutup</button>
        <img data-lightbox-image alt="">
    </dialog>
</body>
</html>

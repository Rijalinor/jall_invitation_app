<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $title }}">
    <title>{{ $title }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/invitation-templates/fun-storybook/assets/theme.css', 'resources/invitation-templates/fun-storybook/assets/theme.js'])
</head>
<body class="fun-storybook" style="--fsb-accent: {{ $theme['accent_color'] ?? '#ff6b81' }}; --fsb-bg: {{ $theme['bg_color'] ?? '#fdf6e4' }};" data-motion="{{ $theme['motion'] ?? 'expressive' }}">
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

    <!-- Fun Cover Overlay -->
    <div class="fsb-cover" id="opening-cover">
        @if ($coverImage)
            <img class="fsb-cover__bg" src="{{ $coverImage }}" alt="" aria-hidden="true">
        @endif
        <div class="fsb-cover__card">
            <span class="fsb-badge fsb-badge--pop">YEAY, AKHIRNYA NIKAH! 🥳</span>
            <h1 class="fsb-cover__title">{{ $displayNames }}</h1>
            @if ($primary_event)
                <div class="fsb-cover__date">📅 {{ $primary_event['date'] }}</div>
            @endif
            <div class="fsb-speech-bubble">
                <small>Spesial Buat Kamu:</small>
                <strong>{{ $recipient }}</strong>
            </div>
            <button type="button" class="fsb-btn fsb-btn--primary fsb-btn--lg" data-open-invitation>
                🚀 Buka Undangan!
            </button>
        </div>
    </div>

    <!-- Sticky Pill Navigation -->
    <nav class="fsb-nav" aria-label="Navigasi undangan">
        <a href="#invitation-content">Awal</a>
        @foreach ($visibleNav as $item)
            <a href="#{{ $item['id'] }}">{{ $item['label'] }}</a>
        @endforeach
    </nav>

    <!-- Floating Background Decor -->
    <div class="fsb-decorations" aria-hidden="true">
        <span class="fsb-decor fsb-decor--star1">✦</span>
        <span class="fsb-decor fsb-decor--star2">★</span>
        <span class="fsb-decor fsb-decor--heart1">💖</span>
        <span class="fsb-decor fsb-decor--sparkle">✺</span>
    </div>

    <main id="invitation-content" tabindex="-1" inert>
        @foreach ($sections as $section)
            @if ($section === 'opening')
                <section class="fsb-section fsb-hero">
                    <div class="fsb-speech-bubble fsb-speech-bubble--hero">
                        <span>Gak Nyangka Kan? Kami Juga Gak Nyangka! 😆✨</span>
                    </div>
                    @if (count($hosts) >= 2)
                        <h2 class="fsb-couple-title">
                            <span>{{ $hosts[0]['nickname'] ?: $hosts[0]['name'] }}</span>
                            <span class="fsb-ampersand">&amp;</span>
                            <span>{{ $hosts[1]['nickname'] ?: $hosts[1]['name'] }}</span>
                        </h2>
                    @else
                        <h2>{{ $title }}</h2>
                    @endif
                    @if ($opening_text)
                        <p class="fsb-hero__text">{{ $opening_text }}</p>
                    @else
                        <p class="fsb-hero__text">Dengan menyebut nama Allah SWT, kami mengundang Bapak/Ibu/Saudara/i untuk hadir dan memberikan doa restu di hari bahagia kami!</p>
                    @endif
                    @if ($primary_event)
                        <div class="fsb-tag">🗓️ {{ $primary_event['date'] }}</div>
                    @endif
                </section>

            @elseif ($section === 'hosts' && count($hosts))
                <section class="fsb-section" id="hosts" aria-labelledby="hosts-title">
                    <div class="fsb-section-header">
                        <span class="fsb-badge">THE MAIN CHARACTERS 👑</span>
                        <h2 id="hosts-title">Tersangka Utama (Mempelai)</h2>
                        <p>Dua sejoli yang akhirnya bakal sah di pelaminan!</p>
                    </div>
                    <div class="fsb-hosts" data-count="{{ count($hosts) }}">
                        @foreach ($hosts as $host)
                            <article class="fsb-host">
                                <div class="fsb-host__frame">
                                    <div class="fsb-host__portrait">
                                        @if ($host['photo_url'])
                                            <img src="{{ $host['photo_url'] }}" alt="Foto {{ $host['name'] }}" loading="lazy" decoding="async">
                                        @else
                                            <span aria-hidden="true">{{ mb_substr($host['name'], 0, 1) }}</span>
                                        @endif
                                    </div>
                                    <span class="fsb-host__role-badge">
                                        {{ match ($host['role']) { 'groom' => '🕺 Mempelai Pria', 'bride' => '💃 Mempelai Wanita', default => '✨ Mempelai' } }}
                                    </span>
                                </div>
                                <div class="fsb-host__details">
                                    <h3 class="fsb-host__name">{{ $host['name'] }}</h3>
                                    @if ($host['birth_order'])
                                        <span class="fsb-host__order">{{ $host['birth_order'] }}</span>
                                    @endif
                                    @if ($host['family'])
                                        <p class="fsb-host__family">{{ match ($host['role']) { 'groom' => 'Putra kesayangan dari', 'bride' => 'Putri tercinta dari', default => 'Putra/putri dari' } }} <strong>{{ $host['family'] }}</strong></p>
                                    @endif
                                    @if ($host['bio'])
                                        <p class="fsb-host__bio">"{{ $host['bio'] }}"</p>
                                    @endif
                                    @if ($host['instagram'])
                                        <a class="fsb-btn fsb-btn--outline fsb-btn--sm" href="{{ $host['instagram'] }}" target="_blank" rel="noopener noreferrer">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>
                                            @<span>{{ Str::after($host['instagram'], 'instagram.com/') ?: 'Instagram' }}</span>
                                        </a>
                                    @endif
                                </div>
                            </article>
                            @if (count($hosts) === 2 && ! $loop->last)
                                <div class="fsb-hosts__divider" aria-hidden="true">
                                    <span class="fsb-heart-badge">💖</span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </section>

            @elseif ($section === 'events' && count($events))
                <section class="fsb-section fsb-section--tint" id="events" aria-labelledby="events-title">
                    <div class="fsb-section-header">
                        <span class="fsb-badge">SAVE THE DATE 📌</span>
                        <h2 id="events-title">Waktunya Pesta!</h2>
                        <p>Jangan sampai salah kostum apalagi salah tanggal ya!</p>
                    </div>
                    <div class="fsb-events">
                        @foreach ($events as $event)
                            <article class="fsb-event">
                                <span class="fsb-event__number">0{{ $loop->iteration }}</span>
                                <div class="fsb-event__header">
                                    <span class="fsb-badge fsb-badge--accent">{{ $event['label'] }}</span>
                                    <h3>{{ $event['date'] }}</h3>
                                    @if ($event['start_time'])
                                        <p class="fsb-event__time">⏰ {{ $event['start_time'] }}{{ $event['end_time'] ? ' - '.$event['end_time'] : '' }} {{ $event['timezone'] }}</p>
                                    @endif
                                </div>
                                @if ($event['venue'] || $event['address'])
                                    <div class="fsb-event__venue">
                                        @if ($event['venue'])
                                            <strong>📍 {{ $event['venue'] }}</strong>
                                        @endif
                                        @if ($event['address'])
                                            <p>{{ $event['address'] }}</p>
                                        @endif
                                    </div>
                                @endif
                                <div class="fsb-actions">
                                    @if (in_array('map', $sections) && $event['directions_url'])
                                        <a class="fsb-btn fsb-btn--primary" href="{{ $event['directions_url'] }}" target="_blank" rel="noopener noreferrer">🗺️ Petunjuk Maps</a>
                                    @endif
                                    @if (in_array('calendar', $sections) && $event['calendar_url'])
                                        <a class="fsb-btn fsb-btn--secondary" href="{{ $event['calendar_url'] }}" target="_blank" rel="noopener noreferrer">📅 Remind via Calendar</a>
                                    @endif
                                    @if ($event['address'])
                                        <button type="button" class="fsb-btn fsb-btn--outline" data-copy="{{ $event['address'] }}">📋 Salin Alamat</button>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>

            @elseif ($section === 'countdown' && $primary_event && $primary_event['timestamp'])
                <section class="fsb-section fsb-countdown" data-countdown="{{ $primary_event['timestamp'] }}" aria-label="Hitung mundur menuju hari bahagia">
                    <div class="fsb-section-header">
                        <span class="fsb-badge">COUNTDOWN ⏳</span>
                        <h2>Hitung Mundur Menuju Hari-H</h2>
                    </div>
                    <div class="fsb-countdown__grid" data-countdown-output>
                        @foreach (['days' => 'Hari', 'hours' => 'Jam', 'minutes' => 'Menit', 'seconds' => 'Detik'] as $unit => $label)
                            <div class="fsb-countdown__card">
                                <span class="fsb-countdown__num" data-countdown-unit="{{ $unit }}">00</span>
                                <small class="fsb-countdown__label">{{ $label }}</small>
                            </div>
                        @endforeach
                    </div>
                </section>

            @elseif ($section === 'map' && $primary_event && $primary_event['map_embed_url'])
                <section class="fsb-section" aria-labelledby="map-title">
                    <div class="fsb-section-header">
                        <span class="fsb-badge">MAP & LOCATION 🧭</span>
                        <h2 id="map-title">Biar Gak Nyasar!</h2>
                    </div>
                    <div class="fsb-map-card">
                        <iframe src="{{ $primary_event['map_embed_url'] }}" title="Peta {{ $primary_event['venue'] ?: $primary_event['label'] }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                    @if ($primary_event['address'])
                        <p class="fsb-map__address">🏠 {{ $primary_event['address'] }}</p>
                    @endif
                    <div class="fsb-actions fsb-actions--center">
                        @if ($primary_event['directions_url'])
                            <a class="fsb-btn fsb-btn--primary" href="{{ $primary_event['directions_url'] }}" target="_blank" rel="noopener noreferrer">🚀 Buka Google Maps</a>
                        @endif
                        @if ($primary_event['address'])
                            <button type="button" class="fsb-btn fsb-btn--outline" data-copy="{{ $primary_event['address'] }}">📋 Salin Alamat</button>
                        @endif
                    </div>
                </section>

            @elseif ($section === 'story' && count($stories))
                <section class="fsb-section fsb-section--tint" id="story" aria-labelledby="story-title">
                    <div class="fsb-section-header">
                        <span class="fsb-badge">LOVE STORY 📖</span>
                        <h2 id="story-title">Kisah Random Kita</h2>
                        <p>Dari cuma iseng ketemu sampai siap arungi hidup bareng!</p>
                    </div>
                    <div class="fsb-timeline">
                        @foreach ($stories as $story)
                            <article class="fsb-story-card">
                                <span class="fsb-story__step">Chapter {{ $loop->iteration }}</span>
                                @if ($story['image_url'])
                                    <img class="fsb-story__img" src="{{ $story['image_url'] }}" alt="{{ $story['title'] }}" loading="lazy">
                                @endif
                                <small class="fsb-story__date">🗓️ {{ $story['date'] }}</small>
                                <h3>{{ $story['title'] }}</h3>
                                @if ($story['body'])
                                    <p>{{ $story['body'] }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>

            @elseif ($section === 'gallery' && count($gallery))
                <section class="fsb-section" id="gallery" aria-labelledby="gallery-title">
                    <div class="fsb-section-header">
                        <span class="fsb-badge">PHOTO GALLERY 📸</span>
                        <h2 id="gallery-title">Koleksi Foto Kece Kita</h2>
                    </div>
                    <div class="fsb-gallery">
                        @foreach (array_chunk($gallery, 4) as $page)
                            <div class="fsb-gallery__page">
                                @foreach ($page as $image)
                                    <figure class="fsb-gallery__item">
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
                <div class="fsb-section-wrapper" id="rsvp">
                    @include('invitations.shared.rsvp')
                </div>

            @elseif ($section === 'guestbook')
                <div class="fsb-section-wrapper" id="guestbook">
                    @include('invitations.shared.guestbook')
                </div>

            @elseif ($section === 'gifts' && count($gifts))
                <section class="fsb-section fsb-section--tint" aria-labelledby="gifts-title">
                    <div class="fsb-section-header">
                        <span class="fsb-badge">DIGITAL GIFT 🎁</span>
                        <h2 id="gifts-title">Tanda Kasih & Amplop Digital</h2>
                        <p>Kehadiran dan doa kalian adalah hadiah terbaik! Tapi kalau mau kirim kado, boleh banget kok 😆</p>
                    </div>
                    <div class="fsb-gifts">
                        @foreach ($gifts as $gift)
                            <details class="fsb-gift-card">
                                <summary class="fsb-gift__summary">
                                    <span>💳 {{ $gift['type_label'] }} · {{ $gift['provider'] }}</span>
                                    <span class="fsb-gift__arrow">▼</span>
                                </summary>
                                <div class="fsb-gift__body">
                                    @if ($gift['account_number'])
                                        <p><small>Nomor Rekening / E-Wallet:</small></p>
                                        <strong class="fsb-gift__num">{{ $gift['account_number'] }}</strong>
                                        <button type="button" class="fsb-btn fsb-btn--sm fsb-btn--primary" data-copy="{{ $gift['account_number'] }}">📋 Salin Nomor</button>
                                    @endif
                                    @if ($gift['account_name'])
                                        <p>Atas nama: <strong>{{ $gift['account_name'] }}</strong></p>
                                    @endif
                                    @if ($gift['delivery_address'])
                                        <p>Alamat Pengiriman Kado:</p>
                                        <p><strong>{{ $gift['delivery_address'] }}</strong></p>
                                        <button type="button" class="fsb-btn fsb-btn--sm fsb-btn--outline" data-copy="{{ $gift['delivery_address'] }}">📋 Salin Alamat</button>
                                    @endif
                                    @if ($gift['notes'])
                                        <p class="fsb-muted">{{ $gift['notes'] }}</p>
                                    @endif
                                </div>
                            </details>
                        @endforeach
                    </div>
                </section>

            @elseif ($section === 'contacts' && count($contacts))
                <section class="fsb-section" aria-labelledby="contacts-title">
                    <div class="fsb-section-header">
                        <span class="fsb-badge">CONTACT US 📞</span>
                        <h2 id="contacts-title">Ada Yang Mau Ditanyain?</h2>
                        <p>Bisa langsung hubungi kontak keluarga di bawah ini ya!</p>
                    </div>
                    <div class="fsb-contacts">
                        @foreach ($contacts as $contact)
                            <article class="fsb-contact-card">
                                <small>{{ $contact['label'] }}</small>
                                <h3>{{ $contact['name'] }}</h3>
                                <div class="fsb-actions fsb-actions--center">
                                    <a class="fsb-btn fsb-btn--primary fsb-btn--sm" href="{{ $contact['whatsapp_url'] }}" target="_blank" rel="noopener noreferrer">💬 WhatsApp</a>
                                    <a class="fsb-btn fsb-btn--outline fsb-btn--sm" href="{{ $contact['phone_url'] }}">📞 Telepon</a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>

            @elseif ($section === 'sharing')
                <section class="fsb-section fsb-section--tint">
                    <div class="fsb-section-header">
                        <span class="fsb-badge">SHARE THE LOVE 💌</span>
                        <h2>Bagikan Ke Teman-Teman!</h2>
                        <p>Bantu sebarkan kabar bahagia ini ke temen-temen dan grup alumni kamu ya!</p>
                    </div>
                    <div class="fsb-actions fsb-actions--center">
                        <button type="button" class="fsb-btn fsb-btn--primary" data-share data-share-url="{{ $share_url }}">🚀 Bagikan Link</button>
                        <a class="fsb-btn fsb-btn--secondary" href="{{ $whatsapp_url }}" target="_blank" rel="noopener noreferrer">💬 Kirim via WhatsApp</a>
                    </div>
                </section>

            @elseif ($section === 'closing')
                <section class="fsb-section fsb-closing">
                    <div class="fsb-speech-bubble">
                        <span>Sampai Jumpa Di Hari Bahagia Kami! 👋❤️</span>
                    </div>
                    <h2>{{ $displayNames }}</h2>
                    @if ($closing_message)
                        <p>{{ $closing_message }}</p>
                    @endif
                    <a class="fsb-btn fsb-btn--outline fsb-btn--sm" href="#invitation-content">⬆️ Kembali Ke Atas</a>
                </section>
            @endif
        @endforeach
    </main>

    @if ($music_url)
        <audio data-music loop preload="none" src="{{ $music_url }}"></audio>
        <button class="fsb-music" type="button" data-music-toggle aria-label="Putar musik">
            <span aria-hidden="true">🎵</span>
        </button>
    @endif

    <dialog class="fsb-lightbox" data-lightbox>
        <button type="button" data-lightbox-close aria-label="Tutup galeri">✕ Tutup</button>
        <img data-lightbox-image alt="">
    </dialog>
</body>
</html>

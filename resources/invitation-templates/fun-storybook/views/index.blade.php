<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $title }}">
    <title>{{ $title }}</title>
    @include('invitations.shared.og')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    {{-- Marks that scripting is available before the first paint. --}}
    <script>document.documentElement.classList.add('js-ready');</script>
    @vite(['resources/css/invitations.css', 'resources/js/invitation-hosts.js', 'resources/js/invitation-forms.js', 'resources/js/invitation-lightbox.js', 'resources/invitation-templates/fun-storybook/assets/theme.css', 'resources/invitation-templates/fun-storybook/assets/theme.js'])
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
        $groomName = collect($hosts)->firstWhere('role', 'groom')['name'] ?? ($hosts[0]['name'] ?? null);
        $brideName = collect($hosts)->firstWhere('role', 'bride')['name'] ?? ($hosts[1]['name'] ?? null);
        $coverVideo = ($theme['cover_video_enabled'] ?? true) ? ($theme['cover_video_desktop'] ?? null) : null;
        $coverVideoMobile = ($theme['cover_video_enabled'] ?? true) ? (($theme['cover_video_mobile'] ?? null) ?: $coverVideo) : null;
        // The opening section reuses the cover video whenever one is uploaded.
        $openingVideo = $coverVideo;
        $openingVideoMobile = $coverVideoMobile;
        $displayNames = collect($hosts)->pluck('nickname')->filter()->whenEmpty(fn ($c) => collect($hosts)->pluck('name'))->take(2)->join(' & ') ?: $title;
    @endphp

    <!-- Fun Cover Overlay -->
    <div class="fsb-cover" id="opening-cover">
        @if ($coverImage)
            <img class="fsb-cover__bg" src="{{ $coverImage }}" alt="" aria-hidden="true">
        @endif
        @if ($coverVideo)
            <video class="fsb-cover__video fsb-cover__video--desktop" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video><source src="{{ $coverVideo }}"></video>
        @endif
        @if ($coverVideoMobile)
            <video class="fsb-cover__video fsb-cover__video--mobile" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video><source src="{{ $coverVideoMobile }}"></video>
        @endif
        <div class="fsb-cover__card">
            <span class="fsb-badge fsb-badge--pop">{{ $labels['cover_eyebrow'] }} 🥳</span>
            @if ($groomName && $brideName)
                <h1 class="fsb-cover__title fsb-cover__title--couple"><span>{{ $groomName }}</span><span class="fsb-cover__amp" aria-hidden="true">&amp;</span><span>{{ $brideName }}</span></h1>
            @else
                <h1 class="fsb-cover__title">{{ $displayNames }}</h1>
            @endif
            @if ($primary_event)
                <div class="fsb-cover__date">📅 {{ $primary_event['date'] }}</div>
            @endif
            @if ($recipient)
                <div class="fsb-speech-bubble">
                    <small>Spesial Buat Kamu:</small>
                    <strong>{{ $recipient }}</strong>
                </div>
            @endif
            <a class="fsb-btn fsb-btn--primary fsb-btn--lg" href="#invitation-content" data-open-invitation>
                🚀 Buka Undangan!
            </a>
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

    <main id="invitation-content" tabindex="-1" data-gate>
        @foreach ($sections as $section)
            @if ($section === 'opening')
                <section data-height="{{ $section_heights['opening'] ?? 'full' }}" class="fsb-section fsb-hero">
                    @if ($openingVideo)
                        @if ($openingVideoMobile !== $openingVideo)
                            <video class="fsb-hero__video fsb-hero__video--desktop" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video><source src="{{ $openingVideo }}"></video>
                            <video class="fsb-hero__video fsb-hero__video--mobile" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video><source src="{{ $openingVideoMobile }}"></video>
                        @else
                            <video class="fsb-hero__video" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video><source src="{{ $openingVideo }}"></video>
                        @endif
                    @endif
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
                        <p class="fsb-hero__text">{!! nl2br(e($opening_text)) !!}</p>
                    @else
                        <p class="fsb-hero__text">Dengan menyebut nama Allah SWT, kami mengundang Anda untuk hadir dan memberikan doa restu di hari bahagia kami!</p>
                    @endif
                    @if ($primary_event)
                        <div class="fsb-tag">🗓️ {{ $primary_event['date'] }}</div>
                    @endif
                </section>

            @elseif ($section === 'hosts' && count($hosts))
                <section data-height="{{ $section_heights['hosts'] ?? 'full' }}" class="fsb-section" id="hosts" aria-labelledby="hosts-title">
                    <div class="fsb-section-header">
                        <span class="fsb-badge">{{ $labels['hosts_eyebrow'] }} 👑</span>
                        <h2 id="hosts-title">{{ $labels['hosts_title'] }}</h2>
                        <p>Dua sejoli yang akhirnya bakal sah di pelaminan!</p>
                    </div>
                    <div class="fsb-hosts" data-count="{{ count($hosts) }}">
                        @foreach ($hosts as $host)
                            <article class="fsb-host" data-host>
                                <button type="button" class="host-card__trigger fsb-host__trigger" data-host-open data-host-name="{{ $host['name'] }}" data-host-role="{{ match ($host['role']) { 'groom' => 'Mempelai Pria', 'bride' => 'Mempelai Wanita', default => 'Mempelai' } }}" aria-haspopup="dialog" aria-label="Lihat profil {{ $host['name'] }}">
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
                                </button>
                                <div class="fsb-host__details">
                                    <h3 class="fsb-host__name">{{ $host['name'] }}</h3>
                                    @if ($host['family'])
                                        <p class="fsb-host__family">
                                            <small>{{ match ($host['role']) { 'groom' => 'Putra kesayangan dari', 'bride' => 'Putri tercinta dari', default => 'Putra/putri dari' } }}</small>
                                            <strong>{{ $host['family'] }}</strong>
                                        </p>
                                    @endif
                                    @if ($host['instagram'])
                                        <a class="fsb-btn fsb-btn--outline fsb-btn--sm" href="{{ $host['instagram'] }}" target="_blank" rel="noopener noreferrer">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>
                                            @<span>{{ Str::after($host['instagram'], 'instagram.com/') ?: 'Instagram' }}</span>
                                        </a>
                                    @endif
                                    <div class="host-card__details" data-host-details>
                                        @if ($host['birth_order'])
                                            <span class="fsb-host__order">{{ $host['birth_order'] }}</span>
                                        @endif
                                        @if ($host['bio'])
                                            <p class="fsb-host__bio">"{!! nl2br(e($host['bio'])) !!}"</p>
                                        @endif
                                    </div>
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
                <section data-height="{{ $section_heights['events'] ?? 'full' }}" class="fsb-section fsb-section--tint" id="events" aria-labelledby="events-title">
                    <div class="fsb-section-header">
                        <span class="fsb-badge">{{ $labels['events_eyebrow'] }} 📌</span>
                        <h2 id="events-title">{{ $labels['events_title'] }}</h2>
                        <p>Jangan sampai salah kostum apalagi salah tanggal ya!</p>
                    </div>
                    <div class="fsb-events">
                        @foreach ($events as $event)
                            <article class="fsb-event">
                                <div class="fsb-event__header">
                                    <span class="fsb-badge fsb-badge--accent">{{ $event['label'] }}</span>
                                    <h3>{{ $event['date'] }}</h3>
                                    @if ($event['start_time'])
                                        <p class="fsb-event__time">⏰ {{ $event['start_time'] }}{{ $event['end_time'] ? ' - '.$event['end_time'] : ' s/d Selesai' }}{{ ($theme['hide_timezone'] ?? false) ? '' : ' '.$event['timezone_label'] }}</p>
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
                                    @if ($event['address'])
                                        <button type="button" class="fsb-btn fsb-btn--outline" data-copy="{{ $event['address'] }}">📋 Salin Alamat</button>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>

            @elseif ($section === 'countdown' && $primary_event && $primary_event['timestamp'])
                <section data-height="{{ $section_heights['countdown'] ?? 'full' }}" class="fsb-section fsb-countdown" data-countdown="{{ $primary_event['timestamp'] }}" aria-label="Hitung mundur menuju hari bahagia">
                    <div class="fsb-section-header">
                        <span class="fsb-badge">{{ $labels['countdown_eyebrow'] }} ⏳</span>
                        <h2>{{ $labels['countdown_title'] }}</h2>
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
                <section data-height="{{ $section_heights['map'] ?? 'full' }}" class="fsb-section" aria-labelledby="map-title">
                    <div class="fsb-section-header">
                        <span class="fsb-badge">{{ $labels['map_eyebrow'] }} 🧭</span>
                        <h2 id="map-title">{{ $labels['map_title'] }}</h2>
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
                <section data-height="{{ $section_heights['story'] ?? 'full' }}" class="fsb-section fsb-section--tint" id="story" aria-labelledby="story-title">
                    <div class="fsb-section-header">
                        <span class="fsb-badge">{{ $labels['story_eyebrow'] }} 📖</span>
                        <h2 id="story-title">{{ $labels['story_title'] }}</h2>
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
                                    <p>{!! nl2br(e($story['body'])) !!}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>

            @elseif ($section === 'gallery' && count($gallery))
                <section data-height="{{ $section_heights['gallery'] ?? 'full' }}" class="fsb-section" id="gallery" aria-labelledby="gallery-title">
                    <div class="fsb-section-header">
                        <span class="fsb-badge">{{ $labels['gallery_eyebrow'] }} 📸</span>
                        <h2 id="gallery-title">{{ $labels['gallery_title'] }}</h2>
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

            @elseif (str_starts_with($section, 'blocks') && ! empty($block_sections[$section] ?? $blocks))
                <div class="fsb-section-wrapper">
                    @include('invitations.shared.blocks', ['blocks' => $block_sections[$section] ?? $blocks])
                </div>

            @elseif ($section === 'rsvp')
                @if ($theme['merge_rsvp_guestbook'] ?? false)
                    <div class="fsb-section-wrapper">@include('invitations.shared.confirmation')</div>
                @else
                    <div class="fsb-section-wrapper" id="rsvp">
                        @include('invitations.shared.rsvp')
                    </div>
                @endif

            @elseif ($section === 'guestbook')
                @unless ($theme['merge_rsvp_guestbook'] ?? false)
                    <div class="fsb-section-wrapper" id="guestbook">
                        @include('invitations.shared.guestbook')
                    </div>
                @endunless

            @elseif ($section === 'gifts' && count($gifts))
                <section data-height="{{ $section_heights['gifts'] ?? 'full' }}" class="fsb-section fsb-section--tint" aria-labelledby="gifts-title">
                    <div class="fsb-section-header">
                        <span class="fsb-badge">{{ $labels['gifts_eyebrow'] }} 🎁</span>
                        <h2 id="gifts-title">{{ $labels['gifts_title'] }}</h2>
                        <p>Kehadiran dan doa kalian adalah hadiah terbaik! Tapi kalau mau kirim kado, boleh banget kok 😆</p>
                    </div>
                    @include('invitations.shared.gifts')
                </section>

            @elseif ($section === 'contacts' && count($contacts))
                <section data-height="{{ $section_heights['contacts'] ?? 'full' }}" class="fsb-section" aria-labelledby="contacts-title">
                    <div class="fsb-section-header">
                        <span class="fsb-badge">{{ $labels['contacts_eyebrow'] }} 📞</span>
                        <h2 id="contacts-title">{{ $labels['contacts_title'] }}</h2>
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
                <section data-height="{{ $section_heights['sharing'] ?? 'full' }}" class="fsb-section fsb-section--tint">
                    <div class="fsb-section-header">
                        <span class="fsb-badge">{{ $labels['sharing_eyebrow'] }} 💌</span>
                        <h2>{{ $labels['sharing_title'] }}</h2>
                        <p>Bantu sebarkan kabar bahagia ini ke temen-temen dan grup alumni kamu ya!</p>
                    </div>
                    <div class="fsb-actions fsb-actions--center">
                        <button type="button" class="fsb-btn fsb-btn--primary" data-share data-share-url="{{ $share_url }}">🚀 Bagikan Link</button>
                        <a class="fsb-btn fsb-btn--secondary" href="{{ $whatsapp_url }}" target="_blank" rel="noopener noreferrer">💬 Kirim via WhatsApp</a>
                    </div>
                </section>

            @elseif ($section === 'closing')
                <section data-height="{{ $section_heights['closing'] ?? 'full' }}" class="fsb-section fsb-closing">
                    <div class="fsb-speech-bubble">
                        <span>Sampai Jumpa Di Hari Bahagia Kami! 👋❤️</span>
                    </div>
                    <h2>{{ $displayNames }}</h2>
                    @if ($closing_message)
                        <p>{!! nl2br(e($closing_message)) !!}</p>
                    @endif
                    @include('invitations.shared.closing-families')
                    @if ($closing_footer)
                        <p>{!! nl2br(e($closing_footer)) !!}</p>
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
    @include('invitations.shared.host-dialog')
</body>
</html>

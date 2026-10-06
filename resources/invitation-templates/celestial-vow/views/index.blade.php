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
    <link href="https://fonts.googleapis.com/css2?family=Marcellus&family=Manrope:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    {{-- Marks that scripting is available before the first paint. --}}
    <script>document.documentElement.classList.add('js-ready');</script>
    @vite(['resources/css/invitations.css', 'resources/js/invitation-hosts.js', 'resources/js/invitation-forms.js', 'resources/js/invitation-lightbox.js', 'resources/invitation-templates/celestial-vow/assets/theme.css', 'resources/invitation-templates/celestial-vow/assets/theme.js'])
</head>
<body class="celestial-vow" style="--cel-accent: {{ $theme['accent_color'] }}; --cel-focal-x: {{ $theme['cover_focal_x'] }}%; --cel-focal-y: {{ $theme['cover_focal_y'] }}%; --cel-overlay: {{ $theme['cover_overlay_opacity'] / 100 }}" data-motion="{{ $theme['motion'] }}">
    @php
        $navItems = [
            'hosts' => ['id' => 'hosts', 'label' => 'Mempelai'],
            'events' => ['id' => 'events', 'label' => 'Acara'],
            'story' => ['id' => 'story', 'label' => 'Kisah'],
            'gallery' => ['id' => 'gallery', 'label' => 'Galeri'],
            'rsvp' => ['id' => 'rsvp', 'label' => 'RSVP'],
        ];
        // A dot only earns its place when the section it points at will render.
        $hasContent = ['hosts' => count($hosts), 'events' => count($events), 'story' => count($stories), 'gallery' => count($gallery), 'rsvp' => true];
        $visibleNav = collect($sections)->filter(fn ($section) => isset($navItems[$section]) && ($hasContent[$section] ?? true))->mapWithKeys(fn ($section) => [$section => $navItems[$section]])->all();
        $groomName = collect($hosts)->firstWhere('role', 'groom')['name'] ?? ($hosts[0]['name'] ?? null);
        $brideName = collect($hosts)->firstWhere('role', 'bride')['name'] ?? ($hosts[1]['name'] ?? null);
        $monogram = $groomName && $brideName
            ? mb_strtoupper(mb_substr($groomName, 0, 1).mb_substr($brideName, 0, 1))
            : mb_strtoupper(mb_substr($title, 0, 2));
        $coverImage = $theme['cover_poster_image'] ?: ($gallery[0]['url'] ?? ($hosts[0]['photo_url'] ?? null));
        $coverDesktop = $theme['cover_video_enabled'] ? $theme['cover_video_desktop'] : null;
        $coverMobile = $theme['cover_video_enabled'] ? ($theme['cover_video_mobile'] ?: $coverDesktop) : null;
        // The opening section reuses the cover video whenever one is uploaded.
        $openingVideo = $coverDesktop;
        $openingVideoMobile = $coverMobile;
    @endphp

    <div class="cel-sky" aria-hidden="true">
        <span class="cel-sky__layer cel-sky__layer--far" data-parallax="0.06"></span>
        <span class="cel-sky__layer cel-sky__layer--near" data-parallax="0.14"></span>
    </div>

    <div class="cel-cover" id="opening-cover">
        @if ($coverImage)<img class="cel-cover__poster" src="{{ $coverImage }}" alt="" aria-hidden="true">@endif
        @if ($coverDesktop)
            <video class="cel-cover__video cel-cover__video--desktop" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video>
                <source src="{{ $coverDesktop }}">
            </video>
        @endif
        @if ($coverMobile && $coverMobile !== $coverDesktop)
            <video class="cel-cover__video cel-cover__video--mobile" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video>
                <source src="{{ $coverMobile }}">
            </video>
        @endif
        <div class="cel-cover__shade" aria-hidden="true"></div>
        <div class="cel-cover__stars" aria-hidden="true"></div>
        <svg class="cel-cover__constellation" viewBox="0 0 420 320" fill="none" aria-hidden="true">
            <g class="cel-constellation__lines" stroke="currentColor" stroke-width="1.1" stroke-linecap="round">
                <path class="cel-line" pathLength="1" d="M52 214 L146 132 L236 182 L330 96 L368 168" />
                <path class="cel-line" pathLength="1" d="M146 132 L196 232 L236 182" />
                <path class="cel-line" pathLength="1" d="M330 96 L286 44" />
            </g>
            <g class="cel-constellation__stars" fill="currentColor">
                <circle cx="52" cy="214" r="2.6" />
                <circle cx="146" cy="132" r="3.4" />
                <circle cx="196" cy="232" r="2.2" />
                <circle cx="236" cy="182" r="3" />
                <circle cx="286" cy="44" r="2.2" />
                <circle cx="330" cy="96" r="3.6" />
                <circle cx="368" cy="168" r="2.4" />
            </g>
        </svg>
        <div class="cel-cover__inner">
            <span class="invitation-eyebrow">{{ $labels['cover_eyebrow'] }}</span>
            @if ($groomName && $brideName)
                <h1 class="cel-couple"><span>{{ $groomName }}</span><i>&amp;</i><span>{{ $brideName }}</span></h1>
            @else
                <h1>{{ $title }}</h1>
            @endif
            @if ($primary_event)<span class="cel-cover__date">{{ $primary_event['date'] }}</span>@endif
            @if ($primary_event && $primary_event['timestamp'])
                <div class="cel-cover__countdown" data-countdown="{{ $primary_event['timestamp'] }}" aria-label="Hitung mundur menuju acara">
                    <span class="cel-cover__countdown-label">{{ $labels['cover_countdown_label'] }}</span>
                    <div data-countdown-output>
                        @foreach (['days' => 'Hari', 'hours' => 'Jam', 'minutes' => 'Menit', 'seconds' => 'Detik'] as $unit => $label)
                            <span><strong data-countdown-unit="{{ $unit }}">00</strong><small>{{ $label }}</small></span>
                        @endforeach
                    </div>
                </div>
            @endif
            @if ($recipient)
                <div class="cel-cover__recipient">
                    <span>{{ $labels['cover_recipient_label'] }}</span>
                    <strong>{{ $recipient }}</strong>
                </div>
            @endif
            <a class="cel-cover__cta" href="#celestial-content" data-open-invitation>{{ $labels['cover_cta'] }}</a>
        </div>
    </div>

    <a class="cel-mark" href="#celestial-content" aria-label="Kembali ke awal">{{ $monogram }}</a>

    <nav class="cel-dots" aria-label="Navigasi undangan">
        <a class="cel-dot cel-dot--home" href="#celestial-content" aria-label="Awal"><span aria-hidden="true"></span></a>
        @foreach ($visibleNav as $item)
            <a class="cel-dot" href="#{{ $item['id'] }}" data-dot-link="{{ $item['id'] }}" aria-label="{{ $item['label'] }}"><span aria-hidden="true"></span></a>
        @endforeach
    </nav>

    <main id="celestial-content" tabindex="-1" data-gate>
        @foreach ($sections as $section)
            @if ($section === 'opening')
                <section class="invitation-section cel-hero" data-height="{{ $section_heights['opening'] ?? 'full' }}" aria-labelledby="cel-opening-title">
                    @if ($openingVideo)
                        @if ($openingVideoMobile !== $openingVideo)
                            <video class="cel-hero__video cel-hero__video--desktop" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video><source src="{{ $openingVideo }}"></video>
                            <video class="cel-hero__video cel-hero__video--mobile" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video><source src="{{ $openingVideoMobile }}"></video>
                        @else
                            <video class="cel-hero__video" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video><source src="{{ $openingVideo }}"></video>
                        @endif
                    @endif
                    <span class="invitation-eyebrow" data-reveal>{{ $labels['opening_eyebrow'] }}</span>
                    @if (count($hosts) >= 2)
                        <h2 id="cel-opening-title" class="cel-couple" data-reveal><span>{{ $hosts[0]['name'] }}</span><i>&amp;</i><span>{{ $hosts[1]['name'] }}</span></h2>
                    @else
                        <h2 id="cel-opening-title" data-reveal>{{ $title }}</h2>
                    @endif
                    @if ($opening_text)<p data-reveal>{!! nl2br(e($opening_text)) !!}</p>@endif
                    @if ($primary_event)<p class="cel-hero__date" data-reveal>{{ $primary_event['date'] }}</p>@endif
                </section>
            @elseif ($section === 'hosts' && count($hosts))
                <section class="invitation-section cel-hosts" id="hosts" data-height="{{ $section_heights['hosts'] ?? 'full' }}" aria-labelledby="cel-hosts-title">
                    <span class="invitation-eyebrow" data-reveal>{{ $labels['hosts_eyebrow'] }}</span>
                    <h2 id="cel-hosts-title" data-reveal>{{ $labels['hosts_title'] }}</h2>
                    <div class="cel-hosts__grid" data-count="{{ count($hosts) }}">
                        @foreach ($hosts as $host)
                            <article class="cel-host" data-host data-reveal>
                                <button type="button" class="host-card__trigger cel-host__trigger" data-host-open data-host-name="{{ $host['name'] }}" data-host-role="{{ match ($host['role']) { 'groom' => 'Mempelai Pria', 'bride' => 'Mempelai Wanita', default => 'Mempelai' } }}" aria-haspopup="dialog" aria-label="Lihat profil {{ $host['name'] }}">
                                    <div class="cel-host__portrait">
                                        @if ($host['photo_url'])<img src="{{ $host['photo_url'] }}" alt="Foto {{ $host['name'] }}" loading="lazy" decoding="async">@else<span aria-hidden="true">{{ mb_substr($host['name'], 0, 1) }}</span>@endif
                                    </div>
                                </button>
                                <small class="cel-host__role">{{ match ($host['role']) { 'groom' => 'Mempelai Pria', 'bride' => 'Mempelai Wanita', default => 'Mempelai' } }}</small>
                                <h3>{{ $host['name'] }}</h3>
                                @if ($host['family'])<p class="cel-host__parents"><small>{{ match ($host['role']) { 'groom' => 'Putra dari', 'bride' => 'Putri dari', default => 'Putra/putri dari' } }}</small><span>{{ $host['family'] }}</span></p>@endif
                                @if ($host['instagram'])<a class="cel-host__social" href="{{ $host['instagram'] }}" target="_blank" rel="noopener noreferrer">Instagram</a>@endif
                                <div class="host-card__details cel-host__details" data-host-details>
                                    @if ($host['birth_order'])<p>{{ $host['birth_order'] }}</p>@endif
                                    @if ($host['bio'])<p>{!! nl2br(e($host['bio'])) !!}</p>@endif
                                </div>
                            </article>
                            @if (count($hosts) === 2 && ! $loop->last)<span class="cel-hosts__and" aria-hidden="true">&amp;</span>@endif
                        @endforeach
                    </div>
                </section>
            @elseif ($section === 'events' && count($events))
                <section class="invitation-section cel-events" id="events" data-height="{{ $section_heights['events'] ?? 'full' }}" aria-labelledby="cel-events-title">
                    <span class="invitation-eyebrow" data-reveal>{{ $labels['events_eyebrow'] }}</span>
                    <h2 id="cel-events-title" data-reveal>{{ $labels['events_title'] }}</h2>
                    <div class="cel-events__list">
                        @foreach ($events as $event)
                            <article class="cel-event" data-reveal>
                                <div class="cel-event__head">
                                    <h3>{{ $event['label'] }}</h3>
                                    <p class="cel-event__date">{{ $event['date'] }}</p>
                                    @if ($event['start_time'])<p class="cel-event__time">{{ $event['start_time'] }}{{ $event['end_time'] ? ' – '.$event['end_time'] : ' s/d Selesai' }}{{ ($theme['hide_timezone'] ?? false) ? '' : ' '.$event['timezone_label'] }}</p>@endif
                                </div>
                                @if ($event['venue'] || $event['address'])
                                    <div class="cel-event__venue">
                                        @if ($event['venue'])<strong>{{ $event['venue'] }}</strong>@endif
                                        @if ($event['address'])<p>{{ $event['address'] }}</p>@endif
                                    </div>
                                @endif
                                <div class="cel-actions">
                                    @if (in_array('map', $sections) && $event['directions_url'])<a href="{{ $event['directions_url'] }}" target="_blank" rel="noopener noreferrer">Petunjuk Arah</a>@endif
                                    @if (in_array('map', $sections) && $event['address'])<button type="button" data-copy="{{ $event['address'] }}">Salin Alamat</button>@endif
                                </div>
                                @if (count($event['notes']))<div class="cel-event__notes">@foreach ($event['notes'] as $note)<small>{!! nl2br(e($note)) !!}</small>@endforeach</div>@endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @elseif ($section === 'countdown' && $primary_event && $primary_event['timestamp'])
                <section class="invitation-section cel-countdown" data-height="{{ $section_heights['countdown'] ?? 'full' }}" data-countdown="{{ $primary_event['timestamp'] }}" aria-labelledby="cel-countdown-title">
                    <span class="cel-moon" aria-hidden="true"></span>
                    <span class="invitation-eyebrow" data-reveal>{{ $labels['countdown_eyebrow'] }}</span>
                    <h2 id="cel-countdown-title" data-reveal>{{ $labels['countdown_title'] }}</h2>
                    <div class="cel-countdown__units" data-countdown-output>
                        @foreach (['days' => 'Hari', 'hours' => 'Jam', 'minutes' => 'Menit', 'seconds' => 'Detik'] as $unit => $label)
                            <span><strong data-countdown-unit="{{ $unit }}">00</strong><small>{{ $label }}</small></span>
                        @endforeach
                    </div>
                </section>
            @elseif ($section === 'map' && $primary_event && $primary_event['map_embed_url'])
                <section class="invitation-section cel-map" id="map" data-height="{{ $section_heights['map'] ?? 'full' }}" aria-labelledby="cel-map-title">
                    <span class="invitation-eyebrow" data-reveal>{{ $labels['map_eyebrow'] }}</span>
                    <h2 id="cel-map-title" data-reveal>{{ $labels['map_title'] }}</h2>
                    <div class="cel-map__frame" data-reveal><iframe src="{{ $primary_event['map_embed_url'] }}" title="Peta {{ $primary_event['venue'] ?: $primary_event['label'] }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe></div>
                    @if ($primary_event['address'])<p class="cel-map__address">{{ $primary_event['address'] }}</p>@endif
                    <div class="cel-actions cel-actions--center">
                        @if ($primary_event['directions_url'])<a href="{{ $primary_event['directions_url'] }}" target="_blank" rel="noopener noreferrer">Buka Google Maps</a>@endif
                        @if ($primary_event['address'])<button type="button" data-copy="{{ $primary_event['address'] }}">Salin Alamat</button>@endif
                    </div>
                </section>
            @elseif ($section === 'story' && count($stories))
                <section class="invitation-section cel-story" id="story" data-height="{{ $section_heights['story'] ?? 'full' }}" aria-labelledby="cel-story-title">
                    <span class="invitation-eyebrow" data-reveal>{{ $labels['story_eyebrow'] }}</span>
                    <h2 id="cel-story-title" data-reveal>{{ $labels['story_title'] }}</h2>
                    <ol class="cel-story__list">
                        @foreach ($stories as $story)
                            <li data-reveal>
                                @if ($story['image_url'])<img src="{{ $story['image_url'] }}" alt="{{ $story['title'] }}" loading="lazy" decoding="async">@endif
                                <small>{{ $story['date'] }}</small>
                                <h3>{{ $story['title'] }}</h3>
                                @if ($story['body'])<p>{!! nl2br(e($story['body'])) !!}</p>@endif
                            </li>
                        @endforeach
                    </ol>
                </section>
            @elseif ($section === 'gallery' && count($gallery))
                <section class="invitation-section cel-gallery" id="gallery" data-height="{{ $section_heights['gallery'] ?? 'full' }}" aria-labelledby="cel-gallery-title">
                    <span class="invitation-eyebrow" data-reveal>{{ $labels['gallery_eyebrow'] }}</span>
                    <h2 id="cel-gallery-title" data-reveal>{{ $labels['gallery_title'] }}</h2>
                    <div class="cel-gallery__grid">
                        @foreach ($gallery as $image)
                            <figure data-reveal>
                                <button type="button" data-lightbox-src="{{ $image['url'] }}" data-lightbox-alt="{{ $image['alt'] }}"><img src="{{ $image['url'] }}" alt="{{ $image['alt'] }}" loading="lazy" decoding="async"></button>
                                @if ($image['caption'])<figcaption>{{ $image['caption'] }}</figcaption>@endif
                            </figure>
                        @endforeach
                    </div>
                </section>
            @elseif (str_starts_with($section, 'blocks') && ! empty($block_sections[$section] ?? $blocks))
                <div class="cel-shared">@include('invitations.shared.blocks', ['blocks' => $block_sections[$section] ?? $blocks])</div>
            @elseif ($section === 'rsvp')
                @if ($theme['merge_rsvp_guestbook'] ?? false)
                    <div class="cel-shared">@include('invitations.shared.confirmation')</div>
                @else
                    <div class="cel-shared" id="rsvp">@include('invitations.shared.rsvp')</div>
                @endif
            @elseif ($section === 'guestbook')
                @unless ($theme['merge_rsvp_guestbook'] ?? false)
                    <div class="cel-shared">@include('invitations.shared.guestbook')</div>
                @endunless
            @elseif ($section === 'gifts' && count($gifts))
                <section class="invitation-section cel-gifts" data-height="{{ $section_heights['gifts'] ?? 'full' }}" aria-labelledby="cel-gifts-title">
                    <span class="invitation-eyebrow" data-reveal>{{ $labels['gifts_eyebrow'] }}</span>
                    <h2 id="cel-gifts-title" data-reveal>{{ $labels['gifts_title'] }}</h2>
                    <p>{!! nl2br(e($labels['gifts_intro'])) !!}</p>
                    @include('invitations.shared.gifts')
                </section>
            @elseif ($section === 'contacts' && count($contacts))
                <section class="invitation-section cel-contacts" data-height="{{ $section_heights['contacts'] ?? 'full' }}" aria-labelledby="cel-contacts-title">
                    <span class="invitation-eyebrow" data-reveal>{{ $labels['contacts_eyebrow'] }}</span>
                    <h2 id="cel-contacts-title" data-reveal>{{ $labels['contacts_title'] }}</h2>
                    <p>{!! nl2br(e($labels['contacts_intro'])) !!}</p>
                    <div class="cel-contacts__grid">
                        @foreach ($contacts as $contact)
                            <article class="cel-contact" data-reveal>
                                <small>{{ $contact['label'] }}</small>
                                <h3>{{ $contact['name'] }}</h3>
                                <div class="cel-actions cel-actions--center">
                                    <a href="{{ $contact['whatsapp_url'] }}" target="_blank" rel="noopener noreferrer">WhatsApp</a>
                                    <a href="{{ $contact['phone_url'] }}">Telepon</a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @elseif ($section === 'livestream' && $livestream_url)
                <section class="invitation-section cel-livestream" data-height="{{ $section_heights['livestream'] ?? 'full' }}">
                    <h2>{{ $livestream_label }}</h2>
                    <div class="cel-actions cel-actions--center"><a href="{{ $livestream_url }}" target="_blank" rel="noopener noreferrer">{{ $livestream_label }}</a></div>
                </section>
            @elseif ($section === 'sharing')
                <section class="invitation-section cel-sharing" data-height="{{ $section_heights['sharing'] ?? 'full' }}" aria-labelledby="cel-sharing-title">
                    <span class="invitation-eyebrow" data-reveal>{{ $labels['sharing_eyebrow'] }}</span>
                    <h2 id="cel-sharing-title" data-reveal>{{ $labels['sharing_title'] }}</h2>
                    <p>{!! nl2br(e($labels['sharing_intro'])) !!}</p>
                    <div class="cel-actions cel-actions--center">
                        <button type="button" data-share data-share-url="{{ $share_url }}">Bagikan Sekarang</button>
                        <a href="{{ $whatsapp_url }}" target="_blank" rel="noopener noreferrer">Kirim via WhatsApp</a>
                    </div>
                </section>
            @elseif ($section === 'closing')
                <section class="invitation-section cel-closing" data-height="{{ $section_heights['closing'] ?? 'full' }}">
                    <span class="cel-closing__glyph" aria-hidden="true">✶</span>
                    <span class="invitation-eyebrow" data-reveal>{{ $labels['closing_eyebrow'] }}</span>
                    <h2 data-reveal>{{ $couple_title }}</h2>
                    @if ($closing_message)<p data-reveal>{!! nl2br(e($closing_message)) !!}</p>@endif
                    @include('invitations.shared.closing-families')
                    @if ($closing_footer)<p data-reveal>{!! nl2br(e($closing_footer)) !!}</p>@endif
                    <p class="cel-closing__kicker" data-reveal>{{ $labels['closing_kicker'] }}</p>
                </section>
            @endif
        @endforeach
    </main>

    @if ($music_url)
        <audio data-music loop preload="none" src="{{ $music_url }}"></audio>
        <button class="cel-music" type="button" data-music-toggle aria-label="Putar musik"><span aria-hidden="true">♪</span></button>
    @endif
    <dialog class="cel-lightbox" data-lightbox><button type="button" data-lightbox-close aria-label="Tutup galeri">Tutup</button><img data-lightbox-image alt=""></dialog>
    @include('invitations.shared.host-dialog')
</body>
</html>

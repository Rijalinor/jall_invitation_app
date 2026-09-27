<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $title }}">
    <title>{{ $title }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600&family=Playfair+Display:ital,wght@0,500;1,500&display=swap" rel="stylesheet">
    {{-- Marks that scripting is available before the first paint. --}}
    <script>document.documentElement.classList.add('js-ready');</script>
    @vite(['resources/invitation-templates/paper-lantern/assets/theme.css', 'resources/invitation-templates/paper-lantern/assets/theme.js'])
    <noscript><style>[data-reveal] { opacity: 1 !important; transform: none !important; clip-path: none !important; }</style></noscript>
</head>
<body class="paper-lantern" style="--pl-accent: {{ $theme['accent_color'] }}; --pl-focal-x: {{ $theme['cover_focal_x'] }}%; --pl-focal-y: {{ $theme['cover_focal_y'] }}%; --pl-overlay: {{ $theme['cover_overlay_opacity'] / 100 }};" data-motion="{{ $theme['motion'] }}">
    @php
        $coverImage = $theme['cover_poster_image'] ?: ($gallery[0]['url'] ?? ($hosts[0]['photo_url'] ?? null));
        $coverDesktop = $theme['cover_video_enabled'] ? $theme['cover_video_desktop'] : null;
        $coverMobile = $theme['cover_video_enabled'] ? ($theme['cover_video_mobile'] ?: $coverDesktop) : null;
        $groom = collect($hosts)->firstWhere('role', 'groom')['name'] ?? ($hosts[0]['name'] ?? null);
        $bride = collect($hosts)->firstWhere('role', 'bride')['name'] ?? ($hosts[1]['name'] ?? null);
        $couple = $groom && $bride ? [$groom, $bride] : [$title];
        $nav = [
            'events' => ['id' => 'agenda', 'label' => 'Acara'],
            'hosts' => ['id' => 'people', 'label' => 'Mempelai'],
            'story' => ['id' => 'story', 'label' => 'Cerita'],
            'gallery' => ['id' => 'gallery', 'label' => 'Galeri'],
            'rsvp' => ['id' => 'rsvp', 'label' => 'RSVP'],
        ];
        $hasContent = [
            'events' => count($events) > 0,
            'hosts' => count($hosts) > 0,
            'story' => count($stories) > 0,
            'gallery' => count($gallery) > 0,
            'rsvp' => in_array('rsvp', $sections, true),
        ];
        $navigation = collect($sections)
            ->filter(fn ($section) => isset($nav[$section]) && ($hasContent[$section] ?? false))
            ->mapWithKeys(fn ($section) => [$section => $nav[$section]])
            ->all();
        $plIndex = 0;
    @endphp

    <div class="pl-cover" data-cover>
        @if ($coverImage)<img class="pl-cover__media" src="{{ $coverImage }}" alt="" aria-hidden="true" fetchpriority="high">@endif
        @if ($coverDesktop)<video class="pl-cover__media pl-cover__video pl-cover__video--desktop" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video><source src="{{ $coverDesktop }}"></video>@endif
        @if ($coverMobile)<video class="pl-cover__media pl-cover__video pl-cover__video--mobile" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video><source src="{{ $coverMobile }}"></video>@endif
        <div class="pl-cover__veil" aria-hidden="true"></div>
        <div class="pl-cover__stamp"><span>JALL / {{ date('Y') }}</span><i aria-hidden="true">✦</i><span>Private invitation</span></div>
        <div class="pl-cover__copy">
            <p class="pl-label">A celebration of two lives</p>
            <h1>@foreach ($couple as $name)<span>{{ $name }}</span>@if (!$loop->last)<em>&amp;</em>@endif @endforeach</h1>
            @if ($primary_event)<p class="pl-cover__date">{{ $primary_event['date'] }}@if ($primary_event['start_time']) · {{ $primary_event['start_time'] }}@endif</p>@endif
            @if ($primary_event && $primary_event['timestamp'])
                <div class="pl-cover__timer" data-countdown="{{ $primary_event['timestamp'] }}" aria-label="Hitung mundur menuju acara">
                    <span>Next chapter</span>
                    <div data-countdown-output>@foreach (['days' => 'hari', 'hours' => 'jam', 'minutes' => 'mnt', 'seconds' => 'dtk'] as $unit => $label)<b><strong data-countdown-unit="{{ $unit }}">00</strong><small>{{ $label }}</small></b>@endforeach</div>
                </div>
            @endif
            <div class="pl-cover__welcome"><span>Kepada Yth.</span><strong>{{ $recipient }}</strong></div>
            <a class="pl-button" href="#top" data-open-invitation><span>Buka undangan</span><i aria-hidden="true">↗</i></a>
        </div>
    </div>

    <header class="pl-rail" aria-label="Navigasi undangan" data-gate>
        <a href="#top" class="pl-brand" aria-label="Ke awal">PL</a>
        <nav>@foreach ($navigation as $item)<a href="#{{ $item['id'] }}"><span aria-hidden="true">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>{{ $item['label'] }}</a>@endforeach @if (in_array('sharing', $sections))<button type="button" data-share data-share-url="{{ $share_url }}"><span aria-hidden="true">↗</span><span data-share-label>Bagikan</span></button>@endif</nav>
        <span class="pl-rail__line" aria-hidden="true"></span>
    </header>
    @if (count($navigation))<nav class="pl-mobile-nav" aria-label="Navigasi cepat" data-gate>@foreach ($navigation as $item)<a href="#{{ $item['id'] }}">{{ $item['label'] }}</a>@endforeach</nav>@endif

    <main id="top" tabindex="-1" data-gate>
        @foreach ($sections as $section)
            @if ($section === 'opening')
                <section class="pl-opening pl-band" aria-labelledby="opening-title">
                    <div class="pl-band__index">{{ str_pad(++$plIndex, 2, '0', STR_PAD_LEFT) }} <span>Opening note</span></div>
                    <div class="pl-opening__title">
                        <p class="pl-label" data-reveal="mask">{{ $primary_event['date'] ?? 'The beginning of forever' }}</p>
                        <h2 id="opening-title" data-reveal="mask">@foreach ($couple as $name)<span>{{ $name }}</span>@if (!$loop->last)<em>&amp;</em>@endif @endforeach</h2>
                        @if ($opening_text)<p data-reveal="rise">{{ $opening_text }}</p>@endif
                    </div>
                    <span class="pl-scroll-mark" aria-hidden="true">Keep scrolling ↓</span>
                </section>
            @elseif ($section === 'events' && count($events))
                <section class="pl-band pl-agenda" id="agenda" aria-labelledby="agenda-title">
                    <div class="pl-band__index">{{ str_pad(++$plIndex, 2, '0', STR_PAD_LEFT) }} <span>When &amp; where</span></div>
                    <header data-reveal="mask"><p class="pl-label">The itinerary</p><h2 id="agenda-title">Meet us in the moment.</h2></header>
                    <div class="pl-events">
                        @foreach ($events as $event)
                            <article data-reveal="slide">
                                <span class="pl-event__number">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <div class="pl-event__main">
                                    <h3>{{ $event['label'] }}</h3>
                                    <p class="pl-large">{{ $event['date'] }}</p>
                                    @if ($event['start_time'])<p class="pl-event__time">{{ $event['start_time'] }}@if ($event['end_time']) – {{ $event['end_time'] }}@endif · {{ $event['timezone'] }}</p>@endif
                                </div>
                                <div class="pl-event__place">
                                    @if ($event['venue'])<strong>{{ $event['venue'] }}</strong>@endif
                                    @if ($event['address'])<p>{{ $event['address'] }}</p>@endif
                                    @if (count($event['notes']))<ul class="pl-event__notes">@foreach ($event['notes'] as $note)<li>{{ $note }}</li>@endforeach</ul>@endif
                                    <div class="pl-actions">
                                        @if (in_array('map', $sections) && $event['directions_url'])<a class="pl-action" href="{{ $event['directions_url'] }}" target="_blank" rel="noopener noreferrer">Petunjuk Arah</a>@endif
                                        @if (in_array('calendar', $sections) && $event['calendar_url'])<a class="pl-action" href="{{ $event['calendar_url'] }}" target="_blank" rel="noopener noreferrer">Google Calendar</a>@endif
                                        @if (in_array('calendar', $sections))<a class="pl-action" href="{{ $event['ics_url'] }}">Unduh ICS</a>@endif
                                        @if ($event['address'])<button class="pl-action" type="button" data-copy="{{ $event['address'] }}">Salin Alamat</button>@endif
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @elseif ($section === 'countdown' && $primary_event && $primary_event['timestamp'])
                <section class="pl-countdown pl-band" data-countdown="{{ $primary_event['timestamp'] }}" aria-label="Hitung mundur menuju hari bahagia">
                    <p class="pl-label" data-reveal="mask">The days between now and then</p>
                    <h2 data-reveal="mask">Almost time.</h2>
                    <div data-countdown-output class="pl-countdown__grid" data-reveal="rise">@foreach (['days' => 'hari', 'hours' => 'jam', 'minutes' => 'menit', 'seconds' => 'detik'] as $unit => $label)<div><strong data-countdown-unit="{{ $unit }}">00</strong><span>{{ $label }}</span></div>@endforeach</div>
                </section>
            @elseif ($section === 'hosts' && count($hosts))
                <section class="pl-band pl-people" id="people" aria-labelledby="people-title">
                    <div class="pl-band__index">{{ str_pad(++$plIndex, 2, '0', STR_PAD_LEFT) }} <span>The people</span></div>
                    <header data-reveal="mask"><p class="pl-label">A little about us</p><h2 id="people-title">Two people,<br><em>one soft place.</em></h2></header>
                    <div class="pl-hosts">
                        @foreach ($hosts as $host)
                            <article data-reveal="rise">
                                <div class="pl-portrait" data-reveal="image">
                                    @if ($host['photo_url'])<img src="{{ $host['photo_url'] }}" alt="Foto {{ $host['name'] }}" loading="lazy" decoding="async">@else<div class="pl-photo-fallback" aria-hidden="true">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</div>@endif
                                </div>
                                <div class="pl-host__copy">
                                    <span class="pl-label">{{ match ($host['role']) { 'groom' => 'Mempelai pria', 'bride' => 'Mempelai wanita', default => 'Host' } }}</span>
                                    <h3>{{ $host['name'] }}</h3>
                                    @if ($host['family'])<p class="pl-host__family">{{ match ($host['role']) { 'groom' => 'Putra', 'bride' => 'Putri', default => 'Putra/putri' } }} dari {{ $host['family'] }}</p>@endif
                                    @if ($host['bio'])<p>{{ $host['bio'] }}</p>@endif
                                    @if ($host['instagram'])<a href="{{ $host['instagram'] }}" target="_blank" rel="noopener noreferrer">Instagram <i aria-hidden="true">↗</i></a>@endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @elseif ($section === 'story' && count($stories))
                <section class="pl-band pl-story" id="story" aria-labelledby="story-title">
                    <div class="pl-band__index">{{ str_pad(++$plIndex, 2, '0', STR_PAD_LEFT) }} <span>Our timeline</span></div>
                    <header data-reveal="mask"><p class="pl-label">The long way around</p><h2 id="story-title">A story worth<br><em>keeping.</em></h2></header>
                    <ol class="pl-timeline">
                        @foreach ($stories as $story)
                            <li class="@if (!$story['image_url']) pl-story__item--text-only @endif" data-reveal="rise">
                                <span class="pl-story__date">{{ $story['date'] }}</span>
                                @if ($story['image_url'])<div class="pl-story__media" data-reveal="image"><img src="{{ $story['image_url'] }}" alt="{{ $story['title'] }}" loading="lazy" decoding="async"></div>@endif
                                <div class="pl-story__copy"><h3>{{ $story['title'] }}</h3>@if ($story['body'])<p>{{ $story['body'] }}</p>@endif</div>
                            </li>
                        @endforeach
                    </ol>
                </section>
            @elseif ($section === 'gallery' && count($gallery))
                <section class="pl-band pl-gallery" id="gallery" aria-labelledby="gallery-title">
                    <div class="pl-band__index">{{ str_pad(++$plIndex, 2, '0', STR_PAD_LEFT) }} <span>In frames</span></div>
                    <header data-reveal="mask"><p class="pl-label">A few favourite frames</p><h2 id="gallery-title">The good<br><em>stuff.</em></h2></header>
                    <div class="pl-gallery__grid">
                        @foreach ($gallery as $image)
                            <figure data-reveal="image">
                                <button type="button" data-lightbox-src="{{ $image['url'] }}" data-lightbox-alt="{{ $image['alt'] }}"><img src="{{ $image['url'] }}" alt="{{ $image['alt'] }}" loading="lazy" decoding="async"></button>
                                @if ($image['caption'])<figcaption>{{ $image['caption'] }}</figcaption>@endif
                            </figure>
                        @endforeach
                    </div>
                </section>
            @elseif ($section === 'map' && $primary_event && $primary_event['map_embed_url'])
                <section class="pl-band pl-location" aria-labelledby="location-title">
                    <div class="pl-band__index">{{ str_pad(++$plIndex, 2, '0', STR_PAD_LEFT) }} <span>Find us here</span></div>
                    <header data-reveal="mask"><p class="pl-label">The coordinates</p><h2 id="location-title">Come as you are.</h2></header>
                    <div class="pl-map" data-reveal="image"><iframe src="{{ $primary_event['map_embed_url'] }}" title="Peta {{ $primary_event['venue'] ?: $primary_event['label'] }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe></div>
                    @if ($primary_event['address'])<p class="pl-location__address">{{ $primary_event['address'] }}</p>@endif
                    <div class="pl-actions">
                        @if ($primary_event['directions_url'])<a class="pl-action" href="{{ $primary_event['directions_url'] }}" target="_blank" rel="noopener noreferrer">Buka Google Maps</a>@endif
                        @if ($primary_event['address'])<button class="pl-action" type="button" data-copy="{{ $primary_event['address'] }}">Salin Alamat</button>@endif
                    </div>
                </section>
            @elseif ($section === 'rsvp')<div class="pl-shared" id="rsvp">@include('invitations.shared.rsvp')</div>
            @elseif ($section === 'guestbook')<div class="pl-shared">@include('invitations.shared.guestbook')</div>
            @elseif ($section === 'gifts' && count($gifts))
                <section class="pl-band pl-gifts" aria-labelledby="gifts-title">
                    <div class="pl-band__index">{{ str_pad(++$plIndex, 2, '0', STR_PAD_LEFT) }} <span>A little something</span></div>
                    <header data-reveal="mask"><p class="pl-label">Tanda kasih</p><h2 id="gifts-title">With thanks.</h2></header>
                    <div class="pl-gift-list">
                        @foreach ($gifts as $gift)
                            <details>
                                <summary>{{ $gift['type_label'] }} · {{ $gift['provider'] }}</summary>
                                <div class="pl-gift__body">
                                    @if ($gift['account_number'])<div class="pl-gift__row"><span class="pl-label">Nomor rekening</span><strong>{{ $gift['account_number'] }}</strong><button class="pl-action" type="button" data-copy="{{ $gift['account_number'] }}">Salin Nomor</button></div>@endif
                                    @if ($gift['account_name'])<p>a.n. {{ $gift['account_name'] }}</p>@endif
                                    @if ($gift['delivery_address'])<div class="pl-gift__row"><span class="pl-label">Alamat pengiriman</span><p>{{ $gift['delivery_address'] }}</p><button class="pl-action" type="button" data-copy="{{ $gift['delivery_address'] }}">Salin Alamat</button></div>@endif
                                    @if ($gift['notes'])<p>{{ $gift['notes'] }}</p>@endif
                                </div>
                            </details>
                        @endforeach
                    </div>
                </section>
            @elseif ($section === 'livestream' && $livestream_url)
                <section class="pl-band pl-livestream" aria-labelledby="livestream-title">
                    <div class="pl-band__index">Live <span>Join remotely</span></div>
                    <header data-reveal="mask"><p class="pl-label">For those watching from afar</p><h2 id="livestream-title">Be there,<br><em>wherever you are.</em></h2></header>
                    <a class="pl-button pl-button--paper" href="{{ $livestream_url }}" target="_blank" rel="noopener noreferrer"><span>{{ $livestream_label }}</span><i aria-hidden="true">↗</i></a>
                </section>
            @elseif ($section === 'contacts' && count($contacts))
                <section class="pl-band pl-contacts" aria-labelledby="contacts-title">
                    <div class="pl-band__index">{{ str_pad(++$plIndex, 2, '0', STR_PAD_LEFT) }} <span>Say hello</span></div>
                    <header data-reveal="mask"><p class="pl-label">Need anything?</p><h2 id="contacts-title">We are one<br><em>message away.</em></h2></header>
                    <div class="pl-contact-list">
                        @foreach ($contacts as $contact)
                            <a href="{{ $contact['whatsapp_url'] }}" target="_blank" rel="noopener noreferrer"><span>{{ $contact['label'] }}</span><strong>{{ $contact['name'] }}</strong><i>WhatsApp ↗</i></a>
                        @endforeach
                    </div>
                </section>
            @elseif ($section === 'sharing')
                <section class="pl-band pl-sharing" aria-labelledby="sharing-title">
                    <div class="pl-band__index">{{ str_pad(++$plIndex, 2, '0', STR_PAD_LEFT) }} <span>Spread the word</span></div>
                    <header data-reveal="mask"><p class="pl-label">Sebarkan kabar bahagia</p><h2 id="sharing-title">Share the <em>joy.</em></h2></header>
                    <div class="pl-actions">
                        <button class="pl-action pl-action--solid" type="button" data-share data-share-url="{{ $share_url }}"><span data-share-label>Bagikan undangan</span></button>
                        <a class="pl-action" href="{{ $whatsapp_url }}" target="_blank" rel="noopener noreferrer">Kirim via WhatsApp</a>
                    </div>
                </section>
            @elseif ($section === 'closing')
                <section class="pl-closing" aria-labelledby="closing-title">
                    @if ($coverImage)<img class="pl-closing__media" src="{{ $coverImage }}" alt="" aria-hidden="true" loading="lazy" decoding="async">@endif
                    <div class="pl-closing__veil" aria-hidden="true"></div>
                    <p class="pl-label" data-reveal="mask">Until then</p>
                    <h2 id="closing-title" data-reveal="mask">@foreach ($couple as $name)<span>{{ $name }}</span>@if (!$loop->last)<em>&amp;</em>@endif @endforeach</h2>
                    @if ($closing_message)<p data-reveal="rise">{{ $closing_message }}</p>@endif
                    <a href="#top" data-reveal="rise" aria-label="Kembali ke atas">Back to top ↑</a>
                </section>
            @endif
        @endforeach
    </main>

    @if ($music_url)<audio data-music loop preload="none" src="{{ $music_url }}"></audio><button class="pl-music" type="button" data-music-toggle aria-pressed="false" aria-label="Putar musik"><span aria-hidden="true">♪</span></button>@endif
    <dialog class="pl-lightbox" data-lightbox><button type="button" data-lightbox-close aria-label="Tutup galeri">×</button><img data-lightbox-image alt=""></dialog>
</body>
</html>

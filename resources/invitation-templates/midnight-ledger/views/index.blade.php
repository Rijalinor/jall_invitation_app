<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $title }}">
    <title>{{ $title }}</title>
    @include('invitations.shared.og')
    {{-- Marks that scripting is available before the first paint. --}}
    <script>document.documentElement.classList.add('js-ready');</script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500;600&family=Jost:wght@300;400;500;600&display=swap">
    @vite(['resources/css/invitations.css', 'resources/js/invitation-hosts.js', 'resources/js/invitation-forms.js', 'resources/js/invitation-lightbox.js', 'resources/invitation-templates/midnight-ledger/assets/theme.css', 'resources/invitation-templates/midnight-ledger/assets/theme.js'])
</head>
<body class="midnight-ledger" style="--ml-accent: {{ $theme['accent_color'] }}; --ml-focal-x: {{ $theme['cover_focal_x'] }}%; --ml-focal-y: {{ $theme['cover_focal_y'] }}%; --ml-overlay: {{ $theme['cover_overlay_opacity'] / 100 }};" data-motion="{{ $theme['motion'] }}">
    @php
        $navItems = [
            'hosts' => ['id' => 'people', 'label' => 'Mempelai'],
            'events' => ['id' => 'agenda', 'label' => 'Acara'],
            'story' => ['id' => 'cerita', 'label' => 'Cerita'],
            'gallery' => ['id' => 'galeri', 'label' => 'Galeri'],
            'rsvp' => ['id' => 'response', 'label' => 'RSVP'],
        ];
        $visibleNav = collect($sections)->filter(fn ($section) => isset($navItems[$section]))->mapWithKeys(fn ($section) => [$section => $navItems[$section]])->all();
        $groomName = collect($hosts)->firstWhere('role', 'groom')['name'] ?? ($hosts[0]['name'] ?? null);
        $brideName = collect($hosts)->firstWhere('role', 'bride')['name'] ?? ($hosts[1]['name'] ?? null);
        $coverImage = $theme['cover_poster_image'] ?: ($gallery[0]['url'] ?? ($hosts[0]['photo_url'] ?? null));
        $coverDesktop = $theme['cover_video_enabled'] ? $theme['cover_video_desktop'] : null;
        $coverMobile = $theme['cover_video_enabled'] ? ($theme['cover_video_mobile'] ?: $coverDesktop) : null;
        // The opening section reuses the cover video whenever one is uploaded.
        $openingVideo = $coverDesktop;
        $openingVideoMobile = $coverMobile;
    @endphp

    {{-- A marching vine-stitch border that frames the page. --}}
    <div class="ml-frame" aria-hidden="true">
        <span class="ml-frame__edge ml-frame__edge--t"></span>
        <span class="ml-frame__edge ml-frame__edge--r"></span>
        <span class="ml-frame__edge ml-frame__edge--b"></span>
        <span class="ml-frame__edge ml-frame__edge--l"></span>
    </div>

    {{-- Far layer: gold vines that draw themselves with the scroll, behind the
         sections, plus a few leaves drifting slowly. --}}
    <div class="ml-bg" aria-hidden="true">
        <svg class="ml-vine" viewBox="0 0 100 100" preserveAspectRatio="none" focusable="false">
            <path pathLength="1" vector-effect="non-scaling-stroke" d="M8 100 C 26 74, 2 46, 20 22 S 34 4, 28 0"/>
            <path pathLength="1" vector-effect="non-scaling-stroke" d="M92 100 C 74 74, 98 46, 80 22 S 66 4, 72 0"/>
        </svg>
        <span class="ml-leaf ml-leaf--1"></span>
        <span class="ml-leaf ml-leaf--2"></span>
        <span class="ml-leaf ml-leaf--3"></span>
    </div>

    {{-- Near layer: leaves in front of the sections, moving faster and softly
         out of focus, so the depth reads as 3D. --}}
    <div class="ml-fore" aria-hidden="true">
        <span class="ml-fore__leaf ml-fore__leaf--1"></span>
        <span class="ml-fore__leaf ml-fore__leaf--2"></span>
        <span class="ml-fore__leaf ml-fore__leaf--3"></span>
        <span class="ml-fore__leaf ml-fore__leaf--4"></span>
    </div>

    <div class="ml-cover" id="opening-cover">
        @if ($coverImage)<img class="ml-cover__poster" src="{{ $coverImage }}" alt="" aria-hidden="true">@endif
        @if ($coverDesktop)
            <video class="ml-cover__video ml-cover__video--desktop" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video>
                <source src="{{ $coverDesktop }}">
            </video>
        @endif
        @if ($coverMobile)
            <video class="ml-cover__video ml-cover__video--mobile" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video>
                <source src="{{ $coverMobile }}">
            </video>
        @endif
        <div class="ml-cover__panel">
            <span class="ml-cover__arch" aria-hidden="true"></span>
            <span class="invitation-eyebrow">{{ $labels['cover_eyebrow'] }}</span>
            @if ($groomName && $brideName)
                <h1 class="ml-cover__couple"><span>{{ $groomName }}</span><em>&amp;</em><span>{{ $brideName }}</span></h1>
            @else
                <h1>{{ $title }}</h1>
            @endif
            @if ($primary_event)
                <span class="ml-cover__date">{{ $primary_event['date'] }}</span>
                @if ($primary_event['timestamp'])
                    <div class="ml-cover__countdown" data-countdown="{{ $primary_event['timestamp'] }}" aria-label="Hitung mundur menuju acara">
                        <div data-countdown-output>
                            @foreach (['days' => 'Hari', 'hours' => 'Jam', 'minutes' => 'Menit', 'seconds' => 'Detik'] as $unit => $label)
                                <span><strong data-countdown-unit="{{ $unit }}">00</strong><small>{{ $label }}</small></span>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif
            @if ($recipient)
                <p class="ml-cover__to">{{ $labels['cover_recipient_label'] }}</p>
                <strong class="ml-cover__name">{{ $recipient }}</strong>
            @endif
            <a class="ml-cover__cta" href="#top" data-open-invitation>{{ $labels['cover_cta'] }}</a>
        </div>
    </div>

    <nav class="ml-nav" aria-label="Navigasi undangan">
        <div class="ml-nav__inner">
            <a href="#top">Awal</a>
            @foreach ($visibleNav as $item)<a href="#{{ $item['id'] }}">{{ $item['label'] }}</a>@endforeach
        </div>
    </nav>

    <main id="top" tabindex="-1" data-gate>
        @foreach ($sections as $section)
            @if ($section === 'opening')
                <section class="ml-hero invitation-section" id="opening" data-height="{{ $section_heights['opening'] ?? 'full' }}">
                    @if ($openingVideo)
                        @if ($openingVideoMobile !== $openingVideo)
                            <video class="ml-hero__video ml-hero__video--desktop" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video><source src="{{ $openingVideo }}"></video>
                            <video class="ml-hero__video ml-hero__video--mobile" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video><source src="{{ $openingVideoMobile }}"></video>
                        @else
                            <video class="ml-hero__video" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video><source src="{{ $openingVideo }}"></video>
                        @endif
                    @endif
                    <div class="ml-hero__inner">
                        <span class="invitation-eyebrow" data-reveal="down">{{ $labels['opening_eyebrow'] }}</span>
                        @if (count($hosts) >= 2)
                            <h2 class="ml-couple-title" data-reveal="unfurl"><span>{{ $hosts[0]['name'] }}</span><em>&amp;</em><span>{{ $hosts[1]['name'] }}</span></h2>
                        @else
                            <h2 data-reveal="unfurl">{{ $title }}</h2>
                        @endif
                        @if ($opening_text)<p data-reveal>{!! nl2br(e($opening_text)) !!}</p>@endif
                        @if ($primary_event)<p class="ml-date" data-reveal>{{ $primary_event['date'] }}</p>@endif
                    </div>
                </section>
            @elseif ($section === 'events' && count($events))
                <section class="invitation-section invitation-section--tint" id="agenda" data-height="{{ $section_heights['events'] ?? 'full' }}" aria-labelledby="agenda-title">
                    <span class="invitation-eyebrow" data-reveal="down">{{ $labels['events_eyebrow'] }}</span>
                    <h2 id="agenda-title" data-reveal="unfurl">{{ $labels['events_title'] }}</h2>
                    <div class="ml-events">
                        @foreach ($events as $event)
                            <article class="ml-event" data-reveal="unfurl">
                                <h3>{{ $event['label'] }}</h3>
                                <div class="ml-event__schedule">
                                    <p><strong>{{ $event['date'] }}</strong></p>
                                    @if ($event['start_time'])<p>{{ $event['start_time'] }}{{ $event['end_time'] ? ' – '.$event['end_time'] : ' s/d Selesai' }}{{ ($theme['hide_timezone'] ?? false) ? '' : ($event['timezone_label'] ? ' '.$event['timezone_label'] : '') }}</p>@endif
                                </div>
                                @if ($event['venue'] || $event['address'])
                                    <div class="ml-event__venue">
                                        @if ($event['venue'])<p><strong>{{ $event['venue'] }}</strong></p>@endif
                                        @if ($event['address'])<p>{{ $event['address'] }}</p>@endif
                                    </div>
                                @endif
                                <div class="ml-actions">
                                    @if (in_array('map', $sections) && $event['directions_url'])<a href="{{ $event['directions_url'] }}" target="_blank" rel="noopener noreferrer">Petunjuk Arah</a>@endif
                                    @if ($event['address'])<button type="button" data-copy="{{ $event['address'] }}">Salin Alamat</button>@endif
                                </div>
                                @if (count($event['notes']))<div class="ml-event__notes">@foreach ($event['notes'] as $note)<small>{!! nl2br(e($note)) !!}</small>@endforeach</div>@endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @elseif ($section === 'countdown' && $primary_event && $primary_event['timestamp'])
                <section class="invitation-section ml-countdown" data-height="{{ $section_heights['countdown'] ?? 'full' }}" data-countdown="{{ $primary_event['timestamp'] }}" aria-label="Hitung mundur menuju hari bahagia">
                    <span class="invitation-eyebrow" data-reveal="down">{{ $labels['countdown_eyebrow'] }}</span>
                    <h2 data-reveal="unfurl">{{ $labels['countdown_title'] }}</h2>
                    <div class="ml-countdown__units" data-countdown-output>
                        @foreach (['days' => 'Hari', 'hours' => 'Jam', 'minutes' => 'Menit', 'seconds' => 'Detik'] as $unit => $label)
                            <span><strong data-countdown-unit="{{ $unit }}">00</strong><small>{{ $label }}</small></span>
                        @endforeach
                    </div>
                </section>
            @elseif ($section === 'hosts' && count($hosts))
                <section class="invitation-section" id="people" data-height="{{ $section_heights['hosts'] ?? 'full' }}" aria-labelledby="people-title">
                    <span class="invitation-eyebrow" data-reveal="down">{{ $labels['hosts_eyebrow'] }}</span>
                    <h2 id="people-title" data-reveal="unfurl">{{ $labels['hosts_title'] }}</h2>
                    <div class="ml-hosts" data-count="{{ count($hosts) }}">
                        @foreach ($hosts as $host)
                            <article class="ml-host" data-host data-reveal="unfurl">
                                <button type="button" class="host-card__trigger ml-host__trigger" data-host-open data-host-name="{{ $host['name'] }}" data-host-role="{{ match ($host['role']) { 'groom' => 'Mempelai Pria', 'bride' => 'Mempelai Wanita', default => 'Mempelai' } }}" aria-haspopup="dialog" aria-label="Lihat profil {{ $host['name'] }}">
                                    <span class="ml-host__portrait">
                                        @if ($host['photo_url'])<img src="{{ $host['photo_url'] }}" alt="Foto {{ $host['name'] }}" loading="lazy" decoding="async">@else<span aria-hidden="true">{{ mb_substr($host['name'], 0, 1) }}</span>@endif
                                    </span>
                                </button>
                                <h3>{{ $host['name'] }}</h3>
                                @if ($host['family'])
                                    <p class="ml-host__parents">
                                        <small>{{ match ($host['role']) { 'groom' => 'Putra', 'bride' => 'Putri', default => 'Putra/putri' } }} dari</small>
                                        <span>{{ $host['family'] }}</span>
                                    </p>
                                @endif
                                @if ($host['instagram'])<a href="{{ $host['instagram'] }}" rel="noopener noreferrer" target="_blank">Instagram</a>@endif
                                <div class="host-card__details ml-host__details" data-host-details>
                                    @if ($host['birth_order'])<p>{{ $host['birth_order'] }}</p>@endif
                                    @if ($host['bio'])<p>{!! nl2br(e($host['bio'])) !!}</p>@endif
                                </div>
                            </article>
                            @if (count($hosts) === 2 && ! $loop->last)<span class="ml-hosts__and" aria-hidden="true">&amp;</span>@endif
                        @endforeach
                    </div>
                </section>
            @elseif ($section === 'story' && count($stories))
                <section class="invitation-section" id="cerita" data-height="{{ $section_heights['story'] ?? 'full' }}" aria-labelledby="story-title">
                    <span class="invitation-eyebrow" data-reveal="down">{{ $labels['story_eyebrow'] }}</span>
                    <h2 id="story-title" data-reveal="unfurl">{{ $labels['story_title'] }}</h2>
                    <div class="ml-timeline">
                        @foreach ($stories as $story)
                            <article class="ml-story" data-reveal="unfurl">
                                @if ($story['image_url'])<img src="{{ $story['image_url'] }}" alt="{{ $story['title'] }}" loading="lazy">@endif
                                <small>{{ $story['date'] }}</small><h3>{{ $story['title'] }}</h3>
                                @if ($story['body'])<p>{!! nl2br(e($story['body'])) !!}</p>@endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @elseif ($section === 'gallery' && count($gallery))
                <section class="invitation-section invitation-section--tint" id="galeri" data-height="{{ $section_heights['gallery'] ?? 'full' }}" aria-labelledby="gallery-title">
                    <span class="invitation-eyebrow" data-reveal="down">{{ $labels['gallery_eyebrow'] }}</span>
                    <h2 id="gallery-title" data-reveal="unfurl">{{ $labels['gallery_title'] }}</h2>
                    <div class="ml-gallery">
                        @foreach ($gallery as $image)
                            <figure data-reveal="unfurl">
                                <button type="button" data-lightbox-src="{{ $image['url'] }}" data-lightbox-alt="{{ $image['alt'] }}"><img src="{{ $image['url'] }}" alt="{{ $image['alt'] }}" loading="lazy" decoding="async"></button>
                                @if ($image['caption'])<figcaption>{{ $image['caption'] }}</figcaption>@endif
                            </figure>
                        @endforeach
                    </div>
                </section>
            @elseif (str_starts_with($section, 'blocks') && ! empty($block_sections[$section] ?? $blocks))
                <div class="ml-shared">@include('invitations.shared.blocks', ['blocks' => $block_sections[$section] ?? $blocks])</div>
            @elseif ($section === 'map' && $primary_event && $primary_event['map_embed_url'])
                <section class="invitation-section" data-height="{{ $section_heights['map'] ?? 'full' }}" aria-labelledby="map-title">
                    <span class="invitation-eyebrow" data-reveal="down">{{ $labels['map_eyebrow'] }}</span>
                    <h2 id="map-title" data-reveal="unfurl">{{ $labels['map_title'] }}</h2>
                    <div class="ml-map" data-reveal="unfurl"><iframe src="{{ $primary_event['map_embed_url'] }}" title="Peta {{ $primary_event['venue'] ?: $primary_event['label'] }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe></div>
                    @if ($primary_event['address'])<p>{{ $primary_event['address'] }}</p>@endif
                    <div class="ml-actions">
                        @if ($primary_event['directions_url'])<a href="{{ $primary_event['directions_url'] }}" target="_blank" rel="noopener noreferrer">Buka Google Maps</a>@endif
                        @if ($primary_event['address'])<button type="button" data-copy="{{ $primary_event['address'] }}">Salin Alamat</button>@endif
                    </div>
                </section>
            @elseif ($section === 'rsvp')
                <div class="ml-shared" id="response">
                    @if ($theme['merge_rsvp_guestbook'] ?? false)
                        @include('invitations.shared.confirmation')
                    @else
                        @include('invitations.shared.rsvp')
                    @endif
                </div>
            @elseif ($section === 'guestbook')
                @unless ($theme['merge_rsvp_guestbook'] ?? false)
                    <div class="ml-shared">@include('invitations.shared.guestbook')</div>
                @endunless
            @elseif ($section === 'gifts' && count($gifts))
                <section class="invitation-section" data-height="{{ $section_heights['gifts'] ?? 'full' }}" aria-labelledby="gifts-title">
                    <span class="invitation-eyebrow" data-reveal="down">{{ $labels['gifts_eyebrow'] }}</span>
                    <h2 id="gifts-title" data-reveal="unfurl">{{ $labels['gifts_title'] }}</h2>
                    <p data-reveal>{!! nl2br(e($labels['gifts_intro'])) !!}</p>
                    @include('invitations.shared.gifts')
                </section>
            @elseif ($section === 'livestream' && $livestream_url)
                <section class="invitation-section ml-livestream" data-height="{{ $section_heights['livestream'] ?? 'full' }}" aria-labelledby="live-title">
                    <span class="invitation-eyebrow" data-reveal="down">{{ $labels['livestream_eyebrow'] }}</span>
                    <h2 id="live-title" data-reveal="unfurl">{{ $livestream_label }}</h2>
                    <div class="ml-actions"><a href="{{ $livestream_url }}" target="_blank" rel="noopener noreferrer">{{ $livestream_label }} ↗</a></div>
                </section>
            @elseif ($section === 'contacts' && count($contacts))
                <section class="invitation-section ml-contacts" data-height="{{ $section_heights['contacts'] ?? 'full' }}" aria-labelledby="contacts-title">
                    <span class="invitation-eyebrow" data-reveal="down">{{ $labels['contacts_eyebrow'] }}</span>
                    <h2 id="contacts-title" data-reveal="unfurl">{{ $labels['contacts_title'] }}</h2>
                    <div class="ml-actions">
                        @foreach ($contacts as $contact)
                            <a href="{{ $contact['whatsapp_url'] }}" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp {{ $contact['name'] }}">{{ $contact['name'] }}</a>
                            <a href="{{ $contact['phone_url'] }}" aria-label="Telepon {{ $contact['name'] }}">☎</a>
                        @endforeach
                    </div>
                </section>
            @elseif ($section === 'sharing')
                <section class="invitation-section ml-sharing" data-height="{{ $section_heights['sharing'] ?? 'full' }}" aria-labelledby="share-title">
                    <span class="invitation-eyebrow" data-reveal="down">{{ $labels['sharing_eyebrow'] }}</span>
                    <h2 id="share-title" data-reveal="unfurl">{{ $labels['sharing_title'] }}</h2>
                    <p data-reveal>{!! nl2br(e($labels['sharing_intro'])) !!}</p>
                    <div class="ml-actions">
                        <button type="button" data-share data-share-url="{{ $share_url }}">Bagikan Link ↗</button>
                        <a href="{{ $whatsapp_url }}" target="_blank" rel="noopener noreferrer">WhatsApp ↗</a>
                    </div>
                </section>
            @elseif ($section === 'closing')
                <section class="invitation-section ml-closing" data-height="{{ $section_heights['closing'] ?? 'full' }}">
                    <span class="invitation-eyebrow" data-reveal="down">{{ $labels['closing_eyebrow'] }}</span>
                    @if (count($hosts) >= 2)
                        <h2 class="ml-couple-title" data-reveal="unfurl"><span>{{ $hosts[0]['name'] }}</span><em>&amp;</em><span>{{ $hosts[1]['name'] }}</span></h2>
                    @else
                        <h2 data-reveal="unfurl">{{ $couple_title }}</h2>
                    @endif
                    @if ($closing_message)<p data-reveal>{!! nl2br(e($closing_message)) !!}</p>@endif
                    @include('invitations.shared.closing-families')
                    @if ($closing_footer)<p data-reveal>{!! nl2br(e($closing_footer)) !!}</p>@endif
                    @if ($labels['closing_kicker'] ?? null)<p class="ml-kicker" data-reveal>{{ $labels['closing_kicker'] }}</p>@endif
                </section>
            @endif
        @endforeach
    </main>

    @if ($music_url)
        <audio data-music loop preload="none" src="{{ $music_url }}"></audio>
        <button class="ml-music" type="button" data-music-toggle aria-label="Putar musik"><span aria-hidden="true">♪</span></button>
    @endif
    <dialog class="ml-lightbox" data-lightbox><button type="button" data-lightbox-close aria-label="Tutup galeri">Tutup</button><img data-lightbox-image alt=""></dialog>
    @include('invitations.shared.host-dialog')
</body>
</html>

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
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400;0,500;1,400&family=Nunito+Sans:wght@300;400;600;700&display=swap">
    @vite(['resources/css/invitations.css', 'resources/js/invitation-hosts.js', 'resources/js/invitation-forms.js', 'resources/js/invitation-lightbox.js', 'resources/invitation-templates/aquarelle/assets/theme.css', 'resources/invitation-templates/aquarelle/assets/theme.js'])
</head>
<body class="aquarelle" style="--aq-accent: {{ $theme['accent_color'] }}; --aq-cover-veil: {{ ($theme['cover_overlay_opacity'] ?? 46) / 100 }};" data-motion="{{ $theme['motion'] }}">
    @php
        $navItems = [
            'hosts' => ['id' => 'hosts', 'label' => 'Mempelai'],
            'events' => ['id' => 'events', 'label' => 'Acara'],
            'story' => ['id' => 'story', 'label' => 'Cerita'],
            'gallery' => ['id' => 'gallery', 'label' => 'Galeri'],
            'rsvp' => ['id' => 'rsvp', 'label' => 'RSVP'],
        ];
        $visibleNav = collect($sections)->filter(fn ($section) => isset($navItems[$section]))->mapWithKeys(fn ($section) => [$section => $navItems[$section]])->all();
        $groomName = collect($hosts)->firstWhere('role', 'groom')['name'] ?? ($hosts[0]['name'] ?? null);
        $brideName = collect($hosts)->firstWhere('role', 'bride')['name'] ?? ($hosts[1]['name'] ?? null);
        $coverImage = $theme['cover_poster_image'] ?: ($gallery[0]['url'] ?? ($hosts[0]['photo_url'] ?? null));
        $coverDesktop = $theme['cover_video_enabled'] ? $theme['cover_video_desktop'] : null;
        $coverMobile = $theme['cover_video_enabled'] ? ($theme['cover_video_mobile'] ?: $coverDesktop) : null;
        $openingVideo = $coverDesktop;
        $openingVideoMobile = $coverMobile;
        $coverFocus = 'object-position: '.($theme['cover_focal_x'] ?? 50).'% '.($theme['cover_focal_y'] ?? 42).'%;';
    @endphp

    {{-- The scroll signature: soft watercolour washes drifting behind the
         sections, each at its own depth, and a brush stroke that paints down the
         edge with the page progress. --}}
    <div class="aq-ambient" aria-hidden="true">
        <span class="aq-wash aq-wash--rose"></span>
        <span class="aq-wash aq-wash--sage"></span>
        <span class="aq-wash aq-wash--sky"></span>
        <span class="aq-wash aq-wash--peach"></span>
    </div>
    <span class="aq-brush" aria-hidden="true"><i class="aq-brush__fill"></i></span>
    <div class="aq-flow" aria-hidden="true"></div>
    <div class="aq-dabs" aria-hidden="true">
        <span class="aq-dab aq-dab--1"></span>
        <span class="aq-dab aq-dab--2"></span>
        <span class="aq-dab aq-dab--3"></span>
        <span class="aq-dab aq-dab--4"></span>
        <span class="aq-dab aq-dab--5"></span>
        <span class="aq-dab aq-dab--6"></span>
    </div>

    {{-- Foreground paint dabs that drift faster than the content, in front of the
         sections, so the layers read with real depth (near = fast + softly out of
         focus). --}}
    <div class="aq-fore" aria-hidden="true">
        <span class="aq-fore__dab aq-fore__dab--1"></span>
        <span class="aq-fore__dab aq-fore__dab--2"></span>
        <span class="aq-fore__dab aq-fore__dab--3"></span>
        <span class="aq-fore__dab aq-fore__dab--4"></span>
        <span class="aq-fore__dab aq-fore__dab--5"></span>
    </div>

    <div class="aq-cover" id="opening-cover" style="{{ $coverFocus }}">
        @if ($coverImage)<img class="aq-cover__poster" src="{{ $coverImage }}" alt="" aria-hidden="true" style="{{ $coverFocus }}">@endif
        @if ($coverDesktop)
            <video class="aq-cover__video aq-cover__video--desktop" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video>
                <source src="{{ $coverDesktop }}">
            </video>
        @endif
        @if ($coverMobile)
            <video class="aq-cover__video aq-cover__video--mobile" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video>
                <source src="{{ $coverMobile }}">
            </video>
        @endif
        <span class="aq-cover__wash" aria-hidden="true"></span>
        <div class="aq-cover__panel">
            <span class="invitation-eyebrow">{{ $labels['cover_eyebrow'] }}</span>
            @if ($groomName && $brideName)
                <h1 class="aq-couple-title aq-couple-title--cover"><span>{{ $groomName }}</span><i>&amp;</i><span>{{ $brideName }}</span></h1>
            @else
                <h1>{{ $title }}</h1>
            @endif
            @if ($primary_event)<span class="aq-cover__date">{{ $primary_event['date'] }}</span>@endif
            @if ($primary_event && $primary_event['timestamp'])
                <div class="aq-cover__countdown" data-countdown="{{ $primary_event['timestamp'] }}" aria-label="Hitung mundur menuju acara">
                    <span class="aq-cover__countdown-label">{{ $labels['cover_countdown_label'] }}</span>
                    <div data-countdown-output>
                        @foreach (['days' => 'Hari', 'hours' => 'Jam', 'minutes' => 'Menit', 'seconds' => 'Detik'] as $unit => $label)
                            <span><strong data-countdown-unit="{{ $unit }}">00</strong><small>{{ $label }}</small></span>
                        @endforeach
                    </div>
                </div>
            @endif
            @if ($recipient)
                <p class="aq-cover__to">{{ $labels['cover_recipient_label'] }}</p>
                <strong class="aq-cover__name">{{ $recipient }}</strong>
            @endif
            <a class="aq-cover__cta" href="#invitation-content" data-open-invitation>{{ $labels['cover_cta'] }}</a>
        </div>
    </div>

    <nav class="aq-nav" aria-label="Navigasi undangan">
        <div class="aq-nav__inner">
            <a href="#invitation-content">Awal</a>
            @foreach ($visibleNav as $item)<a href="#{{ $item['id'] }}">{{ $item['label'] }}</a>@endforeach
        </div>
    </nav>

    <main id="invitation-content" tabindex="-1" data-gate>
        @foreach ($sections as $section)
            @if ($section === 'opening')
                <section class="aq-hero invitation-section" id="opening" data-height="{{ $section_heights['opening'] ?? 'full' }}">
                    @if ($openingVideo)
                        @if ($openingVideoMobile !== $openingVideo)
                            <video class="aq-hero__video aq-hero__video--desktop" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video><source src="{{ $openingVideo }}"></video>
                            <video class="aq-hero__video aq-hero__video--mobile" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video><source src="{{ $openingVideoMobile }}"></video>
                        @else
                            <video class="aq-hero__video" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video><source src="{{ $openingVideo }}"></video>
                        @endif
                    @endif
                    <div class="aq-hero__inner">
                        <span class="invitation-eyebrow" data-reveal="down">{{ $labels['opening_eyebrow'] }}</span>
                        @if (count($hosts) >= 2)
                            <h2 class="aq-couple-title" data-reveal="bloom"><span>{{ $hosts[0]['name'] }}</span><i>&amp;</i><span>{{ $hosts[1]['name'] }}</span></h2>
                        @else
                            <h2 data-reveal="bloom">{{ $title }}</h2>
                        @endif
                        @if ($opening_text)<p data-reveal>{!! nl2br(e($opening_text)) !!}</p>@endif
                        @if ($primary_event)<p class="aq-date" data-reveal>{{ $primary_event['date'] }}</p>@endif
                    </div>
                </section>
            @elseif ($section === 'hosts' && count($hosts))
                <section class="invitation-section" data-height="{{ $section_heights['hosts'] ?? 'full' }}" id="hosts" aria-labelledby="hosts-title">
                    <span class="invitation-eyebrow" data-reveal="down">{{ $labels['hosts_eyebrow'] }}</span>
                    <h2 id="hosts-title" data-reveal="bloom">{{ $labels['hosts_title'] }}</h2>
                    <div class="aq-hosts" data-count="{{ count($hosts) }}">
                        @foreach ($hosts as $host)
                            <article class="aq-host" data-host data-reveal>
                                <button type="button" class="host-card__trigger aq-host__trigger" data-host-open data-host-name="{{ $host['name'] }}" data-host-role="{{ match ($host['role']) { 'groom' => 'Mempelai Pria', 'bride' => 'Mempelai Wanita', default => 'Mempelai' } }}" aria-haspopup="dialog" aria-label="Lihat profil {{ $host['name'] }}">
                                    <span class="aq-host__portrait">
                                        @if ($host['photo_url'])<img src="{{ $host['photo_url'] }}" alt="Foto {{ $host['name'] }}" loading="lazy" decoding="async">@else<span aria-hidden="true">{{ mb_substr($host['name'], 0, 1) }}</span>@endif
                                    </span>
                                </button>
                                <h3>{{ $host['name'] }}</h3>
                                @if ($host['family'])
                                    <p class="aq-host__parents">
                                        <small>{{ match ($host['role']) { 'groom' => 'Putra', 'bride' => 'Putri', default => 'Putra/putri' } }} dari</small>
                                        <span>{{ $host['family'] }}</span>
                                    </p>
                                @endif
                                @if ($host['instagram'])<a href="{{ $host['instagram'] }}" rel="noopener noreferrer" target="_blank">Instagram</a>@endif
                                <div class="host-card__details aq-host__details" data-host-details>
                                    @if ($host['birth_order'])<p>{{ $host['birth_order'] }}</p>@endif
                                    @if ($host['bio'])<p>{!! nl2br(e($host['bio'])) !!}</p>@endif
                                </div>
                            </article>
                            @if (count($hosts) === 2 && ! $loop->last)<span class="aq-hosts__and" aria-hidden="true">&amp;</span>@endif
                        @endforeach
                    </div>
                </section>
            @elseif ($section === 'events' && count($events))
                <section class="invitation-section invitation-section--tint" data-height="{{ $section_heights['events'] ?? 'full' }}" id="events" aria-labelledby="events-title">
                    <span class="invitation-eyebrow" data-reveal="down">{{ $labels['events_eyebrow'] }}</span>
                    <h2 id="events-title" data-reveal="bloom">{{ $labels['events_title'] }}</h2>
                    <div class="aq-events">
                        @foreach ($events as $event)
                            <article class="aq-event" data-reveal>
                                <h3>{{ $event['label'] }}</h3>
                                <div class="aq-event__schedule">
                                    <p><strong>{{ $event['date'] }}</strong></p>
                                    @if ($event['start_time'])<p>{{ $event['start_time'] }}{{ $event['end_time'] ? ' – '.$event['end_time'] : ' s/d Selesai' }}{{ ($theme['hide_timezone'] ?? false) ? '' : ($event['timezone_label'] ? ' '.$event['timezone_label'] : '') }}</p>@endif
                                </div>
                                @if ($event['venue'] || $event['address'])
                                    <div class="aq-event__venue">
                                        @if ($event['venue'])<p><strong>{{ $event['venue'] }}</strong></p>@endif
                                        @if ($event['address'])<p>{{ $event['address'] }}</p>@endif
                                    </div>
                                @endif
                                <div class="aq-actions aq-actions--center">
                                    @if (in_array('map', $sections) && $event['directions_url'])<a href="{{ $event['directions_url'] }}" target="_blank" rel="noopener noreferrer">Petunjuk Arah</a>@endif
                                    @if ($event['address'])<button type="button" data-copy="{{ $event['address'] }}">Salin Alamat</button>@endif
                                </div>
                                @if (count($event['notes']))<div class="aq-event__notes">@foreach ($event['notes'] as $note)<small>{!! nl2br(e($note)) !!}</small>@endforeach</div>@endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @elseif ($section === 'map' && $primary_event && $primary_event['map_embed_url'])
                <section class="invitation-section" data-height="{{ $section_heights['map'] ?? 'full' }}" aria-labelledby="map-title">
                    <span class="invitation-eyebrow" data-reveal="down">{{ $labels['map_eyebrow'] }}</span>
                    <h2 id="map-title" data-reveal="bloom">{{ $labels['map_title'] }}</h2>
                    <div class="aq-map" data-reveal="bloom"><iframe src="{{ $primary_event['map_embed_url'] }}" title="Peta {{ $primary_event['venue'] ?: $primary_event['label'] }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe></div>
                    @if ($primary_event['address'])<p>{{ $primary_event['address'] }}</p>@endif
                    <div class="aq-actions aq-actions--center">
                        @if ($primary_event['directions_url'])<a href="{{ $primary_event['directions_url'] }}" target="_blank" rel="noopener noreferrer">Buka Google Maps</a>@endif
                        @if ($primary_event['address'])<button type="button" data-copy="{{ $primary_event['address'] }}">Salin Alamat</button>@endif
                    </div>
                </section>
            @elseif ($section === 'countdown' && $primary_event && $primary_event['timestamp'])
                <section class="invitation-section aq-countdown" data-height="{{ $section_heights['countdown'] ?? 'full' }}" data-countdown="{{ $primary_event['timestamp'] }}" aria-label="Hitung mundur menuju hari bahagia">
                    <span class="invitation-eyebrow" data-reveal="down">{{ $labels['countdown_eyebrow'] }}</span>
                    <h2 data-reveal="bloom">{{ $labels['countdown_title'] }}</h2>
                    <div class="aq-countdown__units" data-countdown-output>
                        @foreach (['days' => 'Hari', 'hours' => 'Jam', 'minutes' => 'Menit', 'seconds' => 'Detik'] as $unit => $label)
                            <span><strong data-countdown-unit="{{ $unit }}">00</strong><small>{{ $label }}</small></span>
                        @endforeach
                    </div>
                </section>
            @elseif ($section === 'story' && count($stories))
                <section class="invitation-section" data-height="{{ $section_heights['story'] ?? 'full' }}" id="story" aria-labelledby="story-title">
                    <span class="invitation-eyebrow" data-reveal="down">{{ $labels['story_eyebrow'] }}</span>
                    <h2 id="story-title" data-reveal="bloom">{{ $labels['story_title'] }}</h2>
                    <div class="aq-timeline">
                        @foreach ($stories as $story)
                            <article class="aq-story" data-reveal>
                                @if ($story['image_url'])<img src="{{ $story['image_url'] }}" alt="{{ $story['title'] }}" loading="lazy">@endif
                                <small>{{ $story['date'] }}</small><h3>{{ $story['title'] }}</h3>
                                @if ($story['body'])<p>{!! nl2br(e($story['body'])) !!}</p>@endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @elseif ($section === 'gallery' && count($gallery))
                <section class="invitation-section invitation-section--tint" data-height="{{ $section_heights['gallery'] ?? 'full' }}" id="gallery" aria-labelledby="gallery-title">
                    <span class="invitation-eyebrow" data-reveal="down">{{ $labels['gallery_eyebrow'] }}</span>
                    <h2 id="gallery-title" data-reveal="bloom">{{ $labels['gallery_title'] }}</h2>
                    <div class="aq-gallery">
                        @foreach ($gallery as $image)
                            <figure data-reveal="bloom">
                                <button type="button" data-lightbox-src="{{ $image['url'] }}" data-lightbox-alt="{{ $image['alt'] }}"><img src="{{ $image['url'] }}" alt="{{ $image['alt'] }}" loading="lazy" decoding="async"></button>
                                @if ($image['caption'])<figcaption>{{ $image['caption'] }}</figcaption>@endif
                            </figure>
                        @endforeach
                    </div>
                </section>
            @elseif (str_starts_with($section, 'blocks') && ! empty($block_sections[$section] ?? $blocks))
                @include('invitations.shared.blocks', ['blocks' => $block_sections[$section] ?? $blocks])
            @elseif ($section === 'rsvp')
                @if ($theme['merge_rsvp_guestbook'] ?? false)
                    @include('invitations.shared.confirmation')
                @else
                    <div id="rsvp">@include('invitations.shared.rsvp')</div>
                @endif
            @elseif ($section === 'guestbook')
                @unless ($theme['merge_rsvp_guestbook'] ?? false)
                    <div id="guestbook">@include('invitations.shared.guestbook')</div>
                @endunless
            @elseif ($section === 'gifts' && count($gifts))
                <section class="invitation-section" data-height="{{ $section_heights['gifts'] ?? 'full' }}" aria-labelledby="gifts-title">
                    <span class="invitation-eyebrow" data-reveal="down">{{ $labels['gifts_eyebrow'] }}</span>
                    <h2 id="gifts-title" data-reveal="bloom">{{ $labels['gifts_title'] }}</h2>
                    <p data-reveal>{!! nl2br(e($labels['gifts_intro'])) !!}</p>
                    @include('invitations.shared.gifts')
                </section>
            @elseif ($section === 'livestream' && $livestream_url)
                <section class="invitation-section" data-height="{{ $section_heights['livestream'] ?? 'full' }}">
                    <span class="invitation-eyebrow" data-reveal="down">{{ $labels['livestream_eyebrow'] }}</span>
                    <h2 data-reveal="bloom">Live Streaming</h2>
                    <div class="aq-actions aq-actions--center" data-reveal><a href="{{ $livestream_url }}" target="_blank" rel="noopener noreferrer">{{ $livestream_label }}</a></div>
                </section>
            @elseif ($section === 'contacts' && count($contacts))
                <section class="invitation-section aq-contact-section" data-height="{{ $section_heights['contacts'] ?? 'full' }}" aria-labelledby="contacts-title">
                    <span class="invitation-eyebrow" data-reveal="down">{{ $labels['contacts_eyebrow'] }}</span>
                    <h2 id="contacts-title" data-reveal="bloom">{{ $labels['contacts_title'] }}</h2>
                    <p data-reveal>{!! nl2br(e($labels['contacts_intro'])) !!}</p>
                    <div class="aq-contact-list">
                        @foreach ($contacts as $contact)
                            <article class="aq-contact-card" data-reveal>
                                <small>{{ $contact['label'] }}</small>
                                <h3>{{ $contact['name'] }}</h3>
                                <div class="aq-actions aq-actions--center">
                                    <a href="{{ $contact['whatsapp_url'] }}" target="_blank" rel="noopener noreferrer">WhatsApp</a>
                                    <a href="{{ $contact['phone_url'] }}">Telepon</a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @elseif ($section === 'sharing')
                <section class="invitation-section aq-share-section" data-height="{{ $section_heights['sharing'] ?? 'full' }}">
                    <span class="invitation-eyebrow" data-reveal="down">{{ $labels['sharing_eyebrow'] }}</span>
                    <h2 data-reveal="bloom">{{ $labels['sharing_title'] }}</h2>
                    <p data-reveal>{!! nl2br(e($labels['sharing_intro'])) !!}</p>
                    <div class="aq-actions aq-actions--center" data-reveal>
                        <button type="button" data-share data-share-url="{{ $share_url }}">Bagikan Sekarang</button>
                        <a href="{{ $whatsapp_url }}" target="_blank" rel="noopener noreferrer">Kirim via WhatsApp</a>
                    </div>
                </section>
            @elseif ($section === 'closing')
                <section class="invitation-section aq-closing" data-height="{{ $section_heights['closing'] ?? 'full' }}">
                    <span class="invitation-eyebrow" data-reveal="down">{{ $labels['closing_eyebrow'] }}</span>
                    @if (count($hosts) >= 2)
                        <h2 class="aq-couple-title aq-couple-title--closing" data-reveal="bloom"><span>{{ $hosts[0]['name'] }}</span><i>&amp;</i><span>{{ $hosts[1]['name'] }}</span></h2>
                    @else
                        <h2 data-reveal="bloom">{{ $couple_title }}</h2>
                    @endif
                    @if ($closing_message)<p data-reveal>{!! nl2br(e($closing_message)) !!}</p>@endif
                    @include('invitations.shared.closing-families')
                    @if ($closing_footer)<p data-reveal>{!! nl2br(e($closing_footer)) !!}</p>@endif
                    @if ($labels['closing_kicker'] ?? null)<p class="aq-kicker" data-reveal>{{ $labels['closing_kicker'] }}</p>@endif
                </section>
            @endif
        @endforeach
    </main>

    @if ($music_url)
        <audio data-music loop preload="none" src="{{ $music_url }}"></audio>
        <button class="aq-music" type="button" data-music-toggle aria-label="Putar musik"><span aria-hidden="true">♪</span></button>
    @endif
    <dialog class="aq-lightbox" data-lightbox><button type="button" data-lightbox-close aria-label="Tutup galeri">Tutup</button><img data-lightbox-image alt=""></dialog>
    @include('invitations.shared.host-dialog')
</body>
</html>

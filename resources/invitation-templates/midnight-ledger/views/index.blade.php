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
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Inter:wght@400;700&display=swap" rel="stylesheet">
    {{-- Marks that scripting is available before the first paint. --}}
    <script>document.documentElement.classList.add('js-ready');</script>
    @vite(['resources/css/invitations.css', 'resources/js/invitation-hosts.js', 'resources/js/invitation-forms.js', 'resources/js/invitation-lightbox.js', 'resources/invitation-templates/midnight-ledger/assets/theme.css', 'resources/invitation-templates/midnight-ledger/assets/theme.js'])
</head>
<body class="midnight-ledger" style="--ml-accent: {{ $theme['accent_color'] }}; --ml-focal-x: {{ $theme['cover_focal_x'] }}%; --ml-focal-y: {{ $theme['cover_focal_y'] }}%; --ml-overlay: {{ $theme['cover_overlay_opacity'] / 100 }};" data-motion="{{ $theme['motion'] }}">
    @php
        $coverImage = $theme['cover_poster_image'] ?: ($gallery[0]['url'] ?? ($hosts[0]['photo_url'] ?? null));
        $coverDesktop = $theme['cover_video_enabled'] ? $theme['cover_video_desktop'] : null;
        $coverMobile = $theme['cover_video_enabled'] ? ($theme['cover_video_mobile'] ?: $coverDesktop) : null;
        // The opening section reuses the cover video whenever one is uploaded.
        $openingVideo = $coverDesktop;
        $openingVideoMobile = $coverMobile;
        $groomName = collect($hosts)->firstWhere('role', 'groom')['name'] ?? ($hosts[0]['name'] ?? null);
        $brideName = collect($hosts)->firstWhere('role', 'bride')['name'] ?? ($hosts[1]['name'] ?? null);
        $nav = [
            'events' => count($events) ? ['id' => 'agenda', 'icon' => '◈', 'label' => 'Acara'] : null,
            'hosts'  => count($hosts)  ? ['id' => 'people', 'icon' => '◎', 'label' => 'Mempelai'] : null,
            'gallery'=> count($gallery)? ['id' => 'frames', 'icon' => '▣', 'label' => 'Galeri'] : null,
            'rsvp'   => in_array('rsvp', $sections) ? ['id' => 'response', 'icon' => '✦', 'label' => 'RSVP'] : null,
        ];
        $navigation = collect($sections)->mapWithKeys(fn ($s) => isset($nav[$s]) ? [$s => $nav[$s]] : [])->all();
        foreach (['events' => 'Ⅰ', 'hosts' => 'Ⅱ', 'gallery' => 'Ⅲ', 'rsvp' => 'Ⅳ'] as $key => $icon) {
            if (isset($navigation[$key])) $navigation[$key]['icon'] = $icon;
        }
        $sectionNumbers = array_flip(array_keys($navigation));
        $showLivestreamNav = in_array('livestream', $sections) && $livestream_url;
        $showSharingNav = in_array('sharing', $sections);
        // The rail mark used to read "ML", the template name's initials, which told a
        // guest nothing. The couple's initials say whose invitation this is.
        $monogram = $groomName && $brideName
            ? mb_strtoupper(mb_substr($groomName, 0, 1).mb_substr($brideName, 0, 1))
            : mb_strtoupper(mb_substr($title, 0, 2));
    @endphp
    <div class="ml-cover" data-cover>
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
        <div class="ml-cover__shade" aria-hidden="true"></div>
        @if ($groomName && $brideName)
            <h1 class="ml-cover__couple"><span>{{ $groomName }}</span><em>&amp;</em><span>{{ $brideName }}</span></h1>
        @endif
        <p class="ml-kicker">{{ $primary_event['date'] ?? $labels['cover_eyebrow'] }}</p>
        @if ($recipient)
            <div class="ml-cover__recipient"><span>{{ $labels['cover_recipient_label'] }}</span><strong>{{ $recipient }}</strong></div>
        @endif
        <div class="ml-cover__footer"><span>{{ $labels['cover_eyebrow'] }}</span><a href="#top" data-open-invitation>{{ $labels['cover_cta'] }} <i aria-hidden="true">↗</i></a></div>
    </div>

    <header class="ml-rail" aria-label="Navigasi undangan">
        <a href="#top" class="ml-mark" aria-label="Ke awal">{{ $monogram }}</a>
        <nav>
            @foreach ($navigation as $item)<a href="#{{ $item['id'] }}">{{ sprintf('%02d', $loop->iteration) }} <span>{{ $item['label'] }}</span></a>@endforeach
            @if ($showLivestreamNav)<a href="{{ $livestream_url }}" target="_blank" rel="noopener noreferrer" class="ml-nav-action">Live <span>{{ $livestream_label }}</span></a>@endif
            @if ($showSharingNav)<button type="button" class="ml-nav-action" data-share data-share-url="{{ $share_url }}">Bagikan <span>Share</span></button>@endif
        </nav>
        <span class="ml-progress" aria-hidden="true"><i data-scroll-progress></i></span>
    </header>

    <nav class="ml-mobile-nav" aria-label="Navigasi cepat">
        @foreach ($navigation as $item)<a href="#{{ $item['id'] }}" aria-label="{{ $item['label'] }}"><span aria-hidden="true">{{ $item['icon'] }}</span>{{ $item['label'] }}</a>@endforeach
    </nav>
    @if ($showLivestreamNav || $showSharingNav)
        <div class="ml-quick-actions" aria-label="Aksi undangan">
            <button type="button" class="ml-quick-actions__toggle" data-quick-actions-toggle aria-expanded="false" aria-controls="ml-quick-actions-panel" aria-label="Buka aksi undangan">◇</button>
            <div class="ml-quick-actions__panel" id="ml-quick-actions-panel" hidden>
                @if ($showLivestreamNav)<a href="{{ $livestream_url }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $livestream_label }}">▷</a>@endif
                @if ($showSharingNav)<button type="button" data-share data-share-url="{{ $share_url }}" aria-label="Bagikan undangan">↗</button>@endif
            </div>
        </div>
    @endif

    <main id="top" tabindex="-1" data-gate>
        @foreach ($sections as $section)
            @if ($section === 'opening')
                <section class="ml-hero" data-height="{{ $section_heights['opening'] ?? 'full' }}">
                    @if ($openingVideo)
                        @if ($openingVideoMobile !== $openingVideo)
                            <video class="ml-hero__video ml-hero__video--desktop" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video><source src="{{ $openingVideo }}"></video>
                            <video class="ml-hero__video ml-hero__video--mobile" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video><source src="{{ $openingVideoMobile }}"></video>
                        @else
                            <video class="ml-hero__video" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video><source src="{{ $openingVideo }}"></video>
                        @endif
                    @endif
                    <div class="ml-index">{{ $primary_event['date'] ?? $labels['cover_eyebrow'] }}</div>
                    <div class="ml-hero__title"><p>{{ $labels['opening_eyebrow'] }}</p><h1 class="ml-couple-title">@if ($groomName && $brideName)<span>{{ $groomName }}</span><em>&amp;</em><span>{{ $brideName }}</span>@else{{ $title }}@endif</h1></div>
                    <div class="ml-hero__note">@if ($opening_text)<p>{!! nl2br(e($opening_text)) !!}</p>@endif<span aria-hidden="true">↓</span></div>
                </section>
            @elseif ($section === 'events' && count($events))
                <section class="ml-section ml-agenda" id="agenda" data-height="{{ $section_heights['events'] ?? 'full' }}" aria-labelledby="agenda-title">
                    <header><span>{{ sprintf('%02d', $sectionNumbers['events'] + 1) }} · {{ $labels['events_eyebrow'] }}</span><h2 id="agenda-title">{{ $labels['events_title'] }}</h2></header>
                    <div class="ml-event-list">
                        @foreach ($events as $event)
                            <article data-reveal>
                                <div><h3>{{ $event['label'] }}</h3><p class="ml-large">{{ $event['date'] }}</p>@if ($event['start_time'])<p>{{ $event['start_time'] }}{{ $event['end_time'] ? ' – '.$event['end_time'] : ' s/d Selesai' }}{{ ($theme['hide_timezone'] ?? false) ? '' : ' · '.$event['timezone_label'] }}</p>@endif</div>
                                <div>@if ($event['venue'])<strong>{{ $event['venue'] }}</strong>@endif @if ($event['address'])<p>{{ $event['address'] }}</p>@endif
                                    <div class="ml-actions">@if (in_array('map', $sections) && $event['directions_url'])<a href="{{ $event['directions_url'] }}" target="_blank" rel="noopener noreferrer" aria-label="Petunjuk arah">⌖</a>@endif @if (in_array('map', $sections) && $event['address'])<button type="button" data-copy="{{ $event['address'] }}" aria-label="Salin alamat">∥</button>@endif</div>
                                    @foreach ($event['notes'] as $note)<small>{!! nl2br(e($note)) !!}</small>@endforeach
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @elseif ($section === 'countdown' && $primary_event && $primary_event['timestamp'])
                <section class="ml-countdown" data-height="{{ $section_heights['countdown'] ?? 'full' }}" data-countdown="{{ $primary_event['timestamp'] }}">
                    <div class="ml-countdown__heading"><span>{{ $labels['countdown_eyebrow'] }}</span><h2>{{ $labels['countdown_title'] }}</h2></div>
                    <div class="ml-countdown__grid" data-countdown-output role="timer" aria-live="off">
                        @foreach (['days' => 'Hari', 'hours' => 'Jam', 'minutes' => 'Mnt', 'seconds' => 'Dtk'] as $unit => $label)
                            <div><b data-countdown-unit="{{ $unit }}">00</b><span>{{ $label }}</span></div>
                        @endforeach
                    </div>
                </section>
            @elseif ($section === 'hosts' && count($hosts))
                <section class="ml-section ml-people" id="people" data-height="{{ $section_heights['hosts'] ?? 'full' }}" aria-labelledby="people-title"><header><span>{{ sprintf('%02d', $sectionNumbers['hosts'] + 1) }} · {{ $labels['hosts_eyebrow'] }}</span><h2 id="people-title">{{ $labels['hosts_title'] }}</h2></header><div class="ml-hosts">
                    @foreach ($hosts as $host)<article data-host><button type="button" class="host-card__trigger ml-host__photo" data-host-open data-host-name="{{ $host['name'] }}" data-host-role="{{ match ($host['role']) { 'groom' => 'Mempelai Pria', 'bride' => 'Mempelai Wanita', default => 'Mempelai' } }}" aria-haspopup="dialog" aria-label="Lihat profil {{ $host['name'] }}">@if ($host['photo_url'])<img src="{{ $host['photo_url'] }}" alt="Foto {{ $host['name'] }}" loading="lazy" decoding="async">@else<span class="ml-photo-fallback" aria-hidden="true">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>@endif</button><div class="ml-host__copy"><small>{{ match ($host['role']) { 'groom' => 'Mempelai Pria', 'bride' => 'Mempelai Wanita', default => 'Mempelai' } }}</small><h3>{{ $host['name'] }}</h3>@if ($host['family'])<p>{{ $host['family'] }}</p>@endif @if ($host['instagram'])<a href="{{ $host['instagram'] }}" target="_blank" rel="noopener noreferrer" aria-label="Instagram {{ $host['name'] }}">↗</a>@endif<div class="host-card__details" data-host-details>@if ($host['birth_order'])<p>{{ $host['birth_order'] }}</p>@endif @if ($host['bio'])<p>{!! nl2br(e($host['bio'])) !!}</p>@endif</div></div></article>@endforeach
                </div></section>
            @elseif ($section === 'story' && count($stories))
                <section class="ml-section ml-story" data-height="{{ $section_heights['story'] ?? 'full' }}" aria-labelledby="story-title"><header><span>{{ $labels['story_eyebrow'] }}</span><h2 id="story-title">{{ $labels['story_title'] }}</h2></header><ol>@foreach ($stories as $story)<li>@if ($story['image_url'])<img src="{{ $story['image_url'] }}" alt="{{ $story['title'] }}" loading="lazy" decoding="async">@endif<div><small>{{ $story['date'] }}</small><h3>{{ $story['title'] }}</h3>@if ($story['body'])<p>{!! nl2br(e($story['body'])) !!}</p>@endif</div></li>@endforeach</ol></section>
            @elseif ($section === 'gallery' && count($gallery))
                <section class="ml-frames" id="frames" data-height="{{ $section_heights['gallery'] ?? 'full' }}" aria-labelledby="frames-title"><header><span>{{ sprintf('%02d', $sectionNumbers['gallery'] + 1) }} · {{ $labels['gallery_eyebrow'] }}</span><h2 id="frames-title">{{ $labels['gallery_title'] }}</h2></header><div class="ml-gallery">@foreach (array_chunk($gallery, 3) as $page)<div class="ml-gallery__page">@foreach ($page as $image)<figure><button type="button" data-lightbox-src="{{ $image['url'] }}" data-lightbox-alt="{{ $image['alt'] }}"><img src="{{ $image['url'] }}" alt="{{ $image['alt'] }}" loading="lazy" decoding="async"></button>@if ($image['caption'])<figcaption>{{ str_pad($loop->parent->iteration * 3 - 3 + $loop->iteration, 2, '0', STR_PAD_LEFT) }} · {{ $image['caption'] }}</figcaption>@endif</figure>@endforeach</div>@endforeach</div></section>
            @elseif ($section === 'map' && $primary_event && $primary_event['map_embed_url'])
                <section class="ml-section ml-location" data-height="{{ $section_heights['map'] ?? 'full' }}" aria-labelledby="location-title"><header><span>{{ $labels['map_eyebrow'] }}</span><h2 id="location-title">{{ $labels['map_title'] }}</h2></header><div class="ml-map"><iframe src="{{ $primary_event['map_embed_url'] }}" title="Peta {{ $primary_event['venue'] ?: $primary_event['label'] }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe></div>@if ($primary_event['address'])<p>{{ $primary_event['address'] }}</p>@endif<div class="ml-actions">@if ($primary_event['directions_url'])<a href="{{ $primary_event['directions_url'] }}" target="_blank" rel="noopener noreferrer" aria-label="Buka Google Maps">⌖</a>@endif @if ($primary_event['address'])<button type="button" data-copy="{{ $primary_event['address'] }}" aria-label="Salin alamat">∥</button>@endif</div></section>
            @elseif (str_starts_with($section, 'blocks') && ! empty($block_sections[$section] ?? $blocks))
                <div class="ml-shared">@include('invitations.shared.blocks', ['blocks' => $block_sections[$section] ?? $blocks])</div>
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
                <section class="ml-section ml-gifts" data-height="{{ $section_heights['gifts'] ?? 'full' }}" aria-labelledby="gifts-title"><header><span>{{ $labels['gifts_eyebrow'] }}</span><h2 id="gifts-title">{{ $labels['gifts_title'] }}</h2></header><p>{!! nl2br(e($labels['gifts_intro'])) !!}</p>@include('invitations.shared.gifts')</section>
            @elseif ($section === 'contacts' && count($contacts))
                <section class="ml-section ml-contacts" data-height="{{ $section_heights['contacts'] ?? 'full' }}"><header><span>{{ $labels['contacts_eyebrow'] }}</span><h2>{{ $labels['contacts_title'] }}</h2></header><div class="ml-actions">@foreach ($contacts as $contact)<a href="{{ $contact['whatsapp_url'] }}" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp {{ $contact['name'] }}">◌ {{ $contact['name'] }}</a><a href="{{ $contact['phone_url'] }}" aria-label="Telepon {{ $contact['name'] }}">☎</a>@endforeach</div></section>
            @elseif ($section === 'livestream' && $livestream_url)
                <section class="ml-section ml-livestream" data-height="{{ $section_heights['livestream'] ?? 'full' }}" aria-labelledby="live-title"><header><span>{{ $labels['livestream_eyebrow'] }}</span><h2 id="live-title">{{ $livestream_label }}</h2></header><div class="ml-actions"><a href="{{ $livestream_url }}" target="_blank" rel="noopener noreferrer">{{ $livestream_label }} ↗</a></div></section>
            @elseif ($section === 'sharing')
                <section class="ml-section ml-sharing" data-height="{{ $section_heights['sharing'] ?? 'full' }}" aria-labelledby="share-title"><header><span>{{ $labels['sharing_eyebrow'] }}</span><h2 id="share-title">{{ $labels['sharing_title'] }}</h2></header><p>{!! nl2br(e($labels['sharing_intro'])) !!}</p><div class="ml-actions"><button type="button" data-share data-share-url="{{ $share_url }}">Bagikan Link ↗</button><a href="{{ $whatsapp_url }}" target="_blank" rel="noopener noreferrer">WhatsApp ↗</a></div></section>
            @elseif ($section === 'closing')
                <section class="ml-closing" data-height="{{ $section_heights['closing'] ?? 'full' }}">
                    <span>{{ date('Y') }}</span>
                    <div><p class="ml-closing__script">{{ $labels['closing_eyebrow'] }}</p><h2 class="ml-couple-title">@if ($groomName && $brideName)<span>{{ $groomName }}</span><em>&amp;</em><span>{{ $brideName }}</span>@else{{ $title }}@endif</h2></div>
                    <div class="ml-closing__message">@if ($closing_message)<p>{!! nl2br(e($closing_message)) !!}</p>@endif@include('invitations.shared.closing-families')@if ($closing_footer)<p>{!! nl2br(e($closing_footer)) !!}</p>@endif<p class="ml-kicker">{{ $labels['closing_kicker'] }}</p></div>
                    <a href="#top" class="ml-back-to-top" aria-label="Kembali ke atas">↑</a>
                </section>
            @endif
        @endforeach
    </main>

    @if ($music_url)<audio data-music loop preload="none" src="{{ $music_url }}"></audio><button class="ml-music" type="button" data-music-toggle aria-label="Putar musik"><span aria-hidden="true">♪</span></button>@endif
    <dialog class="ml-lightbox" data-lightbox><button type="button" data-lightbox-close aria-label="Tutup galeri">✕</button><img data-lightbox-image alt=""></dialog>
    @include('invitations.shared.host-dialog')
</body>
</html>

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $title }}">
    <title>{{ $title }}</title>
    {{-- Marks that scripting is available before the first paint. --}}
    <script>document.documentElement.classList.add('js-ready');</script>
    @vite(['resources/css/invitations.css', 'resources/invitation-templates/elegant-rose/assets/theme.css', 'resources/invitation-templates/elegant-rose/assets/theme.js'])
</head>
<body class="elegant-rose" style="--rose-accent: {{ $theme['accent_color'] }}" data-motion="{{ $theme['motion'] }}">
    @php
        $navItems = [
            'hosts' => ['id' => 'hosts', 'label' => 'Mempelai'],
            'events' => ['id' => 'events', 'label' => 'Acara'],
            'story' => ['id' => 'story', 'label' => 'Cerita'],
            'gallery' => ['id' => 'gallery', 'label' => 'Galeri'],
            'rsvp' => ['id' => 'rsvp', 'label' => 'RSVP'],
        ];
        $visibleNav = collect($sections)->filter(fn ($section) => isset($navItems[$section]))->mapWithKeys(fn ($section) => [$section => $navItems[$section]])->all();
        $coverImage = $theme['cover_poster_image'] ?: ($gallery[0]['url'] ?? ($hosts[0]['photo_url'] ?? null));
        $coverDesktop = $theme['cover_video_enabled'] ? $theme['cover_video_desktop'] : null;
        $coverMobile = $theme['cover_video_enabled'] ? ($theme['cover_video_mobile'] ?: $coverDesktop) : null;
        // The opening section reuses the same upload, switched on separately so
        // turning the cover video on never changes a section the operator did not
        // ask about.
        $openingVideo = ($theme['opening_video_enabled'] ?? false) ? $coverDesktop : null;
        $openingVideoMobile = ($theme['opening_video_enabled'] ?? false) ? $coverMobile : null;
    @endphp
    <div class="er-cover" id="opening-cover">
        @if ($coverImage)<img class="er-cover__poster" src="{{ $coverImage }}" alt="" aria-hidden="true">@endif
        @if ($coverDesktop)
            <video class="er-cover__video er-cover__video--desktop" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video>
                <source src="{{ $coverDesktop }}">
            </video>
        @endif
        @if ($coverMobile)
            <video class="er-cover__video er-cover__video--mobile" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video>
                <source src="{{ $coverMobile }}">
            </video>
        @endif
        <div class="er-cover__paper">
            <span class="er-cover__ornament" aria-hidden="true">✦</span>
            <span class="invitation-eyebrow">{{ $labels['cover_eyebrow'] }}</span>
            <h1>{{ $title }}</h1>
            @if ($primary_event)<span class="er-cover__date">{{ $primary_event['date'] }}</span>@endif
            @if ($primary_event && $primary_event['timestamp'])
                <div class="er-cover__countdown" data-countdown="{{ $primary_event['timestamp'] }}" aria-label="Hitung mundur menuju acara">
                    <span class="er-cover__countdown-label">{{ $labels['cover_countdown_label'] }}</span>
                    <div data-countdown-output>
                        @foreach (['days' => 'Hari', 'hours' => 'Jam', 'minutes' => 'Menit', 'seconds' => 'Detik'] as $unit => $label)
                            <span><strong data-countdown-unit="{{ $unit }}">00</strong><small>{{ $label }}</small></span>
                        @endforeach
                    </div>
                </div>
            @endif
            <p>{{ $labels['cover_recipient_label'] }}</p>
            <strong>{{ $recipient }}</strong>
            <a href="#invitation-content" data-open-invitation>{{ $labels['cover_cta'] }}</a>
        </div>
    </div>

    <nav class="er-nav" aria-label="Navigasi undangan">
        <a href="#invitation-content">Awal</a>
        @foreach ($visibleNav as $item)<a href="#{{ $item['id'] }}">{{ $item['label'] }}</a>@endforeach
    </nav>

    <div class="er-botanical" aria-hidden="true">
        <span class="er-botanical__corner er-botanical__corner--one"></span>
        <span class="er-botanical__corner er-botanical__corner--two"></span>
        <span class="er-petal er-petal--one"></span>
        <span class="er-petal er-petal--two"></span>
        <span class="er-petal er-petal--three"></span>
        <span class="er-petal er-petal--four"></span>
    </div>

    <main id="invitation-content" tabindex="-1" data-gate>
        @foreach ($sections as $section)
            @if ($section === 'opening')
                <section class="er-hero invitation-section" data-height="{{ $section_heights['opening'] ?? 'full' }}">
                    @if ($openingVideo)
                        @if ($openingVideoMobile !== $openingVideo)
                            <video class="er-hero__video er-hero__video--desktop" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video><source src="{{ $openingVideo }}"></video>
                            <video class="er-hero__video er-hero__video--mobile" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video><source src="{{ $openingVideoMobile }}"></video>
                        @else
                            <video class="er-hero__video" muted loop playsinline preload="metadata" poster="{{ $coverImage }}" data-cover-video><source src="{{ $openingVideo }}"></video>
                        @endif
                    @endif
                    <span class="invitation-eyebrow">{{ $labels['opening_eyebrow'] }}</span>
                    @if (count($hosts) >= 2)
                        <h2 class="er-couple-title"><span>{{ $hosts[0]['name'] }}</span><i>&amp;</i><span>{{ $hosts[1]['name'] }}</span></h2>
                    @else
                        <h2>{{ $title }}</h2>
                    @endif
                    @if ($opening_text)<p>{!! nl2br(e($opening_text)) !!}</p>@endif
                    @if ($primary_event)<p class="er-date">{{ $primary_event['date'] }}</p>@endif
                </section>
            @elseif ($section === 'hosts' && count($hosts))
                <section class="invitation-section" data-height="{{ $section_heights['hosts'] ?? 'full' }}" id="hosts" aria-labelledby="hosts-title">
                    <span class="invitation-eyebrow">{{ $labels['hosts_eyebrow'] }}</span>
                    <h2 id="hosts-title">{{ $labels['hosts_title'] }}</h2>
                    <div class="er-hosts" data-count="{{ count($hosts) }}">
                        @foreach ($hosts as $host)
                            <article class="er-host">
                                <div class="er-host__portrait">
                                    @if ($host['photo_url'])<img src="{{ $host['photo_url'] }}" alt="Foto {{ $host['name'] }}" loading="lazy" decoding="async">@else<span aria-hidden="true">{{ mb_substr($host['name'], 0, 1) }}</span>@endif
                                </div>
                                <h3>{{ $host['name'] }}</h3>
                                @if ($host['family'])<p>{{ match ($host['role']) { 'groom' => 'Putra', 'bride' => 'Putri', default => 'Putra/putri' } }} dari {{ $host['family'] }}</p>@endif
                                @if ($host['instagram'])<a href="{{ $host['instagram'] }}" rel="noopener noreferrer" target="_blank">Instagram</a>@endif
                            </article>
                            @if (count($hosts) === 2 && ! $loop->last)<span class="er-hosts__and" aria-hidden="true">&amp;</span>@endif
                        @endforeach
                    </div>
                </section>
            @elseif ($section === 'events' && count($events))
                <section class="invitation-section invitation-section--tint" data-height="{{ $section_heights['events'] ?? 'full' }}" id="events" aria-labelledby="events-title">
                    <span class="invitation-eyebrow">{{ $labels['events_eyebrow'] }}</span>
                    <h2 id="events-title">{{ $labels['events_title'] }}</h2>
                    <div class="er-events">
                        @foreach ($events as $event)
                            <article class="er-event">
                                <span class="er-event__number">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <h3>{{ $event['label'] }}</h3>
                                <div class="er-event__schedule">
                                    <p><strong>{{ $event['date'] }}</strong></p>
                                    @if ($event['start_time'])<p>{{ $event['start_time'] }}{{ $event['end_time'] ? ' – '.$event['end_time'] : '' }} {{ $event['timezone'] }}</p>@endif
                                </div>
                                @if ($event['venue'] || $event['address'])
                                    <div class="er-event__venue">
                                        @if ($event['venue'])<p><strong>{{ $event['venue'] }}</strong></p>@endif
                                        @if ($event['address'])<p>{{ $event['address'] }}</p>@endif
                                    </div>
                                @endif
                                <div class="er-actions">
                                    @if (in_array('map', $sections) && $event['directions_url'])<a href="{{ $event['directions_url'] }}" target="_blank" rel="noopener noreferrer">Petunjuk Arah</a>@endif
                                    @if (in_array('calendar', $sections) && $event['calendar_url'])<a href="{{ $event['calendar_url'] }}" target="_blank" rel="noopener noreferrer">Google Calendar</a>@endif
                                    @if (in_array('calendar', $sections))<a href="{{ $event['ics_url'] }}">Unduh ICS</a>@endif
                                    @if (in_array('map', $sections) && $event['address'])<button type="button" data-copy="{{ $event['address'] }}">Salin Alamat</button>@endif
                                </div>
                                @if (count($event['notes']))<div class="er-event__notes">@foreach ($event['notes'] as $note)<small>{!! nl2br(e($note)) !!}</small>@endforeach</div>@endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @elseif ($section === 'map' && $primary_event && $primary_event['map_embed_url'])
                <section class="invitation-section" data-height="{{ $section_heights['map'] ?? 'full' }}" aria-labelledby="map-title">
                    <span class="invitation-eyebrow">{{ $labels['map_eyebrow'] }}</span><h2 id="map-title">{{ $labels['map_title'] }}</h2>
                    <div class="er-map"><iframe src="{{ $primary_event['map_embed_url'] }}" title="Peta {{ $primary_event['venue'] ?: $primary_event['label'] }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe></div>
                    @if ($primary_event['address'])<p>{{ $primary_event['address'] }}</p>@endif
                    <div class="er-actions er-actions--center">@if ($primary_event['directions_url'])<a href="{{ $primary_event['directions_url'] }}" target="_blank" rel="noopener noreferrer">Buka Google Maps</a>@endif @if ($primary_event['address'])<button type="button" data-copy="{{ $primary_event['address'] }}">Salin Alamat</button>@endif</div>
                </section>
            @elseif ($section === 'countdown' && $primary_event && $primary_event['timestamp'])
                <section class="invitation-section er-countdown" data-height="{{ $section_heights['countdown'] ?? 'full' }}" data-countdown="{{ $primary_event['timestamp'] }}" aria-label="Hitung mundur menuju hari bahagia">
                    <span class="invitation-eyebrow">{{ $labels['countdown_eyebrow'] }}</span>
                    <div class="er-countdown__units" data-countdown-output>
                        @foreach (['days' => 'Hari', 'hours' => 'Jam', 'minutes' => 'Menit', 'seconds' => 'Detik'] as $unit => $label)
                            <span><strong data-countdown-unit="{{ $unit }}">00</strong><small>{{ $label }}</small></span>
                        @endforeach
                    </div>
                </section>
            @elseif ($section === 'story' && count($stories))
                <section class="invitation-section" data-height="{{ $section_heights['story'] ?? 'full' }}" id="story" aria-labelledby="story-title">
                    <span class="invitation-eyebrow">{{ $labels['story_eyebrow'] }}</span><h2 id="story-title">{{ $labels['story_title'] }}</h2>
                    <div class="er-timeline">
                        @foreach ($stories as $story)
                            <article>
                                @if ($story['image_url'])<img src="{{ $story['image_url'] }}" alt="{{ $story['title'] }}" loading="lazy">@endif
                                <small>{{ $story['date'] }}</small><h3>{{ $story['title'] }}</h3>
                                @if ($story['body'])<p>{!! nl2br(e($story['body'])) !!}</p>@endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @elseif ($section === 'gallery' && count($gallery))
                <section class="invitation-section invitation-section--tint er-gallery-section" data-height="{{ $section_heights['gallery'] ?? 'full' }}" id="gallery" aria-labelledby="gallery-title">
                    <span class="invitation-eyebrow">{{ $labels['gallery_eyebrow'] }}</span><h2 id="gallery-title">{{ $labels['gallery_title'] }}</h2>
                    <div class="er-gallery">@foreach (array_chunk($gallery, 4) as $page)<div class="er-gallery__page">@foreach ($page as $image)<figure><button type="button" data-lightbox-src="{{ $image['url'] }}" data-lightbox-alt="{{ $image['alt'] }}"><img src="{{ $image['url'] }}" alt="{{ $image['alt'] }}" loading="lazy" decoding="async"></button>@if ($image['caption'])<figcaption>{{ $image['caption'] }}</figcaption>@endif</figure>@endforeach</div>@endforeach</div>
                </section>
            @elseif (str_starts_with($section, 'blocks') && ! empty($block_sections[$section] ?? $blocks))
                @include('invitations.shared.blocks', ['blocks' => $block_sections[$section] ?? $blocks])
            @elseif ($section === 'rsvp')
                <div id="rsvp">@include('invitations.shared.rsvp')</div>
            @elseif ($section === 'guestbook')
                @include('invitations.shared.guestbook')
            @elseif ($section === 'gifts' && count($gifts))
                <section class="invitation-section" data-height="{{ $section_heights['gifts'] ?? 'full' }}" aria-labelledby="gifts-title">
                    <span class="invitation-eyebrow">{{ $labels['gifts_eyebrow'] }}</span><h2 id="gifts-title">{{ $labels['gifts_title'] }}</h2>
                    <p>{!! nl2br(e($labels['gifts_intro'])) !!}</p>
                    <div class="er-gifts">
                        @foreach ($gifts as $gift)
                            <details class="er-gift"><summary>{{ $gift['type_label'] }} · {{ $gift['provider'] }}</summary><div>
                                @if ($gift['account_number'])<p><small>Nomor rekening / e-wallet</small><strong>{{ $gift['account_number'] }}</strong></p><button type="button" data-copy="{{ $gift['account_number'] }}">Salin Nomor</button>@endif
                                @if ($gift['account_name'])<p>Atas nama {{ $gift['account_name'] }}</p>@endif
                                @if ($gift['delivery_address'])<p>{{ $gift['delivery_address'] }}</p><button type="button" data-copy="{{ $gift['delivery_address'] }}">Salin Alamat Hadiah</button>@endif
                                @if ($gift['notes'])<p>{!! nl2br(e($gift['notes'])) !!}</p>@endif
                            </div></details>
                        @endforeach
                    </div>
                </section>
            @elseif ($section === 'livestream' && $livestream_url)
                <section class="invitation-section" data-height="{{ $section_heights['livestream'] ?? 'full' }}"><h2>Live Streaming</h2><a class="er-button" href="{{ $livestream_url }}" target="_blank" rel="noopener noreferrer">{{ $livestream_label }}</a></section>
            @elseif ($section === 'contacts' && count($contacts))
                <section class="invitation-section er-contact-section" data-height="{{ $section_heights['contacts'] ?? 'full' }}" aria-labelledby="contacts-title">
                    <span class="invitation-eyebrow">{{ $labels['contacts_eyebrow'] }}</span>
                    <h2 id="contacts-title">{{ $labels['contacts_title'] }}</h2>
                    <p>{!! nl2br(e($labels['contacts_intro'])) !!}</p>
                    <div class="er-contact-list">
                        @foreach ($contacts as $contact)
                            <article class="er-contact-card">
                                <small>{{ $contact['label'] }}</small>
                                <h3>{{ $contact['name'] }}</h3>
                                <div class="er-actions er-actions--center">
                                    <a href="{{ $contact['whatsapp_url'] }}" target="_blank" rel="noopener noreferrer">WhatsApp</a>
                                    <a href="{{ $contact['phone_url'] }}">Telepon</a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @elseif ($section === 'sharing')
                <section class="invitation-section er-share-section" data-height="{{ $section_heights['sharing'] ?? 'full' }}">
                    <span class="invitation-eyebrow">{{ $labels['sharing_eyebrow'] }}</span>
                    <h2>{{ $labels['sharing_title'] }}</h2>
                    <p>{!! nl2br(e($labels['sharing_intro'])) !!}</p>
                    <div class="er-actions er-actions--center"><button type="button" data-share data-share-url="{{ $share_url }}">Bagikan Sekarang</button><a href="{{ $whatsapp_url }}" target="_blank" rel="noopener noreferrer">Kirim via WhatsApp</a></div>
                </section>
            @elseif ($section === 'closing')
                <section class="invitation-section er-closing" data-height="{{ $section_heights['closing'] ?? 'full' }}"><span class="invitation-eyebrow">{{ $labels['closing_eyebrow'] }}</span><h2>{{ $title }}</h2>@if ($closing_message)<p>{!! nl2br(e($closing_message)) !!}</p>@endif</section>
            @endif
        @endforeach
    </main>

    @if ($music_url)
        <audio data-music loop preload="none" src="{{ $music_url }}"></audio>
        <button class="er-music" type="button" data-music-toggle aria-label="Putar musik"><span aria-hidden="true">♪</span></button>
    @endif
    <dialog class="er-lightbox" data-lightbox><button type="button" data-lightbox-close aria-label="Tutup galeri">Tutup</button><img data-lightbox-image alt=""></dialog>
</body>
</html>

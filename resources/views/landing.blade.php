<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $brand }} — Undangan pernikahan digital</title>
    <meta name="description" content="Pilih dari {{ $total }} desain undangan digital, lengkap dengan RSVP, buku ucapan, galeri foto, dan hadiah digital. Anda mengisi datanya, kami siapkan sampai tayang.">

    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $brand }} — Undangan pernikahan digital">
    <meta property="og:description" content="Pilih dari {{ $total }} desain undangan digital, lengkap dengan RSVP, buku ucapan, galeri foto, dan hadiah digital.">
    @if ($ogImageUrl)
        <meta property="og:image" content="{{ $ogImageUrl }}">
    @endif

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family=fraunces:400,600,700|karla:400,500,700">

    <style>
        /* ── Tokens ─────────────────────────────────────────────────────────
           The page chrome stays quiet ink-on-bone. All the colour on this
           page comes from the templates themselves, injected per plate as
           --accent from each manifest's settings_schema. */
        :root {
            --paper: #f3f1ea;
            --paper-raised: #fbfaf6;
            --ink: #191713;
            --ink-soft: #6b655c;
            --rule: rgb(25 23 19 / 14%);
            --signal: #2b3a8c;
            --signal-wash: rgb(43 58 140 / 8%);
            --radius: 3px;
            --measure: 60ch;
            --gutter: clamp(1.25rem, 5vw, 4.5rem);
            --display: 'Fraunces', ui-serif, Georgia, serif;
            --text: 'Karla', ui-sans-serif, system-ui, sans-serif;
        }

        *, *::before, *::after { box-sizing: border-box; }

        html { scroll-behavior: smooth; -webkit-text-size-adjust: 100%; }

        body {
            margin: 0;
            background: var(--paper);
            color: var(--ink);
            font-family: var(--text);
            font-size: clamp(1rem, 0.96rem + 0.2vw, 1.0625rem);
            line-height: 1.6;
            overflow-x: hidden;
            font-kerning: normal;
            text-rendering: optimizeLegibility;
        }

        img { display: block; max-width: 100%; }
        a { color: inherit; }
        h1, h2, h3 { font-family: var(--display); font-weight: 600; line-height: 1.1; letter-spacing: -0.015em; margin: 0; }
        p { margin: 0; max-width: var(--measure); }

        :focus-visible { outline: 2px solid var(--signal); outline-offset: 3px; border-radius: 2px; }

        .wrap { width: 100%; max-width: 76rem; margin-inline: auto; padding-inline: var(--gutter); }

        .skip {
            position: absolute; left: var(--gutter); top: -100%;
            background: var(--ink); color: var(--paper);
            padding: .75rem 1rem; border-radius: var(--radius); z-index: 10;
        }
        .skip:focus { top: 1rem; }

        /* ── Masthead ─────────────────────────────────────────────────────── */
        .masthead { border-bottom: 1px solid var(--rule); }
        .masthead__inner {
            display: flex; flex-wrap: wrap; gap: 1rem;
            align-items: center; justify-content: space-between;
            padding-block: 1rem;
        }
        .wordmark {
            font-family: var(--display); font-weight: 700;
            font-size: 1.125rem; letter-spacing: -0.02em;
            min-height: 44px;
            text-decoration: none; display: inline-flex; align-items: center; gap: .625rem;
        }
        .wordmark::before {
            content: ''; width: .5rem; height: 1.25rem; border-radius: 1px;
            background: var(--signal);
        }

        /* ── Buttons ──────────────────────────────────────────────────────── */
        .button {
            display: inline-flex; align-items: center; justify-content: center; gap: .5rem;
            min-height: 44px; padding: .625rem 1.125rem;
            border: 1px solid var(--ink); border-radius: var(--radius);
            background: var(--ink); color: var(--paper);
            font: inherit; font-weight: 500; text-decoration: none;
            transition: transform 140ms ease, background-color 140ms ease, color 140ms ease;
        }
        .button:hover { transform: translateY(-1px); }
        .button--quiet { background: transparent; color: var(--ink); border-color: var(--rule); }
        .button--quiet:hover { background: var(--paper-raised); border-color: var(--ink); }

        /* ── Hero ─────────────────────────────────────────────────────────── */
        .hero { padding-block: clamp(2.5rem, 7vw, 5rem) clamp(2.5rem, 6vw, 4.5rem); }
        .hero__inner { display: grid; gap: clamp(2rem, 5vw, 3.5rem); align-items: center; }
        .hero h1 { font-size: clamp(2.125rem, 1.2rem + 4.2vw, 3.75rem); max-width: 22ch; }
        .hero__lede { margin-top: 1.25rem; color: var(--ink-soft); font-size: 1.0625rem; }
        .hero__actions { display: flex; flex-wrap: wrap; gap: .75rem; margin-top: 1.75rem; }

        .fan { display: flex; justify-content: center; align-items: center; padding-block: 1.5rem; }
        .fan img {
            width: clamp(94px, 30%, 15rem);
            aspect-ratio: 3 / 2; object-fit: cover; object-position: top center;
            background: var(--paper-raised);
            border: 1px solid var(--rule); border-radius: var(--radius);
            margin-inline-start: -8%;
            transform: rotate(calc((var(--i) - 1) * 5deg));
            animation: settle 900ms cubic-bezier(.2, .8, .2, 1) backwards;
            animation-delay: calc(var(--i) * 90ms);
        }
        .fan img:first-child { margin-inline-start: 0; }

        @keyframes settle {
            from { opacity: 0; transform: translateY(1.25rem) rotate(0deg); }
            to   { opacity: 1; transform: rotate(calc((var(--i) - 1) * 5deg)); }
        }

        /* ── Section furniture ────────────────────────────────────────────── */
        .section { padding-block: clamp(2.5rem, 6vw, 4.5rem); border-top: 1px solid var(--rule); }
        .section h2 { font-size: clamp(1.5rem, 1.15rem + 1.4vw, 2.125rem); }
        .section__lede { margin-top: .875rem; color: var(--ink-soft); }

        /* ── Catalogue ────────────────────────────────────────────────────── */
        .catalogue__head { display: flex; flex-wrap: wrap; gap: 1.25rem; align-items: flex-end; justify-content: space-between; }

        .filters { display: flex; flex-wrap: wrap; gap: .25rem; margin: 0; padding: 0; list-style: none; }
        .filters a {
            display: inline-flex; align-items: center; gap: .375rem;
            min-height: 44px; padding: .5rem .75rem;
            border-radius: var(--radius); text-decoration: none;
            color: var(--ink-soft); font-size: .9375rem;
        }
        .filters a:hover { background: var(--paper-raised); color: var(--ink); }
        .filters a[aria-current='page'] { background: var(--signal-wash); color: var(--signal); font-weight: 500; }
        .filters span { font-variant-numeric: tabular-nums; opacity: .65; }

        .plates {
            display: grid; gap: clamp(1.5rem, 3vw, 2.5rem);
            grid-template-columns: repeat(auto-fill, minmax(min(100%, 19rem), 1fr));
            margin: clamp(1.75rem, 4vw, 2.75rem) 0 0; padding: 0; list-style: none;
        }

        .plate { display: flex; flex-direction: column; gap: .875rem; }
        .plate__frame {
            display: block; padding: 0; border: 1px solid var(--rule); border-radius: var(--radius);
            background: var(--paper-raised); overflow: hidden; cursor: zoom-in;
            transition: border-color 160ms ease, transform 160ms ease;
        }
        .plate__frame:hover { border-color: var(--accent); transform: translateY(-2px); }
        .plate__frame img { width: 100%; aspect-ratio: 3 / 2; object-fit: cover; object-position: top center; }
        .plate__body { border-left: 3px solid var(--accent); padding-left: .875rem; }
        .plate__body h3 { font-size: 1.25rem; }
        .plate__types { color: var(--ink-soft); font-size: .9375rem; margin-top: .25rem; }
        .plate__actions { display: flex; flex-wrap: wrap; align-items: center; gap: .25rem 1rem; margin-top: .25rem; }
        .plate__actions a {
            display: inline-flex; align-items: center; min-height: 44px;
            font-weight: 500; text-decoration-thickness: 1px; text-underline-offset: 3px;
        }
        .plate__demo { color: var(--ink); text-decoration: underline; }
        .plate__demo:hover { color: var(--accent); }
        .plate__cta { color: var(--signal); }

        .empty { margin-top: 1.75rem; color: var(--ink-soft); }
        .empty a { color: var(--signal); }

        /* ── Steps & features ─────────────────────────────────────────────── */
        .steps, .features { margin: clamp(1.75rem, 4vw, 2.5rem) 0 0; padding: 0; list-style: none; }
        .steps { counter-reset: step; display: grid; gap: 1.5rem; grid-template-columns: repeat(auto-fill, minmax(min(100%, 16rem), 1fr)); }
        .steps li { counter-increment: step; padding-top: 1rem; border-top: 1px solid var(--ink); }
        .steps li::before {
            content: counter(step, decimal-leading-zero);
            display: block; font-family: var(--display); font-weight: 600;
            font-size: 1rem; color: var(--signal); margin-bottom: .5rem;
            font-variant-numeric: tabular-nums;
        }
        .steps h3 { font-size: 1.125rem; }
        .steps p { margin-top: .375rem; color: var(--ink-soft); font-size: .9375rem; }

        .features { display: grid; gap: 1rem 2rem; grid-template-columns: repeat(auto-fill, minmax(min(100%, 18rem), 1fr)); }
        .features dt { font-family: var(--display); font-weight: 600; font-size: 1.0625rem; }
        .features dd { margin: .25rem 0 0; color: var(--ink-soft); font-size: .9375rem; }

        /* ── Contact & footer ─────────────────────────────────────────────── */
        .contact__actions { display: flex; flex-wrap: wrap; gap: .75rem; margin-top: 1.5rem; }
        .footer { border-top: 1px solid var(--rule); padding-block: 1.75rem 2.5rem; color: var(--ink-soft); font-size: .875rem; }
        .footer__inner { display: flex; flex-wrap: wrap; gap: .75rem; justify-content: space-between; }

        /* ── Lightbox ─────────────────────────────────────────────────────── */
        .lightbox {
            width: min(94vw, 60rem); max-height: 94vh; overflow: auto; padding: 0;
            border: 1px solid var(--rule); border-radius: var(--radius);
            background: var(--paper-raised); color: var(--ink);
        }
        .lightbox--live { width: min(94vw, 30rem); }
        .lightbox::backdrop { background: rgb(25 23 19 / 72%); }
        .lightbox__stage { display: grid; place-items: center; background: var(--paper); }
        .lightbox__stage img { width: 100%; max-height: 74vh; object-fit: contain; object-position: top center; }
        .lightbox__stage iframe { display: block; width: 100%; height: min(72vh, 46rem); border: 0; background: #fff; }
        .lightbox__bar {
            display: flex; flex-wrap: wrap; gap: .75rem 1rem;
            align-items: center; justify-content: space-between;
            padding: .75rem 1rem; border-top: 1px solid var(--rule);
        }
        .lightbox__bar p { font-family: var(--display); font-weight: 600; }
        .lightbox__bar-actions { display: flex; flex-wrap: wrap; gap: .75rem; align-items: center; }
        #pratinjau-tautan { display: inline-flex; align-items: center; min-height: 44px; color: var(--signal); font-weight: 500; }

        /* ── Wide screens ─────────────────────────────────────────────────── */
        @media (min-width: 60rem) {
            .hero__inner { grid-template-columns: minmax(0, 1fr) minmax(0, 1.05fr); }
        }

        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }
            *, *::before, *::after {
                animation-duration: .01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: .01ms !important;
            }
            .fan img { animation: none; }
        }
    </style>
</head>
<body>
    <a class="skip" href="#katalog">Lewati ke katalog desain</a>

    <header class="masthead">
        <div class="wrap masthead__inner">
            <a class="wordmark" href="{{ url('/') }}">{{ $brand }}</a>
            @if ($whatsappUrl)
                <a class="button button--quiet" href="{{ $whatsappUrl }}" rel="noopener">Chat WhatsApp</a>
            @endif
        </div>
    </header>

    <main>
        <section class="hero">
            <div class="wrap hero__inner">
                <div>
                    <h1>Pilih desainnya. Kami yang mengerjakan sisanya.</h1>
                    <p class="hero__lede">Undangan digital dengan RSVP, buku ucapan, galeri foto, dan hadiah digital. Anda mengisi datanya sekali, kami siapkan sampai tayang.</p>
                    <div class="hero__actions">
                        <a class="button" href="#katalog">Lihat {{ $total }} desain</a>
                        @if ($whatsappUrl)
                            <a class="button button--quiet" href="{{ $whatsappUrl }}" rel="noopener">Tanya lewat WhatsApp</a>
                        @endif
                    </div>
                </div>

                @if ($featured->isNotEmpty())
                    <div class="fan" aria-hidden="true">
                        @foreach ($featured as $item)
                            <img src="{{ $item['preview_url'] }}" alt="" style="--i: {{ $loop->index }}" decoding="async">
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

        <section class="section" id="katalog">
            <div class="wrap">
                <div class="catalogue__head">
                    <div>
                        <h2>Katalog desain</h2>
                        <p class="section__lede">Semua desain memakai data undangan yang sama. Anda bisa berganti desain kapan saja tanpa mengisi ulang.</p>
                    </div>

                    <ul class="filters">
                        <li>
                            <a href="{{ route('landing') }}#katalog" @if ($activeType === null) aria-current="page" @endif>
                                Semua <span>{{ $total }}</span>
                            </a>
                        </li>
                        @foreach ($filters as $filter)
                            <li>
                                <a href="{{ route('landing', ['acara' => $filter['value']]) }}#katalog" @if ($activeType === $filter['value']) aria-current="page" @endif>
                                    {{ $filter['label'] }} <span>{{ $filter['count'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                @if ($catalogue->isEmpty())
                    <p class="empty">
                        Belum ada desain untuk acara ini.
                        <a href="{{ route('landing') }}#katalog">Lihat semua desain</a>.
                    </p>
                @else
                    <ul class="plates">
                        @foreach ($catalogue as $item)
                            <li class="plate" style="--accent: {{ $item['accent'] }}">
                                @if ($item['preview_url'])
                                    <a
                                        class="plate__frame"
                                        href="{{ $item['demo_url'] ?? $item['preview_url'] }}"
                                        data-preview="{{ $item['preview_url'] }}"
                                        @if ($item['demo_url']) data-live="{{ $item['demo_url'] }}" @endif
                                        data-name="{{ $item['name'] }}"
                                        rel="noopener"
                                    >
                                        <img src="{{ $item['preview_url'] }}" alt="Pratinjau desain {{ $item['name'] }}" loading="lazy" decoding="async">
                                    </a>
                                @endif

                                <div class="plate__body">
                                    <h3>{{ $item['name'] }}</h3>
                                    @php($labels = collect($item['event_types'])->map(fn (string $type): string => $eventTypes[$type] ?? $type)->implode(', '))
                                    @if ($labels !== '')
                                        <p class="plate__types">{{ $labels }}</p>
                                    @endif
                                    <div class="plate__actions">
                                        @if ($item['demo_url'])
                                            <a class="plate__demo" href="{{ $item['demo_url'] }}" data-live="{{ $item['demo_url'] }}" data-name="{{ $item['name'] }}">Lihat contoh</a>
                                        @endif
                                        @if ($item['contact_url'])
                                            <a class="plate__cta" href="{{ $item['contact_url'] }}" rel="noopener">Pilih desain ini</a>
                                        @endif
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </section>

        <section class="section" id="cara">
            <div class="wrap">
                <h2>Cara kerjanya</h2>
                <ol class="steps">
                    <li>
                        <h3>Pilih desain</h3>
                        <p>Lihat katalog di atas, lalu tentukan yang paling cocok dengan acara Anda.</p>
                    </li>
                    <li>
                        <h3>Hubungi kami</h3>
                        <p>Kami siapkan undangannya dan kirimkan tautan pengisian data lewat WhatsApp.</p>
                    </li>
                    <li>
                        <h3>Isi data</h3>
                        <p>Nama, jadwal acara, foto, dan cerita. Bisa dicicil, tidak harus selesai sekali duduk.</p>
                    </li>
                    <li>
                        <h3>Undangan tayang</h3>
                        <p>Kami periksa hasilnya, lalu undangan siap dibagikan beserta tautan tamu.</p>
                    </li>
                </ol>
            </div>
        </section>

        <section class="section" id="fitur">
            <div class="wrap">
                <h2>Yang sudah termasuk</h2>
                <dl class="features">
                    <div>
                        <dt>RSVP dan rekap kehadiran</dt>
                        <dd>Tamu konfirmasi langsung dari undangan. Rekapnya bisa diunduh.</dd>
                    </div>
                    <div>
                        <dt>Buku ucapan</dt>
                        <dd>Ucapan tamu tampil setelah Anda setujui.</dd>
                    </div>
                    <div>
                        <dt>Galeri foto</dt>
                        <dd>Foto acara tampil dengan pratinjau layar penuh.</dd>
                    </div>
                    <div>
                        <dt>Hadiah digital</dt>
                        <dd>Rekening dan e-wallet dengan tombol salin.</dd>
                    </div>
                    <div>
                        <dt>Peta dan kalender</dt>
                        <dd>Lokasi acara, petunjuk arah, dan pengingat kalender.</dd>
                    </div>
                    <div>
                        <dt>Musik latar</dt>
                        <dd>Lagu pilihan Anda, diputar setelah tamu membuka undangan.</dd>
                    </div>
                </dl>
            </div>
        </section>

        <section class="section" id="kontak">
            <div class="wrap">
                <h2>Mulai dari sini</h2>
                <p class="section__lede">Pilih desain, lalu hubungi kami. Tidak sempat mengisi datanya sendiri? Kami bisa mengerjakan pengisiannya untuk Anda.</p>
                <div class="contact__actions">
                    @if ($whatsappUrl)
                        <a class="button" href="{{ $whatsappUrl }}" rel="noopener">Chat WhatsApp</a>
                    @endif
                    @if ($contactEmail)
                        <a class="button button--quiet" href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a>
                    @endif
                </div>
                @if (! $whatsappUrl && ! $contactEmail)
                    <p class="empty">Kontak belum diatur. Isi <code>INVITATION_WHATSAPP</code> atau <code>INVITATION_CONTACT_EMAIL</code> pada file <code>.env</code>.</p>
                @endif
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="wrap footer__inner">
            <span>{{ $brand }}</span>
            <span>{{ now()->year }}</span>
        </div>
    </footer>

    <dialog class="lightbox" id="pratinjau">
        <div class="lightbox__stage">
            <img id="pratinjau-gambar" alt="">
            <iframe id="pratinjau-rangka" title="" hidden></iframe>
        </div>
        <div class="lightbox__bar">
            <p id="pratinjau-nama"></p>
            <div class="lightbox__bar-actions">
                <a id="pratinjau-tautan" href="#" target="_blank" rel="noopener" hidden>Buka tab baru</a>
                <form method="dialog"><button class="button button--quiet" type="submit">Tutup</button></form>
            </div>
        </div>
    </dialog>

    <script>
        (function () {
            var dialog = document.getElementById('pratinjau');
            var image = document.getElementById('pratinjau-gambar');
            var frame = document.getElementById('pratinjau-rangka');
            var caption = document.getElementById('pratinjau-nama');
            var link = document.getElementById('pratinjau-tautan');

            if (!dialog || typeof dialog.showModal !== 'function') {
                return;
            }

            var triggers = document.querySelectorAll('[data-preview], [data-live]');

            Array.prototype.forEach.call(triggers, function (trigger) {
                trigger.addEventListener('click', function (event) {
                    event.preventDefault();

                    var name = trigger.getAttribute('data-name');
                    var live = trigger.getAttribute('data-live');

                    caption.textContent = name;

                    if (live) {
                        // Embed the real invitation so people can walk through it.
                        dialog.classList.add('lightbox--live');
                        image.removeAttribute('src');
                        image.hidden = true;
                        frame.src = live;
                        frame.title = 'Contoh undangan ' + name;
                        frame.hidden = false;
                        link.href = live;
                        link.hidden = false;
                    } else {
                        dialog.classList.remove('lightbox--live');
                        frame.removeAttribute('src');
                        frame.hidden = true;
                        image.src = trigger.getAttribute('data-preview');
                        image.alt = 'Pratinjau desain ' + name;
                        image.hidden = false;
                        link.removeAttribute('href');
                        link.hidden = true;
                    }

                    dialog.showModal();
                });
            });

            dialog.addEventListener('close', function () {
                // Dropping the source stops the sample's music and animations.
                image.removeAttribute('src');
                image.hidden = false;
                frame.removeAttribute('src');
                frame.hidden = true;
                link.hidden = true;
            });
        })();
    </script>
</body>
</html>

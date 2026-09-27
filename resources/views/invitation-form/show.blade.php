<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- The token lives in the URL, so never leak it through the Referer header. --}}
    <meta name="referrer" content="no-referrer">
    <meta name="robots" content="noindex, nofollow">

    <title>Isi data undangan — {{ $invitation->title }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family=fraunces:400,600|karla:400,500,700">

    <style>
        :root {
            --paper: #f3f1ea;
            --paper-raised: #fbfaf6;
            --ink: #191713;
            --ink-soft: #6b655c;
            --rule: rgb(25 23 19 / 14%);
            --signal: #2b3a8c;
            --signal-wash: rgb(43 58 140 / 8%);
            --danger: #9b2226;
            --radius: 3px;
            --gutter: clamp(1rem, 4vw, 3rem);
            --display: 'Fraunces', ui-serif, Georgia, serif;
            --text: 'Karla', ui-sans-serif, system-ui, sans-serif;
        }

        *, *::before, *::after { box-sizing: border-box; }

        body {
            margin: 0;
            background: var(--paper);
            color: var(--ink);
            font-family: var(--text);
            line-height: 1.6;
            overflow-x: hidden;
        }

        h1, h2, legend { font-family: var(--display); font-weight: 600; line-height: 1.15; margin: 0; }
        p { margin: 0; }
        img { display: block; max-width: 100%; }

        :focus-visible { outline: 2px solid var(--signal); outline-offset: 2px; border-radius: 2px; }

        .wrap { width: 100%; max-width: 54rem; margin-inline: auto; padding-inline: var(--gutter); }

        header.masthead { border-bottom: 1px solid var(--rule); background: var(--paper-raised); }
        .masthead__inner { display: flex; flex-wrap: wrap; gap: .5rem 1rem; align-items: baseline; justify-content: space-between; padding-block: 1rem; }
        .masthead strong { font-family: var(--display); font-weight: 600; }
        .masthead span { color: var(--ink-soft); font-size: .875rem; }

        main { padding-block: clamp(1.5rem, 4vw, 2.5rem) 3rem; }

        .steps { display: flex; flex-wrap: wrap; gap: .25rem; margin: 0 0 1.75rem; padding: 0; list-style: none; }
        .steps a {
            display: inline-flex; align-items: center; min-height: 44px; padding: .5rem .75rem;
            border-radius: var(--radius); color: var(--ink-soft); text-decoration: none; font-size: .9375rem;
        }
        .steps a:hover { background: var(--paper-raised); color: var(--ink); }
        .steps a[aria-current='page'] { background: var(--signal-wash); color: var(--signal); font-weight: 500; }

        .alert { border: 1px solid var(--rule); border-left: 4px solid var(--danger); background: var(--paper-raised); padding: .875rem 1rem; border-radius: var(--radius); margin-bottom: 1.5rem; }
        .alert ul { margin: .5rem 0 0; padding-left: 1.15rem; }
        .alert li { margin-bottom: .25rem; }

        .alert--ok { border-left-color: var(--signal); }

        h1 { font-size: clamp(1.5rem, 1.1rem + 1.6vw, 2rem); }
        .lede { color: var(--ink-soft); margin-top: .5rem; }

        form { margin-top: 1.5rem; display: grid; gap: 1.25rem; }

        .row { margin: 0; padding: 1rem; border: 1px solid var(--rule); border-radius: var(--radius); background: var(--paper-raised); }
        .row legend { padding-inline: .5rem; font-size: 1rem; color: var(--signal); }
        .row__grid { display: grid; gap: .875rem; grid-template-columns: repeat(auto-fit, minmax(min(100%, 14rem), 1fr)); }
        .row__remove { display: inline-flex; align-items: center; gap: .5rem; min-height: 44px; margin-top: .5rem; color: var(--danger); font-size: .9375rem; }

        .field { display: grid; gap: .25rem; }
        .field--wide { grid-column: 1 / -1; }
        .field > span { font-size: .875rem; font-weight: 500; }
        .field small { color: var(--ink-soft); font-size: .8125rem; }
        .photo { width: 96px; height: 96px; object-fit: cover; border-radius: var(--radius); border: 1px solid var(--rule); margin-bottom: .5rem; }

        input[type='text'], input[type='url'], input[type='date'], input[type='time'], select, textarea {
            width: 100%; min-height: 44px; padding: .5rem .625rem;
            border: 1px solid var(--rule); border-radius: var(--radius);
            background: #fff; color: var(--ink); font: inherit;
        }
        textarea { min-height: 4.5rem; resize: vertical; }
        input[type='file'] { width: 100%; min-height: 44px; font-size: .875rem; }
        input[type='checkbox'] { width: 1.125rem; height: 1.125rem; }

        .actions { display: flex; flex-wrap: wrap; gap: .75rem; align-items: center; }
        .button {
            display: inline-flex; align-items: center; justify-content: center; gap: .5rem;
            min-height: 44px; padding: .625rem 1.125rem;
            border: 1px solid var(--ink); border-radius: var(--radius);
            background: var(--ink); color: var(--paper);
            font: inherit; font-weight: 500; cursor: pointer; text-decoration: none;
        }
        .button--quiet { background: transparent; color: var(--ink); border-color: var(--rule); }
        .button--quiet:hover { background: var(--paper-raised); border-color: var(--ink); }
        .button[disabled] { opacity: .45; cursor: not-allowed; }

        .summary { margin: 1.5rem 0 0; padding: 0; list-style: none; display: grid; gap: .75rem; }
        .summary li { border-left: 3px solid var(--signal); padding-left: .875rem; }
        .summary strong { font-family: var(--display); }

        footer { border-top: 1px solid var(--rule); padding-block: 1.25rem 2rem; color: var(--ink-soft); font-size: .875rem; }

        @media (prefers-reduced-motion: reduce) {
            * { transition-duration: .01ms !important; animation-duration: .01ms !important; }
        }
    </style>
</head>
<body>
    <header class="masthead">
        <div class="wrap masthead__inner">
            <strong>Isi data undangan</strong>
            <span>{{ $invitation->title }}</span>
        </div>
    </header>

    <main class="wrap">
        <h1>{{ $steps[$step] ?? 'Isi data' }}</h1>
        <p class="lede">
            Data tersimpan setiap kali kamu menekan tombol simpan. Kamu boleh berhenti dan kembali lagi lewat tautan ini.
        </p>

        <ul class="steps">
            @foreach ($steps as $key => $label)
                <li>
                    <a href="{{ route('invitation-form.show', ['token' => $token, 'step' => $key]) }}"
                       @if ($key === $step) aria-current="page" @endif>{{ $label }}</a>
                </li>
            @endforeach
        </ul>

        @if (session('form_saved'))
            <div class="alert alert--ok">Perubahan tersimpan.</div>
        @endif

        @if ($errors->form->isNotEmpty())
            <div class="alert">
                <p><strong>Ada isian yang perlu diperbaiki.</strong></p>
                <ul>
                    @foreach (array_unique($errors->form->all()) as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($step === \App\Http\Controllers\InvitationFormController::STEP_HOSTS)
            <form method="POST" action="{{ route('invitation-form.update', ['token' => $token, 'step' => $step]) }}" enctype="multipart/form-data">
                @csrf

                <div id="host-rows" style="display:grid;gap:1rem">
                    @foreach ($hosts as $index => $host)
                        @include('invitation-form.partials.host-row', ['index' => $index, 'host' => $host])
                    @endforeach

                    @if ($hosts->count() < $maxHosts)
                        @include('invitation-form.partials.host-row', ['index' => $hosts->count(), 'host' => null])
                    @endif
                </div>

                <div class="actions">
                    <button class="button--quiet button" type="button" id="add-host" data-max="{{ $maxHosts }}">Tambah mempelai</button>
                    <button class="button" type="submit">Simpan dan lanjut ke acara</button>
                </div>

                <p style="color:var(--ink-soft);font-size:.875rem">
                    Sisa kuota foto: {{ $remainingPhotoSlots }}.
                </p>
            </form>
        @elseif ($step === \App\Http\Controllers\InvitationFormController::STEP_EVENTS)
            <form method="POST" action="{{ route('invitation-form.update', ['token' => $token, 'step' => $step]) }}">
                @csrf

                <div id="event-rows" style="display:grid;gap:1rem">
                    @foreach ($events as $index => $event)
                        @include('invitation-form.partials.event-row', ['index' => $index, 'event' => $event])
                    @endforeach

                    @if ($events->count() < $maxEvents)
                        @include('invitation-form.partials.event-row', ['index' => $events->count(), 'event' => null])
                    @endif
                </div>

                <div class="actions">
                    <button class="button--quiet button" type="button" id="add-event" data-max="{{ $maxEvents }}">Tambah acara</button>
                    <button class="button" type="submit">Simpan dan lanjut ke cerita</button>
                </div>
            </form>
        @elseif ($step === \App\Http\Controllers\InvitationFormController::STEP_STORIES)
            <form method="POST" action="{{ route('invitation-form.update', ['token' => $token, 'step' => $step]) }}" enctype="multipart/form-data">
                @csrf

                <p class="lede">Bagian ini opsional dan boleh dikosongkan. Isi momen penting yang ingin kamu tampilkan di undangan.</p>

                <div id="story-rows" style="display:grid;gap:1rem;margin-top:1.25rem">
                    @foreach ($stories as $index => $story)
                        @include('invitation-form.partials.story-row', ['index' => $index, 'story' => $story])
                    @endforeach

                    @if ($stories->count() < $maxStories)
                        @include('invitation-form.partials.story-row', ['index' => $stories->count(), 'story' => null])
                    @endif
                </div>

                <div class="actions">
                    <button class="button--quiet button" type="button" id="add-story" data-max="{{ $maxStories }}">Tambah momen</button>
                    <button class="button" type="submit">Simpan dan lanjut ke galeri</button>
                </div>

                <p style="color:var(--ink-soft);font-size:.875rem">Sisa kuota foto: {{ $remainingPhotoSlots }}.</p>
            </form>
        @elseif ($step === \App\Http\Controllers\InvitationFormController::STEP_GALLERY)
            <form method="POST" action="{{ route('invitation-form.update', ['token' => $token, 'step' => $step]) }}" enctype="multipart/form-data">
                @csrf

                <p class="lede">Bagian ini opsional. Maksimal {{ $maxGallery }} foto. Foto cover utama diatur oleh kami.</p>

                <div id="gallery-rows" style="display:grid;gap:1rem;margin-top:1.25rem">
                    @foreach ($gallery as $index => $item)
                        @include('invitation-form.partials.gallery-row', ['index' => $index, 'item' => $item])
                    @endforeach

                    @if ($gallery->count() < $maxGallery)
                        @include('invitation-form.partials.gallery-row', ['index' => $gallery->count(), 'item' => null])
                    @endif
                </div>

                <div class="actions">
                    <button class="button--quiet button" type="button" id="add-gallery" data-max="{{ $maxGallery }}">Tambah foto</button>
                    <button class="button" type="submit">Simpan dan lanjut ke hadiah</button>
                </div>

                <p style="color:var(--ink-soft);font-size:.875rem">Sisa kuota foto: {{ $remainingPhotoSlots }}.</p>
            </form>
        @elseif ($step === \App\Http\Controllers\InvitationFormController::STEP_GIFTS)
            <form method="POST" action="{{ route('invitation-form.update', ['token' => $token, 'step' => $step]) }}">
                @csrf

                <p class="lede">Bagian ini opsional. Isi kalau kamu ingin tamu bisa mengirim tanda kasih. Nomor rekening tampil di undangan, jadi periksa lagi sebelum disimpan.</p>

                <div id="gift-rows" style="display:grid;gap:1rem;margin-top:1.25rem">
                    @foreach ($gifts as $index => $gift)
                        @include('invitation-form.partials.gift-row', ['index' => $index, 'gift' => $gift])
                    @endforeach

                    @if ($gifts->count() < $maxGifts)
                        @include('invitation-form.partials.gift-row', ['index' => $gifts->count(), 'gift' => null])
                    @endif
                </div>

                <div class="actions">
                    <button class="button--quiet button" type="button" id="add-gift" data-max="{{ $maxGifts }}">Tambah hadiah</button>
                    <button class="button" type="submit">Simpan dan lanjut ke kontak</button>
                </div>
            </form>
        @elseif ($step === \App\Http\Controllers\InvitationFormController::STEP_CONTACTS)
            <form method="POST" action="{{ route('invitation-form.update', ['token' => $token, 'step' => $step]) }}">
                @csrf

                <p class="lede">Bagian ini opsional. Isi orang yang bisa dihubungi tamu kalau ada pertanyaan soal acara.</p>

                <div id="contact-rows" style="display:grid;gap:1rem;margin-top:1.25rem">
                    @foreach ($contacts as $index => $contact)
                        @include('invitation-form.partials.contact-row', ['index' => $index, 'contact' => $contact])
                    @endforeach

                    @if ($contacts->count() < $maxContacts)
                        @include('invitation-form.partials.contact-row', ['index' => $contacts->count(), 'contact' => null])
                    @endif
                </div>

                <div class="actions">
                    <button class="button--quiet button" type="button" id="add-contact" data-max="{{ $maxContacts }}">Tambah kontak</button>
                    <button class="button" type="submit">Simpan dan selesai</button>
                </div>
            </form>
        @else
            <ul class="summary">
                <li>
                    <strong>Mempelai</strong>
                    @forelse ($hosts as $host)
                        <div>{{ $host->name }}@if ($host->role) — {{ $hostRoles[$host->role] ?? $host->role }}@endif</div>
                    @empty
                        <div>Belum ada data mempelai.</div>
                    @endforelse
                </li>
                <li>
                    <strong>Acara</strong>
                    @forelse ($events as $event)
                        <div>
                            {{ $event->label }} — {{ $event->date?->locale('id')->translatedFormat('d F Y') }}
                            @if ($event->venue_name) di {{ $event->venue_name }}@endif
                        </div>
                    @empty
                        <div>Belum ada data acara.</div>
                    @endforelse
                </li>
                <li>
                    <strong>Cerita</strong>
                    @forelse ($stories as $story)
                        <div>{{ $story->title }}@if ($story->date) — {{ $story->date }}@endif</div>
                    @empty
                        <div>Belum ada cerita (opsional).</div>
                    @endforelse
                </li>
                <li>
                    <strong>Galeri</strong>
                    <div>{{ $gallery->count() }} foto</div>
                </li>
                <li>
                    <strong>Hadiah</strong>
                    @forelse ($gifts as $gift)
                        <div>{{ $gift->type->label() }} — {{ $gift->provider }}@if ($gift->account_number) · {{ $gift->account_number }}@endif</div>
                    @empty
                        <div>Belum ada data hadiah (opsional).</div>
                    @endforelse
                </li>
                <li>
                    <strong>Kontak</strong>
                    @forelse ($contacts as $contact)
                        <div>{{ $contact->label }} — {{ $contact->name }} · {{ $contact->phone }}</div>
                    @empty
                        <div>Belum ada kontak (opsional).</div>
                    @endforelse
                </li>
            </ul>

            <p class="lede" style="margin-top:1.5rem">
                Terima kasih. Data ini sudah masuk ke sistem kami dan akan diperiksa sebelum undangan ditayangkan.
                Kalau ada tambahan seperti foto galeri, cerita, atau hadiah digital, kirimkan lewat WhatsApp.
            </p>

            <div class="actions" style="margin-top:1.5rem">
                <a class="button button--quiet" href="{{ route('invitation-form.show', ['token' => $token, 'step' => \App\Http\Controllers\InvitationFormController::STEP_HOSTS]) }}">Kembali ke data mempelai</a>
            </div>
        @endif
    </main>

    <footer>
        <div class="wrap">
            Tautan ini bersifat pribadi. Mohon jangan dibagikan ke orang lain.
        </div>
    </footer>

    @if ($step === \App\Http\Controllers\InvitationFormController::STEP_HOSTS)
        <template id="host-row-template">
            @include('invitation-form.partials.host-row', ['index' => '__INDEX__', 'host' => null])
        </template>
    @endif

    @if ($step === \App\Http\Controllers\InvitationFormController::STEP_EVENTS)
        <template id="event-row-template">
            @include('invitation-form.partials.event-row', ['index' => '__INDEX__', 'event' => null])
        </template>
    @endif

    @if ($step === \App\Http\Controllers\InvitationFormController::STEP_STORIES)
        <template id="story-row-template">
            @include('invitation-form.partials.story-row', ['index' => '__INDEX__', 'story' => null])
        </template>
    @endif

    @if ($step === \App\Http\Controllers\InvitationFormController::STEP_GALLERY)
        <template id="gallery-row-template">
            @include('invitation-form.partials.gallery-row', ['index' => '__INDEX__', 'item' => null])
        </template>
    @endif

    @if ($step === \App\Http\Controllers\InvitationFormController::STEP_GIFTS)
        <template id="gift-row-template">
            @include('invitation-form.partials.gift-row', ['index' => '__INDEX__', 'gift' => null])
        </template>
    @endif

    @if ($step === \App\Http\Controllers\InvitationFormController::STEP_CONTACTS)
        <template id="contact-row-template">
            @include('invitation-form.partials.contact-row', ['index' => '__INDEX__', 'contact' => null])
        </template>
    @endif

    <script>
        (function () {
            function wire(buttonId, listId, templateId, rowSelector) {
                var button = document.getElementById(buttonId);
                var list = document.getElementById(listId);
                var template = document.getElementById(templateId);

                if (!button || !list || !template) {
                    return;
                }

                var max = parseInt(button.getAttribute('data-max'), 10) || 1;
                var index = list.querySelectorAll(rowSelector).length;

                function sync() {
                    button.disabled = index >= max;
                }

                button.addEventListener('click', function () {
                    if (index >= max) {
                        return;
                    }

                    list.insertAdjacentHTML('beforeend', template.innerHTML.split('__INDEX__').join(index));
                    index++;
                    sync();
                });

                sync();
            }

            wire('add-host', 'host-rows', 'host-row-template', '[data-host-row]');
            wire('add-event', 'event-rows', 'event-row-template', '[data-event-row]');
            wire('add-story', 'story-rows', 'story-row-template', '[data-story-row]');
            wire('add-gallery', 'gallery-rows', 'gallery-row-template', '[data-gallery-row]');
            wire('add-gift', 'gift-rows', 'gift-row-template', '[data-gift-row]');
            wire('add-contact', 'contact-rows', 'contact-row-template', '[data-contact-row]');
        })();
    </script>
</body>
</html>

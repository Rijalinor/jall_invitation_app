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
        .row__grid { display: grid; gap: .875rem; grid-template-columns: repeat(auto-fit, minmax(min(100%, 13rem), 1fr)); }
        .row__remove { display: inline-flex; align-items: center; gap: .5rem; min-height: 44px; margin-top: .5rem; color: var(--danger); font-size: .9375rem; }

        .field { display: grid; gap: .25rem; }
        .field--wide { grid-column: 1 / -1; }
        .field > span { font-size: .875rem; font-weight: 600; }
        .field small { color: var(--ink-soft); font-size: .8125rem; }
        .photo { width: 96px; height: 96px; object-fit: cover; border-radius: var(--radius); border: 1px solid var(--rule); margin-bottom: .5rem; }

        input[type='text'], input[type='url'], input[type='date'], input[type='time'], input[type='tel'], input[type='number'], select, textarea {
            width: 100%; min-height: 44px; padding: .5rem .625rem;
            border: 1px solid var(--rule); border-radius: var(--radius);
            background: #fff; color: var(--ink); font: inherit;
        }
        input[readonly] { background: var(--paper); color: var(--ink-soft); }
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

        .list { margin: 0; padding: 0; list-style: none; display: grid; gap: .75rem; }
        .list > li { display: flex; flex-wrap: wrap; gap: .75rem; align-items: flex-start; justify-content: space-between; padding: 1rem; border: 1px solid var(--rule); border-radius: var(--radius); background: var(--paper-raised); }
        .list > li > div { min-width: 0; }
        .list p { margin: .35rem 0 0; }
        .list small { display: block; color: var(--ink-soft); font-size: .8125rem; }
        .list__actions { display: flex; flex-wrap: wrap; gap: .5rem; align-items: flex-start; }
        .list__actions form { margin: 0; display: inline-flex; }
        .badge { display: inline-flex; align-items: center; margin-left: .5rem; padding: .125rem .5rem; border-radius: 999px; font-size: .6875rem; font-weight: 600; letter-spacing: .04em; text-transform: uppercase; }
        .badge--ok { color: #1b5e20; background: #e3f2e8; }
        .badge--warn { color: #7a5300; background: #fdf0d5; }
        .badge--bad { color: #8a1c1c; background: #f9e0e4; }
        .panel { margin-top: 1.25rem; padding: .25rem 1rem 1rem; border: 1px solid var(--rule); border-radius: var(--radius); background: var(--paper-raised); }
        .panel > summary { display: flex; align-items: center; min-height: 44px; cursor: pointer; font-weight: 600; }
        .panel form { margin-top: .75rem; }
        .toolbar { display: flex; flex-wrap: wrap; gap: .5rem; align-items: center; margin: 1.25rem 0 .75rem; }
        .toolbar__search { flex: 1 1 14rem; }
        .toolbar__count { color: var(--ink-soft); font-size: .875rem; }

        /* The step's save buttons stay reachable while a long list scrolls. */
        .actions--end {
            position: sticky;
            bottom: 0;
            z-index: 5;
            margin-top: .75rem;
            padding-block: .75rem;
            background: linear-gradient(180deg, rgb(243 241 234 / 0%), var(--paper) 40%);
            border-top: 1px solid var(--rule);
        }

        .button--sm { min-height: 34px; padding: .3rem .6rem; font-size: .75rem; }
        .visually-hidden { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; border: 0; }

        .guest-table { width: 100%; margin-top: .75rem; border: 1px solid var(--rule); border-radius: var(--radius); border-collapse: collapse; background: var(--paper-raised); }
        .guest-table th, .guest-table td { padding: .6rem .7rem; text-align: left; vertical-align: middle; border-bottom: 1px solid var(--rule); }
        .guest-table th { font-size: .72rem; font-weight: 600; letter-spacing: .05em; text-transform: uppercase; color: var(--ink-soft); }
        .guest-table tbody tr:last-child td { border-bottom: 0; }
        .guest-table tr[hidden] { display: none; }
        .guest-table__empty { padding: 1.25rem; text-align: center !important; color: var(--ink-soft); }
        .guest-row__actions { display: flex; flex-wrap: wrap; gap: .35rem; justify-content: flex-end; }
        .guest-row__actions form { margin: 0; display: inline-flex; }

        /* On narrow screens the table collapses to labelled cards. */
        @media (max-width: 40rem) {
            .guest-table { border: 0; background: transparent; }
            .guest-table thead { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); }
            .guest-table, .guest-table tbody, .guest-table tr, .guest-table td { display: block; width: 100%; }
            .guest-table tr { margin-bottom: .6rem; padding: .35rem 0; border: 1px solid var(--rule); border-radius: var(--radius); background: var(--paper-raised); }
            .guest-table td { display: flex; justify-content: space-between; gap: 1rem; padding: .3rem .8rem; border-bottom: 0; }
            .guest-table td::before { content: attr(data-label); font-size: .72rem; font-weight: 600; letter-spacing: .05em; text-transform: uppercase; color: var(--ink-soft); }
            .guest-table td.guest-row__actions { justify-content: flex-start; }
            .guest-table td.guest-row__actions::before { display: none; }
        }

        .guest-dialog { width: min(94vw, 32rem); padding: 0; border: 1px solid var(--rule); border-radius: var(--radius); background: var(--paper); color: var(--ink); }
        .guest-dialog::backdrop { background: rgb(25 23 19 / .45); }
        .guest-dialog form { margin: 0; padding: 1.25rem; display: grid; gap: .9rem; }
        .guest-dialog h2 { font-size: 1.25rem; }
        .guest-dialog__actions { display: flex; flex-wrap: wrap; gap: .6rem; justify-content: flex-end; }
        @media (prefers-reduced-motion: no-preference) {
            .guest-dialog[open] { animation: guest-dialog-in .18s ease-out; }
            @keyframes guest-dialog-in { from { opacity: 0; transform: translateY(.5rem); } }
        }

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

        @if (session('imported_count') !== null)
            <div class="alert alert--ok">{{ session('imported_count') }} tamu berhasil diimpor.</div>
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

                <div class="actions actions--end">
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

                <div class="actions actions--end">
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

                <div class="actions actions--end">
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

                <div class="actions actions--end">
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

                <div class="actions actions--end">
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

                <div class="actions actions--end">
                    <button class="button--quiet button" type="button" id="add-contact" data-max="{{ $maxContacts }}">Tambah kontak</button>
                    <button class="button" type="submit">Simpan dan lanjut ke tamu</button>
                </div>
            </form>
        @elseif ($step === \App\Http\Controllers\InvitationFormController::STEP_GUESTS)
            <p class="lede">Kelola daftar tamu dan bagikan link personal. Setiap tamu punya satu link yang otomatis menyapa namanya di undangan.</p>

            {{-- Import and the template sit at the top, reachable without scrolling
                 past every guest. --}}
            <details class="panel" @if ($guests->isEmpty()) open @endif>
                <summary>Impor tamu dari CSV</summary>
                <form method="POST" action="{{ route('invitation-form.guests-import', ['token' => $token]) }}" enctype="multipart/form-data">
                    @csrf

                    <label class="field">
                        <span>File CSV</span>
                        <input type="file" name="file" accept=".csv,text/csv,text/plain" required>
                        <small>Isi di Excel atau Google Sheets lalu simpan sebagai CSV. Header: name,group,phone,invitation_limit. Contoh baris di template tinggal diganti.</small>
                    </label>

                    <div class="actions">
                        <button class="button" type="submit">Impor CSV</button>
                        <a class="button button--quiet" href="{{ route('invitation-form.guests-template', ['token' => $token]) }}">Unduh template CSV</a>
                    </div>
                </form>
            </details>

            <div class="toolbar">
                <button class="button button--quiet" type="button" id="guest-add" @disabled($guests->count() >= $maxGuests)>Tambah tamu</button>
                <input type="search" id="guest-search" class="toolbar__search" placeholder="Cari nama, grup, atau nomor…" aria-label="Cari tamu">
                <span class="toolbar__count" id="guest-count" data-max="{{ $maxGuests }}">{{ $guests->count() }} / {{ $maxGuests }} tamu</span>
            </div>

            <table class="guest-table">
                <thead>
                    <tr>
                        <th scope="col">Nama tamu</th>
                        <th scope="col">Grup</th>
                        <th scope="col">WhatsApp</th>
                        <th scope="col">Batas</th>
                        <th scope="col"><span class="visually-hidden">Aksi</span></th>
                    </tr>
                </thead>
                <tbody id="guest-rows">
                    @forelse ($guests as $guest)
                        @include('invitation-form.partials.guest-row', ['guest' => $guest])
                    @empty
                        <tr data-guest-empty><td class="guest-table__empty" colspan="5">Belum ada tamu. Klik “Tambah tamu”.</td></tr>
                    @endforelse
                </tbody>
            </table>

            <div class="actions actions--end">
                <a class="button" href="{{ route('invitation-form.show', ['token' => $token, 'step' => \App\Http\Controllers\InvitationFormController::STEP_RSVP]) }}">Lanjut ke RSVP</a>
            </div>

            <dialog class="guest-dialog" id="guest-dialog" aria-labelledby="guest-dialog-title">
                <form method="POST" action="{{ route('invitation-form.update', ['token' => $token, 'step' => $step]) }}">
                    @csrf
                    <input type="hidden" name="after" value="stay">
                    <input type="hidden" name="tamu[0][id]" id="guest-dialog-id" value="">

                    <h2 id="guest-dialog-title">Tambah tamu</h2>

                    <label class="field">
                        <span>Nama tamu</span>
                        <input type="text" name="tamu[0][display_name]" id="guest-dialog-name" maxlength="255" placeholder="Budi Santoso" required>
                    </label>
                    <label class="field">
                        <span>Grup</span>
                        <input type="text" name="tamu[0][group]" id="guest-dialog-group" maxlength="255" placeholder="Keluarga / Teman / Kantor">
                    </label>
                    <label class="field">
                        <span>Nomor WhatsApp</span>
                        <input type="tel" name="tamu[0][phone]" id="guest-dialog-phone" maxlength="50" inputmode="tel" placeholder="081234567890">
                    </label>
                    <label class="field">
                        <span>Batas undangan</span>
                        <input type="number" name="tamu[0][invitation_limit]" id="guest-dialog-limit" min="1" max="20" value="2">
                    </label>

                    <div class="guest-dialog__actions">
                        <button class="button button--quiet" type="button" data-guest-cancel>Batal</button>
                        <button class="button" type="submit">Simpan</button>
                    </div>
                </form>
            </dialog>
        @elseif ($step === \App\Http\Controllers\InvitationFormController::STEP_RSVP)
            @php($rsvpBadges = ['attending' => 'ok', 'tentative' => 'warn', 'not_attending' => 'bad'])

            <p class="lede">Daftar konfirmasi kehadiran dari tamu. Unduh CSV untuk rekap atau hapus entri yang tidak diperlukan.</p>

            <div class="actions" style="margin-top:1rem">
                <a class="button" href="{{ route('invitation-form.rsvp-export', ['token' => $token]) }}">Ekspor CSV</a>
            </div>

            @if ($rsvps->isEmpty())
                <p class="lede" style="margin-top:1.5rem">Belum ada konfirmasi kehadiran.</p>
            @else
                <ul class="list" style="margin-top:1.5rem">
                    @foreach ($rsvps as $rsvp)
                        <li>
                            <div>
                                <strong>{{ $rsvp->name }}</strong>
                                <span class="badge badge--{{ $rsvpBadges[$rsvp->status->value] ?? 'warn' }}">{{ $rsvp->status->label() }}</span>
                                <small>{{ $rsvp->party_size }} orang · {{ $rsvp->submitted_at?->format('d M Y H:i') }}</small>
                                @if ($rsvp->note)<small>{{ $rsvp->note }}</small>@endif
                            </div>
                            <div class="list__actions">
                                <form method="POST" action="{{ route('invitation-form.rsvp-delete', ['token' => $token, 'rsvp' => $rsvp]) }}">
                                    @csrf
                                    <button class="button button--quiet" type="submit">Hapus</button>
                                </form>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        @elseif ($step === \App\Http\Controllers\InvitationFormController::STEP_GUESTBOOK)
            @php($moderationBadges = ['approved' => 'ok', 'pending' => 'warn', 'rejected' => 'bad'])

            <p class="lede">Ucapan hanya tampil di undangan setelah kamu setujui.</p>

            @if ($guestbookEntries->isEmpty())
                <p class="lede" style="margin-top:1.5rem">Belum ada ucapan masuk.</p>
            @else
                <ul class="list" style="margin-top:1.5rem">
                    @foreach ($guestbookEntries as $entry)
                        <li>
                            <div>
                                <strong>{{ $entry->name }}</strong>
                                <span class="badge badge--{{ $moderationBadges[$entry->moderation_status->value] ?? 'warn' }}">{{ $entry->moderation_status->label() }}</span>
                                <small>{{ $entry->created_at->format('d M Y H:i') }}</small>
                                <p>{{ $entry->message }}</p>
                            </div>
                            <div class="list__actions">
                                @if ($entry->moderation_status !== \App\Enums\ModerationStatus::APPROVED)
                                    <form method="POST" action="{{ route('invitation-form.guestbook-moderate', ['token' => $token, 'entry' => $entry]) }}">
                                        @csrf
                                        <input type="hidden" name="action" value="approve">
                                        <button class="button button--quiet" type="submit">Setujui</button>
                                    </form>
                                @endif
                                @if ($entry->moderation_status !== \App\Enums\ModerationStatus::REJECTED)
                                    <form method="POST" action="{{ route('invitation-form.guestbook-moderate', ['token' => $token, 'entry' => $entry]) }}">
                                        @csrf
                                        <input type="hidden" name="action" value="reject">
                                        <button class="button button--quiet" type="submit">Tolak</button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('invitation-form.guestbook-moderate', ['token' => $token, 'entry' => $entry]) }}">
                                    @csrf
                                    <input type="hidden" name="action" value="delete">
                                    <button class="button button--quiet" type="submit">Hapus</button>
                                </form>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
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
                <li>
                    <strong>Tamu</strong>
                    <div>{{ $guests->count() }} tamu terdaftar</div>
                </li>
                <li>
                    <strong>RSVP</strong>
                    @forelse ($rsvps as $rsvp)
                        <div>{{ $rsvp->name }} — {{ $rsvp->status->label() }} ({{ $rsvp->party_size }})</div>
                    @empty
                        <div>Belum ada konfirmasi.</div>
                    @endforelse
                </li>
                <li>
                    <strong>Ucapan</strong>
                    <div>{{ $guestbookEntries->count() }} ucapan · {{ $guestbookEntries->where('moderation_status', \App\Enums\ModerationStatus::APPROVED)->count() }} disetujui</div>
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
        // Copy a guest's personal link without a page round-trip.
        (function () {
            document.querySelectorAll('[data-copy]').forEach(function (button) {
                button.addEventListener('click', function () {
                    var label = button.textContent;
                    navigator.clipboard.writeText(button.getAttribute('data-copy')).then(function () {
                        button.textContent = 'Tersalin';
                        setTimeout(function () { button.textContent = label; }, 1500);
                    }).catch(function () {});
                });
            });
        })();

        (function () {
            function wire(buttonId, listId, templateId, rowSelector, prepend) {
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

                    var html = template.innerHTML.split('__INDEX__').join(index);

                    list.insertAdjacentHTML(prepend ? 'afterbegin' : 'beforeend', html);
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

        // Guests: the add / edit dialog, the per-row delete confirmation, and the
        // search box that narrows the table.
        (function () {
            var dialog = document.getElementById('guest-dialog');
            var add = document.getElementById('guest-add');
            var list = document.getElementById('guest-rows');
            var search = document.getElementById('guest-search');
            var count = document.getElementById('guest-count');

            if (dialog && add && typeof dialog.showModal === 'function') {
                var form = dialog.querySelector('form');
                var title = document.getElementById('guest-dialog-title');
                var idField = document.getElementById('guest-dialog-id');
                var nameField = document.getElementById('guest-dialog-name');
                var groupField = document.getElementById('guest-dialog-group');
                var phoneField = document.getElementById('guest-dialog-phone');
                var limitField = document.getElementById('guest-dialog-limit');

                var open = function (data) {
                    data = data || {};

                    title.textContent = data.id ? 'Ubah tamu' : 'Tambah tamu';
                    idField.value = data.id || '';
                    nameField.value = data.name || '';
                    groupField.value = data.group || '';
                    phoneField.value = data.phone || '';
                    limitField.value = data.limit || 2;

                    dialog.showModal();
                    nameField.focus();
                };

                add.addEventListener('click', function () { open(null); });

                if (list) {
                    list.addEventListener('click', function (event) {
                        var button = event.target.closest('[data-guest-edit]');

                        if (button) {
                            open({
                                id: button.dataset.id,
                                name: button.dataset.name,
                                group: button.dataset.group,
                                phone: button.dataset.phone,
                                limit: button.dataset.limit,
                            });
                        }
                    });
                }

                dialog.querySelector('[data-guest-cancel]').addEventListener('click', function () { dialog.close(); });
                dialog.addEventListener('close', function () { form.reset(); });
            }

            // Every per-row delete form asks before it submits.
            document.querySelectorAll('[data-confirm]').forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!window.confirm(form.getAttribute('data-confirm'))) {
                        event.preventDefault();
                    }
                });
            });

            if (!list) {
                return;
            }

            function rows() {
                return Array.prototype.slice.call(list.querySelectorAll('[data-guest-row]'));
            }

            function refreshCount() {
                if (!count) {
                    return;
                }

                var all = rows();
                var shown = all.filter(function (row) { return !row.hidden; }).length;
                var max = count.dataset.max;

                count.textContent = shown === all.length
                    ? all.length + (max ? ' / ' + max : '') + ' tamu'
                    : shown + ' dari ' + all.length + ' tamu';
            }

            if (search) {
                search.addEventListener('input', function () {
                    var query = search.value.trim().toLowerCase();

                    rows().forEach(function (row) {
                        var haystack = Array.prototype.map.call(row.querySelectorAll('td[data-label]'), function (cell) {
                            return cell.textContent;
                        }).join(' ').toLowerCase();

                        row.hidden = query !== '' && haystack.indexOf(query) === -1;
                    });

                    refreshCount();
                });

                search.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                    }
                });
            }
        })();
    </script>
</body>
</html>

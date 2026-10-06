{{--
    Combined "konfirmasi & ucapan" section, shared.

    Some customers want one form instead of two: the guest states attendance and
    leaves a single note that doubles as their wish. It posts to
    invitations.confirmation and the shared RSVP/guestbook styling is reused.
--}}
<section class="invitation-section" id="rsvp" data-height="{{ $section_heights['rsvp'] ?? 'full' }}" aria-labelledby="rsvp-title">
    <span class="invitation-eyebrow">Konfirmasi Kehadiran</span>
    <h2 id="rsvp-title">RSVP &amp; Ucapan</h2>
    <p>{!! nl2br(e($labels['rsvp_intro'] ?? 'Mohon berikan konfirmasi kehadiran Anda.')) !!}</p>
    @if (($guestbook_count ?? 0) > 0)<p class="invitation-wishes__count">{{ $guestbook_count }} Ucapan</p>@endif

    <form class="invitation-form" method="post" action="{{ $confirmation_url }}" data-invitation-form>
        @csrf
        @if ($guest_token)<input type="hidden" name="guest_token" value="{{ $guest_token }}">@endif
        <label class="invitation-honeypot" aria-hidden="true">Website<input name="website" tabindex="-1" autocomplete="off"></label>
        <label>Nama<input name="name" value="{{ old('name', $recipient ?? '') }}" maxlength="150" required></label>
        <label>Status kehadiran
            <select name="status" required>
                <option value="">Pilih status</option>
                <option value="attending" @selected(old('status') === 'attending')>Hadir</option>
                <option value="not_attending" @selected(old('status') === 'not_attending')>Tidak hadir</option>
                <option value="tentative" @selected(old('status') === 'tentative')>Belum pasti</option>
            </select>
        </label>
        <label>Catatan &amp; Ucapan<textarea name="message" maxlength="1000" rows="4" required>{{ old('message') }}</textarea></label>
        <div class="invitation-errors" role="alert" data-form-errors @unless ($errors->rsvp->any()) hidden @endunless>{{ $errors->rsvp->first() }}</div>
        <p class="invitation-notice" role="status" data-form-status @unless (session('rsvp_success')) hidden @endunless>{{ session('rsvp_success') }}</p>
        <button type="submit">Kirim Konfirmasi &amp; Ucapan</button>
    </form>

    @include('invitations.shared.rsvp-summary')

    @if (count($wishes))
        <div class="invitation-wishes" role="region" aria-label="Daftar ucapan" tabindex="0">
            @foreach ($wishes as $wish)<blockquote><p>“{{ $wish['message'] }}”</p><cite>— {{ $wish['name'] }}</cite></blockquote>@endforeach
        </div>
    @endif
</section>

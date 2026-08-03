<section class="er-section" aria-labelledby="rsvp-title">
    <span class="er-eyebrow">Konfirmasi Kehadiran</span>
    <h2 id="rsvp-title">RSVP</h2>
    <p>Mohon berikan konfirmasi kehadiran Anda.</p>

    @if (session('rsvp_success'))<p class="er-notice" role="status">{{ session('rsvp_success') }}</p>@endif

    <form class="er-form" method="post" action="{{ $rsvp_url }}">
        @csrf
        @if ($guest_token)<input type="hidden" name="guest_token" value="{{ $guest_token }}">@endif
        <label>Nama<input name="name" value="{{ old('name', $recipient) }}" maxlength="150" required></label>
        <label>Status kehadiran
            <select name="status" required>
                <option value="">Pilih status</option>
                <option value="attending" @selected(old('status') === 'attending')>Hadir</option>
                <option value="not_attending" @selected(old('status') === 'not_attending')>Tidak hadir</option>
                <option value="tentative" @selected(old('status') === 'tentative')>Belum pasti</option>
            </select>
        </label>
        <label>Jumlah yang hadir<input type="number" name="party_size" value="{{ old('party_size', 1) }}" min="0" max="{{ $invitation_limit }}" required></label>
        <label>Catatan<textarea name="note" maxlength="500" rows="3">{{ old('note') }}</textarea></label>
        @if ($errors->rsvp->any())<div class="er-errors" role="alert">{{ $errors->rsvp->first() }}</div>@endif
        <button type="submit">Kirim Konfirmasi</button>
    </form>
</section>

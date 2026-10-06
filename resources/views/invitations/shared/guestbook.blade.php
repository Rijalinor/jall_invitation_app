<section class="invitation-section invitation-section--tint" data-height="{{ $section_heights['guestbook'] ?? 'full' }}" aria-labelledby="guestbook-title">
    <span class="invitation-eyebrow">Doa &amp; Ucapan</span>
    <h2 id="guestbook-title">Buku Ucapan</h2>
    @if (($guestbook_count ?? 0) > 0)<p class="invitation-wishes__count">{{ $guestbook_count }} Ucapan</p>@endif

    <form class="invitation-form" method="post" action="{{ $guestbook_url }}" data-invitation-form>
        @csrf
        @if ($guest_token)<input type="hidden" name="guest_token" value="{{ $guest_token }}">@endif
        <label class="invitation-honeypot" aria-hidden="true">Website<input name="website" tabindex="-1" autocomplete="off"></label>
        <label>Nama<input name="name" value="{{ old('name', $recipient ?? '') }}" maxlength="150" required></label>
        <label>Ucapan<textarea name="message" maxlength="1000" rows="4" required>{{ old('message') }}</textarea></label>
        <div class="invitation-errors" role="alert" data-form-errors @unless ($errors->guestbook->any()) hidden @endunless>{{ $errors->guestbook->first() }}</div>
        <p class="invitation-notice" role="status" data-form-status @unless (session('guestbook_success')) hidden @endunless>{{ session('guestbook_success') }}</p>
        <button type="submit">Kirim Ucapan</button>
    </form>

    @if (count($wishes))
        <div class="invitation-wishes" role="region" aria-label="Daftar ucapan" tabindex="0">
            @foreach ($wishes as $wish)<blockquote><p>“{{ $wish['message'] }}”</p><cite>— {{ $wish['name'] }}</cite></blockquote>@endforeach
        </div>
    @endif
</section>

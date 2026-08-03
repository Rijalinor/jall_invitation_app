<section class="er-section er-section--tint" aria-labelledby="guestbook-title">
    <span class="er-eyebrow">Doa &amp; Ucapan</span>
    <h2 id="guestbook-title">Buku Ucapan</h2>

    @if (session('guestbook_success'))<p class="er-notice" role="status">{{ session('guestbook_success') }}</p>@endif

    <form class="er-form" method="post" action="{{ $guestbook_url }}">
        @csrf
        @if ($guest_token)<input type="hidden" name="guest_token" value="{{ $guest_token }}">@endif
        <label class="er-honeypot" aria-hidden="true">Website<input name="website" tabindex="-1" autocomplete="off"></label>
        <label>Nama<input name="name" value="{{ old('name', $recipient) }}" maxlength="150" required></label>
        <label>Ucapan<textarea name="message" maxlength="1000" rows="4" required>{{ old('message') }}</textarea></label>
        @if ($errors->guestbook->any())<div class="er-errors" role="alert">{{ $errors->guestbook->first() }}</div>@endif
        <button type="submit">Kirim Ucapan</button>
    </form>

    @if (count($wishes))
        <div class="er-wishes">
            @foreach ($wishes as $wish)<blockquote><p>“{{ $wish['message'] }}”</p><cite>— {{ $wish['name'] }}</cite></blockquote>@endforeach
        </div>
    @endif
</section>

{{--
    A bank transfer drawn as an ATM/debit card, shared by every template.

    Only the presentation lives here: the account number, holder, provider, and
    notes all come from the shared gift view model, so an operator still fills in
    the same fields. The provider name drives a text wordmark and its brand
    colour; an unrecognised bank falls back to the invitation's own accent.

    `introInDelivery` (optional) heads the physical delivery block with the
    shared "Kirim Hadiah" mark instead of leaving it at the top of the section.
--}}
@php
    $provider = trim((string) ($gift['provider'] ?? ''));
    $haystack = mb_strtolower($provider);

    $brands = [
        'bca' => ['wordmark' => 'BCA', 'color' => '#0060af'],
        'central asia' => ['wordmark' => 'BCA', 'color' => '#0060af'],
        'mandiri' => ['wordmark' => 'mandiri', 'color' => '#0a3a75'],
        'bni' => ['wordmark' => 'BNI', 'color' => '#0b6e6e'],
        'bri' => ['wordmark' => 'BRI', 'color' => '#00529c'],
        'syariah indonesia' => ['wordmark' => 'BSI', 'color' => '#00a39d'],
        'bsi' => ['wordmark' => 'BSI', 'color' => '#00a39d'],
        'cimb' => ['wordmark' => 'CIMB Niaga', 'color' => '#7a0f2e'],
        'permata' => ['wordmark' => 'Permata', 'color' => '#0e5e5e'],
        'danamon' => ['wordmark' => 'Danamon', 'color' => '#f26f21'],
        'btn' => ['wordmark' => 'BTN', 'color' => '#005e6a'],
        'ocbc' => ['wordmark' => 'OCBC', 'color' => '#d0021b'],
        'maybank' => ['wordmark' => 'Maybank', 'color' => '#c8a300'],
    ];

    $brand = null;
    foreach ($brands as $needle => $candidate) {
        if (str_contains($haystack, $needle)) {
            $brand = $candidate;
            break;
        }
    }

    $wordmark = $brand['wordmark'] ?? (mb_strtoupper(preg_replace('/^bank\s+/i', '', $provider)) ?: 'BANK');
    $number = trim((string) ($gift['account_number'] ?? ''));
    $grouped = trim((string) chunk_split((string) preg_replace('/\s+/', '', $number), 4, ' '));
    $holder = trim((string) ($gift['account_name'] ?? ''));
@endphp

<article class="invitation-bank-card" style="--inv-bank: {{ $brand['color'] ?? 'var(--inv-accent, #555)' }}">
    <div class="invitation-bank-card__head">
        <span class="invitation-bank-card__chip" aria-hidden="true"></span>
        <span class="invitation-bank-card__contactless" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" focusable="false">
                <path d="M6 9a7 7 0 0 1 0 6"></path>
                <path d="M9.5 6.5a11 11 0 0 1 0 11"></path>
                <path d="M13 4a15 15 0 0 1 0 16"></path>
            </svg>
        </span>
        <span class="invitation-bank-card__logo">{{ $wordmark }}</span>
    </div>
    <div class="invitation-bank-card__body">
        <span class="invitation-bank-card__label">Nomor Rekening</span>
        <span class="invitation-bank-card__number">{{ $grouped }}</span>
    </div>
    <div class="invitation-bank-card__foot">
        <span class="invitation-bank-card__holder">
            <small>Atas Nama</small>
            <strong>{{ $holder !== '' ? mb_strtoupper($holder) : '—' }}</strong>
        </span>
        <button type="button" class="invitation-bank-card__copy" data-copy="{{ $number }}">Salin Nomor</button>
    </div>
</article>
@if (! empty($gift['delivery_address']))
    <div class="invitation-bank-card__delivery">
        @if ($introInDelivery ?? false)
            @include('invitations.shared.gift-intro')
        @endif
        <small>Kirim Hadiah Fisik</small>
        <span>{{ $gift['delivery_address'] }}</span>
        <button type="button" data-copy="{{ $gift['delivery_address'] }}">Salin Alamat Hadiah</button>
    </div>
@endif
@if (! empty($gift['notes']))
    <p class="invitation-bank-card__note">{!! nl2br(e($gift['notes'])) !!}</p>
@endif

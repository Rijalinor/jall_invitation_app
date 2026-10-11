{{--
    A non-bank gift method (e-wallet or physical gift) as a tidy card, shared by
    every template. Only presentation lives here; the label, provider, number,
    holder, address, and notes all come from the shared gift view model, so an
    operator fills the same fields as before.

    A physical gift is headed by the shared "Kirim Hadiah" mark instead of a small
    icon, so the mark sits with the delivery address it describes rather than
    floating above the whole section.
--}}
<article class="invitation-gift-method">
    @if (($gift['type'] ?? null) === 'physical_gift')
        @include('invitations.shared.gift-intro')
    @endif

    <header class="invitation-gift-method__head">
        @if (($gift['type'] ?? null) !== 'physical_gift')
            <span class="invitation-gift-method__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                    <path d="M3 8a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v2H3z"></path>
                    <path d="M3 10v8a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-8"></path>
                    <path d="M16 13h2"></path>
                </svg>
            </span>
        @endif
        <span class="invitation-gift-method__titles">
            <small>{{ $gift['type_label'] }}</small>
            <strong>{{ $gift['provider'] }}</strong>
        </span>
    </header>

    @if ($gift['account_number'])
        <div class="invitation-gift-method__number">
            <small>Nomor rekening / e-wallet</small>
            <strong>{{ $gift['account_number'] }}</strong>
        </div>
    @endif

    @if ($gift['delivery_address'])
        <div class="invitation-gift-method__number">
            <small>Alamat pengiriman hadiah</small>
            <strong>{{ $gift['delivery_address'] }}</strong>
        </div>
    @endif

    @if ($gift['account_name'])
        <p class="invitation-gift-method__holder">Atas nama <strong>{{ $gift['account_name'] }}</strong></p>
    @endif

    @if ($gift['account_number'] || $gift['delivery_address'])
        <div class="invitation-gift-method__actions">
            @if ($gift['account_number'])<button type="button" data-copy="{{ $gift['account_number'] }}">Salin Nomor</button>@endif
            @if ($gift['delivery_address'])<button type="button" data-copy="{{ $gift['delivery_address'] }}">Salin Alamat Hadiah</button>@endif
        </div>
    @endif

    @if ($gift['notes'])
        <p class="invitation-gift-method__note">{!! nl2br(e($gift['notes'])) !!}</p>
    @endif
</article>

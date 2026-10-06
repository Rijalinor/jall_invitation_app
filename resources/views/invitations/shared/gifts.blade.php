{{--
    Digital gifts, shared by every template: one "Kirim Hadiah" mark followed by
    a card per method. A bank transfer is drawn as an ATM card; e-wallet and
    physical gifts get the neutral method card. Only presentation lives here, so
    every design fills the same admin fields.

    `introInDelivery` (optional, off by default) drops the "Kirim Hadiah" mark
    from the top of the section and places it inside the bank transfer's physical
    delivery block instead, so a design can head that card with it.
--}}
@php
    $introInDelivery = $introInDelivery ?? false;

    // The mark sits at the top of the section by default. A design may ask for
    // it inside a bank transfer's physical-delivery block instead; that block
    // only renders when a gift carries a delivery address, so fall back to the
    // top rather than dropping the mark when none does.
    $introInDelivery = $introInDelivery
        && collect($gifts)->contains(fn ($gift) => ($gift['type'] ?? null) === 'bank_transfer'
            && ! empty($gift['account_number'])
            && ! empty($gift['delivery_address']));
@endphp
<div class="invitation-gifts">
    @unless ($introInDelivery)
        @include('invitations.shared.gift-intro')
    @endunless

    @foreach ($gifts as $gift)
        @if (($gift['type'] ?? null) === 'bank_transfer' && $gift['account_number'])
            @include('invitations.shared.gift-bank-card', ['gift' => $gift, 'introInDelivery' => $introInDelivery])
        @else
            @include('invitations.shared.gift-method', ['gift' => $gift])
        @endif
    @endforeach
</div>

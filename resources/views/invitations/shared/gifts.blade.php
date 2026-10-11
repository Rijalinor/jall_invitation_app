{{--
    Digital gifts, shared by every template: a card per method. A bank transfer
    is drawn as an ATM card; e-wallet and physical gifts get the neutral method
    card. Only presentation lives here, so every design fills the same admin
    fields.

    The "Kirim Hadiah" mark never floats above the section: it belongs with the
    physical delivery it describes. A dedicated physical-gift method heads its
    card with it; when only a bank transfer carries a delivery address, that
    block heads it instead.
--}}
@php
    $introInDelivery = $introInDelivery ?? false;

    $hasPhysicalMethod = collect($gifts)->contains(fn ($gift) => ($gift['type'] ?? null) === 'physical_gift');

    // The bank card only heads its delivery block with the mark when there is no
    // dedicated physical-gift card to carry it, so the mark never appears twice.
    $introInDelivery = $introInDelivery
        && ! $hasPhysicalMethod
        && collect($gifts)->contains(fn ($gift) => ($gift['type'] ?? null) === 'bank_transfer'
            && ! empty($gift['account_number'])
            && ! empty($gift['delivery_address']));
@endphp
<div class="invitation-gifts">
    @foreach ($gifts as $gift)
        @if (($gift['type'] ?? null) === 'bank_transfer' && $gift['account_number'])
            @include('invitations.shared.gift-bank-card', ['gift' => $gift, 'introInDelivery' => $introInDelivery])
        @else
            @include('invitations.shared.gift-method', ['gift' => $gift])
        @endif
    @endforeach
</div>

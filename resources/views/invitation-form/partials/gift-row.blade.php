@php
    $field = fn (string $name): string => "hadiah[{$index}][{$name}]";
    $value = fn (string $name, mixed $default = null): mixed => old("hadiah.{$index}.{$name}", $default);
    $currentType = $value('type', $gift?->type?->value ?? 'bank_transfer');
@endphp

<fieldset class="row" data-gift-row>
    <legend>{{ $gift ? 'Hadiah' : 'Hadiah tambahan' }}</legend>

    @if ($gift)
        <input type="hidden" name="{{ $field('id') }}" value="{{ $gift->id }}">
    @endif

    <div class="row__grid">
        <label class="field">
            <span>Jenis hadiah</span>
            <select name="{{ $field('type') }}">
                @foreach ($giftTypes as $type => $label)
                    <option value="{{ $type }}" @selected($currentType === $type)>{{ $label }}</option>
                @endforeach
            </select>
        </label>

        <label class="field">
            <span>Bank, e-wallet, atau kurir</span>
            <input type="text" name="{{ $field('provider') }}" value="{{ $value('provider', $gift?->provider) }}" maxlength="100" placeholder="BCA / GoPay / JNE">
        </label>

        <label class="field">
            <span>Atas nama</span>
            <input type="text" name="{{ $field('account_name') }}" value="{{ $value('account_name', $gift?->account_name) }}" maxlength="255">
        </label>

        <label class="field">
            <span>Nomor rekening atau e-wallet</span>
            <input type="text" name="{{ $field('account_number') }}" value="{{ $value('account_number', $gift?->account_number) }}" maxlength="255" inputmode="numeric">
        </label>

        <label class="field field--wide">
            <span>Alamat pengiriman</span>
            <textarea name="{{ $field('delivery_address') }}" rows="2" maxlength="500">{{ $value('delivery_address', $gift?->delivery_address) }}</textarea>
            <small>Hanya perlu diisi kalau memilih jenis hadiah fisik.</small>
        </label>

        <label class="field field--wide">
            <span>Catatan tambahan</span>
            <textarea name="{{ $field('notes') }}" rows="2" maxlength="500">{{ $value('notes', $gift?->notes) }}</textarea>
        </label>
    </div>

    @if ($gift)
        <label class="row__remove">
            <input type="checkbox" name="{{ $field('remove') }}" value="1" @checked($value('remove'))>
            <span>Hapus hadiah ini</span>
        </label>
    @endif
</fieldset>

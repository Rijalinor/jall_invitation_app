@php
    $field = fn (string $name): string => "kontak[{$index}][{$name}]";
    $value = fn (string $name, mixed $default = null): mixed => old("kontak.{$index}.{$name}", $default);
@endphp

<fieldset class="row" data-contact-row>
    <legend>{{ $contact ? 'Kontak' : 'Kontak tambahan' }}</legend>

    @if ($contact)
        <input type="hidden" name="{{ $field('id') }}" value="{{ $contact->id }}">
    @endif

    <div class="row__grid">
        <label class="field">
            <span>Peran</span>
            <input type="text" name="{{ $field('label') }}" value="{{ $value('label', $contact?->label) }}" maxlength="255" placeholder="CP Keluarga Pria / WO / Panitia">
        </label>

        <label class="field">
            <span>Nama</span>
            <input type="text" name="{{ $field('name') }}" value="{{ $value('name', $contact?->name) }}" maxlength="255">
        </label>

        <label class="field">
            <span>Nomor WhatsApp</span>
            <input type="tel" name="{{ $field('phone') }}" value="{{ $value('phone', $contact?->phone) }}" maxlength="50" inputmode="tel" placeholder="081234567890">
            <small>Boleh ditulis 08… atau +62…</small>
        </label>
    </div>

    @if ($contact)
        <label class="row__remove">
            <input type="checkbox" name="{{ $field('remove') }}" value="1" @checked($value('remove'))>
            <span>Hapus kontak ini</span>
        </label>
    @endif
</fieldset>

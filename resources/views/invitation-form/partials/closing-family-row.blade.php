@php
    $field = fn (string $name): string => "closing_families[{$index}][{$name}]";
    $value = fn (string $name, mixed $default = null): mixed => old("closing_families.{$index}.{$name}", $default);
@endphp

<fieldset class="row row--family" data-family-row>
    <legend class="visually-hidden">Keluarga</legend>

    <div class="row__grid">
        <label class="field">
            <span>Label</span>
            <input type="text" name="{{ $field('label') }}" value="{{ $value('label', $family?->label) }}" maxlength="120" placeholder="Keluarga Mempelai Pria">
        </label>

        <label class="field">
            <span>Nama anggota</span>
            <textarea name="{{ $field('names') }}" rows="2" maxlength="400" placeholder="Bapak Ahmad &amp; Ibu Siti">{{ $value('names', $family?->names) }}</textarea>
            <small>Satu nama per baris bila ingin ditumpuk.</small>
        </label>
    </div>

    <button class="button button--quiet button--sm" type="button" data-family-remove>Hapus baris</button>
</fieldset>

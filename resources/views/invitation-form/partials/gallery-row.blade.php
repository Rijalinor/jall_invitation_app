@php
    $field = fn (string $name): string => "galeri[{$index}][{$name}]";
    $value = fn (string $name, mixed $default = null): mixed => old("galeri.{$index}.{$name}", $default);
    $photoUrl = $item?->path
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($item->path)
        : null;
@endphp

<fieldset class="row" data-gallery-row>
    <legend>{{ $item ? 'Foto' : 'Foto tambahan' }}</legend>

    @if ($item)
        <input type="hidden" name="{{ $field('id') }}" value="{{ $item->id }}">
    @endif

    <div class="row__grid">
        <label class="field field--wide">
            <span>{{ $item ? 'Ganti foto (biarkan kosong jika tidak diubah)' : 'Pilih foto' }}</span>
            @if ($photoUrl)
                <img class="photo" src="{{ $photoUrl }}" alt="{{ $item->alt_text ?: 'Foto galeri' }}" loading="lazy">
            @endif
            <input type="file" name="{{ $field('photo') }}" accept="image/jpeg,image/png,image/webp">
            <small>JPG, PNG, atau WebP. Maksimal 4 MB per foto.</small>
        </label>

        <label class="field">
            <span>Keterangan foto (opsional)</span>
            <input type="text" name="{{ $field('caption') }}" value="{{ $value('caption', $item?->caption) }}" maxlength="500" placeholder="Foto diambil di Bali, 2024">
        </label>

        <label class="field">
            <span>Deskripsi untuk pembaca layar (opsional)</span>
            <input type="text" name="{{ $field('alt_text') }}" value="{{ $value('alt_text', $item?->alt_text) }}" maxlength="255" placeholder="Foto prewedding 1">
        </label>
    </div>

    @if ($item)
        <label class="row__remove">
            <input type="checkbox" name="{{ $field('remove') }}" value="1" @checked($value('remove'))>
            <span>Hapus foto ini</span>
        </label>
    @endif
</fieldset>

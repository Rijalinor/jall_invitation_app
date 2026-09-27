@php
    $field = fn (string $name): string => "cerita[{$index}][{$name}]";
    $value = fn (string $name, mixed $default = null): mixed => old("cerita.{$index}.{$name}", $default);
    $photoUrl = $story?->image_path
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($story->image_path)
        : null;
@endphp

<fieldset class="row" data-story-row>
    <legend>{{ $story ? 'Momen' : 'Momen tambahan' }}</legend>

    @if ($story)
        <input type="hidden" name="{{ $field('id') }}" value="{{ $story->id }}">
    @endif

    <div class="row__grid">
        <label class="field">
            <span>Tanggal atau momen</span>
            <input type="text" name="{{ $field('date') }}" value="{{ $value('date', $story?->date) }}" maxlength="100" placeholder="14 Februari 2020">
        </label>

        <label class="field">
            <span>Judul momen</span>
            <input type="text" name="{{ $field('title') }}" value="{{ $value('title', $story?->title) }}" maxlength="255" placeholder="Awal Pertemuan">
        </label>

        <label class="field field--wide">
            <span>Cerita singkat</span>
            <textarea name="{{ $field('body') }}" rows="3" maxlength="1200">{{ $value('body', $story?->body) }}</textarea>
        </label>

        <label class="field field--wide">
            <span>Foto kenangan (opsional)</span>
            @if ($photoUrl)
                <img class="photo" src="{{ $photoUrl }}" alt="Foto {{ $story?->title }}" loading="lazy">
            @endif
            <input type="file" name="{{ $field('photo') }}" accept="image/jpeg,image/png,image/webp">
            <small>JPG, PNG, atau WebP. Maksimal 4 MB.</small>
        </label>
    </div>

    @if ($story)
        <label class="row__remove">
            <input type="checkbox" name="{{ $field('remove') }}" value="1" @checked($value('remove'))>
            <span>Hapus momen ini</span>
        </label>
    @endif
</fieldset>

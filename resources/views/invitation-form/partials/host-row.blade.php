@php
    $field = fn (string $name): string => "hosts[{$index}][{$name}]";
    $value = fn (string $name, mixed $default = null): mixed => old("hosts.{$index}.{$name}", $default);
    $photoUrl = $host?->photo_path
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($host->photo_path)
        : null;
@endphp

<fieldset class="row" data-host-row>
    <legend>{{ $host ? 'Mempelai' : 'Mempelai tambahan' }}</legend>

    @if ($host)
        <input type="hidden" name="{{ $field('id') }}" value="{{ $host->id }}">
    @endif

    <div class="row__grid">
        <label class="field">
            <span>Nama lengkap</span>
            <input type="text" name="{{ $field('name') }}" value="{{ $value('name', $host?->name) }}" maxlength="150" autocomplete="name">
        </label>

        <label class="field">
            <span>Peran</span>
            <select name="{{ $field('role') }}">
                <option value="">Pilih peran</option>
                @foreach ($hostRoles as $role => $label)
                    <option value="{{ $role }}" @selected($value('role', $host?->role) === $role)>{{ $label }}</option>
                @endforeach
            </select>
        </label>

        <label class="field">
            <span>Nama panggilan</span>
            <input type="text" name="{{ $field('nickname') }}" value="{{ $value('nickname', $host?->nickname) }}" maxlength="100">
        </label>

        <label class="field">
            <span>Anak ke-</span>
            <input type="text" name="{{ $field('birth_order') }}" value="{{ $value('birth_order', $host?->birth_order) }}" maxlength="100" placeholder="Putra pertama dari">
        </label>

        <label class="field">
            <span>Nama ayah</span>
            <input type="text" name="{{ $field('parent_father') }}" value="{{ $value('parent_father', $host?->parent_father) }}" maxlength="150">
        </label>

        <label class="field">
            <span>Nama ibu</span>
            <input type="text" name="{{ $field('parent_mother') }}" value="{{ $value('parent_mother', $host?->parent_mother) }}" maxlength="150">
        </label>

        <label class="field">
            <span>Instagram</span>
            <input type="text" name="{{ $field('social_instagram') }}" value="{{ $value('social_instagram', $host?->social_instagram) }}" maxlength="100" placeholder="tanpa tanda @">
        </label>

        <label class="field">
            <span>TikTok</span>
            <input type="text" name="{{ $field('social_tiktok') }}" value="{{ $value('social_tiktok', $host?->social_tiktok) }}" maxlength="100" placeholder="tanpa tanda @">
        </label>

        <label class="field field--wide">
            <span>Biodata singkat</span>
            <textarea name="{{ $field('bio') }}" rows="3" maxlength="600">{{ $value('bio', $host?->bio) }}</textarea>
        </label>

        <label class="field field--wide">
            <span>Foto</span>
            @if ($photoUrl)
                <img class="photo" src="{{ $photoUrl }}" alt="Foto {{ $host?->name }}" loading="lazy">
            @endif
            <input type="file" name="{{ $field('photo') }}" accept="image/jpeg,image/png,image/webp">
            <small>JPG, PNG, atau WebP. Maksimal 4 MB. Foto besar akan dikecilkan otomatis.</small>
        </label>
    </div>

    @if ($host)
        <label class="row__remove">
            <input type="checkbox" name="{{ $field('remove') }}" value="1" @checked($value('remove'))>
            <span>Hapus mempelai ini dari undangan</span>
        </label>
    @endif
</fieldset>

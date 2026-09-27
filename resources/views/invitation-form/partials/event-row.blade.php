@php
    $field = fn (string $name): string => "acara[{$index}][{$name}]";
    $value = fn (string $name, mixed $default = null): mixed => old("acara.{$index}.{$name}", $default);
@endphp

<fieldset class="row" data-event-row>
    <legend>{{ $event ? 'Acara' : 'Acara tambahan' }}</legend>

    @if ($event)
        <input type="hidden" name="{{ $field('id') }}" value="{{ $event->id }}">
    @endif

    <div class="row__grid">
        <label class="field">
            <span>Nama acara</span>
            <input type="text" name="{{ $field('label') }}" value="{{ $value('label', $event?->label) }}" maxlength="150" placeholder="Akad Nikah / Resepsi">
        </label>

        <label class="field">
            <span>Tanggal</span>
            <input type="date" name="{{ $field('date') }}" value="{{ $value('date', $event?->date?->toDateString()) }}">
        </label>

        <label class="field">
            <span>Mulai</span>
            <input type="time" name="{{ $field('start_time') }}" value="{{ $value('start_time', $event?->start_time ? \Illuminate\Support\Str::substr($event->start_time, 0, 5) : null) }}">
        </label>

        <label class="field">
            <span>Selesai</span>
            <input type="time" name="{{ $field('end_time') }}" value="{{ $value('end_time', $event?->end_time ? \Illuminate\Support\Str::substr($event->end_time, 0, 5) : null) }}">
        </label>

        <label class="field">
            <span>Zona waktu</span>
            <select name="{{ $field('timezone') }}">
                @foreach ($timezones as $zone => $label)
                    <option value="{{ $zone }}" @selected($value('timezone', $event?->timezone ?? 'Asia/Jakarta') === $zone)>{{ $label }}</option>
                @endforeach
            </select>
        </label>

        <label class="field">
            <span>Nama tempat</span>
            <input type="text" name="{{ $field('venue_name') }}" value="{{ $value('venue_name', $event?->venue_name) }}" maxlength="150">
        </label>

        <label class="field field--wide">
            <span>Alamat lengkap</span>
            <textarea name="{{ $field('address') }}" rows="2" maxlength="500">{{ $value('address', $event?->address) }}</textarea>
        </label>

        <label class="field field--wide">
            <span>Link Google Maps</span>
            <input type="url" name="{{ $field('map_url') }}" value="{{ $value('map_url', $event?->map_url) }}" maxlength="500" placeholder="https://maps.app.goo.gl/...">
            <small>Buka lokasi di Google Maps, tekan Bagikan, lalu tempel tautannya di sini.</small>
        </label>

        <label class="field">
            <span>Dress code</span>
            <input type="text" name="{{ $field('dress_code') }}" value="{{ $value('dress_code', $event?->dress_code) }}" maxlength="100">
        </label>
    </div>

    @if ($event)
        <label class="row__remove">
            <input type="checkbox" name="{{ $field('remove') }}" value="1" @checked($value('remove'))>
            <span>Hapus acara ini dari undangan</span>
        </label>
    @endif
</fieldset>

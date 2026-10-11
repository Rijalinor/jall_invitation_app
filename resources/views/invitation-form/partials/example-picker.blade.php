@php($examples = $examples ?? [])

@if ($examples !== [])
    <div class="examples">
        <span class="examples__label">Belum ada ide? Klik contoh untuk memakainya:</span>
        <ul>
            @foreach ($examples as $example)
                <li>
                    <button type="button" class="button button--quiet button--sm"
                        data-fill-target="{{ $name }}"
                        data-fill-text="{{ $example }}">{{ \Illuminate\Support\Str::limit(str_replace("\n", ' ', $example), 64) }}</button>
                </li>
            @endforeach
            <li>
                <button type="button" class="button button--quiet button--sm"
                    data-fill-target="{{ $name }}" data-fill-text="">Kosongkan</button>
            </li>
        </ul>
    </div>
@endif

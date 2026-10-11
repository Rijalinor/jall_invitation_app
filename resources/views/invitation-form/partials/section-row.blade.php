@php
    $name = $sectionLabels[$section->key] ?? ucwords(str_replace(['_', ':'], ' ', (string) $section->key));
@endphp

<label class="section-row" data-section-row>
    <input type="hidden" name="sections[{{ $index }}][key]" value="{{ $section->key }}">
    <input type="checkbox" name="sections[{{ $index }}][enabled]" value="1" @checked(filter_var($section->enabled, FILTER_VALIDATE_BOOL))>
    <span class="section-row__name">{{ $name }}</span>
    <span class="section-row__move">
        <button class="button button--quiet button--sm" type="button" data-move="up" aria-label="Naikkan {{ $name }}" title="Naikkan">↑</button>
        <button class="button button--quiet button--sm" type="button" data-move="down" aria-label="Turunkan {{ $name }}" title="Turunkan">↓</button>
    </span>
</label>

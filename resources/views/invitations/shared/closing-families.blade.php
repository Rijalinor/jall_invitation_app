{{--
    Closing families, shared by every template.

    The operator adds one entry per family in the admin; two entries sit beside
    each other, on a phone as well, and the markup stays template-neutral
    (`closing-families*`) and draws its colour from each theme's `--inv-*` tokens.
--}}
@if (count($closing_families))
    <div class="closing-families">
        @foreach ($closing_families as $family)
            <div class="closing-families__item">
                @if ($family['label'])<small>{{ $family['label'] }}</small>@endif
                @if ($family['names'])<p>{!! nl2br(e($family['names'])) !!}</p>@endif
            </div>
        @endforeach
    </div>
@endif

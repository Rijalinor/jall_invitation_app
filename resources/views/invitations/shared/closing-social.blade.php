{{--
    Optional closing social row, shared.

    An Instagram button for each host that published one, plus the WhatsApp share
    action. It renders nothing when no host has an Instagram link, so a design can
    include it unconditionally without leaving an empty row. Template-neutral
    (`closing-social*`) and coloured from each theme's `--inv-*` tokens.
--}}
@php
    $social = collect($hosts ?? [])->filter(fn ($host) => ! empty($host['instagram']));
@endphp
@if ($social->isNotEmpty())
    <div class="closing-social">
        @foreach ($social as $host)
            <a class="closing-social__link" href="{{ $host['instagram'] }}" target="_blank" rel="noopener noreferrer" aria-label="Instagram {{ $host['name'] }}">Instagram</a>
        @endforeach
        @if (! empty($whatsapp_url))
            <a class="closing-social__link" href="{{ $whatsapp_url }}" target="_blank" rel="noopener noreferrer">Bagikan via WhatsApp</a>
        @endif
    </div>
@endif

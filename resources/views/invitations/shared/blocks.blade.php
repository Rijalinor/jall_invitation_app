{{--
    Operator-built blocks, shared by every template that declares the "blocks"
    section. The markup stays template-neutral (`invitation-*`) so each template
    styles it with its own theme, exactly like the shared RSVP and guestbook
    partials.

    Each element may carry its own alignment (`content_json.align`); a heading
    block sets the alignment for its whole band, and an element without its own
    value inherits the band's. The value reaches the page as `data-align`, which
    `resources/css/invitations.css` turns into left/center/right for every theme.
--}}
@php
    /**
     * The operator edits one flat, reorderable list, and a heading block turns it
     * into separate bands: the heading opens a new band and the content blocks
     * below it land inside. That is how several custom sections come out of a
     * single list. A list that starts with content still renders, just without a
     * heading.
     */
    $bands = [];

    foreach ($blocks as $block) {
        if ($block['type'] === 'section') {
            $bands[] = ['title' => $block['title'], 'align' => $block['align'] ?? null, 'blocks' => []];

            continue;
        }

        if ($bands === []) {
            $bands[] = ['title' => null, 'align' => null, 'blocks' => []];
        }

        $bands[array_key_last($bands)]['blocks'][] = $block;
    }
@endphp

@foreach ($bands as $band)
    @php
        $headingId = 'invitation-blocks-'.$loop->iteration;
        $bandAttr = $band['align'] ? ' data-align="'.e($band['align']).'"' : '';
    @endphp

    <section class="invitation-section invitation-blocks" data-height="{{ $section_heights['blocks'] ?? 'full' }}" @if ($band['title']) aria-labelledby="{{ $headingId }}" @endif{!! $bandAttr !!}>
        @if ($band['title'])
            <h2 id="{{ $headingId }}">{{ $band['title'] }}</h2>
        @endif

        @foreach ($band['blocks'] as $block)
            @php
                $align = $block['align'] ?? $band['align'] ?? null;
                $alignAttr = $align ? ' data-align="'.e($align).'"' : '';
            @endphp

            @if ($block['type'] === 'text')
                @if ($block['title'])<h3{!! $alignAttr !!}>{{ $block['title'] }}</h3>@endif
                <p{!! $alignAttr !!}>{!! nl2br(e($block['body'])) !!}</p>
            @elseif ($block['type'] === 'quote')
                <blockquote class="invitation-block-quote"{!! $alignAttr !!}>
                    <p>{!! nl2br(e($block['quote'])) !!}</p>
                    @if ($block['source'])<cite>{{ $block['source'] }}</cite>@endif
                </blockquote>
            @elseif ($block['type'] === 'image')
                <figure class="invitation-block-image"{!! $alignAttr !!}>
                    <img src="{{ $block['url'] }}" alt="{{ $block['caption'] }}" loading="lazy" decoding="async">
                    @if ($block['caption'])<figcaption>{{ $block['caption'] }}</figcaption>@endif
                </figure>
            @elseif ($block['type'] === 'note')
                <p class="invitation-block-note"{!! $alignAttr !!}>{!! nl2br(e($block['body'])) !!}</p>
            @endif
        @endforeach
    </section>
@endforeach

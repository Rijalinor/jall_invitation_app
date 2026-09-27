{{--
    Operator-built blocks, shared by every template that declares the "blocks"
    section. The markup stays template-neutral (`invitation-*`) so each template
    styles it with its own theme, exactly like the shared RSVP and guestbook
    partials.
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
            $bands[] = ['title' => $block['title'], 'blocks' => []];

            continue;
        }

        if ($bands === []) {
            $bands[] = ['title' => null, 'blocks' => []];
        }

        $bands[array_key_last($bands)]['blocks'][] = $block;
    }
@endphp

@foreach ($bands as $band)
    @php $headingId = 'invitation-blocks-'.$loop->iteration; @endphp

    <section class="invitation-section invitation-blocks" data-height="{{ $section_heights['blocks'] ?? 'full' }}" @if ($band['title']) aria-labelledby="{{ $headingId }}" @endif>
        @if ($band['title'])
            <h2 id="{{ $headingId }}">{{ $band['title'] }}</h2>
        @endif

        @foreach ($band['blocks'] as $block)
            @if ($block['type'] === 'text')
                @if ($block['title'])<h3>{{ $block['title'] }}</h3>@endif
                <p>{!! nl2br(e($block['body'])) !!}</p>
            @elseif ($block['type'] === 'quote')
                <blockquote class="invitation-block-quote">
                    <p>{!! nl2br(e($block['quote'])) !!}</p>
                    @if ($block['source'])<cite>{{ $block['source'] }}</cite>@endif
                </blockquote>
            @elseif ($block['type'] === 'image')
                <figure class="invitation-block-image">
                    <img src="{{ $block['url'] }}" alt="{{ $block['caption'] }}" loading="lazy" decoding="async">
                    @if ($block['caption'])<figcaption>{{ $block['caption'] }}</figcaption>@endif
                </figure>
            @elseif ($block['type'] === 'note')
                <p class="invitation-block-note">{!! nl2br(e($block['body'])) !!}</p>
            @endif
        @endforeach
    </section>
@endforeach

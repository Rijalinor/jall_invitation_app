{{--
    One guest as a compact table row. The details sit on one line and the actions
    a couple reaches for most ride along: send the personal link over WhatsApp,
    open a plain WhatsApp chat, edit through the dialog, or delete. The raw
    personal link stays in data-guest-link for the helpers and the tests. A new
    guest is added through the dialog, which posts one row to the same endpoint.
--}}
@php
    $guestLink = route('invitations.guest', [$invitation->slug, $guest->token]);
    $shareMessage = str_replace(
        '[nama]',
        $guest->display_name,
        $invitation->share_message ?: 'Kepada Yth. [nama], kami mengundang Anda ke acara kami.',
    );
    $wa = app(\App\Services\WhatsAppLink::class);
    $waNumber = $wa->normalize($guest->phone);
    $waText = rawurlencode($shareMessage."\n".$guestLink);

    // A guest without a number gets no recipient on purpose: WhatsApp opens the
    // contact picker instead of routing the link to the operator's own number
    // (the config fallback is meant for the public catalogue, not this table).
    $whatsapp = $waNumber
        ? 'https://wa.me/'.$waNumber.'?text='.$waText
        : 'https://wa.me/?text='.$waText;
@endphp

<tr class="guest-row" data-guest-row data-guest-link="{{ $guestLink }}">
    <td data-label="Nama tamu">{{ $guest->display_name }}</td>
    <td data-label="Grup">{{ $guest->group ?: '—' }}</td>
    <td data-label="WhatsApp">{{ $guest->phone ?: '—' }}</td>
    <td data-label="Batas">{{ $guest->invitation_limit }}</td>
    <td class="guest-row__actions">
        <a class="button button--quiet button--sm" href="{{ $whatsapp }}" target="_blank" rel="noopener noreferrer">Kirim link</a>

        @if ($waNumber)
            <a class="button button--quiet button--sm" href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener noreferrer">WhatsApp</a>
        @endif

        <button class="button button--quiet button--sm" type="button" data-guest-edit
            data-id="{{ $guest->id }}" data-name="{{ $guest->display_name }}" data-group="{{ $guest->group }}"
            data-phone="{{ $guest->phone }}" data-limit="{{ $guest->invitation_limit }}">Ubah</button>

        <form method="POST" action="{{ route('invitation-form.update', ['token' => $token, 'step' => 'tamu']) }}" class="guest-row__delete" data-confirm="Hapus tamu ini?">
            @csrf
            <input type="hidden" name="after" value="stay">
            <input type="hidden" name="tamu[0][id]" value="{{ $guest->id }}">
            <input type="hidden" name="tamu[0][remove]" value="1">
            <button class="button button--quiet button--sm" type="submit">Hapus</button>
        </form>
    </td>
</tr>

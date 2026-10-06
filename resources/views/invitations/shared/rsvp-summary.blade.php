{{--
    Anonymous attendance recap, shared by every template.

    The guest sees how many answered each way — never who. It sits below the
    RSVP / confirmation form (and the wishes list when the two sections are
    merged), so it reads as a result of the responses already given rather than
    a status the guest has to interpret.
--}}
@php
    $summary = array_merge(
        ['attending' => 0, 'tentative' => 0, 'not_attending' => 0],
        is_array($rsvp_summary ?? null) ? $rsvp_summary : [],
    );
@endphp
@if (($theme['show_rsvp_summary'] ?? true) && array_sum($summary) > 0)
    <div class="invitation-rsvp-summary" role="status" aria-label="Rekap konfirmasi kehadiran">
        <p class="invitation-rsvp-summary__title">Rekap Kehadiran</p>
        <ul class="invitation-rsvp-summary__list">
            <li class="invitation-rsvp-summary__item invitation-rsvp-summary__item--attending">
                <strong>{{ $summary['attending'] }}</strong>
                <span>Hadir</span>
            </li>
            <li class="invitation-rsvp-summary__item invitation-rsvp-summary__item--tentative">
                <strong>{{ $summary['tentative'] }}</strong>
                <span>Ragu-ragu</span>
            </li>
            <li class="invitation-rsvp-summary__item invitation-rsvp-summary__item--not-attending">
                <strong>{{ $summary['not_attending'] }}</strong>
                <span>Tidak Hadir</span>
            </li>
        </ul>
    </div>
@endif

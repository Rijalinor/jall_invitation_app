<?php

namespace App\Http\Controllers;

use App\Enums\InvitationStatus;
use App\Models\Event;
use App\Models\Invitation;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class InvitationCalendarController extends Controller
{
    public function download(string $slug, Event $event): Response
    {
        $invitation = Invitation::query()
            ->where('slug', $slug)
            ->where('status', InvitationStatus::PUBLISHED)
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->firstOrFail();
        abort_unless($event->invitation_id === $invitation->id, 404);

        $start = Carbon::parse($event->date->format('Y-m-d').' '.($event->start_time ?: '00:00'), $event->timezone)->utc();
        $end = $event->end_time
            ? Carbon::parse($event->date->format('Y-m-d').' '.$event->end_time, $event->timezone)->utc()
            : $start->copy()->addHours(2);
        $escape = fn (?string $value): string => str_replace(["\r\n", "\n", '\\', ';', ','], ['\\n', '\\n', '\\\\', '\\;', '\\,'], $value ?? '');
        $location = implode(', ', array_filter([$event->venue_name, $event->address]));
        $ics = implode("\r\n", [
            'BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//JALL Invitation//ID', 'CALSCALE:GREGORIAN',
            'BEGIN:VEVENT', 'UID:event-'.$event->id.'@'.parse_url(config('app.url'), PHP_URL_HOST),
            'DTSTAMP:'.now()->utc()->format('Ymd\THis\Z'),
            'DTSTART:'.$start->format('Ymd\THis\Z'), 'DTEND:'.$end->format('Ymd\THis\Z'),
            'SUMMARY:'.$escape($event->label.' — '.$invitation->title),
            'LOCATION:'.$escape($location), 'DESCRIPTION:'.$escape($event->landmark_notes),
            'END:VEVENT', 'END:VCALENDAR', '',
        ]);

        return response($ics, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.str($event->label)->slug().'.ics"',
        ]);
    }
}

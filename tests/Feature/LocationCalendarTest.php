<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationCalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_location_actions_map_and_timezone_aware_calendar_are_rendered(): void
    {
        [$invitation, $event] = $this->invitationWithEvent();

        $this->get('/acara')->assertOk()
            ->assertSee('https://maps.google.com/maps?q=-6.20000000%2C106.81660000&amp;output=embed', false)
            ->assertSee('https://www.google.com/maps/dir/?api=1&amp;destination=-6.20000000%2C106.81660000', false)
            ->assertSee('Salin Alamat')
            ->assertSee(route('invitations.calendar', [$invitation->slug, $event->id]), false)
            ->assertSee('2027-01-10T08:00:00+07:00', false);
    }

    public function test_ics_download_uses_utc_and_rejects_foreign_or_unpublished_events(): void
    {
        [$invitation, $event] = $this->invitationWithEvent();
        $url = route('invitations.calendar', [$invitation->slug, $event->id], false);

        $this->get($url)->assertOk()
            ->assertHeader('content-type', 'text/calendar; charset=utf-8')
            ->assertSee('DTSTART:20270110T010000Z', false)
            ->assertSee('DTEND:20270110T030000Z', false)
            ->assertSee('LOCATION:Gedung Bahagia\\, Jalan Mawar 10', false);

        $invitation->update(['status' => 'draft']);
        $this->get($url)->assertNotFound();
    }

    private function invitationWithEvent(): array
    {
        $invitation = Invitation::create([
            'customer_id' => Customer::create(['name' => 'Pemilik Acara'])->id,
            'title' => 'Acara Bahagia', 'slug' => 'acara', 'event_type' => 'wedding',
            'template_id' => 'elegant-rose', 'status' => 'published',
        ]);
        $event = $invitation->events()->create([
            'label' => 'Akad Nikah', 'date' => '2027-01-10', 'start_time' => '08:00', 'end_time' => '10:00',
            'timezone' => 'Asia/Jakarta', 'venue_name' => 'Gedung Bahagia', 'address' => 'Jalan Mawar 10',
            'latitude' => -6.2, 'longitude' => 106.8166, 'landmark_notes' => 'Sebelah taman kota', 'is_primary' => true,
        ]);

        return [$invitation, $event];
    }
}

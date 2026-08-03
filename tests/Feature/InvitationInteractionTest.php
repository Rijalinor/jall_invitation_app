<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvitationInteractionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_rsvp_respects_party_limit_and_updates_instead_of_duplicating(): void
    {
        $invitation = $this->invitation();
        $guest = $invitation->guests()->create(['display_name' => 'Siti', 'invitation_limit' => 2]);

        $this->from('/acara')->post('/acara/rsvp', [
            'guest_token' => $guest->token, 'name' => 'Nama Palsu', 'status' => 'attending', 'party_size' => 3,
        ])->assertSessionHasErrors('party_size', null, 'rsvp');

        foreach ([2, 1] as $partySize) {
            $this->from('/acara')->post('/acara/rsvp', [
                'guest_token' => $guest->token, 'name' => 'Nama Palsu', 'status' => 'attending', 'party_size' => $partySize,
            ])->assertRedirect('/acara')->assertSessionHas('rsvp_success');
        }

        $this->assertDatabaseCount('rsvps', 1);
        $this->assertDatabaseHas('rsvps', ['guest_id' => $guest->id, 'name' => 'Siti', 'party_size' => 1]);
    }

    public function test_guestbook_is_sanitized_pending_and_honeypot_rejects_bots(): void
    {
        $this->invitation();

        $this->post('/acara/guestbook', [
            'name' => '<b>Budi</b>', 'message' => '<script>alert(1)</script> Semoga bahagia', 'website' => '',
        ])->assertSessionHas('guestbook_success');

        $this->assertDatabaseHas('guestbook_entries', [
            'name' => 'Budi', 'message' => 'alert(1) Semoga bahagia', 'moderation_status' => 'pending',
        ]);

        $this->post('/acara/guestbook', [
            'name' => 'Bot', 'message' => 'Spam', 'website' => 'https://spam.test',
        ])->assertSessionHasErrors('website', null, 'guestbook');
        $this->assertDatabaseCount('guestbook_entries', 1);
    }

    private function invitation(): Invitation
    {
        return Invitation::create([
            'customer_id' => Customer::create(['name' => 'Pemilik Acara'])->id,
            'title' => 'Acara Bahagia', 'slug' => 'acara', 'event_type' => 'wedding',
            'template_id' => 'elegant-rose', 'status' => 'published',
        ]);
    }
}

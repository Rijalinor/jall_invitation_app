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

    public function test_the_combined_confirmation_saves_an_rsvp_and_a_pending_ucapan(): void
    {
        $invitation = $this->invitation();
        $guest = $invitation->guests()->create(['display_name' => 'Siti', 'invitation_limit' => 2]);

        $this->from('/acara')->post('/acara/konfirmasi', [
            'guest_token' => $guest->token,
            'name' => 'Nama Palsu',
            'status' => 'attending',
            'message' => '<script>alert(1)</script> Selamat ya!',
            'website' => '',
        ])->assertRedirect('/acara')->assertSessionHas('rsvp_success');

        $this->assertDatabaseHas('rsvps', ['guest_id' => $guest->id, 'name' => 'Siti', 'status' => 'attending', 'party_size' => 1]);
        $this->assertDatabaseHas('guestbook_entries', [
            'guest_id' => $guest->id, 'name' => 'Siti', 'message' => 'alert(1) Selamat ya!', 'moderation_status' => 'pending',
        ]);

        // The honeypot that guards the guestbook still rejects bots here.
        $this->post('/acara/konfirmasi', [
            'name' => 'Bot', 'status' => 'attending', 'message' => 'Spam', 'website' => 'https://spam.test',
        ])->assertSessionHasErrors('website', null, 'rsvp');
        $this->assertDatabaseCount('guestbook_entries', 1);
    }

    public function test_rsvp_sanitizes_its_note_and_syncs_the_guest_attendance(): void
    {
        $invitation = $this->invitation();
        $guest = $invitation->guests()->create(['display_name' => 'Siti', 'invitation_limit' => 2]);

        $this->from('/acara')->post('/acara/rsvp', [
            'guest_token' => $guest->token, 'name' => 'Siti', 'status' => 'attending', 'party_size' => 2,
            'note' => '<script>alert(1)</script> Datang ya', 'website' => '',
        ])->assertRedirect('/acara')->assertSessionHas('rsvp_success');

        $this->assertDatabaseHas('rsvps', ['guest_id' => $guest->id, 'note' => 'alert(1) Datang ya']);
        $this->assertTrue($guest->fresh()->has_attended);
    }

    public function test_the_rsvp_honeypot_rejects_bots(): void
    {
        $this->invitation();

        $this->post('/acara/rsvp', [
            'name' => 'Bot', 'status' => 'attending', 'party_size' => 1, 'website' => 'https://spam.test',
        ])->assertSessionHasErrors('website', null, 'rsvp');

        $this->assertDatabaseCount('rsvps', 0);
    }

    /**
     * The shared forms submit without reloading via fetch, which asks for JSON.
     * The same submit has to come back as data instead of a redirect.
     */
    public function test_rsvp_ajax_submission_returns_json_instead_of_redirecting(): void
    {
        $invitation = $this->invitation();
        $guest = $invitation->guests()->create(['display_name' => 'Siti', 'invitation_limit' => 2]);

        $this->postJson('/acara/rsvp', [
            'guest_token' => $guest->token, 'name' => 'Nama Palsu', 'status' => 'attending', 'party_size' => 2,
        ])->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('message', 'Konfirmasi kehadiran berhasil disimpan.');

        $this->assertDatabaseHas('rsvps', ['guest_id' => $guest->id, 'party_size' => 2]);
    }

    public function test_ajax_submission_reports_validation_errors_as_json(): void
    {
        $this->invitation();

        $this->postJson('/acara/rsvp', ['status' => 'attending', 'party_size' => 1])
            ->assertStatus(422)
            ->assertJsonValidationErrors('name');

        $this->assertDatabaseCount('rsvps', 0);
    }

    public function test_ajax_party_size_below_one_returns_a_json_error(): void
    {
        $this->invitation();

        $this->postJson('/acara/rsvp', ['name' => 'Budi', 'status' => 'attending', 'party_size' => 0])
            ->assertStatus(422)
            ->assertJsonPath('errors.party_size.0', 'Jumlah tamu hadir minimal 1.');

        $this->assertDatabaseCount('rsvps', 0);
    }

    public function test_guestbook_ajax_submission_returns_json_and_keeps_the_honeypot(): void
    {
        $this->invitation();

        $this->postJson('/acara/guestbook', ['name' => 'Budi', 'message' => 'Semoga bahagia', 'website' => ''])
            ->assertOk()
            ->assertJsonPath('message', 'Ucapan terkirim dan menunggu moderasi.');

        $this->assertDatabaseHas('guestbook_entries', ['name' => 'Budi', 'moderation_status' => 'pending']);

        $this->postJson('/acara/guestbook', ['name' => 'Bot', 'message' => 'Spam', 'website' => 'https://spam.test'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('website');
    }

    public function test_combined_confirmation_ajax_submission_returns_json(): void
    {
        $this->invitation();

        $this->postJson('/acara/konfirmasi', ['name' => 'Budi', 'status' => 'attending', 'message' => 'Selamat ya!'])
            ->assertOk()
            ->assertJsonPath('ok', true);

        $this->assertDatabaseHas('rsvps', ['name' => 'Budi', 'status' => 'attending']);
        $this->assertDatabaseHas('guestbook_entries', ['name' => 'Budi', 'moderation_status' => 'pending']);
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

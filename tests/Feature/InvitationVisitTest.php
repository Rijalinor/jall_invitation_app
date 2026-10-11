<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvitationVisitTest extends TestCase
{
    use RefreshDatabase;

    private function invitation(array $attributes = []): Invitation
    {
        return Invitation::create(array_merge([
            'customer_id' => Customer::create(['name' => 'Pelanggan'])->id,
            'title' => 'Pernikahan Teddy & Anindya',
            'slug' => 'undangan-analitik',
            'event_type' => 'wedding',
            'template_id' => 'elegant-rose',
            'status' => 'published',
        ], $attributes));
    }

    public function test_opening_a_published_invitation_records_a_privacy_safe_visit(): void
    {
        $invitation = $this->invitation();

        $this->get('/'.$invitation->slug)->assertOk();

        $this->assertSame(1, $invitation->visits()->count());

        $visit = $invitation->visits()->first();
        $this->assertNull($visit->guest_id);
        // The raw address must never be stored, only a 64-char hash of it.
        $this->assertSame(64, strlen($visit->ip_hash));
        $this->assertNotSame('127.0.0.1', $visit->ip_hash);
    }

    public function test_a_personal_link_attributes_the_visit_to_its_guest(): void
    {
        $invitation = $this->invitation();
        $guest = $invitation->guests()->create(['display_name' => 'Budi', 'invitation_limit' => 2]);

        $this->get('/'.$invitation->slug.'/g/'.$guest->token)->assertOk();

        $this->assertSame(1, $invitation->visits()->where('guest_id', $guest->id)->count());
    }

    public function test_unpublished_and_expired_invitations_are_not_counted(): void
    {
        $draft = $this->invitation(['slug' => 'undangan-draft', 'status' => 'draft']);
        $expired = $this->invitation(['slug' => 'undangan-kedaluwarsa', 'expires_at' => now()->subDay()]);

        $this->get('/'.$draft->slug)->assertNotFound();
        $this->get('/'.$expired->slug)->assertNotFound();

        $this->assertSame(0, $draft->visits()->count());
        $this->assertSame(0, $expired->visits()->count());
    }

    public function test_the_catalogue_sample_is_not_counted(): void
    {
        // The catalogue opens this row under every design, so it is not traffic
        // a customer cares about.
        $invitation = $this->invitation(['slug' => 'undangan-contoh', 'is_catalog_demo' => true]);

        $this->get('/'.$invitation->slug)->assertOk();

        $this->assertSame(0, $invitation->visits()->count());
    }

    public function test_repeat_opens_are_counted_but_only_once_per_visitor(): void
    {
        $invitation = $this->invitation();

        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.1'])->get('/'.$invitation->slug)->assertOk();
        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.1'])->get('/'.$invitation->slug)->assertOk();
        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.2'])->get('/'.$invitation->slug)->assertOk();

        $this->assertSame(3, $invitation->visits()->count());
        $this->assertSame(2, $invitation->visits()->whereNotNull('ip_hash')->distinct()->count('ip_hash'));
    }
}

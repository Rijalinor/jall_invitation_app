<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicInvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_published_invitations_render_with_safe_personalization(): void
    {
        $invitation = $this->invitation('published');
        $invitation->hosts()->create(['role' => 'groom', 'name' => 'Raka', 'parent_father' => 'Bapak Raka']);
        $invitation->hosts()->create(['role' => 'bride', 'name' => 'Nara', 'parent_mother' => 'Ibu Nara']);
        $invitation->events()->create([
            'label' => 'Akad Nikah', 'date' => '2027-01-10', 'start_time' => '08:00',
            'timezone' => 'Asia/Jakarta', 'venue_name' => 'Gedung Bahagia', 'address' => 'Jalan Mawar 10', 'is_primary' => true,
        ]);

        $this->get('/raka-nara?to='.rawurlencode('<b>Élodie & 家族</b>'))
            ->assertOk()
            ->assertSee('id="invitation-content" tabindex="-1" inert', false)
            ->assertSee('class="er-hosts" data-count="2"', false)
            ->assertSee('class="er-hosts__and"', false)
            ->assertSee('Putra dari Bapak Raka')
            ->assertSee('Putri dari Ibu Nara')
            ->assertSee('Élodie &amp; 家族', false)
            ->assertDontSee('<b>', false)
            ->assertSee('Gedung Bahagia');

        $invitation->update(['status' => 'draft']);
        $this->get('/raka-nara')->assertNotFound();

        $invitation->update(['status' => 'published', 'template_id' => 'template-tidak-dikenal']);
        $this->get('/raka-nara')->assertNotFound();
    }

    public function test_guest_token_and_sparse_invitation_render_with_fallbacks(): void
    {
        $invitation = $this->invitation('published');
        $guest = $invitation->guests()->create(['display_name' => 'Siti Nur Aisyah', 'invitation_limit' => 2]);

        $this->get("/raka-nara/g/{$guest->token}")
            ->assertOk()
            ->assertSee('Siti Nur Aisyah');

        $this->get('/raka-nara/g/token-tidak-valid')
            ->assertOk()
            ->assertSee('Bapak/Ibu/Saudara/i');
    }

    private function invitation(string $status): Invitation
    {
        return Invitation::create([
            'customer_id' => Customer::create(['name' => 'Raka & Nara'])->id,
            'title' => 'Pernikahan Raka & Nara',
            'slug' => 'raka-nara',
            'event_type' => 'wedding',
            'template_id' => 'elegant-rose',
            'status' => $status,
        ]);
    }
}

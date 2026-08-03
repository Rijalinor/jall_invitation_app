<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class InvitationPreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_admin_can_preview_draft_with_validated_theme(): void
    {
        $invitation = $this->invitation(['accent_color' => '#245566', 'motion' => 'off']);
        $url = URL::temporarySignedRoute('invitations.preview', now()->addMinutes(5), $invitation);

        $this->actingAs(User::factory()->create())
            ->get($url)->assertOk()
            ->assertSee('style="--rose-accent: #245566"', false)
            ->assertSee('data-motion="off"', false)
            ->assertSee('Konten Tetap');

        $this->get('/preview/'.$invitation->id)->assertForbidden();
        $this->get('/tema-preview')->assertNotFound();
    }

    public function test_invalid_theme_values_fall_back_without_changing_content(): void
    {
        $invitation = $this->invitation(['accent_color' => 'red; color:black', 'motion' => 'liar']);
        $url = URL::temporarySignedRoute('invitations.preview', now()->addMinutes(5), $invitation);

        $this->actingAs(User::factory()->create())->get($url)->assertOk()
            ->assertSee('style="--rose-accent: #7b2639"', false)
            ->assertSee('data-motion="calm"', false)
            ->assertSee('Konten Tetap');
    }

    private function invitation(array $settings): Invitation
    {
        $invitation = Invitation::create([
            'customer_id' => Customer::create(['name' => 'Pemilik Acara'])->id,
            'title' => 'Tema Preview', 'slug' => 'tema-preview', 'event_type' => 'wedding',
            'template_id' => 'elegant-rose', 'status' => 'draft', 'settings_json' => $settings,
        ]);
        $invitation->stories()->create(['title' => 'Konten Tetap', 'position' => 1]);

        return $invitation;
    }
}

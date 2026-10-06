<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CelestialVowTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_renders_the_full_invitation_with_the_celestial_theme(): void
    {
        $invitation = $this->invitation();

        $invitation->hosts()->createMany([
            ['role' => 'groom', 'name' => 'Rendra', 'parent_father' => 'Hendra', 'parent_mother' => 'Sri', 'position' => 0],
            ['role' => 'bride', 'name' => 'Alya', 'parent_father' => 'Bambang', 'parent_mother' => 'Ratna', 'position' => 1],
        ]);
        $invitation->events()->create([
            'label' => 'Akad Nikah', 'date' => '2027-06-12', 'start_time' => '08:00', 'end_time' => '10:00',
            'timezone' => 'Asia/Makassar', 'venue_name' => 'Pendopo Pantai Indah', 'address' => 'Jl. Pantai No. 1, Bali',
            'is_primary' => true,
        ]);
        $invitation->stories()->create(['date' => '2021', 'title' => 'Pertemuan', 'body' => 'Kami bertemu.', 'position' => 0]);
        $invitation->media()->create(['type' => 'image', 'path' => 'invitations/media/bintang.webp', 'alt_text' => 'Foto malam', 'position' => 0]);
        $invitation->giftMethods()->create([
            'type' => 'bank_transfer', 'provider' => 'Bank Mandiri', 'account_name' => 'Alya',
            'account_number' => '1234567890', 'position' => 0,
        ]);
        $invitation->contacts()->create(['label' => 'Keluarga', 'name' => 'Dewi', 'phone' => '081234567890', 'position' => 0]);

        $this->get('/undangan-langit')
            ->assertOk()
            ->assertSee('celestial-vow', false)
            ->assertSee('--cel-accent: #d8b26a', false)
            ->assertSee('class="cel-cover__cta" href="#celestial-content" data-open-invitation', false)
            ->assertSee('>Buka Undangan</a>', false)
            ->assertSee('id="hosts"', false)
            ->assertSee('id="events"', false)
            ->assertSee('id="story"', false)
            ->assertSee('id="gallery"', false)
            ->assertSee('id="map"', false)
            ->assertSee('id="rsvp"', false)
            ->assertSee('Petunjuk Arah', false)
            ->assertSee('Salin Alamat', false)
            ->assertSee('WITA', false)
            ->assertSee('Rendra &amp; Alya', false)
            ->assertDontSee('Google Calendar')
            ->assertDontSee('Unduh ICS');
    }

    public function test_optional_sections_and_navigation_are_omitted_when_empty(): void
    {
        $this->invitation();

        $response = $this->get('/undangan-langit')->assertOk();

        $response->assertDontSee('id="hosts"', false)
            ->assertDontSee('id="events"', false)
            ->assertDontSee('id="story"', false)
            ->assertDontSee('id="gallery"', false)
            ->assertDontSee('id="map"', false)
            ->assertDontSee('cel-countdown', false)
            ->assertDontSee('href="#hosts"', false)
            ->assertSee('id="cel-opening-title"', false);
    }

    public function test_the_cover_and_gate_work_without_scripting(): void
    {
        $view = (string) file_get_contents(resource_path('invitation-templates/celestial-vow/views/index.blade.php'));
        $css = (string) file_get_contents(resource_path('invitation-templates/celestial-vow/assets/theme.css'));

        $this->assertMatchesRegularExpression('/<a\b[^>]*href="#celestial-content"[^>]*data-open-invitation/', $view);
        $this->assertDoesNotMatchRegularExpression('/<button[^>]*data-open-invitation/', $view);
        $this->assertStringContainsString("document.documentElement.classList.add('js-ready')", $view);

        $this->assertStringContainsString('body:has(main:target)', $css);
        $this->assertStringContainsString('.js-ready [data-gate]', $css);
    }

    public function test_the_accent_colour_is_validated_before_it_reaches_the_page(): void
    {
        $invitation = $this->invitation();
        $invitation->update(['settings_json' => ['accent_color' => '#7fe0c9']]);

        $this->get('/undangan-langit')->assertOk()->assertSee('--cel-accent: #7fe0c9', false);

        $invitation->update(['settings_json' => ['accent_color' => 'url(javascript:alert(1))']]);

        $this->get('/undangan-langit')->assertOk()->assertSee('--cel-accent: #d8b26a', false);
    }

    private function invitation(string $slug = 'undangan-langit'): Invitation
    {
        return Invitation::create([
            'customer_id' => Customer::create(['name' => 'Pemilik Acara'])->id,
            'title' => 'Pernikahan Rendra & Alya',
            'slug' => $slug,
            'event_type' => 'wedding',
            'template_id' => 'celestial-vow',
            'status' => 'published',
        ]);
    }
}

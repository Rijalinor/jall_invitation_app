<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoastalVowTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_renders_the_full_invitation_with_the_coastal_theme(): void
    {
        $invitation = $this->invitation();

        $invitation->hosts()->createMany([
            ['role' => 'groom', 'name' => 'Bagaskara', 'parent_father' => 'Sutrisno', 'parent_mother' => 'Retno', 'position' => 0],
            ['role' => 'bride', 'name' => 'Anindya', 'parent_father' => 'Hartono', 'parent_mother' => 'Sari', 'position' => 1],
        ]);
        $invitation->events()->create([
            'label' => 'Akad Nikah', 'date' => '2027-01-10', 'start_time' => '08:00', 'end_time' => '10:00',
            'timezone' => 'Asia/Jakarta', 'venue_name' => 'Pantai Indah', 'address' => 'Jl. Pantai No. 1, Bali',
            'parking_notes' => "Parkir di area timur\nIkuti papan petunjuk", 'is_primary' => true,
        ]);
        $invitation->stories()->create(['date' => '2021', 'title' => 'Pertemuan', 'body' => 'Kami bertemu di pantai.', 'position' => 0]);
        $invitation->media()->create(['type' => 'image', 'path' => 'invitations/media/pantai.webp', 'alt_text' => 'Foto di pantai', 'position' => 0]);
        $invitation->giftMethods()->create([
            'type' => 'bank_transfer', 'provider' => 'Bank Mandiri', 'account_name' => 'Anindya',
            'account_number' => '1234567890', 'notes' => 'Terima kasih.', 'position' => 0,
        ]);
        $invitation->contacts()->create(['label' => 'Keluarga', 'name' => 'Budi', 'phone' => '081234567890', 'position' => 0]);

        $this->get('/undangan-pesisir')
            ->assertOk()
            ->assertSee('coastal-vow', false)
            ->assertSee('--cv-accent: #2b7a78', false)
            ->assertSee('class="cv-cover__cta" href="#coastal-content" data-open-invitation', false)
            ->assertSee('>Buka Undangan</a>', false)
            ->assertSee('id="hosts"', false)
            ->assertSee('id="events"', false)
            ->assertSee('id="story"', false)
            ->assertSee('id="gallery"', false)
            ->assertSee('id="map"', false)
            ->assertSee('id="rsvp"', false)
            ->assertSee('Petunjuk Arah', false)
            ->assertSee('Salin Alamat', false)
            ->assertSee('data-copy="1234567890"', false)
            ->assertSee('https://wa.me/6281234567890', false)
            ->assertSee('Bagaskara')
            ->assertSee('Anindya');
    }

    public function test_the_multi_line_text_is_escaped_and_keeps_its_breaks(): void
    {
        $invitation = $this->invitation();
        $invitation->update([
            'opening_text' => "Baris pertama\n<script>alert('x')</script> & \"kutip\"",
        ]);
        $invitation->giftMethods()->create([
            'type' => 'bank_transfer', 'provider' => 'Bank Mandiri', 'account_name' => 'Anindya',
            'account_number' => '1234567890', 'notes' => "Mohon konfirmasi\nsetelah transfer", 'position' => 0,
        ]);

        $this->get('/undangan-pesisir')
            ->assertOk()
            ->assertSee("Baris pertama<br />\n&lt;script&gt;", false)
            ->assertDontSee('<script>alert', false)
            ->assertSee("Mohon konfirmasi<br />\nsetelah transfer", false);
    }

    public function test_optional_sections_and_their_navigation_are_omitted_when_empty(): void
    {
        $this->invitation();

        $response = $this->get('/undangan-pesisir')->assertOk();

        $response->assertDontSee('id="hosts"', false)
            ->assertDontSee('id="events"', false)
            ->assertDontSee('id="story"', false)
            ->assertDontSee('id="gallery"', false)
            ->assertDontSee('id="map"', false)
            ->assertDontSee('cv-countdown', false)
            ->assertDontSee('invitation-blocks', false)
            ->assertDontSee('id="cv-gifts-title"', false)
            // No dock link may point at a section that never rendered.
            ->assertDontSee('href="#hosts"', false)
            ->assertDontSee('href="#events"', false)
            ->assertDontSee('href="#story"', false)
            ->assertDontSee('href="#gallery"', false)
            // The invitation itself is still complete and readable.
            ->assertSee('id="cv-opening-title"', false)
            ->assertSee('cv-closing', false);
    }

    public function test_the_cover_and_gate_work_without_scripting(): void
    {
        $view = (string) file_get_contents(resource_path('invitation-templates/coastal-vow/views/index.blade.php'));
        $css = (string) file_get_contents(resource_path('invitation-templates/coastal-vow/assets/theme.css'));

        // The cover control is a real anchor into the invitation root, never a button.
        $this->assertMatchesRegularExpression('/<a\b[^>]*href="#coastal-content"[^>]*data-open-invitation/', $view);
        $this->assertDoesNotMatchRegularExpression('/<button[^>]*data-open-invitation/', $view);
        $this->assertStringContainsString("document.documentElement.classList.add('js-ready')", $view);

        // One CSS rule dismisses the cover, another keeps the gate shut only while
        // scripting is available.
        $this->assertStringContainsString('body:has(main:target)', $css);
        $this->assertStringContainsString('.js-ready [data-gate]', $css);
    }

    public function test_the_accent_colour_is_validated_before_it_reaches_the_page(): void
    {
        $invitation = $this->invitation();
        $invitation->update(['settings_json' => ['accent_color' => '#0f5c58']]);

        $this->get('/undangan-pesisir')->assertOk()->assertSee('--cv-accent: #0f5c58', false);

        $invitation->update(['settings_json' => ['accent_color' => 'url(javascript:alert(1))']]);

        $this->get('/undangan-pesisir')->assertOk()->assertSee('--cv-accent: #2b7a78', false);
    }

    public function test_recipient_names_with_spaces_and_non_ascii_are_shown(): void
    {
        $this->invitation();

        $this->get('/undangan-pesisir?to='.rawurlencode('Bapak Andreas Wijaya — Sørensen'))
            ->assertOk()
            ->assertSee('Bapak Andreas Wijaya', false)
            ->assertSee('Sørensen', false);
    }

    private function invitation(string $slug = 'undangan-pesisir'): Invitation
    {
        return Invitation::create([
            'customer_id' => Customer::create(['name' => 'Pemilik Acara'])->id,
            'title' => 'Pernikahan Anindya & Bagaskara',
            'slug' => $slug,
            'event_type' => 'wedding',
            'template_id' => 'coastal-vow',
            'status' => 'published',
        ]);
    }
}

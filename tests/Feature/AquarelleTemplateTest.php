<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AquarelleTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_invitation_renders_with_its_watercolour_surface_and_motion_hooks(): void
    {
        $invitation = $this->invitation();

        $invitation->hosts()->create(['role' => 'groom', 'name' => 'Bagaskara', 'position' => 0]);
        $invitation->hosts()->create(['role' => 'bride', 'name' => 'Anindya', 'position' => 1]);
        $invitation->events()->create([
            'label' => 'Akad Nikah', 'date' => '2027-01-10', 'start_time' => '08:00',
            'timezone' => 'Asia/Jakarta', 'venue_name' => 'Taman Bunga', 'is_primary' => true,
        ]);

        $response = $this->get('/undangan-aquarelle')->assertOk();

        $response
            ->assertSee('--aq-accent: #d98ba3', false)
            ->assertSee('data-motion="calm"', false)
            ->assertSee('aq-ambient', false)
            ->assertSee('aq-wash--rose', false)
            ->assertSee('aq-brush__fill', false)
            ->assertSee('class="aq-flow"', false)
            ->assertSee('aq-dab--1', false)
            ->assertSee('class="aq-fore"', false)
            ->assertSee('aq-fore__dab--1', false)
            ->assertSee('class="aq-nav"', false)
            ->assertSee('data-reveal', false);

        $response
            ->assertSee('data-open-invitation', false)
            ->assertSee('id="invitation-content" tabindex="-1" data-gate', false);
    }

    public function test_scroll_driven_motion_and_reveal_are_declared_in_the_theme(): void
    {
        $css = (string) file_get_contents(resource_path('invitation-templates/aquarelle/assets/theme.css'));

        // Content is hidden only under the class the script adds.
        $this->assertStringContainsString('.aq-armed [data-reveal]', $css);
        $this->assertStringContainsString('.aq-armed [data-reveal].is-visible', $css);
        // Scroll drives the drifting washes and the painted brush stroke.
        $this->assertStringContainsString('--aq-scroll', $css);
        $this->assertStringContainsString('--aq-progress', $css);
        $this->assertStringContainsString('scaleY(var(--aq-progress', $css);
        $this->assertStringContainsString('--aq-depth', $css);
        $this->assertStringContainsString('background-position: 50% calc(var(--aq-progress', $css);
        $this->assertStringContainsString('.aq-dab ', $css);
        $this->assertStringContainsString('--aq-drift', $css);
        $this->assertStringContainsString('@keyframes aq-drift', $css);
        $this->assertStringContainsString('filter: blur(10px)', $css);
        // Per-section height stays honoured.
        $this->assertStringContainsString('[data-height="half"] { min-height: 50svh; }', $css);
        $this->assertStringContainsString('[data-height="auto"] { min-height: 0; }', $css);
    }

    public function test_multi_line_text_keeps_the_line_breaks_the_operator_typed(): void
    {
        $invitation = $this->invitation();
        $invitation->stories()->create([
            'date' => '2021', 'title' => 'Pertemuan',
            'body' => "Kami bertemu di kampus.\n\nLalu memutuskan menikah.",
            'position' => 1,
        ]);

        $this->get('/undangan-aquarelle')
            ->assertOk()
            ->assertSee("Baris pertama<br />\nBaris kedua", false)
            ->assertSee("Terima kasih<br />\natas doa restunya", false)
            ->assertSee("Kami bertemu di kampus.<br />\n<br />\nLalu memutuskan menikah.", false);
    }

    public function test_multi_line_text_is_escaped_before_the_breaks_are_added(): void
    {
        $invitation = $this->invitation();
        $invitation->update(['opening_text' => "Baris aman\n<script>alert('x')</script>"]);

        $this->get('/undangan-aquarelle')
            ->assertOk()
            ->assertSee("Baris aman<br />\n&lt;script&gt;", false)
            ->assertDontSee('<script>alert', false);
    }

    public function test_switching_template_changes_presentation_without_changing_content(): void
    {
        $invitation = $this->invitation();
        $invitation->update(['opening_text' => 'Konten tetap tersimpan.']);

        $this->get('/undangan-aquarelle')
            ->assertOk()
            ->assertSee('aquarelle', false)
            ->assertSee('Konten tetap tersimpan.');

        $invitation->update(['template_id' => 'elegant-rose']);

        $this->get('/undangan-aquarelle')
            ->assertOk()
            ->assertSee('elegant-rose', false)
            ->assertSee('Konten tetap tersimpan.')
            ->assertDontSee('--aq-accent', false);
    }

    public function test_the_rendered_section_order_follows_the_position_field(): void
    {
        $invitation = $this->invitation();

        $invitation->sections()->create(['key' => 'closing', 'enabled' => true, 'position' => 20]);
        $invitation->sections()->create(['key' => 'hosts', 'enabled' => true, 'position' => 1]);

        $content = $this->get('/undangan-aquarelle')->assertOk()->getContent();

        $this->assertLessThan(strpos($content, 'aq-closing'), strpos($content, 'id="hosts"'));
    }

    public function test_the_gallery_reads_down_the_page(): void
    {
        $invitation = $this->invitation();
        $invitation->hosts()->create(['role' => 'bride', 'name' => 'Anindya', 'position' => 0]);

        for ($i = 1; $i <= 4; $i++) {
            $invitation->media()->create(['type' => 'image', 'path' => 'invitations/media/foto'.$i.'.webp', 'alt_text' => 'Foto '.$i, 'position' => $i]);
        }

        $this->assertSame(4, substr_count($this->get('/undangan-aquarelle')->assertOk()->getContent(), '<figure'));

        $css = (string) file_get_contents(resource_path('invitation-templates/aquarelle/assets/theme.css'));
        $this->assertStringNotContainsString('scroll-snap', $css);
    }

    public function test_a_section_can_be_set_to_fit_its_content(): void
    {
        $invitation = $this->invitation();
        $invitation->hosts()->create(['role' => 'bride', 'name' => 'Anindya', 'position' => 0]);

        $invitation->sections()->create([
            'key' => 'hosts', 'enabled' => true, 'position' => 1, 'content_json' => ['height' => 'auto'],
        ]);

        $this->get('/undangan-aquarelle')
            ->assertOk()
            ->assertSee('data-height="auto" id="hosts"', false);
    }

    public function test_the_opening_section_reuses_the_cover_video_automatically(): void
    {
        $invitation = $this->invitation();
        $invitation->update(['settings_json' => ['cover_video_desktop' => 'invitations/cover-videos/pastel.mp4']]);

        $this->get('/undangan-aquarelle')
            ->assertOk()
            ->assertSee('class="aq-hero__video"', false)
            ->assertSee('invitations/cover-videos/pastel.mp4');
    }

    private function invitation(string $slug = 'undangan-aquarelle'): Invitation
    {
        return Invitation::create([
            'customer_id' => Customer::create(['name' => 'Pemilik Acara'])->id,
            'title' => 'Pernikahan Anindya & Bagaskara',
            'slug' => $slug, 'event_type' => 'wedding',
            'template_id' => 'aquarelle', 'status' => 'published',
            'opening_text' => "Baris pertama\nBaris kedua",
            'closing_message' => "Terima kasih\natas doa restunya",
        ]);
    }
}

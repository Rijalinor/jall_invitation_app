<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnsoTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_invitation_renders_with_its_zen_surface_and_motion_hooks(): void
    {
        $invitation = $this->invitation();

        $invitation->hosts()->create(['role' => 'groom', 'name' => 'Rangga', 'position' => 0]);
        $invitation->hosts()->create(['role' => 'bride', 'name' => 'Sinta', 'position' => 1]);
        $invitation->events()->create([
            'label' => 'Akad Nikah', 'date' => '2027-01-10', 'start_time' => '08:00',
            'timezone' => 'Asia/Jakarta', 'venue_name' => 'Pendopo Sunyi', 'is_primary' => true,
        ]);

        $response = $this->get('/undangan-enso')->assertOk();

        $response
            ->assertSee('--en-accent: #b23a2e', false)
            ->assertSee('data-motion="calm"', false)
            ->assertSee('class="en-wash"', false)
            ->assertSee('en-wash__blot--1', false)
            ->assertSee('class="en-petals"', false)
            ->assertSee('en-petal--1', false)
            ->assertSee('en-petal__blossom', false)
            ->assertSee('class="en-enso"', false)
            ->assertSee('en-enso__ring', false)
            ->assertSee('class="en-brush"', false)
            ->assertSee('class="en-nav"', false)
            ->assertSee('en-hanko', false)
            ->assertSee('data-reveal', false);

        $response
            ->assertSee('data-open-invitation', false)
            ->assertSee('id="invitation-content" tabindex="-1" data-gate', false);
    }

    public function test_the_enso_scroll_signature_and_sakura_layers_are_declared_in_the_theme(): void
    {
        $css = (string) file_get_contents(resource_path('invitation-templates/enso/assets/theme.css'));
        $script = (string) file_get_contents(resource_path('invitation-templates/enso/assets/theme.js'));

        // Content is hidden only under the class the script adds.
        $this->assertStringContainsString('.en-armed [data-reveal]', $css);
        $this->assertStringContainsString('.en-armed [data-reveal].is-visible', $css);
        // The signature: the ensō draws itself once, then turns on its own.
        $this->assertStringContainsString('.en-enso__ring', $css);
        $this->assertStringContainsString('stroke-dashoffset: .92', $css);
        $this->assertStringContainsString('@keyframes en-turn', $css);
        // Scroll-linked highlight: washes drift on --en-scroll, the ink rule and
        // the petals answer --en-progress, and the petals loop across the viewport.
        $this->assertStringContainsString('--en-scroll', $css);
        $this->assertStringContainsString('--en-progress', $css);
        $this->assertStringContainsString('@keyframes en-fall', $css);
        // Layered behind and in front of the content.
        $this->assertStringContainsString('.en-wash {', $css);
        $this->assertStringContainsString('.en-petals { z-index: 5; }', $css);
        // Removed under motion: off and reduced motion.
        $this->assertStringContainsString('.enso[data-motion="off"] .en-petals', $css);
        // One rAF-throttled listener publishes both numbers.
        $this->assertStringContainsString("setProperty('--en-scroll'", $script);
        $this->assertStringContainsString("setProperty('--en-progress'", $script);
    }

    public function test_multi_line_text_keeps_the_line_breaks_the_operator_typed(): void
    {
        $invitation = $this->invitation();
        $invitation->stories()->create([
            'date' => '2021', 'title' => 'Pertemuan',
            'body' => "Kami bertemu di kampus.\n\nLalu memutuskan menikah.",
            'position' => 1,
        ]);

        $this->get('/undangan-enso')
            ->assertOk()
            ->assertSee("Baris pertama<br />\nBaris kedua", false)
            ->assertSee("Terima kasih<br />\natas doa restunya", false)
            ->assertSee("Kami bertemu di kampus.<br />\n<br />\nLalu memutuskan menikah.", false);
    }

    public function test_multi_line_text_is_escaped_before_the_breaks_are_added(): void
    {
        $invitation = $this->invitation();
        $invitation->update(['opening_text' => "Baris aman\n<script>alert('x')</script>"]);

        $this->get('/undangan-enso')
            ->assertOk()
            ->assertSee("Baris aman<br />\n&lt;script&gt;", false)
            ->assertDontSee('<script>alert', false);
    }

    public function test_switching_template_changes_presentation_without_changing_content(): void
    {
        $invitation = $this->invitation();
        $invitation->update(['opening_text' => 'Konten tetap tersimpan.']);

        $this->get('/undangan-enso')
            ->assertOk()
            ->assertSee('enso', false)
            ->assertSee('Konten tetap tersimpan.');

        $invitation->update(['template_id' => 'elegant-rose']);

        $this->get('/undangan-enso')
            ->assertOk()
            ->assertSee('elegant-rose', false)
            ->assertSee('Konten tetap tersimpan.')
            ->assertDontSee('--en-accent', false);
    }

    public function test_the_rendered_section_order_follows_the_position_field(): void
    {
        $invitation = $this->invitation();

        $invitation->sections()->create(['key' => 'closing', 'enabled' => true, 'position' => 20]);
        $invitation->sections()->create(['key' => 'hosts', 'enabled' => true, 'position' => 1]);

        $content = $this->get('/undangan-enso')->assertOk()->getContent();

        $this->assertLessThan(strpos($content, 'en-closing'), strpos($content, 'id="hosts"'));
    }

    public function test_the_gallery_reads_down_the_page(): void
    {
        $invitation = $this->invitation();
        $invitation->hosts()->create(['role' => 'bride', 'name' => 'Sinta', 'position' => 0]);

        for ($i = 1; $i <= 4; $i++) {
            $invitation->media()->create(['type' => 'image', 'path' => 'invitations/media/foto'.$i.'.webp', 'alt_text' => 'Foto '.$i, 'position' => $i]);
        }

        $this->assertSame(4, substr_count($this->get('/undangan-enso')->assertOk()->getContent(), '<figure'));

        $css = (string) file_get_contents(resource_path('invitation-templates/enso/assets/theme.css'));
        $this->assertStringNotContainsString('scroll-snap', $css);
    }

    public function test_a_section_can_be_set_to_fit_its_content(): void
    {
        $invitation = $this->invitation();
        $invitation->hosts()->create(['role' => 'bride', 'name' => 'Sinta', 'position' => 0]);

        $invitation->sections()->create([
            'key' => 'hosts', 'enabled' => true, 'position' => 1, 'content_json' => ['height' => 'auto'],
        ]);

        $this->get('/undangan-enso')
            ->assertOk()
            ->assertSee('data-height="auto" id="hosts"', false);
    }

    public function test_the_opening_section_reuses_the_cover_video_automatically(): void
    {
        $invitation = $this->invitation();
        $invitation->update(['settings_json' => ['cover_video_desktop' => 'invitations/cover-videos/sunyi.mp4']]);

        $this->get('/undangan-enso')
            ->assertOk()
            ->assertSee('class="en-hero__video"', false)
            ->assertSee('invitations/cover-videos/sunyi.mp4');
    }

    private function invitation(string $slug = 'undangan-enso'): Invitation
    {
        return Invitation::create([
            'customer_id' => Customer::create(['name' => 'Pemilik Acara'])->id,
            'title' => 'Pernikahan Rangga & Sinta',
            'slug' => $slug, 'event_type' => 'wedding',
            'template_id' => 'enso', 'status' => 'published',
            'opening_text' => "Baris pertama\nBaris kedua",
            'closing_message' => "Terima kasih\natas doa restunya",
        ]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DecoGalaTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_invitation_renders_with_its_deco_surface_and_motion_hooks(): void
    {
        $invitation = $this->invitation();

        $invitation->hosts()->create(['role' => 'groom', 'name' => 'Bagaskara', 'position' => 0]);
        $invitation->hosts()->create(['role' => 'bride', 'name' => 'Anindya', 'position' => 1]);
        $invitation->events()->create([
            'label' => 'Akad Nikah', 'date' => '2027-01-10', 'start_time' => '08:00',
            'timezone' => 'Asia/Jakarta', 'venue_name' => 'Balai Kartini', 'is_primary' => true,
        ]);

        $response = $this->get('/undangan-deco')->assertOk();

        // The accent setting reaches the page, and the motion mode with it.
        $response
            ->assertSee('--dg-accent: #c9a227', false)
            ->assertSee('data-motion="calm"', false)
            ->assertSee('class="dg-progress"', false)
            ->assertSee('dg-fans', false)
            ->assertSee('dg-backdrop__rays', false)
            ->assertSee('dg-spine__fill', false)
            ->assertSee('dg-cover__rays', false)
            ->assertSee('class="dg-nav"', false)
            ->assertSee('data-reveal', false);

        // The cover opens through a real anchor, and the gate exists for the
        // scriptless open path.
        $response
            ->assertSee('data-open-invitation', false)
            ->assertSee('id="invitation-content" tabindex="-1" data-gate', false);
    }

    public function test_scroll_driven_motion_and_reveal_are_declared_in_the_theme(): void
    {
        $css = (string) file_get_contents(resource_path('invitation-templates/deco-gala/assets/theme.css'));

        // Content is hidden only under the class the script adds.
        $this->assertStringContainsString('.dg-armed [data-reveal]', $css);
        $this->assertStringContainsString('.dg-armed [data-reveal].is-visible', $css);
        // Scroll drives the gold rules, the progress bar, the turning sunburst and
        // the drawing spine — the "wow" the guest feels while scrolling.
        $this->assertStringContainsString('--dg-scroll', $css);
        $this->assertStringContainsString('--dg-progress', $css);
        $this->assertStringContainsString('scaleX(var(--dg-progress', $css);
        $this->assertStringContainsString('.dg-backdrop__rays', $css);
        $this->assertStringContainsString('rotate(calc(var(--dg-progress', $css);
        $this->assertStringContainsString('.dg-spine__fill', $css);
        $this->assertStringContainsString('rotate(calc(var(--dg-scroll', $css);
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

        $this->get('/undangan-deco')
            ->assertOk()
            ->assertSee("Baris pertama<br />\nBaris kedua", false)
            ->assertSee("Terima kasih<br />\natas doa restunya", false)
            ->assertSee("Kami bertemu di kampus.<br />\n<br />\nLalu memutuskan menikah.", false);
    }

    public function test_multi_line_text_is_escaped_before_the_breaks_are_added(): void
    {
        $invitation = $this->invitation();
        $invitation->update(['opening_text' => "Baris aman\n<script>alert('x')</script>"]);

        $this->get('/undangan-deco')
            ->assertOk()
            ->assertSee("Baris aman<br />\n&lt;script&gt;", false)
            ->assertDontSee('<script>alert', false);
    }

    public function test_switching_template_changes_presentation_without_changing_content(): void
    {
        $invitation = $this->invitation();
        $invitation->update(['opening_text' => 'Konten tetap tersimpan.']);

        $this->get('/undangan-deco')
            ->assertOk()
            ->assertSee('deco-gala', false)
            ->assertSee('Konten tetap tersimpan.');

        $invitation->update(['template_id' => 'elegant-rose']);

        $this->get('/undangan-deco')
            ->assertOk()
            ->assertSee('elegant-rose', false)
            ->assertSee('Konten tetap tersimpan.')
            ->assertDontSee('--dg-accent', false);
    }

    public function test_the_rendered_section_order_follows_the_position_field(): void
    {
        $invitation = $this->invitation();

        $invitation->sections()->create(['key' => 'closing', 'enabled' => true, 'position' => 20]);
        $invitation->sections()->create(['key' => 'hosts', 'enabled' => true, 'position' => 1]);

        $content = $this->get('/undangan-deco')->assertOk()->getContent();

        $this->assertLessThan(strpos($content, 'dg-closing'), strpos($content, 'id="hosts"'));
    }

    public function test_the_gallery_reads_down_the_page(): void
    {
        $invitation = $this->invitation();
        $invitation->hosts()->create(['role' => 'bride', 'name' => 'Anindya', 'position' => 0]);

        for ($i = 1; $i <= 4; $i++) {
            $invitation->media()->create(['type' => 'image', 'path' => 'invitations/media/foto'.$i.'.webp', 'alt_text' => 'Foto '.$i, 'position' => $i]);
        }

        $this->assertSame(4, substr_count($this->get('/undangan-deco')->assertOk()->getContent(), '<figure'));

        $css = (string) file_get_contents(resource_path('invitation-templates/deco-gala/assets/theme.css'));
        $this->assertStringNotContainsString('scroll-snap', $css);
    }

    public function test_a_section_can_be_set_to_fit_its_content(): void
    {
        $invitation = $this->invitation();
        $invitation->hosts()->create(['role' => 'bride', 'name' => 'Anindya', 'position' => 0]);

        $invitation->sections()->create([
            'key' => 'hosts', 'enabled' => true, 'position' => 1, 'content_json' => ['height' => 'auto'],
        ]);

        $this->get('/undangan-deco')
            ->assertOk()
            ->assertSee('data-height="auto" id="hosts"', false);
    }

    public function test_the_opening_section_reuses_the_cover_video_automatically(): void
    {
        $invitation = $this->invitation();
        $invitation->update(['settings_json' => ['cover_video_desktop' => 'invitations/cover-videos/gala.mp4']]);

        $this->get('/undangan-deco')
            ->assertOk()
            ->assertSee('class="dg-hero__video"', false)
            ->assertSee('invitations/cover-videos/gala.mp4');
    }

    private function invitation(string $slug = 'undangan-deco'): Invitation
    {
        return Invitation::create([
            'customer_id' => Customer::create(['name' => 'Pemilik Acara'])->id,
            'title' => 'Pernikahan Anindya & Bagaskara',
            'slug' => $slug, 'event_type' => 'wedding',
            'template_id' => 'deco-gala', 'status' => 'published',
            'opening_text' => "Baris pertama\nBaris kedua",
            'closing_message' => "Terima kasih\natas doa restunya",
        ]);
    }
}

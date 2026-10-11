<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JejakPerjalananTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_invitation_renders_with_its_route_and_motion_hooks(): void
    {
        $invitation = $this->invitation();

        $invitation->hosts()->create(['role' => 'groom', 'name' => 'Rangga', 'position' => 0]);
        $invitation->hosts()->create(['role' => 'bride', 'name' => 'Sinta', 'position' => 1]);
        $invitation->events()->create([
            'label' => 'Akad Nikah', 'date' => '2027-01-10', 'start_time' => '08:00',
            'timezone' => 'Asia/Jakarta', 'venue_name' => 'Pendopo Kota', 'is_primary' => true,
        ]);

        $response = $this->get('/undangan-jejak-perjalanan')->assertOk();

        $response
            ->assertSee('--jp-accent: #2b4a6f', false)
            ->assertSee('data-motion="calm"', false)
            ->assertSee('class="jp-bg"', false)
            ->assertSee('jp-contour--1', false)
            ->assertSee('jp-postcard--1', false)
            ->assertSee('class="jp-route"', false)
            ->assertSee('jp-route__dot', false)
            ->assertSee('class="jp-compass"', false)
            ->assertSee('class="jp-nav"', false)
            ->assertSee('jp-ticket', false)
            ->assertSee('data-reveal', false);

        $response
            ->assertSee('data-open-invitation', false)
            ->assertSee('id="invitation-content" tabindex="-1" data-gate', false);
    }

    public function test_the_scroll_route_and_ambient_postcards_are_declared_in_the_theme(): void
    {
        $css = (string) file_get_contents(resource_path('invitation-templates/jejak-perjalanan/assets/theme.css'));

        // Content is hidden only under the class the script adds.
        $this->assertStringContainsString('.jp-armed [data-reveal]', $css);
        $this->assertStringContainsString('.jp-armed [data-reveal].is-visible', $css);
        // Scroll-linked highlight: a route that draws and a compass that answers.
        $this->assertStringContainsString('--jp-scroll', $css);
        $this->assertStringContainsString('--jp-progress', $css);
        $this->assertStringContainsString('calc(var(--jp-progress, 0) * 100%)', $css);
        $this->assertStringContainsString('.jp-compass__needle', $css);
        // Ambient postcards loop across the whole viewport with bounded scroll drift.
        $this->assertStringContainsString('--jp-pdrift', $css);
        $this->assertStringContainsString('@keyframes jp-fall', $css);
        // A detail that animates on its own: the route dashes and the compass ring.
        $this->assertStringContainsString('@keyframes jp-march-y', $css);
        $this->assertStringContainsString('@keyframes jp-spin', $css);
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

        $this->get('/undangan-jejak-perjalanan')
            ->assertOk()
            ->assertSee("Baris pertama<br />\nBaris kedua", false)
            ->assertSee("Terima kasih<br />\natas doa restunya", false)
            ->assertSee("Kami bertemu di kampus.<br />\n<br />\nLalu memutuskan menikah.", false);
    }

    public function test_multi_line_text_is_escaped_before_the_breaks_are_added(): void
    {
        $invitation = $this->invitation();
        $invitation->update(['opening_text' => "Baris aman\n<script>alert('x')</script>"]);

        $this->get('/undangan-jejak-perjalanan')
            ->assertOk()
            ->assertSee("Baris aman<br />\n&lt;script&gt;", false)
            ->assertDontSee('<script>alert', false);
    }

    public function test_switching_template_changes_presentation_without_changing_content(): void
    {
        $invitation = $this->invitation();
        $invitation->update(['opening_text' => 'Konten tetap tersimpan.']);

        $this->get('/undangan-jejak-perjalanan')
            ->assertOk()
            ->assertSee('jejak-perjalanan', false)
            ->assertSee('Konten tetap tersimpan.');

        $invitation->update(['template_id' => 'elegant-rose']);

        $this->get('/undangan-jejak-perjalanan')
            ->assertOk()
            ->assertSee('elegant-rose', false)
            ->assertSee('Konten tetap tersimpan.')
            ->assertDontSee('--jp-accent', false);
    }

    public function test_the_rendered_section_order_follows_the_position_field(): void
    {
        $invitation = $this->invitation();

        $invitation->sections()->create(['key' => 'closing', 'enabled' => true, 'position' => 20]);
        $invitation->sections()->create(['key' => 'hosts', 'enabled' => true, 'position' => 1]);

        $content = $this->get('/undangan-jejak-perjalanan')->assertOk()->getContent();

        $this->assertLessThan(strpos($content, 'jp-closing'), strpos($content, 'id="hosts"'));
    }

    public function test_the_gallery_reads_down_the_page(): void
    {
        $invitation = $this->invitation();
        $invitation->hosts()->create(['role' => 'bride', 'name' => 'Sinta', 'position' => 0]);

        for ($i = 1; $i <= 4; $i++) {
            $invitation->media()->create(['type' => 'image', 'path' => 'invitations/media/foto'.$i.'.webp', 'alt_text' => 'Foto '.$i, 'position' => $i]);
        }

        $this->assertSame(4, substr_count($this->get('/undangan-jejak-perjalanan')->assertOk()->getContent(), '<figure'));

        $css = (string) file_get_contents(resource_path('invitation-templates/jejak-perjalanan/assets/theme.css'));
        $this->assertStringNotContainsString('scroll-snap', $css);
    }

    public function test_a_section_can_be_set_to_fit_its_content(): void
    {
        $invitation = $this->invitation();
        $invitation->hosts()->create(['role' => 'bride', 'name' => 'Sinta', 'position' => 0]);

        $invitation->sections()->create([
            'key' => 'hosts', 'enabled' => true, 'position' => 1, 'content_json' => ['height' => 'auto'],
        ]);

        $this->get('/undangan-jejak-perjalanan')
            ->assertOk()
            ->assertSee('data-height="auto" id="hosts"', false);
    }

    public function test_the_opening_section_reuses_the_cover_video_automatically(): void
    {
        $invitation = $this->invitation();
        $invitation->update(['settings_json' => ['cover_video_desktop' => 'invitations/cover-videos/jalan.mp4']]);

        $this->get('/undangan-jejak-perjalanan')
            ->assertOk()
            ->assertSee('class="jp-hero__video"', false)
            ->assertSee('invitations/cover-videos/jalan.mp4');
    }

    private function invitation(string $slug = 'undangan-jejak-perjalanan'): Invitation
    {
        return Invitation::create([
            'customer_id' => Customer::create(['name' => 'Pemilik Acara'])->id,
            'title' => 'Perjalanan Rangga & Sinta',
            'slug' => $slug, 'event_type' => 'wedding',
            'template_id' => 'jejak-perjalanan', 'status' => 'published',
            'opening_text' => "Baris pertama\nBaris kedua",
            'closing_message' => "Terima kasih\natas doa restunya",
        ]);
    }
}

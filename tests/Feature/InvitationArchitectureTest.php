<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invitation;
use App\Services\TemplateRegistry;
use App\ViewModels\InvitationViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvitationArchitectureTest extends TestCase
{
    use RefreshDatabase;

    public function test_template_registry_only_resolves_registered_templates(): void
    {
        $registry = app(TemplateRegistry::class);

        $this->assertSame('elegant-rose', $registry->find('elegant-rose')['id']);
        $this->assertNull($registry->find('../elegant-rose'));
        $this->assertSame('midnight-ledger', $registry->find('midnight-ledger')['id']);
        $this->assertStringEndsWith('midnight-ledger'.DIRECTORY_SEPARATOR.'preview.svg', $registry->previewPath('midnight-ledger'));
        $this->assertSame('fun-storybook', $registry->find('fun-storybook')['id']);
        $this->assertStringEndsWith('fun-storybook'.DIRECTORY_SEPARATOR.'preview.svg', $registry->previewPath('fun-storybook'));
        $this->assertNull($registry->previewPath('../midnight-ledger'));
    }

    public function test_template_previews_are_served_publicly(): void
    {
        // The public catalogue shows these previews to prospective customers,
        // so the route is intentionally not behind the admin guard.
        $this->get('/template-previews/midnight-ledger')
            ->assertOk()
            ->assertHeader('content-type', 'image/svg+xml');

        $this->get('/template-previews/not-a-real-template')->assertNotFound();
    }

    public function test_view_model_applies_safe_theme_and_section_contract(): void
    {
        $invitation = Invitation::create([
            'customer_id' => Customer::create(['name' => 'Pelanggan'])->id,
            'title' => 'Undangan Aman',
            'slug' => 'undangan-aman',
            'event_type' => 'wedding',
            'template_id' => 'elegant-rose',
            'status' => 'published',
            'settings_json' => ['accent_color' => 'url(javascript:alert(1))', 'motion' => 'liar'],
        ]);
        $invitation->sections()->create(['key' => 'opening', 'enabled' => true, 'position' => 1]);
        $invitation->sections()->create(['key' => 'unknown', 'enabled' => true, 'position' => 2]);
        $manifest = app(TemplateRegistry::class)->find('elegant-rose');

        $data = InvitationViewModel::from($invitation->fresh(), 'Tamu', $manifest)->data;

        $this->assertSame(['opening'], $data['sections']);
        $this->assertSame([
            'accent_color' => '#7b2639',
            'motion' => 'calm',
            'cover_video_enabled' => true,
            'opening_video_enabled' => false,
            'cover_video_desktop' => null,
            'cover_video_mobile' => null,
            'cover_poster_image' => null,
        ], $data['theme']);
    }

    /**
     * A block belongs to a section, and the view model hands them over grouped that
     * way. That grouping is what lets a template render several extra sections and
     * an operator place each one on its own.
     */
    public function test_blocks_are_grouped_by_the_section_that_holds_them(): void
    {
        $invitation = Invitation::create([
            'customer_id' => Customer::create(['name' => 'Pelanggan'])->id,
            'title' => 'Undangan Berkelompok',
            'slug' => 'undangan-berkelompok',
            'event_type' => 'wedding',
            'template_id' => 'elegant-rose',
            'status' => 'published',
        ]);

        $invitation->blocks()->create(['type' => 'text', 'content_json' => ['body' => 'Isi'], 'position' => 0]);

        $data = InvitationViewModel::from(
            $invitation->fresh(), 'Tamu', app(TemplateRegistry::class)->find('elegant-rose'),
        )->data;

        $this->assertSame(['blocks'], array_keys($data['block_sections']));
        $this->assertSame('Isi', $data['block_sections']['blocks'][0]['body']);
    }

    /**
     * The section editor offers an "Urutan Tampilan" field, so the rendered order
     * has to follow it. If it does not, moving a section to the bottom silently
     * does nothing — and the answer to it is structural, not cosmetic.
     */
    public function test_the_rendered_section_order_follows_the_position_field(): void
    {
        $invitation = Invitation::create([
            'customer_id' => Customer::create(['name' => 'Pelanggan'])->id,
            'title' => 'Undangan Urut',
            'slug' => 'undangan-urut',
            'event_type' => 'wedding',
            'template_id' => 'elegant-rose',
            'status' => 'published',
        ]);

        // Created last, but asked to appear first.
        $invitation->sections()->create(['key' => 'closing', 'enabled' => true, 'position' => 20]);
        $invitation->sections()->create(['key' => 'hosts', 'enabled' => true, 'position' => 1]);

        $data = InvitationViewModel::from(
            $invitation->fresh(), 'Tamu', app(TemplateRegistry::class)->find('elegant-rose'),
        )->data;

        $this->assertSame(['hosts', 'closing'], $data['sections']);
    }

    /**
     * fun-storybook declares a background colour, so it has to be changeable and it
     * has to reach the page. A declared setting with no way to set it is a setting
     * that silently does nothing.
     */
    public function test_a_templates_declared_background_colour_reaches_the_render(): void
    {
        $invitation = Invitation::create([
            'customer_id' => Customer::create(['name' => 'Pelanggan'])->id,
            'title' => 'Undangan Latar',
            'slug' => 'undangan-latar',
            'event_type' => 'wedding',
            'template_id' => 'fun-storybook',
            'status' => 'published',
            'settings_json' => ['bg_color' => '#123456'],
        ]);

        $this->get('/undangan-latar')->assertOk()->assertSee('--fsb-bg: #123456', false);

        // Anything that is not a hex colour falls back to the template default.
        $invitation->update(['settings_json' => ['bg_color' => 'url(javascript:alert(1))']]);

        $this->get('/undangan-latar')->assertOk()->assertSee('--fsb-bg: #fdf6e4', false);
    }

    public function test_switching_template_changes_presentation_without_changing_content(): void
    {
        $invitation = Invitation::create([
            'customer_id' => Customer::create(['name' => 'Pelanggan'])->id,
            'title' => 'Cerita Tengah Malam',
            'slug' => 'cerita-tengah-malam',
            'event_type' => 'wedding',
            'template_id' => 'elegant-rose',
            'status' => 'published',
            'opening_text' => 'Konten tetap tersimpan.',
        ]);

        $this->get('/cerita-tengah-malam')->assertOk()->assertSee('elegant-rose', false);

        $invitation->update(['template_id' => 'midnight-ledger']);

        $this->get('/cerita-tengah-malam')->assertOk()
            ->assertSee('midnight-ledger', false)
            ->assertSee('Konten tetap tersimpan.')
            ->assertSee('--ml-accent: #c6a15b', false);

        $invitation->update(['template_id' => 'fun-storybook']);

        $this->get('/cerita-tengah-malam')->assertOk()
            ->assertSee('fun-storybook', false)
            ->assertSee('Konten tetap tersimpan.')
            ->assertSee('--fsb-accent: #ff6b81', false);
        $this->assertSame('Konten tetap tersimpan.', $invitation->fresh()->opening_text);
    }
}

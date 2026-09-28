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

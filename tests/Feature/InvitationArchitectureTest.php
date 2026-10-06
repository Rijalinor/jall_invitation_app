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

    /**
     * Every template offers recommended colours, and the first swatch is always
     * the template default, so an operator can always get back to the stock look.
     * Only plain hex survives, so a manifest cannot inject a bad value.
     */
    public function test_template_colour_presets_are_validated_and_start_from_the_default(): void
    {
        $registry = app(TemplateRegistry::class);

        foreach ($registry->all() as $id => $manifest) {
            $presets = $registry->colorPresets($id, 'accent_color');

            $this->assertNotEmpty($presets, 'Template "'.$id.'" declares no accent presets.');
            $this->assertSame(
                $manifest['settings_schema']['accent_color']['default'],
                $presets[0]['value'],
                'Template "'.$id.'" must lead its presets with the default colour.',
            );

            foreach ($presets as $preset) {
                $this->assertMatchesRegularExpression('/^#[0-9a-f]{6}$/', $preset['value']);
                $this->assertNotSame('', $preset['label']);
            }
        }

        // fun-storybook is the only template with a background setting, so it is
        // the only one that offers background presets.
        $this->assertNotEmpty($registry->colorPresets('fun-storybook', 'bg_color'));
        $this->assertSame([], $registry->colorPresets('coastal-vow', 'bg_color'));

        // Unknown settings and unknown templates simply yield nothing.
        $this->assertSame([], $registry->colorPresets('coastal-vow', 'tidak_ada'));
        $this->assertSame([], $registry->colorPresets('tidak-ada', 'accent_color'));
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
            'cover_video_desktop' => null,
            'cover_video_mobile' => null,
            'cover_poster_image' => null,
            'hide_timezone' => false,
            'merge_rsvp_guestbook' => false,
            'show_rsvp_summary' => true,
        ], $data['theme']);
    }

    /**
     * The RSVP recap is a per-invitation choice, and it has to exist in every
     * template's schema or an operator could not switch it for that design.
     */
    public function test_every_template_declares_the_rsvp_recap_toggle(): void
    {
        $registry = app(TemplateRegistry::class);

        foreach ($registry->all() as $id => $manifest) {
            $definition = $manifest['settings_schema']['show_rsvp_summary'] ?? null;

            $this->assertIsArray($definition, 'Template "'.$id.'" does not declare show_rsvp_summary.');
            $this->assertSame('boolean', $definition['type'] ?? null, 'Template "'.$id.'" must declare it as a boolean.');
        }
    }

    public function test_closing_families_are_cleaned_and_empty_rows_dropped(): void
    {
        $invitation = Invitation::create([
            'customer_id' => Customer::create(['name' => 'Pelanggan'])->id,
            'title' => 'Pernikahan Teddy & Anindya',
            'slug' => 'undangan-keluarga',
            'event_type' => 'wedding',
            'template_id' => 'elegant-rose',
            'status' => 'published',
            'settings_json' => ['closing_families' => [
                ['label' => 'Keluarga Mempelai Pria', 'names' => 'Bapak A & Ibu B'],
                ['label' => '', 'names' => ''],
                ['label' => '<b>Keluarga Wanita</b>', 'names' => "Bapak C\nIbu D"],
            ]],
        ]);

        $data = InvitationViewModel::from(
            $invitation->fresh(), 'Tamu', app(TemplateRegistry::class)->find('elegant-rose'),
        )->data;

        $this->assertSame([
            ['label' => 'Keluarga Mempelai Pria', 'names' => 'Bapak A & Ibu B'],
            ['label' => 'Keluarga Wanita', 'names' => "Bapak C\nIbu D"],
        ], $data['closing_families']);
    }

    public function test_the_couple_title_uses_the_names_and_falls_back_to_the_title(): void
    {
        $invitation = Invitation::create([
            'customer_id' => Customer::create(['name' => 'Pelanggan'])->id,
            'title' => 'Pernikahan Teddy & Anindya',
            'slug' => 'undangan-couple',
            'event_type' => 'wedding',
            'template_id' => 'elegant-rose',
            'status' => 'published',
        ]);
        $manifest = app(TemplateRegistry::class)->find('elegant-rose');

        // Without two hosts, the heading falls back to the invitation title.
        $this->assertSame(
            'Pernikahan Teddy & Anindya',
            InvitationViewModel::from($invitation->fresh(), 'Tamu', $manifest)->data['couple_title'],
        );

        $invitation->hosts()->create(['role' => 'groom', 'name' => 'Teddy', 'position' => 0]);
        $invitation->hosts()->create(['role' => 'bride', 'name' => 'Anindya', 'position' => 1]);

        $this->assertSame(
            'Teddy & Anindya',
            InvitationViewModel::from($invitation->fresh(), 'Tamu', $manifest)->data['couple_title'],
        );
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

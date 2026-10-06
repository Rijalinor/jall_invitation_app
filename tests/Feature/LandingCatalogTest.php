<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invitation;
use App\Services\TemplateRegistry;
use App\Services\WhatsAppLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_lists_every_registered_template(): void
    {
        $templates = app(TemplateRegistry::class)->all();

        $this->assertNotEmpty($templates, 'Expected at least one registered template.');

        $response = $this->get('/')->assertOk();
        $response->assertSee('Katalog desain');

        foreach ($templates as $template) {
            $response->assertSee($template['name']);
            $response->assertSee(route('templates.preview', $template['id']), false);
        }
    }

    public function test_catalogue_only_shows_templates_supporting_the_selected_event_type(): void
    {
        $birthday = $this->get('/?acara=birthday')->assertOk();

        $templates = collect(app(TemplateRegistry::class)->all());

        $supports = $templates->filter(
            fn (array $template): bool => in_array('birthday', $template['event_types'] ?? [], true)
        );
        $unsupported = $templates->reject(
            fn (array $template): bool => in_array('birthday', $template['event_types'] ?? [], true)
        );

        $this->assertNotEmpty($supports, 'Expected at least one birthday template.');
        $this->assertNotEmpty($unsupported, 'Expected at least one non-birthday template.');

        foreach ($supports as $template) {
            $birthday->assertSee($template['name']);
        }

        foreach ($unsupported as $template) {
            $birthday->assertDontSee($template['name']);
        }
    }

    public function test_unknown_event_type_falls_back_to_the_full_catalogue(): void
    {
        $templates = app(TemplateRegistry::class)->all();

        $response = $this->get('/?acara=bukan-acara')->assertOk();

        foreach ($templates as $template) {
            $response->assertSee($template['name']);
        }
    }

    public function test_template_action_carries_the_chosen_design_into_whatsapp(): void
    {
        config()->set('invitation.whatsapp', '08123456789');

        $expected = 'https://wa.me/628123456789?text='.rawurlencode(
            'Halo, saya ingin membuat undangan digital. Desain pilihan saya: Elegant Rose.'
        );

        $this->get('/')->assertOk()->assertSee($expected, false);
    }

    public function test_whatsapp_actions_are_hidden_when_no_number_is_configured(): void
    {
        // Pin both channels so the assertion never depends on the developer's .env.
        config()->set('invitation.whatsapp', null);
        config()->set('invitation.email', null);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('wa.me')
            ->assertSee('INVITATION_WHATSAPP');
    }

    public function test_the_email_channel_carries_the_page_when_whatsapp_is_not_configured(): void
    {
        config()->set('invitation.whatsapp', null);
        config()->set('invitation.email', 'halo@example.com');

        $this->get('/')
            ->assertOk()
            ->assertDontSee('wa.me')
            ->assertSee('mailto:halo@example.com', false)
            ->assertDontSee('INVITATION_WHATSAPP');
    }

    public function test_social_preview_image_is_declared_when_a_raster_preview_exists(): void
    {
        $registry = app(TemplateRegistry::class);

        $hasRaster = collect($registry->all())->contains(function (array $template) use ($registry): bool {
            $path = $registry->previewPath($template['id']);

            return $path !== null && preg_match('/\.(png|jpe?g|webp)$/i', $path) === 1;
        });

        if (! $hasRaster) {
            $this->markTestSkipped('No raster template preview is available for social sharing.');
        }

        $this->get('/')->assertOk()->assertSee('property="og:image"', false);
    }

    public function test_whatsapp_numbers_are_normalised_to_wa_me_format(): void
    {
        $link = app(WhatsAppLink::class);

        $this->assertSame('628123456789', $link->normalize('0812-3456-789'));
        $this->assertSame('628123456789', $link->normalize('+62 812 3456 789'));
        $this->assertSame('628123456789', $link->normalize('62 812 3456 789'));
        $this->assertSame('628123456789', $link->normalize('8123456789'));

        $this->assertNull($link->normalize(''));
        $this->assertNull($link->normalize('0812'));
        $this->assertNull($link->normalize('62'));
    }

    public function test_every_catalogue_entry_has_a_resolvable_preview(): void
    {
        $registry = app(TemplateRegistry::class);

        foreach ($registry->all() as $template) {
            $this->assertNotNull(
                $registry->previewPath($template['id']),
                'Template "'.$template['id'].'" declares a preview file that cannot be resolved.',
            );

            $this->get(route('templates.preview', $template['id']))->assertOk();
        }
    }

    public function test_published_sample_invitation_is_linked_from_the_catalogue(): void
    {
        $sample = $this->sample('elegant-rose', 'contoh-elegant-rose');

        $this->get('/')
            ->assertOk()
            ->assertSee('Lihat contoh')
            ->assertSee(route('invitations.show', $sample->slug), false);
    }

    public function test_sample_must_be_published_and_unexpired_to_appear(): void
    {
        $this->sample('elegant-rose', 'contoh-draft', ['status' => 'draft']);
        $this->sample('midnight-ledger', 'contoh-kedaluwarsa', ['expires_at' => now()->subDay()]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('Lihat contoh')
            ->assertDontSee('/contoh-draft', false)
            ->assertDontSee('/contoh-kedaluwarsa', false);
    }

    public function test_invitation_without_the_sample_flag_is_never_linked(): void
    {
        $this->sample('elegant-rose', 'undangan-biasa', ['is_catalog_demo' => false]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('Lihat contoh')
            ->assertDontSee('/undangan-biasa', false);
    }

    public function test_the_newest_sample_wins_when_a_template_has_several(): void
    {
        $this->sample('elegant-rose', 'contoh-lama');
        $this->sample('elegant-rose', 'contoh-baru');

        $this->get('/')
            ->assertOk()
            ->assertSee('/contoh-baru', false)
            ->assertDontSee('/contoh-lama', false);
    }

    public function test_sample_invitation_stays_embeddable_by_the_catalogue(): void
    {
        // The catalogue previews a sample by embedding it in an iframe, so the
        // public invitation must not send frame blocking headers.
        $sample = $this->sample('elegant-rose', 'contoh-embed');

        $this->get(route('invitations.show', $sample->slug))
            ->assertOk()
            ->assertHeaderMissing('X-Frame-Options')
            ->assertHeaderMissing('Content-Security-Policy');
    }

    /**
     * One sample represents the whole catalogue: the operator fills in a single
     * invitation and every design renders that same content, so there is no
     * per-template sample to maintain.
     */
    public function test_one_sample_serves_every_template_in_its_own_design(): void
    {
        $sample = $this->sample('coastal-vow', 'contoh-tunggal');

        $this->get('/')
            ->assertOk()
            ->assertSee('/contoh-tunggal?template=coastal-vow', false)
            ->assertSee('/contoh-tunggal?template=elegant-rose', false)
            ->assertSee('/contoh-tunggal?template=midnight-ledger', false)
            ->assertSee('/contoh-tunggal?template=fun-storybook', false);

        // The URL picks the design; the sample keeps its own stored template.
        $this->get('/contoh-tunggal?template=elegant-rose')
            ->assertOk()
            ->assertSee('class="elegant-rose"', false)
            ->assertSee('--rose-accent: #7b2639', false)
            ->assertDontSee('class="coastal-vow"', false);

        $this->assertSame('coastal-vow', $sample->fresh()->template_id);
    }

    /**
     * A sample shown in another design must borrow that design's palette, not
     * carry over whichever colours the sample happens to be saved with. The
     * sample's own card keeps the operator's chosen colours.
     */
    public function test_a_sample_shown_in_another_design_uses_that_designs_own_colours(): void
    {
        $this->sample('coastal-vow', 'contoh-tunggal', [
            'settings_json' => ['accent_color' => '#123456'],
        ]);

        // Another design gets its prime colour, not the sample's saved one.
        $this->get('/contoh-tunggal?template=elegant-rose')
            ->assertOk()
            ->assertSee('--rose-accent: #7b2639', false)
            ->assertDontSee('#123456', false);

        // The sample's own card keeps the operator's colour.
        $this->get('/contoh-tunggal?template=coastal-vow')
            ->assertOk()
            ->assertSee('--cv-accent: #123456', false);

        // And so does the plain public link.
        $this->get('/contoh-tunggal')
            ->assertOk()
            ->assertSee('--cv-accent: #123456', false);
    }

    public function test_an_unknown_template_override_falls_back_to_the_samples_own_design(): void
    {
        $this->sample('coastal-vow', 'contoh-tunggal');

        $this->get('/contoh-tunggal?template=tidak-ada')
            ->assertOk()
            ->assertSee('class="coastal-vow"', false)
            ->assertDontSee('--rose-accent', false);
    }

    public function test_a_regular_invitation_ignores_a_template_override(): void
    {
        $this->sample('coastal-vow', 'undangan-biasa-override', ['is_catalog_demo' => false]);

        $this->get('/undangan-biasa-override?template=elegant-rose')
            ->assertOk()
            ->assertSee('class="coastal-vow"', false)
            ->assertDontSee('--rose-accent', false);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function sample(string $template, string $slug, array $attributes = []): Invitation
    {
        return Invitation::create(array_merge([
            'customer_id' => Customer::create(['name' => 'Pemilik Contoh'])->id,
            'title' => 'Contoh '.$slug,
            'slug' => $slug,
            'event_type' => 'wedding',
            'template_id' => $template,
            'status' => 'published',
            'is_catalog_demo' => true,
            'published_at' => now(),
        ], $attributes));
    }
}

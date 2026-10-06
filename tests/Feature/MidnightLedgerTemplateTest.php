<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MidnightLedgerTemplateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The label contract is static, so this proves the wording a template declares
     * actually reaches the page rather than merely being present in its source.
     */
    public function test_the_declared_wording_renders(): void
    {
        $this->invitation();

        // A personalized link greets by name; a general link stays without a name.
        $this->get('/undangan-uji?to='.rawurlencode('Bapak Contoh'))
            ->assertOk()
            ->assertSee('Kepada Yth.')
            ->assertSee('Bapak Contoh')
            ->assertSee('Buka Undangan')
            ->assertSee('Dengan cinta,')
            ->assertSee('Terima kasih telah menjadi bagian dari cerita kami.')
            // The rail mark must not advertise the template's own initials.
            ->assertDontSee('>ML<', false);
    }

    public function test_an_override_replaces_the_template_wording(): void
    {
        $invitation = $this->invitation();
        $invitation->update(['settings_json' => ['labels' => ['cover_cta' => 'Masuk Undangan']]]);

        $this->get('/undangan-uji')
            ->assertOk()
            ->assertSee('Masuk Undangan')
            ->assertDontSee('Buka Undangan');
    }

    /**
     * A branch header pointing at the wrong key is invisible: the section either
     * renders nothing or renders another section's markup. This is that case —
     * contacts present, no gift at all — which is exactly how a mis-wired branch
     * would slip through.
     */
    public function test_the_contacts_section_renders_without_any_gift(): void
    {
        $invitation = $this->invitation();
        $invitation->contacts()->create(['label' => 'Keluarga', 'name' => 'Rani', 'phone' => '081234567890', 'position' => 0]);

        $this->get('/undangan-uji')
            ->assertOk()
            ->assertSee('ml-contacts', false)
            ->assertSee('WhatsApp Rani', false)
            ->assertSee('☎', false);
    }

    /**
     * The operator can build extra sections, and can ask a section to fit its
     * content instead of filling the screen.
     */
    public function test_blocks_and_section_height_work(): void
    {
        $invitation = $this->invitation();
        $invitation->blocks()->createMany([
            ['type' => 'section', 'content_json' => ['title' => 'Kisah Keluarga'], 'position' => 0],
            ['type' => 'quote', 'content_json' => ['quote' => 'Cinta itu sabar.', 'source' => 'Ibu'], 'position' => 1],
        ]);

        $this->get('/undangan-uji')
            ->assertOk()
            ->assertSee('<h2 id="invitation-blocks-1">Kisah Keluarga</h2>', false)
            ->assertSee('Cinta itu sabar.', false)
            ->assertSee('data-height="full"', false);

        $invitation->sections()->where('key', 'blocks')->update(['content_json' => ['height' => 'half']]);

        $this->get('/undangan-uji')->assertOk()->assertSee('data-height="half"', false);

        // The attribute is only meaningful if the rule behind it exists.
        $this->assertStringContainsString(
            '.midnight-ledger [data-height="half"] { min-height: 50svh; }',
            (string) file_get_contents(resource_path('invitation-templates/midnight-ledger/assets/theme.css')),
        );
    }

    /**
     * livestream and sharing are declared sections, so enabling them must render
     * a real body rather than only a navigation action.
     */
    public function test_livestream_and_sharing_render_as_sections(): void
    {
        $invitation = $this->invitation();
        $invitation->update(['livestream_url' => 'https://youtube.com/watch?v=abc']);

        $this->get('/undangan-uji')
            ->assertOk()
            ->assertSee('ml-livestream', false)
            ->assertSee('Siaran Langsung')
            ->assertSee('ml-sharing', false)
            ->assertSee('Sebarkan Undangan')
            ->assertSee('data-share', false);
    }

    private function invitation(): Invitation
    {
        return Invitation::create([
            'customer_id' => Customer::create(['name' => 'Pelanggan'])->id,
            'title' => 'Undangan Uji',
            'slug' => 'undangan-uji',
            'event_type' => 'wedding',
            'template_id' => 'midnight-ledger',
            'status' => 'published',
        ]);
    }
}

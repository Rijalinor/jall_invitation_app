<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invitation;
use App\Services\TemplateRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenunPusakaTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_template_is_registered_and_shippable(): void
    {
        $registry = app(TemplateRegistry::class);
        $manifest = $registry->find('tenun-pusaka');

        $this->assertNotNull($manifest, 'Tenun Pusaka is not registered.');
        $this->assertNotNull($registry->previewPath('tenun-pusaka'), 'Tenun Pusaka has no resolvable preview.');
        $this->assertContains('wedding', $manifest['event_types']);
        $this->assertContains('engagement', $manifest['event_types']);
    }

    public function test_complete_invitation_renders_the_woven_shell(): void
    {
        $invitation = $this->invitation();
        $invitation->hosts()->create(['name' => 'Anindya Puspa', 'role' => 'bride', 'parent_father' => 'Bapak Hartono', 'parent_mother' => 'Ibu Sri', 'position' => 0]);
        $invitation->hosts()->create(['name' => 'Bagaskara Adi', 'role' => 'groom', 'parent_father' => 'Bapak Wibowo', 'position' => 1]);
        $invitation->events()->create(['label' => 'Akad Nikah', 'date' => '2027-01-10', 'start_time' => '08:00', 'timezone' => 'Asia/Jakarta', 'venue_name' => 'Masjid Agung', 'address' => 'Jl. Merdeka 1', 'is_primary' => true, 'position' => 0]);
        $invitation->events()->create(['label' => 'Resepsi', 'date' => '2027-01-10', 'start_time' => '11:00', 'timezone' => 'Asia/Jakarta', 'venue_name' => 'Balai Kartini', 'is_primary' => false, 'position' => 1]);
        $invitation->stories()->create(['date' => 'Maret 2021', 'title' => 'Pertemuan pertama', 'body' => 'Kami bertemu di kampus.', 'position' => 0]);
        $invitation->media()->create(['type' => 'image', 'path' => 'invitations/media/a.jpg', 'alt_text' => 'Foto satu', 'position' => 0]);
        $invitation->giftMethods()->create(['type' => 'bank_transfer', 'provider' => 'Bank Mandiri', 'account_name' => 'Anindya Puspa', 'account_number' => '1234567890', 'position' => 0]);
        $invitation->contacts()->create(['label' => 'Keluarga', 'name' => 'Rani', 'phone' => '08123456789', 'position' => 0]);

        $response = $this->get('/'.$invitation->slug)->assertOk();

        $response->assertSee('tenun-pusaka');
        $response->assertSee('Anindya Puspa');
        $response->assertSee('Bagaskara Adi');
        $response->assertSee('Akad Nikah');
        $response->assertSee('Masjid Agung');
        $response->assertSee('Pertemuan pertama');
        $response->assertSee('Bank Mandiri');

        // The cover control must stay a real anchor so the invitation opens
        // without scripting.
        $response->assertSee('data-open-invitation', false);
        $response->assertSee('href="#tp-content"', false);
        $response->assertDontSee('inert', false);
    }

    public function test_sparse_invitation_renders_with_fallbacks(): void
    {
        $invitation = $this->invitation('undangan-sederhana');

        $this->get('/'.$invitation->slug)
            ->assertOk()
            ->assertSee('tenun-pusaka')
            // The opening statement falls back instead of rendering an empty band.
            ->assertSee('Dengan menyebut nama Tuhan', false);
    }

    public function test_the_manifest_schema_matches_what_the_view_reads(): void
    {
        $manifest = app(TemplateRegistry::class)->find('tenun-pusaka');
        $view = file_get_contents(resource_path('invitation-templates/tenun-pusaka/views/index.blade.php'));

        preg_match_all('/\$theme\[\'(?<key>[a-z_]+)\'\]/', (string) $view, $matches);

        $this->assertNotSame([], $matches['key']);

        foreach (array_unique($matches['key']) as $key) {
            $this->assertArrayHasKey(
                $key,
                $manifest['settings_schema'],
                sprintf('Tenun Pusaka reads theme key "%s" that its manifest never declares, so it would fall back at runtime.', $key),
            );
        }
    }

    private function invitation(string $slug = 'undangan-tenun'): Invitation
    {
        return Invitation::create([
            'customer_id' => Customer::create(['name' => 'Pelanggan'])->id,
            'title' => 'Pernikahan Anindya & Bagaskara',
            'slug' => $slug,
            'event_type' => 'wedding',
            'template_id' => 'tenun-pusaka',
            'status' => 'published',
            'published_at' => now(),
        ]);
    }
}

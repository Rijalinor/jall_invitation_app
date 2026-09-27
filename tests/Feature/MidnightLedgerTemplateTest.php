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

        $this->get('/undangan-uji')
            ->assertOk()
            ->assertSee('Kepada Yth.')
            ->assertSee('Buka Undangan')
            ->assertSee('Dengan cinta,')
            ->assertSee('Terima kasih telah menjadi bagian dari cerita kami.');
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

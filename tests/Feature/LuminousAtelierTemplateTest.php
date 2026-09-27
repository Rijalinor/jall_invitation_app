<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LuminousAtelierTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_template_is_registered_and_sparse_invitation_renders_its_cinematic_shell(): void
    {
        Invitation::create([
            'customer_id' => Customer::create(['name' => 'Pemilik Acara'])->id,
            'title' => 'Acara Bahagia',
            'slug' => 'luminous-atelier',
            'event_type' => 'wedding',
            'template_id' => 'luminous-atelier',
            'status' => 'published',
        ]);

        $this->get('/luminous-atelier')
            ->assertOk()
            ->assertSee('luminous-atelier', false)
            ->assertSee('Instrument+Serif', false)
            ->assertSee('Bapak/Ibu/Saudara/i')
            ->assertSee('Buka undangan')
            ->assertSee('Pass the', false)
            ->assertDontSee('data-countdown', false)
            ->assertDontSee('data-lightbox-src', false);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FunStorybookTemplateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * livestream is a declared section, so enabling it must render a real body
     * rather than disappearing without a trace.
     */
    public function test_the_livestream_section_renders_when_enabled(): void
    {
        $this->invitation()->update(['livestream_url' => 'https://youtube.com/watch?v=abc']);

        $this->get('/undangan-fsb')
            ->assertOk()
            ->assertSee('id="livestream-title"', false)
            ->assertSee('https://youtube.com/watch?v=abc', false)
            ->assertSee('SIARAN LANGSUNG');
    }

    /**
     * The section is declared but has no URL, so it must not leave an empty band.
     */
    public function test_the_livestream_section_is_hidden_without_a_url(): void
    {
        $this->invitation()->update(['livestream_url' => 'javascript:alert(1)']);

        $this->get('/undangan-fsb')
            ->assertOk()
            ->assertDontSee('id="livestream-title"', false);
    }

    private function invitation(): Invitation
    {
        return Invitation::create([
            'customer_id' => Customer::create(['name' => 'Pelanggan'])->id,
            'title' => 'Undangan Uji FSB',
            'slug' => 'undangan-fsb',
            'event_type' => 'wedding',
            'template_id' => 'fun-storybook',
            'status' => 'published',
        ]);
    }
}

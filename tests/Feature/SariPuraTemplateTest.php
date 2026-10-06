<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SariPuraTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_renders_the_full_invitation_with_the_sari_pura_theme(): void
    {
        $invitation = $this->invitation();
        $invitation->hosts()->createMany([
            ['role' => 'groom', 'name' => 'Made Arya', 'parent_father' => 'Wayan', 'parent_mother' => 'Ni Luh', 'position' => 0],
            ['role' => 'bride', 'name' => 'Ayu Kirana', 'parent_father' => 'Putu', 'parent_mother' => 'Kadek', 'position' => 1],
        ]);
        $invitation->events()->create([
            'label' => 'Upacara Pernikahan', 'date' => '2027-06-12', 'start_time' => '10:00', 'end_time' => '12:00',
            'timezone' => 'Asia/Makassar', 'venue_name' => 'Taman Sari Ubud', 'address' => 'Jalan Raya Ubud, Bali',
            'is_primary' => true,
        ]);
        $invitation->stories()->create(['date' => '2021', 'title' => 'Pertemuan pertama', 'body' => 'Di sebuah sore.', 'position' => 0]);
        $invitation->media()->create(['type' => 'image', 'path' => 'invitations/media/ubud.webp', 'alt_text' => 'Pasangan di Ubud', 'position' => 0]);
        $invitation->giftMethods()->create([
            'type' => 'bank_transfer', 'provider' => 'Bank Mandiri', 'account_name' => 'Ayu Kirana',
            'account_number' => '1234567890', 'position' => 0,
        ]);
        $invitation->contacts()->create(['label' => 'Keluarga', 'name' => 'Dewi', 'phone' => '081234567890', 'position' => 0]);

        $this->get('/undangan-sari')
            ->assertOk()
            ->assertSee('sari-pura', false)
            ->assertSee('--sp-accent: #a9543b', false)
            ->assertSee('class="sp-button sp-button--solid" href="#sari-content" data-open-invitation', false)
            ->assertSee('>Buka Undangan</a>', false)
            ->assertSee('id="hosts"', false)
            ->assertSee('id="events"', false)
            ->assertSee('id="story"', false)
            ->assertSee('id="gallery"', false)
            ->assertSee('id="map"', false)
            ->assertSee('id="rsvp"', false)
            ->assertSee('Jalan Raya Ubud, Bali')
            ->assertSee('WITA', false)
            ->assertSee('Made Arya')
            ->assertSee('Ayu Kirana')
            ->assertSee('invitation-bank-card', false);
    }

    public function test_optional_sections_and_navigation_are_omitted_when_empty(): void
    {
        $this->invitation();

        $this->get('/undangan-sari')
            ->assertOk()
            ->assertDontSee('id="hosts"', false)
            ->assertDontSee('id="events"', false)
            ->assertDontSee('id="story"', false)
            ->assertDontSee('id="gallery"', false)
            ->assertDontSee('id="map"', false)
            ->assertDontSee('sp-countdown', false)
            ->assertDontSee('href="#hosts"', false)
            ->assertSee('id="sp-opening-title"', false)
            ->assertSee('class="invitation-section sp-closing"', false);
    }

    public function test_the_cover_and_gate_work_without_scripting(): void
    {
        $view = (string) file_get_contents(resource_path('invitation-templates/sari-pura/views/index.blade.php'));
        $css = (string) file_get_contents(resource_path('invitation-templates/sari-pura/assets/theme.css'));

        $this->assertMatchesRegularExpression('/<a\b[^>]*href="#sari-content"[^>]*data-open-invitation/', $view);
        $this->assertDoesNotMatchRegularExpression('/<button[^>]*data-open-invitation/', $view);
        $this->assertStringContainsString("document.documentElement.classList.add('js-ready')", $view);
        $this->assertStringContainsString('body:has(main:target)', $css);
        $this->assertStringContainsString('.js-ready [data-gate]', $css);
    }

    public function test_the_accent_colour_is_validated_before_it_reaches_the_page(): void
    {
        $invitation = $this->invitation();
        $invitation->update(['settings_json' => ['accent_color' => '#53634e']]);

        $this->get('/undangan-sari')->assertOk()->assertSee('--sp-accent: #53634e', false);

        $invitation->update(['settings_json' => ['accent_color' => 'url(javascript:alert(1))']]);

        $this->get('/undangan-sari')->assertOk()->assertSee('--sp-accent: #a9543b', false);
    }

    public function test_recipient_names_with_spaces_and_non_ascii_are_shown(): void
    {
        $this->invitation();

        $this->get('/undangan-sari?to='.rawurlencode('Bapak Made Sørensen — परिवार'))
            ->assertOk()
            ->assertSee('Bapak Made Sørensen', false)
            ->assertSee('परिवार', false);
    }

    private function invitation(): Invitation
    {
        return Invitation::create([
            'customer_id' => Customer::create(['name' => 'Pemilik Acara'])->id,
            'title' => 'Pernikahan Made Arya & Ayu Kirana',
            'slug' => 'undangan-sari',
            'event_type' => 'wedding',
            'template_id' => 'sari-pura',
            'status' => 'published',
        ]);
    }
}

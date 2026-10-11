<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MahligaiTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_renders_the_full_invitation_with_the_mahligai_theme(): void
    {
        $invitation = $this->invitation();
        $invitation->hosts()->createMany([
            ['role' => 'groom', 'name' => 'Ahmad Fauzan', 'parent_father' => 'H. Ismail', 'parent_mother' => 'Hj. Aminah', 'social_instagram' => 'ahmad.fauzan', 'position' => 0],
            ['role' => 'bride', 'name' => 'Rahmah Az-Zahra', 'parent_father' => 'H. Yusuf', 'parent_mother' => 'Hj. Maryam', 'position' => 1],
        ]);
        $invitation->events()->create([
            'label' => 'Walimatul Ursy', 'date' => '2027-06-12', 'start_time' => '10:00', 'end_time' => '12:00',
            'timezone' => 'Asia/Jakarta', 'venue_name' => 'Masjid Al-Ikhlas', 'address' => 'Jalan Merdeka 10, Bandung',
            'is_primary' => true,
        ]);
        $invitation->stories()->create(['date' => '2021', 'title' => 'Pertemuan', 'body' => 'Di sebuah majelis.', 'position' => 0]);
        $invitation->media()->create(['type' => 'image', 'path' => 'invitations/media/akad.webp', 'alt_text' => 'Pasangan', 'position' => 0]);
        $invitation->giftMethods()->create([
            'type' => 'bank_transfer', 'provider' => 'Bank Syariah', 'account_name' => 'Rahmah Az-Zahra',
            'account_number' => '1234567890', 'position' => 0,
        ]);
        $invitation->contacts()->create(['label' => 'Keluarga', 'name' => 'Fatimah', 'phone' => '081234567890', 'position' => 0]);
        $invitation->guestbookEntries()->create(['name' => 'Budi', 'message' => 'Barakallahu lakuma', 'moderation_status' => 'approved']);

        $this->get('/undangan-mahligai')
            ->assertOk()
            ->assertSee('mahligai', false)
            ->assertSee('--mh-accent: #0f4d3c', false)
            ->assertSee('class="mh-btn mh-btn--solid" href="#mahligai-content" data-open-invitation', false)
            ->assertSee('>Buka Undangan</a>', false)
            ->assertSee('id="hosts"', false)
            ->assertSee('id="events"', false)
            ->assertSee('id="story"', false)
            ->assertSee('id="gallery"', false)
            ->assertSee('id="map"', false)
            ->assertSee('id="rsvp"', false)
            ->assertSee('Jalan Merdeka 10, Bandung')
            ->assertSee('WIB', false)
            ->assertSee('Ahmad Fauzan')
            ->assertSee('Rahmah Az-Zahra')
            ->assertSee('invitation-bank-card', false)
            // The wish tally.
            ->assertSee('1 Ucapan', false);
    }

    public function test_optional_sections_and_navigation_are_omitted_when_empty(): void
    {
        $this->invitation();

        $this->get('/undangan-mahligai')
            ->assertOk()
            ->assertDontSee('id="hosts"', false)
            ->assertDontSee('id="events"', false)
            ->assertDontSee('id="story"', false)
            ->assertDontSee('id="gallery"', false)
            ->assertDontSee('id="map"', false)
            ->assertDontSee('mh-countdown', false)
            ->assertDontSee('href="#hosts"', false)
            ->assertSee('id="mh-opening-title"', false)
            ->assertSee('class="invitation-section mh-closing"', false);
    }

    public function test_the_cover_and_gate_work_without_scripting(): void
    {
        $view = (string) file_get_contents(resource_path('invitation-templates/mahligai/views/index.blade.php'));
        $css = (string) file_get_contents(resource_path('invitation-templates/mahligai/assets/theme.css'));

        $this->assertMatchesRegularExpression('/<a\b[^>]*href="#mahligai-content"[^>]*data-open-invitation/', $view);
        $this->assertDoesNotMatchRegularExpression('/<button[^>]*data-open-invitation/', $view);
        $this->assertStringContainsString("document.documentElement.classList.add('js-ready')", $view);
        $this->assertStringContainsString('body:has(main:target)', $css);
        $this->assertStringContainsString('.js-ready [data-gate]', $css);
    }

    public function test_the_accent_colour_is_validated_before_it_reaches_the_page(): void
    {
        $invitation = $this->invitation();
        $invitation->update(['settings_json' => ['accent_color' => '#1f4a6b']]);

        $this->get('/undangan-mahligai')->assertOk()->assertSee('--mh-accent: #1f4a6b', false);

        $invitation->update(['settings_json' => ['accent_color' => 'url(javascript:alert(1))']]);

        $this->get('/undangan-mahligai')->assertOk()->assertSee('--mh-accent: #0f4d3c', false);
    }

    public function test_recipient_names_with_spaces_and_non_ascii_are_shown(): void
    {
        $this->invitation();

        $this->get('/undangan-mahligai?to='.rawurlencode('Bapak Ahmad Sørensen — परिवार'))
            ->assertOk()
            ->assertSee('Bapak Ahmad Sørensen', false)
            ->assertSee('परिवार', false);
    }

    /**
     * The reference invitation drifts the page forward on its own. That is an
     * opt-in per invitation here, and off by default.
     */
    public function test_the_auto_scroll_setting_is_off_by_default_and_can_be_enabled(): void
    {
        $invitation = $this->invitation();
        $invitation->hosts()->create(['role' => 'groom', 'name' => 'Ahmad', 'position' => 0]);

        $this->get('/undangan-mahligai')->assertOk()->assertSee('data-auto-scroll="0"', false);

        $invitation->update(['settings_json' => ['auto_scroll' => true]]);

        $this->get('/undangan-mahligai')->assertOk()->assertSee('data-auto-scroll="1"', false);
    }

    public function test_the_closing_has_no_instagram_or_share_row(): void
    {
        $invitation = $this->invitation();
        $invitation->hosts()->create([
            'role' => 'groom', 'name' => 'Ahmad', 'position' => 0, 'social_instagram' => 'ahmad.fauzan',
        ]);

        // The Instagram / WhatsApp row was removed from every design on purpose.
        $this->get('/undangan-mahligai')
            ->assertOk()
            ->assertDontSee('closing-social', false)
            ->assertDontSee('Bagikan via WhatsApp', false);
    }

    public function test_the_wish_tally_is_hidden_until_there_are_approved_wishes(): void
    {
        $invitation = $this->invitation();
        $invitation->hosts()->create(['role' => 'groom', 'name' => 'Ahmad', 'position' => 0]);

        $this->get('/undangan-mahligai')->assertOk()->assertDontSee('invitation-wishes__count', false);

        // A pending wish is not public yet, so it must not be counted.
        $invitation->guestbookEntries()->create(['name' => 'Budi', 'message' => 'Barakallah', 'moderation_status' => 'pending']);

        $this->get('/undangan-mahligai')->assertOk()->assertDontSee('invitation-wishes__count', false);

        $invitation->guestbookEntries()->create(['name' => 'Siti', 'message' => 'Selamat', 'moderation_status' => 'approved']);

        $this->get('/undangan-mahligai')->assertOk()->assertSee('1 Ucapan</p>', false);
    }

    public function test_the_ambient_light_layers_and_scroll_highlight_are_declared(): void
    {
        $this->invitation();

        $this->get('/undangan-mahligai')
            ->assertOk()
            ->assertSee('class="mh-glow"', false)
            ->assertSee('class="mh-motes"', false)
            ->assertSee('mh-mote--1', false);

        $css = (string) file_get_contents(resource_path('invitation-templates/mahligai/assets/theme.css'));
        $script = (string) file_get_contents(resource_path('invitation-templates/mahligai/assets/theme.js'));

        // A scroll-linked highlight: the glow drifts on --mh-progress and the
        // light motes rise across the whole viewport on a loop.
        $this->assertStringContainsString('--mh-progress', $css);
        $this->assertStringContainsString('@keyframes mh-rise', $css);
        // One detail animates on its own, with or without scrolling.
        $this->assertStringContainsString('@keyframes mh-shimmer', $css);
        // Layered behind and in front of the content.
        $this->assertStringContainsString('.mh-glow { position: fixed; inset: 0; z-index: 0;', $css);
        $this->assertStringContainsString('.mh-motes { position: fixed; z-index: 4;', $css);
        // Removed under motion: off and reduced motion.
        $this->assertStringContainsString('.mahligai[data-motion="off"] .mh-motes', $css);
        $this->assertStringContainsString('.mh-motes { display: none; }', $css);
        // One rAF-throttled listener publishes both numbers.
        $this->assertStringContainsString("setProperty('--mh-scroll'", $script);
        $this->assertStringContainsString("setProperty('--mh-progress'", $script);
    }

    private function invitation(): Invitation
    {
        return Invitation::create([
            'customer_id' => Customer::create(['name' => 'Pemilik Acara'])->id,
            'title' => 'Pernikahan Ahmad Fauzan & Rahmah Az-Zahra',
            'slug' => 'undangan-mahligai',
            'event_type' => 'wedding',
            'template_id' => 'mahligai',
            'status' => 'published',
        ]);
    }
}

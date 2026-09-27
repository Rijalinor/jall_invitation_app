<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaperLanternTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_fixture_renders_editorial_shell_structured_actions_and_dynamic_content(): void
    {
        $invitation = $this->invitation();
        $invitation->update([
            'livestream_url' => 'https://youtube.com/live/aman',
            'music_path' => 'music/theme.mp3',
        ]);
        $invitation->hosts()->create(['role' => 'groom', 'name' => 'Raka', 'parent_father' => 'Bapak Raka']);
        $invitation->hosts()->create(['role' => 'bride', 'name' => 'Nara', 'parent_mother' => 'Ibu Nara']);
        $event = $invitation->events()->create([
            'label' => 'Akad Nikah', 'date' => '2027-01-10', 'start_time' => '08:00', 'end_time' => '10:00',
            'timezone' => 'Asia/Jakarta', 'venue_name' => 'Gedung Bahagia', 'address' => 'Jalan Mawar 10',
            'latitude' => -6.2, 'longitude' => 106.8166, 'landmark_notes' => 'Sebelah taman kota', 'is_primary' => true,
        ]);
        $invitation->stories()->create(['date' => '2024', 'title' => 'Pertemuan', 'body' => 'Cerita kami', 'position' => 1]);
        $invitation->media()->create(['type' => 'image', 'path' => 'invitations/media/foto.webp', 'alt_text' => 'Foto bersama', 'position' => 1]);
        $invitation->giftMethods()->create([
            'type' => 'bank_transfer', 'provider' => 'BCA', 'account_name' => 'Raka',
            'account_number' => '1234567890', 'delivery_address' => 'Jalan Melati 5',
        ]);
        $invitation->contacts()->create(['label' => 'Keluarga', 'name' => 'Budi', 'phone' => '0812-3456-7890']);
        $invitation->guestbookEntries()->create([
            'name' => 'Tamu Undangan', 'message' => '<script>alert(1)</script> Semoga bahagia',
            'moderation_status' => 'approved',
        ]);

        $response = $this->get('/undangan-lengkap');

        $response->assertOk()
            ->assertSee('family=Manrope:wght@400;500;600', false)
            ->assertSee('family=Playfair+Display', false)
            ->assertDontSee('DM+Mono', false)
            ->assertSee('--pl-accent: #d68b62', false)
            ->assertSee('data-motion="calm"', false)
            ->assertSee('fetchpriority="high"', false)
            ->assertSee('<header class="pl-rail" aria-label="Navigasi undangan" data-gate>', false)
            ->assertSee('<main id="top" tabindex="-1" data-gate>', false)
            ->assertSee('01 <span>Opening note</span>', false)
            ->assertSee('02 <span>The people</span>', false)
            ->assertSee('Putra dari Bapak Raka')
            ->assertSee('Putri dari Ibu Nara')
            ->assertSee('Meet us in the moment.')
            ->assertSee('Petunjuk Arah')
            ->assertSee('Google Calendar')
            ->assertSee(route('invitations.calendar', [$invitation->slug, $event->id]), false)
            ->assertSee('Unduh ICS')
            ->assertSee('Salin Alamat')
            ->assertSee('data-countdown="2027-01-10T08:00:00+07:00"', false)
            ->assertSee('A story worth')
            ->assertSee('data-lightbox-src="http://localhost/storage/invitations/media/foto.webp"', false)
            ->assertSee('Transfer Bank · BCA')
            ->assertSee('data-copy="1234567890"', false)
            ->assertSee('https://wa.me/6281234567890', false)
            ->assertSee('Saksikan Live Streaming', false)
            ->assertSee('data-share', false)
            ->assertSee('Bagikan undangan')
            ->assertSee('<audio data-music', false)
            ->assertSee('Semoga bahagia')
            ->assertDontSee('<script>alert(1)</script>', false);

        $this->assertSame(1, substr_count($response->getContent(), 'fonts.googleapis.com/css2?family='));
    }

    public function test_sparse_fixture_renders_200_with_fallbacks_and_no_empty_sections(): void
    {
        $this->invitation();

        $this->get('/undangan-lengkap')
            ->assertOk()
            ->assertSee('Bapak/Ibu/Saudara/i')
            ->assertSee('The beginning of forever')
            ->assertSee('Buka undangan')
            ->assertSee('Bagikan undangan')
            ->assertDontSee('Meet us in the moment.')
            ->assertDontSee('Two people,')
            ->assertDontSee('A story worth')
            ->assertDontSee('The good')
            ->assertDontSee('With thanks.')
            ->assertDontSee('We are one')
            ->assertDontSee('Be there,')
            ->assertDontSee('data-lightbox-src', false)
            ->assertDontSee('data-countdown', false)
            ->assertDontSee('<audio data-music', false)
            ->assertDontSee('href="#agenda"', false)
            ->assertDontSee('href="#people"', false);
    }

    private function invitation(): Invitation
    {
        return Invitation::create([
            'customer_id' => Customer::create(['name' => 'Pemilik Acara'])->id,
            'title' => 'Acara Bahagia', 'slug' => 'undangan-lengkap', 'event_type' => 'wedding',
            'template_id' => 'paper-lantern', 'status' => 'published',
        ]);
    }
}

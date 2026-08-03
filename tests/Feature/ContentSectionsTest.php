<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentSectionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_story_gallery_gifts_contacts_and_livestream_render_safely(): void
    {
        $invitation = $this->invitation();
        $invitation->stories()->create(['date' => '2024', 'title' => 'Pertemuan', 'body' => 'Cerita kami', 'position' => 1]);
        $invitation->media()->create(['type' => 'image', 'path' => 'invitations/media/foto.webp', 'alt_text' => 'Foto bersama', 'position' => 1]);
        $invitation->media()->create(['type' => 'banner', 'path' => 'invitations/media/banner.webp', 'position' => 2]);
        $invitation->giftMethods()->create([
            'type' => 'bank_transfer', 'provider' => 'BCA', 'account_name' => 'Raka',
            'account_number' => '1234567890', 'delivery_address' => 'Jalan Melati 5',
        ]);
        $invitation->contacts()->create(['label' => 'Keluarga', 'name' => 'Budi', 'phone' => '0812-3456-7890']);

        $this->get('/acara')->assertOk()
            ->assertSee('Pertemuan')
            ->assertSee('data-lightbox-src="http://localhost/storage/invitations/media/foto.webp"', false)
            ->assertDontSee('banner.webp')
            ->assertSee('Hadiah Digital')
            ->assertSee('data-copy="1234567890"', false)
            ->assertSee('data-copy="Jalan Melati 5"', false)
            ->assertSee('https://wa.me/6281234567890', false)
            ->assertSee('tel:+6281234567890', false)
            ->assertSee('https://youtube.com/live/aman', false);
    }

    public function test_empty_optional_content_leaves_no_empty_cards_and_unsafe_stream_url_is_hidden(): void
    {
        $invitation = $this->invitation();
        $invitation->update(['livestream_url' => 'javascript:alert(1)']);

        $this->get('/acara')->assertOk()
            ->assertDontSee('Hadiah Digital')
            ->assertDontSee('Live Streaming')
            ->assertDontSee('data-lightbox-src', false);
    }

    private function invitation(): Invitation
    {
        return Invitation::create([
            'customer_id' => Customer::create(['name' => 'Pemilik Acara'])->id,
            'title' => 'Acara Bahagia', 'slug' => 'acara', 'event_type' => 'wedding',
            'template_id' => 'elegant-rose', 'status' => 'published',
            'livestream_url' => 'https://youtube.com/live/aman',
        ]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invitation;
use App\Services\InvitationDuplicator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvitationDuplicationTest extends TestCase
{
    use RefreshDatabase;

    private function source(): Invitation
    {
        $invitation = Invitation::create([
            'customer_id' => Customer::create(['name' => 'Pelanggan'])->id,
            'title' => 'Pernikahan Teddy & Anindya',
            'slug' => 'undangan-asal',
            'event_type' => 'wedding',
            'template_id' => 'elegant-rose',
            'status' => 'published',
            'published_at' => now(),
            'settings_json' => ['accent_color' => '#7b2639'],
        ]);

        $invitation->hosts()->create(['role' => 'groom', 'name' => 'Teddy', 'position' => 0]);
        $invitation->events()->create(['label' => 'Resepsi', 'date' => '2027-01-10', 'timezone' => 'Asia/Makassar', 'venue_name' => 'Gedung', 'is_primary' => true, 'position' => 0]);
        $invitation->stories()->create(['title' => 'Pertemuan', 'body' => 'Awal kisah', 'position' => 0]);
        $invitation->media()->create(['type' => 'image', 'path' => 'invitations/media/a.webp', 'position' => 0]);
        $invitation->giftMethods()->create(['type' => 'bank_transfer', 'provider' => 'Mandiri', 'account_name' => 'Anindya', 'account_number' => '123', 'position' => 0]);
        $invitation->contacts()->create(['label' => 'CP', 'name' => 'Rani', 'phone' => '0812', 'position' => 0]);

        $invitation->sections()->createMany([
            ['key' => 'opening', 'enabled' => true, 'position' => 0],
            ['key' => 'blocks', 'enabled' => true, 'position' => 1],
        ]);

        $invitation->blocks()->create([
            'section_id' => $invitation->sections()->where('key', 'blocks')->value('id'),
            'type' => 'text',
            'content_json' => ['body' => 'Catatan tambahan'],
            'position' => 0,
        ]);

        return $invitation;
    }

    public function test_duplicating_copies_content_into_a_fresh_draft(): void
    {
        $source = $this->source();

        $copy = app(InvitationDuplicator::class)->duplicate($source);

        $this->assertNotSame($source->id, $copy->id);
        $this->assertSame('draft', $copy->status->value);
        $this->assertFalse($copy->is_catalog_demo);
        $this->assertNull($copy->published_at);
        $this->assertSame('undangan-asal-salinan', $copy->slug);
        $this->assertSame($source->settings_json, $copy->settings_json);

        foreach (['hosts', 'events', 'stories', 'media', 'giftMethods', 'contacts', 'sections', 'blocks'] as $relation) {
            $this->assertSame($source->{$relation}()->count(), $copy->{$relation}()->count(), $relation.' was not fully copied');
        }
    }

    public function test_a_duplicated_block_points_at_the_copied_section(): void
    {
        $copy = app(InvitationDuplicator::class)->duplicate($this->source());

        $copiedBlock = $copy->blocks()->first();
        $copyBlocksSection = $copy->sections()->where('key', 'blocks')->sole();

        $this->assertSame($copyBlocksSection->id, $copiedBlock->section_id);
    }

    public function test_audience_data_and_stats_are_not_copied(): void
    {
        $source = $this->source();
        $source->guests()->create(['display_name' => 'Budi', 'invitation_limit' => 2]);
        $source->rsvps()->create(['name' => 'Budi', 'status' => 'attending', 'party_size' => 1]);
        $source->guestbookEntries()->create(['name' => 'Budi', 'message' => 'Selamat', 'moderation_status' => 'approved']);
        $source->visits()->create(['ip_hash' => str_repeat('a', 64)]);

        $copy = app(InvitationDuplicator::class)->duplicate($source);

        $this->assertSame(0, $copy->guests()->count());
        $this->assertSame(0, $copy->rsvps()->count());
        $this->assertSame(0, $copy->guestbookEntries()->count());
        $this->assertSame(0, $copy->visits()->count());
        $this->assertNull($copy->form_token_hash);
    }

    public function test_the_copy_slug_stays_unique_when_one_already_exists(): void
    {
        $source = $this->source();
        Invitation::create([
            'customer_id' => $source->customer_id, 'title' => 'Salinan lama', 'slug' => 'undangan-asal-salinan',
            'event_type' => 'wedding', 'template_id' => 'elegant-rose', 'status' => 'draft',
        ]);

        $copy = app(InvitationDuplicator::class)->duplicate($source);

        $this->assertSame('undangan-asal-salinan-2', $copy->slug);
    }

    public function test_the_source_invitation_is_left_untouched(): void
    {
        $source = $this->source();

        app(InvitationDuplicator::class)->duplicate($source);

        $this->assertSame('published', $source->fresh()->status->value);
        $this->assertSame(1, $source->hosts()->count());
        $this->assertSame(1, $source->blocks()->count());
    }
}

<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invitation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The public invitation is the hottest page in the app: it renders for every
 * guest who opens a link. Its relations must be eager-loaded so nothing is
 * fetched once per row, and the page must not drift into running a query for
 * every section it shows.
 */
class InvitationQueryBudgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_public_invitation_lazy_loads_nothing(): void
    {
        $invitation = $this->fullInvitation();

        // A lazy load throws here, so a relation that slips out of the
        // controller's eager-load list turns this page into a 500 and fails.
        Model::preventLazyLoading();

        try {
            $this->get('/'.$invitation->slug)->assertOk();
        } finally {
            Model::preventLazyLoading(false);
        }
    }

    public function test_the_public_invitation_stays_within_its_query_budget(): void
    {
        $invitation = $this->fullInvitation();

        DB::enableQueryLog();
        $this->get('/'.$invitation->slug)->assertOk();
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // One query for the invitation, one per eager-loaded relation, plus the
        // guestbook count and the RSVP recap. A lazy load per section would push
        // this well past the ceiling.
        $this->assertLessThanOrEqual(16, count($queries), 'Public invitation ran '.count($queries).' queries');
    }

    private function fullInvitation(): Invitation
    {
        $invitation = Invitation::create([
            'customer_id' => Customer::create(['name' => 'Pelanggan'])->id,
            'title' => 'Pernikahan Teddy & Anindya',
            'slug' => 'undangan-budget',
            'event_type' => 'wedding',
            'template_id' => 'elegant-rose',
            'status' => 'published',
        ]);

        $invitation->hosts()->createMany([
            ['role' => 'groom', 'name' => 'Teddy', 'position' => 0],
            ['role' => 'bride', 'name' => 'Anindya', 'position' => 1],
        ]);
        $invitation->events()->create([
            'label' => 'Resepsi', 'date' => '2027-01-10', 'start_time' => '08:00',
            'timezone' => 'Asia/Makassar', 'venue_name' => 'Gedung', 'is_primary' => true,
        ]);
        $invitation->sections()->createMany([
            ['key' => 'opening', 'enabled' => true, 'position' => 0],
            ['key' => 'hosts', 'enabled' => true, 'position' => 1],
            ['key' => 'events', 'enabled' => true, 'position' => 2],
            ['key' => 'gallery', 'enabled' => true, 'position' => 3],
            ['key' => 'gifts', 'enabled' => true, 'position' => 4],
            ['key' => 'closing', 'enabled' => true, 'position' => 5],
        ]);
        $invitation->blocks()->create([
            'type' => 'text',
            'content_json' => ['body' => 'Catatan tambahan'],
            'position' => 0,
        ]);
        $invitation->stories()->create(['title' => 'Pertemuan', 'body' => 'Awal kisah', 'position' => 0]);
        $invitation->media()->create(['type' => 'image', 'path' => 'invitations/media/foto.webp', 'position' => 0]);
        $invitation->giftMethods()->create(['type' => 'bank_transfer', 'provider' => 'Bank Mandiri', 'account_name' => 'Anindya', 'account_number' => '1234567890', 'position' => 0]);
        $invitation->contacts()->create(['label' => 'CP', 'name' => 'Rani', 'phone' => '081234567890', 'position' => 0]);
        $invitation->guestbookEntries()->create(['name' => 'Budi', 'message' => 'Selamat', 'moderation_status' => 'approved']);
        $invitation->rsvps()->create(['name' => 'Budi', 'status' => 'attending', 'party_size' => 1]);

        return $invitation;
    }
}

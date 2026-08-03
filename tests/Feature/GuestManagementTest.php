<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invitation;
use App\Services\GuestCsvImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_csv_import_creates_opaque_tokens_and_normalizes_rows(): void
    {
        $invitation = $this->invitation();
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, "name,group,phone,invitation_limit\nÉlodie & 家族,Keluarga,+62 812-345,3\nKosong,,,0\n");
        rewind($stream);

        $this->assertSame(2, app(GuestCsvImporter::class)->import($invitation, $stream));
        fclose($stream);

        $first = $invitation->guests()->where('display_name', 'Élodie & 家族')->firstOrFail();
        $this->assertSame(32, strlen($first->token));
        $this->assertSame('+62812345', $first->phone);
        $this->assertDatabaseHas('guests', ['display_name' => 'Kosong', 'invitation_limit' => 1]);
    }

    public function test_personal_link_tracks_first_open_and_preserves_personal_sharing(): void
    {
        $invitation = $this->invitation();
        $guest = $invitation->guests()->create(['display_name' => 'Élodie & 家族', 'invitation_limit' => 3]);
        $url = route('invitations.guest', [$invitation->slug, $guest->token], false);

        $this->get($url)->assertOk()->assertSee('Élodie &amp; 家族', false)->assertSee(rawurlencode($url), false);
        $this->assertNotNull($guest->fresh()->link_opened_at);
    }

    private function invitation(): Invitation
    {
        return Invitation::create([
            'customer_id' => Customer::create(['name' => 'Pemilik Acara'])->id,
            'title' => 'Acara Bahagia', 'slug' => 'acara', 'event_type' => 'wedding',
            'template_id' => 'elegant-rose', 'status' => 'published',
        ]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * The RSVP recap below the confirmation form: how many answered each way, never
 * who. The couple keeps the names; the guest only sees the tally.
 */
class RsvpSummaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_confirmation_shows_anonymous_attendance_counts_without_names(): void
    {
        $invitation = $this->invitation();
        $invitation->rsvps()->createMany([
            ['name' => 'Budi Rahasia', 'status' => 'attending', 'party_size' => 2],
            ['name' => 'Siti Rahasia', 'status' => 'attending', 'party_size' => 1],
            ['name' => 'Andi Rahasia', 'status' => 'tentative', 'party_size' => 0],
            ['name' => 'Rina Rahasia', 'status' => 'not_attending', 'party_size' => 0],
        ]);

        $this->get('/undangan-rekap')
            ->assertOk()
            ->assertSee('invitation-rsvp-summary', false)
            ->assertSee('Rekap Kehadiran')
            ->assertSee('invitation-rsvp-summary__item--attending', false)
            ->assertSee('invitation-rsvp-summary__item--tentative', false)
            ->assertSee('invitation-rsvp-summary__item--not-attending', false)
            // Two hadir, and one each for ragu-ragu / tidak hadir.
            ->assertSee('<strong>2</strong>', false)
            ->assertSee('<strong>1</strong>', false)
            // The recap never names anyone.
            ->assertDontSee('Budi Rahasia')
            ->assertDontSee('Siti Rahasia')
            ->assertDontSee('Andi Rahasia')
            ->assertDontSee('Rina Rahasia');
    }

    public function test_the_recap_is_hidden_until_someone_has_responded(): void
    {
        $this->invitation();

        $this->get('/undangan-rekap')
            ->assertOk()
            ->assertDontSee('invitation-rsvp-summary', false)
            ->assertDontSee('Rekap Kehadiran');
    }

    public function test_the_merged_confirmation_also_carries_the_recap(): void
    {
        $invitation = $this->invitation(['merge_rsvp_guestbook' => true]);
        $invitation->rsvps()->create(['name' => 'Budi', 'status' => 'attending', 'party_size' => 1]);
        $invitation->guestbookEntries()->create([
            'name' => 'Dewi', 'message' => 'Selamat ya', 'moderation_status' => 'approved',
        ]);

        $this->get('/undangan-rekap')
            ->assertOk()
            ->assertSee('Kirim Konfirmasi &amp; Ucapan', false)
            ->assertSee('invitation-rsvp-summary', false)
            ->assertSee('<strong>1</strong>', false);

        // The recap reads as a result of the form: it sits below the form and
        // above the wishes, not after the whole block.
        $content = $this->get('/undangan-rekap')->assertOk()->getContent();
        $submittedAt = strpos($content, 'Kirim Konfirmasi &amp; Ucapan');
        $recapAt = strpos($content, 'invitation-rsvp-summary');
        $wishesAt = strpos($content, '<blockquote>');

        $this->assertNotFalse($submittedAt);
        $this->assertNotFalse($recapAt);
        $this->assertNotFalse($wishesAt);
        $this->assertLessThan($recapAt, $submittedAt, 'The recap must sit below the form.');
        $this->assertLessThan($wishesAt, $recapAt, 'The recap must sit above the wishes.');
    }

    public function test_the_recap_can_be_switched_off_per_invitation(): void
    {
        $invitation = $this->invitation(['show_rsvp_summary' => false]);
        $invitation->rsvps()->create(['name' => 'Budi Rahasia', 'status' => 'attending', 'party_size' => 1]);

        $this->get('/undangan-rekap')
            ->assertOk()
            ->assertDontSee('invitation-rsvp-summary', false)
            ->assertDontSee('Rekap Kehadiran')
            ->assertDontSee('Budi Rahasia');
    }

    /**
     * The toggle is part of every design's schema, so an operator can turn the
     * recap on or off whatever template the invitation uses.
     */
    #[DataProvider('templates')]
    public function test_the_recap_toggle_works_in_every_template(string $template, string $slug): void
    {
        $invitation = $this->invitation([], $template, $slug);
        $invitation->rsvps()->create(['name' => 'Budi', 'status' => 'attending', 'party_size' => 1]);

        // On by default.
        $this->get('/'.$slug)->assertOk()->assertSee('invitation-rsvp-summary', false);

        $invitation->update(['settings_json' => ['show_rsvp_summary' => false]]);

        $this->get('/'.$slug)
            ->assertOk()
            ->assertDontSee('invitation-rsvp-summary', false)
            ->assertDontSee('Rekap Kehadiran');
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function templates(): array
    {
        return [
            'elegant-rose' => ['elegant-rose', 'rekap-elegant-rose'],
            'midnight-ledger' => ['midnight-ledger', 'rekap-midnight-ledger'],
            'fun-storybook' => ['fun-storybook', 'rekap-fun-storybook'],
            'coastal-vow' => ['coastal-vow', 'rekap-coastal-vow'],
            'celestial-vow' => ['celestial-vow', 'rekap-celestial-vow'],
            'sari-pura' => ['sari-pura', 'rekap-sari-pura'],
        ];
    }

    private function invitation(array $settings = [], string $template = 'elegant-rose', string $slug = 'undangan-rekap'): Invitation
    {
        return Invitation::create([
            'customer_id' => Customer::create(['name' => 'Pelanggan'])->id,
            'title' => 'Pernikahan Uji',
            'slug' => $slug,
            'event_type' => 'wedding',
            'template_id' => $template,
            'status' => 'published',
            'settings_json' => $settings ?: null,
        ]);
    }
}

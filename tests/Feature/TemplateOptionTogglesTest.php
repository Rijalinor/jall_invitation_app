<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Four per-invitation options are now automatic: the couple-name cover, the
 * "s/d Selesai" end time, the opening video, and the single gift block. Only
 * hiding the timezone and merging RSVP with the guestbook stay as toggles. This
 * exercises the behaviour once per design.
 */
class TemplateOptionTogglesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  string|null  $coverMarker  A marker only the templates whose cover
     *                                    changes visibly declare; the others
     *                                    already show the couple by design.
     */
    #[DataProvider('templates')]
    public function test_the_naturalized_behaviours_and_remaining_toggles(string $template, string $slug, string $heroVideoClass, ?string $coverMarker): void
    {
        $invitation = $this->invitation($template, $slug);
        $invitation->hosts()->createMany([
            ['role' => 'groom', 'name' => 'Rendra', 'position' => 0],
            ['role' => 'bride', 'name' => 'Alya', 'position' => 1],
        ]);
        $invitation->events()->create([
            'label' => 'Akad Nikah', 'date' => '2027-06-12', 'start_time' => '08:00',
            'timezone' => 'Asia/Jakarta', 'venue_name' => 'Pendopo', 'is_primary' => true,
        ]);
        $invitation->giftMethods()->create([
            'type' => 'bank_transfer', 'provider' => 'Bank Mandiri', 'account_name' => 'Alya',
            'account_number' => '1234567890', 'position' => 0,
        ]);

        // The four naturalized options are never set here: they are automatic.
        // Only the two remaining toggles and the cover video are configured.
        $invitation->update(['settings_json' => [
            'hide_timezone' => true,
            'merge_rsvp_guestbook' => true,
            'cover_video_desktop' => 'invitations/cover-videos/latar.mp4',
        ]]);

        $response = $this->get('/'.$slug)->assertOk();

        // Automatic: the empty end time and the single gift block.
        $response->assertSee('08:00 s/d Selesai', false)
            ->assertSee('aria-label="Kirim hadiah"', false);

        if ($coverMarker !== null) {
            $response->assertSee($coverMarker, false);
        }

        // Automatic: the opening section reuses the cover video.
        $response->assertSee($heroVideoClass, false)
            ->assertSee('invitations/cover-videos/latar.mp4', false);

        // Toggles: hide the timezone and merge RSVP with the guestbook.
        $response->assertDontSee('WIB', false)
            ->assertSee('Kirim Konfirmasi &amp; Ucapan', false)
            ->assertDontSee('Buku Ucapan', false);
    }

    /**
     * The couple-name cover is now the default, so it renders even with no
     * settings anywhere.
     */
    #[DataProvider('coverTemplates')]
    public function test_the_couple_cover_needs_no_setting(string $template, string $slug, string $marker): void
    {
        $invitation = $this->invitation($template, $slug);
        $invitation->hosts()->createMany([
            ['role' => 'groom', 'name' => 'Rendra', 'position' => 0],
            ['role' => 'bride', 'name' => 'Alya', 'position' => 1],
        ]);

        $this->get('/'.$slug)->assertOk()->assertSee($marker, false);
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: string, 3: string|null}>
     */
    public static function templates(): array
    {
        return [
            'midnight-ledger' => ['midnight-ledger', 'undangan-opsi-ml', 'ml-hero__video', 'ml-cover__couple'],
            'fun-storybook' => ['fun-storybook', 'undangan-opsi-fsb', 'fsb-hero__video', 'fsb-cover__title--couple'],
            'coastal-vow' => ['coastal-vow', 'undangan-opsi-cv', 'cv-hero__video', null],
            'celestial-vow' => ['celestial-vow', 'undangan-opsi-cl', 'cel-hero__video', null],
            'sari-pura' => ['sari-pura', 'undangan-opsi-sp', 'sp-opening__video', null],
            'mahligai' => ['mahligai', 'undangan-opsi-mh', 'mh-hero__video', null],
        ];
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: string}>
     */
    public static function coverTemplates(): array
    {
        return [
            'midnight-ledger' => ['midnight-ledger', 'undangan-couple-ml', 'ml-cover__couple'],
            'fun-storybook' => ['fun-storybook', 'undangan-couple-fsb', 'fsb-cover__title--couple'],
        ];
    }

    /**
     * Every template presents gifts as the same shared cards: a bank transfer as
     * an ATM card and an e-wallet as a method card.
     */
    #[DataProvider('giftTemplates')]
    public function test_gifts_render_as_shared_cards_in_every_template(string $template, string $slug): void
    {
        $invitation = $this->invitation($template, $slug.'-gift');
        $invitation->giftMethods()->createMany([
            ['type' => 'bank_transfer', 'provider' => 'Bank Mandiri', 'account_name' => 'Alya', 'account_number' => '1234567890', 'position' => 0],
            ['type' => 'ewallet', 'provider' => 'GoPay', 'account_name' => 'Rendra', 'account_number' => '081234567890', 'position' => 1],
        ]);

        $this->get('/'.$slug.'-gift')
            ->assertOk()
            ->assertSee('invitation-bank-card', false)
            ->assertSee('invitation-gift-method', false)
            ->assertSee('data-copy="1234567890"', false)
            ->assertSee('data-copy="081234567890"', false);
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function giftTemplates(): array
    {
        return [
            'elegant-rose' => ['elegant-rose', 'undangan-gift-er'],
            'midnight-ledger' => ['midnight-ledger', 'undangan-gift-ml'],
            'fun-storybook' => ['fun-storybook', 'undangan-gift-fsb'],
            'coastal-vow' => ['coastal-vow', 'undangan-gift-cv'],
            'celestial-vow' => ['celestial-vow', 'undangan-gift-cl'],
            'sari-pura' => ['sari-pura', 'undangan-gift-sp'],
            'mahligai' => ['mahligai', 'undangan-gift-mh'],
        ];
    }

    private function invitation(string $template, string $slug): Invitation
    {
        return Invitation::create([
            'customer_id' => Customer::create(['name' => 'Pelanggan'])->id,
            'title' => 'Pernikahan Rendra & Alya',
            'slug' => $slug,
            'event_type' => 'wedding',
            'template_id' => $template,
            'status' => 'published',
        ]);
    }
}

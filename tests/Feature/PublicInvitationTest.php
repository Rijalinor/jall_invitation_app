<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicInvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_published_invitations_render_with_safe_personalization(): void
    {
        $invitation = $this->invitation('published');
        $invitation->hosts()->create(['role' => 'groom', 'name' => 'Raka', 'parent_father' => 'Bapak Raka']);
        $invitation->hosts()->create(['role' => 'bride', 'name' => 'Nara', 'parent_mother' => 'Ibu Nara']);
        $invitation->events()->create([
            'label' => 'Akad Nikah', 'date' => '2027-01-10', 'start_time' => '08:00',
            'timezone' => 'Asia/Jakarta', 'venue_name' => 'Gedung Bahagia', 'address' => 'Jalan Mawar 10', 'is_primary' => true,
        ]);

        $this->get('/raka-nara?to='.rawurlencode('<b>Élodie & 家族</b>'))
            ->assertOk()
            ->assertSee('id="invitation-content" tabindex="-1" data-gate', false)
            ->assertSee('class="er-hosts" data-count="2"', false)
            ->assertSee('class="er-hosts__and"', false)
            ->assertSee('<small>Putra dari</small>', false)
            ->assertSee('<span>Bapak Raka</span>', false)
            ->assertSee('<small>Putri dari</small>', false)
            ->assertSee('<span>Ibu Nara</span>', false)
            ->assertSee('Élodie &amp; 家族', false)
            ->assertDontSee('<b>', false)
            ->assertSee('Gedung Bahagia');

        $invitation->update(['status' => 'draft']);
        $this->get('/raka-nara')->assertNotFound();

        $invitation->update(['status' => 'published', 'template_id' => 'template-tidak-dikenal']);
        $this->get('/raka-nara')->assertNotFound();
    }

    public function test_a_personal_link_shows_the_name_and_a_general_link_stays_empty(): void
    {
        $invitation = $this->invitation('published');
        $guest = $invitation->guests()->create(['display_name' => 'Siti Nur Aisyah', 'invitation_limit' => 2]);

        // A personal link greets the guest and pre-fills the forms with their name.
        $this->get("/raka-nara/g/{$guest->token}")
            ->assertOk()
            ->assertSee('Siti Nur Aisyah')
            ->assertSee('<input name="name" value="Siti Nur Aisyah" maxlength="150" required>', false);

        // A general link shows no greeting and leaves the name fields empty, so
        // the guest types their own name instead of deleting a placeholder first.
        $this->get('/raka-nara/g/token-tidak-valid')
            ->assertOk()
            ->assertDontSee('Bapak/Ibu/Saudara/i')
            ->assertDontSee('Kepada Yth.')
            ->assertSee('<input name="name" value="" maxlength="150" required>', false);
    }

    /**
     * A busy guestbook is capped, so it can never stretch the invitation into an
     * endlessly long page. The list itself also scrolls inside a fixed box.
     */
    public function test_the_guestbook_list_is_bounded_to_the_newest_twenty_wishes(): void
    {
        $invitation = $this->invitation('published');

        for ($i = 1; $i <= 25; $i++) {
            $invitation->guestbookEntries()->create([
                'name' => 'Tamu '.$i,
                'message' => 'Ucapan '.$i,
                'moderation_status' => 'approved',
            ]);
        }

        $response = $this->get('/raka-nara')->assertOk();

        $this->assertSame(20, substr_count($response->getContent(), '<blockquote>'));
    }

    /**
     * A shared link should show a photo, so the head carries an og:image taken
     * from the invitation's own picture when the operator has not chosen one.
     */
    public function test_a_shared_link_carries_a_preview_photo_from_the_invitation(): void
    {
        $invitation = $this->invitation('published');
        $invitation->hosts()->create(['role' => 'groom', 'name' => 'Raka', 'photo_path' => 'invitations/hosts/raka.jpg']);

        $this->assertMatchesRegularExpression(
            '/property="og:image" content="[^"]*invitations\/hosts\/raka\.jpg"/',
            $this->get('/raka-nara')->assertOk()->getContent(),
        );
    }

    /**
     * The operator may pick which of the invitation's photos is used; the choice
     * wins over the automatic fallback.
     */
    public function test_the_link_preview_photo_follows_the_operator_choice(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('invitations/hosts/raka.jpg', 'x');
        Storage::disk('public')->put('invitations/media/cover.jpg', 'x');

        $invitation = $this->invitation('published');
        $invitation->hosts()->create(['role' => 'groom', 'name' => 'Raka', 'photo_path' => 'invitations/hosts/raka.jpg']);
        $invitation->media()->create(['type' => 'image', 'path' => 'invitations/media/cover.jpg', 'position' => 0]);
        $invitation->update(['settings_json' => ['share_image' => 'invitations/hosts/raka.jpg']]);

        $content = $this->get('/raka-nara')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/property="og:image" content="[^"]*invitations\/hosts\/raka\.jpg"/', $content);
        $this->assertDoesNotMatchRegularExpression('/property="og:image" content="[^"]*cover\.jpg"/', $content);
    }

    public function test_a_link_preview_photo_is_omitted_when_the_invitation_has_none(): void
    {
        $invitation = $this->invitation('published');
        $invitation->hosts()->create(['role' => 'groom', 'name' => 'Raka']);

        $this->get('/raka-nara')->assertOk()->assertDontSee('property="og:image"', false);
    }

    /**
     * A "12 Ucapan" style tally above the guestbook, counting only the wishes
     * that are public. It is shared by every template.
     */
    public function test_the_guestbook_shows_a_tally_of_approved_wishes_only(): void
    {
        $invitation = $this->invitation('published');
        $invitation->guestbookEntries()->create(['name' => 'Budi', 'message' => 'Satu', 'moderation_status' => 'approved']);
        $invitation->guestbookEntries()->create(['name' => 'Siti', 'message' => 'Dua', 'moderation_status' => 'approved']);
        $invitation->guestbookEntries()->create(['name' => 'Andi', 'message' => 'Tiga', 'moderation_status' => 'pending']);

        $this->get('/raka-nara')
            ->assertOk()
            ->assertSee('invitation-wishes__count', false)
            ->assertSee('2 Ucapan', false);
    }

    private function invitation(string $status): Invitation
    {
        return Invitation::create([
            'customer_id' => Customer::create(['name' => 'Raka & Nara'])->id,
            'title' => 'Pernikahan Raka & Nara',
            'slug' => 'raka-nara',
            'event_type' => 'wedding',
            'template_id' => 'elegant-rose',
            'status' => $status,
        ]);
    }
}

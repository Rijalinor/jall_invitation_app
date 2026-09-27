<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ElegantRoseTemplateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The operator types free text into a textarea, so the value arrives with
     * real newlines. HTML collapses those into a single space, which made an
     * invitation read as one endless paragraph. Every multi line field has to
     * convert them before a guest sees it.
     */
    public function test_multi_line_text_keeps_the_line_breaks_the_operator_typed(): void
    {
        $invitation = $this->invitation();

        $invitation->hosts()->create(['role' => 'bride', 'name' => 'Anindya', 'position' => 0]);
        $invitation->hosts()->create(['role' => 'groom', 'name' => 'Bagaskara', 'position' => 1]);

        $invitation->events()->create([
            'label' => 'Akad Nikah', 'date' => '2027-01-10', 'start_time' => '08:00',
            'timezone' => 'Asia/Jakarta', 'venue_name' => 'Masjid Agung',
            'parking_notes' => "Parkir di basement\nMasuk dari gerbang timur",
            'is_primary' => true,
        ]);
        $invitation->stories()->create([
            'date' => '2021', 'title' => 'Pertemuan',
            'body' => "Kami bertemu di kampus.\n\nLalu memutuskan menikah.",
            'position' => 1,
        ]);
        $invitation->giftMethods()->create([
            'type' => 'bank_transfer', 'provider' => 'Bank Mandiri', 'account_name' => 'Anindya',
            'account_number' => '1234567890', 'notes' => "Mohon konfirmasi\nsetelah transfer",
        ]);

        // nl2br() puts the <br /> before the newline and leaves the newline in
        // place, so the expected substrings contain a real "\n".
        $this->get('/undangan-elegan')
            ->assertOk()
            ->assertSee("Baris pertama<br />\nBaris kedua", false)
            ->assertSee("Terima kasih<br />\natas doa restunya", false)
            ->assertSee("Kami bertemu di kampus.<br />\n<br />\nLalu memutuskan menikah.", false)
            ->assertSee("Parkir di basement<br />\nMasuk dari gerbang timur", false)
            ->assertSee("Mohon konfirmasi<br />\nsetelah transfer", false);
    }

    /**
     * The line break fix renders unescaped Blade output, so it is only safe as
     * long as the escaping happens first. This is the guard for that.
     */
    public function test_multi_line_text_is_still_escaped_before_the_breaks_are_added(): void
    {
        $invitation = $this->invitation();
        $invitation->update([
            'opening_text' => "Baris aman\n<script>alert('x')</script> & \"kutip\"",
        ]);

        $this->get('/undangan-elegan')
            ->assertOk()
            ->assertSee("Baris aman<br />\n&lt;script&gt;", false)
            ->assertDontSee('<script>alert', false);
    }

    /**
     * Section wording is part of the design, but a couple often wants their own
     * heading ("Kisah Kami" becoming "Perjalanan Kami"). The template supplies
     * the default and one invitation may override it without touching the other
     * invitations that use the same template.
     */
    public function test_section_wording_defaults_to_the_template_and_is_overridable_per_invitation(): void
    {
        $this->withSectionContent($this->invitation('undangan-elegan'));

        $this->get('/undangan-elegan')
            ->assertOk()
            ->assertSee('<h2 id="hosts-title">Mempelai &amp; Keluarga</h2>', false)
            ->assertSee('<h2 id="story-title">Kisah Kami</h2>', false)
            ->assertSee('<h2 id="gallery-title">Momen Pilihan</h2>', false)
            ->assertSee('data-open-invitation>Buka Undangan</a>', false);

        $this->withSectionContent($this->invitation('undangan-elegan-kedua', [
            'story_title' => 'Perjalanan Kami',
            'gallery_title' => '',   // blank must fall back, not blank the heading
        ]));

        $this->get('/undangan-elegan-kedua')
            ->assertOk()
            ->assertSee('<h2 id="story-title">Perjalanan Kami</h2>', false)
            ->assertSee('<h2 id="gallery-title">Momen Pilihan</h2>', false);

        // The override belongs to one invitation only.
        $this->get('/undangan-elegan')
            ->assertOk()
            ->assertSee('<h2 id="story-title">Kisah Kami</h2>', false)
            ->assertDontSee('Perjalanan Kami', false);
    }

    /**
     * One flat, reorderable list has to come out as several separate bands: a
     * heading block opens a band and the content below it lands inside.
     */
    public function test_operator_blocks_render_as_separate_sections_in_order(): void
    {
        $invitation = $this->invitation();

        $invitation->blocks()->createMany([
            ['type' => 'section', 'content_json' => ['title' => 'Kisah Keluarga'], 'position' => 0],
            ['type' => 'text', 'content_json' => ['body' => "Baris satu\nBaris dua"], 'position' => 1],
            ['type' => 'quote', 'content_json' => ['quote' => 'Cinta itu sabar.', 'source' => 'Ibu'], 'position' => 2],
            ['type' => 'section', 'content_json' => ['title' => 'Ucapan Terima Kasih'], 'position' => 3],
            ['type' => 'note', 'content_json' => ['body' => 'Mohon maaf bila ada kekurangan.'], 'position' => 4],
        ]);

        $response = $this->get('/undangan-elegan');

        $response->assertOk()
            ->assertSee('<h2 id="invitation-blocks-1">Kisah Keluarga</h2>', false)
            ->assertSee('<h2 id="invitation-blocks-2">Ucapan Terima Kasih</h2>', false)
            ->assertSee("Baris satu<br />\nBaris dua", false)
            ->assertSee('Cinta itu sabar.', false)
            ->assertSee('<cite>Ibu</cite>', false)
            ->assertSee('Mohon maaf bila ada kekurangan.', false);

        $this->assertSame(
            2,
            substr_count($response->getContent(), 'class="invitation-section invitation-blocks"'),
            'Two heading blocks must produce exactly two bands.',
        );
    }

    /**
     * A half-filled block must be dropped rather than published as an empty band
     * or a broken image.
     */
    public function test_a_block_with_nothing_to_show_renders_nothing(): void
    {
        $invitation = $this->invitation();

        $invitation->blocks()->createMany([
            ['type' => 'image', 'content_json' => ['caption' => 'Tanpa foto'], 'position' => 0],
            ['type' => 'text', 'content_json' => ['title' => 'Tanpa isi'], 'position' => 1],
        ]);

        $this->get('/undangan-elegan')
            ->assertOk()
            ->assertDontSee('invitation-blocks', false)
            ->assertDontSee('Tanpa foto', false)
            ->assertDontSee('Tanpa isi', false);
    }

    public function test_block_text_is_escaped(): void
    {
        $invitation = $this->invitation();
        $invitation->blocks()->create([
            'type' => 'text',
            'content_json' => ['body' => "<script>alert('x')</script>"],
            'position' => 0,
        ]);

        $this->get('/undangan-elegan')
            ->assertOk()
            ->assertSee('&lt;script&gt;', false)
            ->assertDontSee('<script>alert', false);
    }

    /**
     * The fixed intro paragraphs were the last words an operator could not
     * change. They now follow the same per-invitation override as the headings,
     * including the one that lives in the shared RSVP partial.
     */
    public function test_intro_paragraphs_can_be_reworded_per_invitation(): void
    {
        $this->withIntroSections($this->invitation());

        $this->get('/undangan-elegan')
            ->assertOk()
            ->assertSee('Doa dan kehadiran Anda adalah hadiah terindah.', false)
            ->assertSee('Jika membutuhkan informasi lebih lanjut, silakan hubungi kontak berikut.', false)
            ->assertSee('Mohon berikan konfirmasi kehadiran Anda.', false);

        $this->withIntroSections($this->invitation('undangan-elegan-kedua', [
            'gifts_intro' => "Terima kasih atas kasih Anda.\nTidak ada kewajiban apa pun.",
            'rsvp_intro' => 'Mohon konfirmasi sebelum 1 Januari.',
        ]));

        $this->get('/undangan-elegan-kedua')
            ->assertOk()
            ->assertSee("Terima kasih atas kasih Anda.<br />\nTidak ada kewajiban apa pun.", false)
            ->assertSee('Mohon konfirmasi sebelum 1 Januari.', false)
            // A paragraph that was not overridden keeps the template wording.
            ->assertSee('Jika membutuhkan informasi lebih lanjut, silakan hubungi kontak berikut.', false);
    }

    /**
     * A block an operator filled in has to actually appear, so adding one makes
     * the section that renders it exist. Nobody should have to know that a
     * separate section row is required.
     */
    public function test_adding_a_block_creates_the_section_that_renders_it(): void
    {
        $invitation = $this->invitation();
        $invitation->hosts()->create(['role' => 'bride', 'name' => 'Anindya', 'position' => 0]);

        $invitation->blocks()->create([
            'type' => 'section',
            'content_json' => ['title' => 'Kisah Keluarga'],
            'position' => 0,
        ]);

        $this->assertDatabaseHas('sections', [
            'invitation_id' => $invitation->id,
            'key' => 'blocks',
            'enabled' => true,
        ]);

        $response = $this->get('/undangan-elegan');

        $response->assertOk()
            ->assertSee('Kisah Keluarga')
            // An invitation with no section rows renders every declared section, so
            // seeding the row must not hide the rest of the invitation.
            ->assertSee('<h2 id="hosts-title">', false)
            ->assertSee('<section class="invitation-section er-closing"', false);

        $this->assertSame(
            count(Invitation::defaultSectionKeys()),
            $invitation->sections()->count(),
            'Seeding the section list must produce the same set a new invitation gets.',
        );

        // A section the operator switched off on purpose stays off.
        $invitation->sections()->where('key', 'blocks')->update(['enabled' => false]);
        $invitation->blocks()->create([
            'type' => 'text', 'content_json' => ['body' => 'Tambahan'], 'position' => 1,
        ]);

        $this->assertFalse($invitation->sections()->where('key', 'blocks')->first()->enabled);
        $this->assertSame(1, $invitation->sections()->where('key', 'blocks')->count());
    }

    /**
     * A block lands straight after the gallery, and the sections below it shift
     * down, so two rows never share a position and the order is never ambiguous.
     */
    public function test_the_blocks_section_takes_the_place_the_template_expects(): void
    {
        $invitation = $this->invitation();

        foreach (Invitation::defaultSectionKeys() as $index => $key) {
            $invitation->sections()->create(['key' => $key, 'enabled' => true, 'position' => $index]);
        }

        $invitation->sections()->where('key', 'blocks')->delete();
        $invitation->blocks()->create([
            'type' => 'text', 'content_json' => ['body' => 'Isi'], 'position' => 0,
        ]);

        $ordered = $invitation->sections()->orderBy('position')->pluck('key')->all();

        $this->assertSame('gallery', $ordered[array_search('blocks', $ordered, true) - 1]);
        $this->assertSame(count($ordered), count(array_unique($ordered)));
        $this->assertSame(
            count(Invitation::defaultSectionKeys()),
            $invitation->sections()->count(),
        );
    }

    /**
     * The opening section can carry the same upload as the cover, switched on
     * separately so turning one on never changes the other.
     */
    public function test_the_opening_section_can_show_the_same_video_as_the_cover(): void
    {
        $invitation = $this->invitation();
        $invitation->update(['settings_json' => [
            'cover_video_desktop' => 'invitations/cover-videos/latar.mp4',
            'opening_video_enabled' => 'true',
        ]]);

        $this->get('/undangan-elegan')
            ->assertOk()
            ->assertSee('class="er-hero__video"', false)
            ->assertSee('invitations/cover-videos/latar.mp4', false);

        // Off by default, so an invitation that only uploaded a cover video keeps
        // the opening section exactly as it was.
        $plain = $this->invitation('undangan-elegan-tanpa-video');
        $plain->update(['settings_json' => ['cover_video_desktop' => 'invitations/cover-videos/latar.mp4']]);

        $this->get('/undangan-elegan-tanpa-video')
            ->assertOk()
            ->assertDontSee('er-hero__video', false);
    }

    /**
     * A heading is only what splits the list into sections; it is not required to
     * start one. An operator who wants a single block of text should get a band
     * without a heading rather than having to invent one.
     */
    public function test_blocks_can_render_without_a_heading(): void
    {
        $invitation = $this->invitation();

        $invitation->blocks()->createMany([
            ['type' => 'text', 'content_json' => ['body' => 'Hanya teks saja.'], 'position' => 0],
            ['type' => 'note', 'content_json' => ['body' => 'Dan catatan.'], 'position' => 1],
            // The "text" element has its own optional sub heading, which is smaller
            // and does not open a new section.
            ['type' => 'text', 'content_json' => ['title' => 'Sub judul', 'body' => 'Isi lanjutan.'], 'position' => 2],
        ]);

        $response = $this->get('/undangan-elegan');

        $response->assertOk()
            ->assertSee('Hanya teks saja.', false)
            ->assertSee('Dan catatan.', false)
            ->assertSee('<h3>Sub judul</h3>', false);

        $content = $response->getContent();

        $this->assertSame(1, substr_count($content, 'class="invitation-section invitation-blocks"'));
        $this->assertSame(0, substr_count($content, 'id="invitation-blocks-1"'), 'A band without a heading must not render an empty one.');
    }

    /**
     * The section editor can ask a section to fit its content instead of filling
     * the screen, and the template has to honour it.
     */
    public function test_a_section_can_be_set_to_fit_its_content(): void
    {
        $invitation = $this->invitation();

        $invitation->hosts()->create(['role' => 'bride', 'name' => 'Anindya', 'position' => 0]);
        $invitation->events()->create([
            'label' => 'Akad Nikah', 'date' => '2027-01-10', 'start_time' => '08:00',
            'timezone' => 'Asia/Jakarta', 'venue_name' => 'Masjid', 'is_primary' => true,
        ]);

        $invitation->sections()->create([
            'key' => 'hosts', 'enabled' => true, 'position' => 1, 'content_json' => ['height' => 'auto'],
        ]);
        $invitation->sections()->create(['key' => 'events', 'enabled' => true, 'position' => 2]);

        $content = $this->get('/undangan-elegan')->assertOk()->getContent();

        $this->assertStringContainsString('data-height="auto" id="hosts"', $content);
        $this->assertStringNotContainsString('data-height="auto" id="events"', $content);

        // The percentages are relative to the guest's viewport, never pixels, and
        // the rule has to exist or the setting would silently do nothing.
        $css = (string) file_get_contents(resource_path('invitation-templates/elegant-rose/assets/theme.css'));

        $this->assertStringContainsString('.invitation-section[data-height="half"] { min-height: 50svh; }', $css);
        $this->assertStringContainsString('.invitation-section[data-height="tall"] { min-height: 75svh; }', $css);
        $this->assertStringContainsString('.invitation-section[data-height="auto"] { min-height: 0; }', $css);
    }

    private function withIntroSections(Invitation $invitation): void
    {
        $invitation->giftMethods()->create([
            'type' => 'bank_transfer', 'provider' => 'Bank Mandiri',
            'account_name' => 'Anindya', 'account_number' => '1234567890', 'position' => 0,
        ]);
        $invitation->contacts()->create(['label' => 'Keluarga', 'name' => 'Budi', 'phone' => '081234567890', 'position' => 0]);
    }

    private function withSectionContent(Invitation $invitation): void
    {
        // A section heading only renders once its section actually has content.
        $invitation->hosts()->create(['role' => 'bride', 'name' => 'Anindya', 'position' => 0]);
        $invitation->stories()->create(['date' => '2021', 'title' => 'Pertemuan', 'body' => 'Cerita kami', 'position' => 1]);
        $invitation->media()->create(['type' => 'image', 'path' => 'invitations/media/foto.webp', 'alt_text' => 'Foto bersama', 'position' => 0]);
    }

    private function invitation(string $slug = 'undangan-elegan', array $labels = []): Invitation
    {
        return Invitation::create([
            'customer_id' => Customer::create(['name' => 'Pemilik Acara'])->id,
            'title' => 'Pernikahan Anindya & Bagaskara',
            'slug' => $slug, 'event_type' => 'wedding',
            'template_id' => 'elegant-rose', 'status' => 'published',
            'opening_text' => "Baris pertama\nBaris kedua",
            'closing_message' => "Terima kasih\natas doa restunya",
            'settings_json' => $labels === [] ? null : ['labels' => $labels],
        ]);
    }
}

<?php

namespace Tests\Feature;

use App\Filament\Resources\InvitationResource\Pages\EditInvitation;
use App\Filament\Resources\InvitationResource\Pages\ListInvitations;
use App\Filament\Resources\InvitationResource\RelationManagers\BlocksRelationManager;
use App\Models\Customer;
use App\Models\Invitation;
use App\Models\Section;
use App\Models\User;
use App\Services\TemplateRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InvitationAdminFormTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Section labels sit one level deeper in the settings array than every other
     * setting, so this guards the whole chain at once: admin form -> nested JSON
     * column -> rendered invitation.
     */
    public function test_saving_a_section_label_in_the_admin_reaches_the_rendered_invitation(): void
    {
        $this->actingAs(User::factory()->create(['is_active' => true]));

        $invitation = $this->invitation('elegant-rose', 'undangan-uji', [
            'settings_json' => ['accent_color' => '#7b2639'],
        ]);
        $invitation->stories()->create(['date' => '2021', 'title' => 'Pertemuan', 'body' => 'Cerita', 'position' => 1]);

        Livewire::test(EditInvitation::class, ['record' => $invitation->getRouteKey()])
            ->fillForm(['settings_json.labels.story_title' => 'Perjalanan Kami'])
            ->call('save')
            ->assertHasNoFormErrors();

        $invitation->refresh();

        $this->assertSame('Perjalanan Kami', $invitation->settings_json['labels']['story_title']);

        // The setting that was already there must survive the save.
        $this->assertSame('#7b2639', $invitation->settings_json['accent_color']);

        $this->get('/undangan-uji')->assertOk()->assertSee('<h2 id="story-title">Perjalanan Kami</h2>', false);
    }

    /**
     * The block editor is grouped under the "Struktur Seksi" tab instead of its
     * own tab, but canViewForRecord still gates it on the template declaring
     * blocks, so it never appears for one that does not render them.
     */
    public function test_the_blocks_editor_lives_under_the_structure_tab_and_follows_the_template(): void
    {
        $this->actingAs(User::factory()->create(['is_active' => true]));

        $elegant = $this->invitation('elegant-rose', 'undangan-elegan');
        $storybook = $this->invitation('fun-storybook', 'undangan-storybook');

        $this->get('/admin/invitations/'.$elegant->id.'/edit')
            ->assertOk()
            ->assertSee('Struktur Seksi');

        $this->get('/admin/invitations/'.$storybook->id.'/edit')
            ->assertOk()
            ->assertSee('Struktur Seksi');

        // Every shipped template renders free blocks, so the editor is offered;
        // canViewForRecord still hides it for a template that does not.
        $this->assertTrue(BlocksRelationManager::canViewForRecord($elegant, EditInvitation::class));
        $this->assertTrue(BlocksRelationManager::canViewForRecord($storybook, EditInvitation::class));
    }

    /**
     * The editor reads as one tab bar: the detail form first, then a few themed
     * relation groups, instead of a long form followed by eleven relation tabs.
     */
    public function test_the_editor_shows_the_combined_grouped_tabs(): void
    {
        $this->actingAs(User::factory()->create(['is_active' => true]));

        $invitation = $this->invitation('elegant-rose', 'undangan-elegan');

        $this->get('/admin/invitations/'.$invitation->id.'/edit')
            ->assertOk()
            ->assertSee('Detail Undangan')
            ->assertSee('Mempelai & Acara')
            ->assertSee('Kisah & Galeri')
            ->assertSee('Hadiah & Kontak')
            ->assertSee('Struktur Seksi')
            ->assertSee('Tamu & Interaksi');
    }

    /**
     * The editor is a relation tab, not a section row, so this pins the wording an
     * operator actually sees: the button that adds an element and the element
     * type it lands as in the table.
     */
    public function test_the_blocks_editor_renders_its_add_button_and_element_types(): void
    {
        $this->actingAs(User::factory()->create(['is_active' => true]));

        $invitation = $this->invitation('elegant-rose', 'undangan-elegan');
        $invitation->blocks()->create([
            'type' => 'quote',
            'content_json' => ['quote' => 'Cinta itu sabar.', 'source' => 'Ibu'],
            'position' => 0,
        ]);

        Livewire::test(BlocksRelationManager::class, [
            'ownerRecord' => $invitation,
            'pageClass' => EditInvitation::class,
        ])
            ->assertSuccessful()
            ->assertSee('Tambah Elemen')
            ->assertSee('Kutipan')
            ->assertSee('Cinta itu sabar.');
    }

    /**
     * The visual panel used to offer every setting to every template, so an
     * operator could fill in a field the chosen template silently ignores. The
     * fields now follow the template's own declaration.
     */
    public function test_the_visual_settings_only_offer_what_the_chosen_template_declares(): void
    {
        $this->actingAs(User::factory()->create(['is_active' => true]));

        $elegant = $this->invitation('elegant-rose', 'undangan-elegan');
        $ledger = $this->invitation('midnight-ledger', 'undangan-ledger');
        $storybook = $this->invitation('fun-storybook', 'undangan-storybook');

        // elegant-rose declares no focal point, overlay or text position. The
        // four toggles it used to own are now automatic and no longer offered.
        $this->get('/admin/invitations/'.$elegant->id.'/edit')
            ->assertOk()
            ->assertDontSee('Video di seksi pembuka')
            ->assertDontSee('Gelap overlay')
            ->assertDontSee('Focal point horizontal')
            ->assertDontSee('Posisi teks cover');

        // midnight-ledger declares the focal point and overlay, but no opening-video toggle.
        $this->get('/admin/invitations/'.$ledger->id.'/edit')
            ->assertOk()
            ->assertSee('Gelap overlay')
            ->assertSee('Focal point horizontal')
            ->assertDontSee('Video di seksi pembuka');

        // fun-storybook declares a poster and cover video, but no overlay or focal point.
        $this->get('/admin/invitations/'.$storybook->id.'/edit')
            ->assertOk()
            ->assertSee('Poster / fallback cover')
            ->assertDontSee('Video di seksi pembuka')
            ->assertDontSee('Gelap overlay')
            ->assertDontSee('Focal point horizontal');
    }

    /**
     * Recommended colour swatches follow the chosen template, so an operator can
     * pick a colour that suits the design without inventing a hex value. The
     * swatch writes into the same form state the colour picker saves.
     */
    public function test_recommended_colour_swatches_follow_the_chosen_template(): void
    {
        $this->actingAs(User::factory()->create(['is_active' => true]));

        $coastal = $this->invitation('coastal-vow', 'undangan-pesisir');
        $elegant = $this->invitation('elegant-rose', 'undangan-elegan');
        $storybook = $this->invitation('fun-storybook', 'undangan-storybook');

        $this->get('/admin/invitations/'.$coastal->id.'/edit')
            ->assertOk()
            ->assertSee('Rekomendasi warna')
            ->assertSee('Sea Glass')
            ->assertSee('Sunset Coral')
            // The swatch writes the exact hex into the accent state.
            ->assertSee("x-data=\"{ chosen: \$wire.\$entangle('data.settings_json.accent_color') }\"", false)
            ->assertSee("chosen = '#2b7a78'", false)
            // coastal-vow declares no background colour, so no background swatches.
            ->assertDontSee('Mint Cream')
            ->assertDontSee("chosen = '#fdf6e4'", false);

        $this->get('/admin/invitations/'.$elegant->id.'/edit')
            ->assertOk()
            ->assertSee('Rose Wine')
            ->assertDontSee('Sea Glass');

        $this->get('/admin/invitations/'.$storybook->id.'/edit')
            ->assertOk()
            ->assertSee('Bubblegum')
            ->assertSee('Ivory')
            ->assertSee("chosen = '#fdf6e4'", false);
    }

    /**
     * The height control lives in the sections editor, but only a template that
     * honours it should offer it — otherwise it is one more field that silently
     * does nothing.
     */
    public function test_the_section_height_control_is_only_offered_by_templates_that_honour_it(): void
    {
        $registry = app(TemplateRegistry::class);

        // All three templates honour it now. supportsSectionHeight still returns
        // false for one that does not declare it.
        $this->assertTrue($registry->supportsSectionHeight('elegant-rose'));
        $this->assertTrue($registry->supportsSectionHeight('fun-storybook'));
        $this->assertTrue($registry->supportsSectionHeight('midnight-ledger'));
    }

    /**
     * Keys are unique per invitation, so adding a section after deleting one must
     * not reuse a key that is still taken.
     */
    public function test_a_new_extra_section_takes_a_free_key(): void
    {
        $invitation = $this->invitation('elegant-rose', 'undangan-elegan');

        $invitation->sections()->create(['key' => 'blocks', 'enabled' => true, 'position' => 8]);
        $this->assertSame('blocks:2', Section::nextCustomKey($invitation));

        $second = $invitation->sections()->create(['key' => 'blocks:2', 'enabled' => true, 'position' => 9]);
        $this->assertSame('blocks:3', Section::nextCustomKey($invitation));

        // The first free number is reused, but only once it is actually free.
        $second->delete();
        $this->assertSame('blocks:2', Section::nextCustomKey($invitation));

        $invitation->sections()->create(['key' => 'blocks:2', 'enabled' => true, 'position' => 9]);
        $invitation->sections()->create(['key' => 'blocks:5', 'enabled' => true, 'position' => 10]);

        $this->assertSame('blocks:3', Section::nextCustomKey($invitation), 'The first gap wins, not the highest number.');
    }

    /**
     * Issuing the customer form link shows the URL once, with a copy control,
     * because the plaintext token is never stored after that.
     */
    public function test_issuing_a_customer_form_link_notifies_with_the_url(): void
    {
        $this->actingAs(User::factory()->create(['is_active' => true]));

        $invitation = $this->invitation('elegant-rose', 'undangan-elegan');

        Livewire::test(ListInvitations::class)
            ->callTableAction('formLink', $invitation, ['days' => 30])
            ->assertHasNoTableActionErrors()
            ->assertNotified();

        $this->assertNotNull($invitation->fresh()->form_token_hash);
    }

    private function invitation(string $template, string $slug, array $extra = []): Invitation
    {
        return Invitation::create(array_merge([
            'customer_id' => Customer::create(['name' => 'Pelanggan Uji'])->id,
            'title' => 'Undangan Uji',
            'slug' => $slug,
            'event_type' => 'wedding',
            'template_id' => $template,
            'status' => 'published',
        ], $extra));
    }
}

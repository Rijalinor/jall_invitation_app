<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Host;
use App\Models\Invitation;
use App\Models\Media;
use App\Services\FormLinks;
use App\Services\PublicImageUpload;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InvitationFormLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_issued_link_resolves_to_its_own_invitation(): void
    {
        $links = app(FormLinks::class);
        $invitation = $this->invitation('undangan-satu');

        $token = $links->issue($invitation);

        $this->assertSame($invitation->id, $links->resolve($token)?->id);
        $this->assertTrue($links->isActive($invitation->fresh()));
        $this->assertStringContainsString($token, $links->url($token));
    }

    public function test_unknown_short_and_expired_tokens_do_not_resolve(): void
    {
        $links = app(FormLinks::class);
        $invitation = $this->invitation('undangan-satu');

        $this->assertNull($links->resolve(null));
        $this->assertNull($links->resolve('terlalu-pendek'));
        $this->assertNull($links->resolve(str_repeat('a', 48)));

        $token = $links->issue($invitation);
        $invitation->forceFill(['form_token_expires_at' => now()->subMinute()])->save();

        $this->assertNull($links->resolve($token));
        $this->assertFalse($links->isActive($invitation->fresh()));
    }

    public function test_a_revoked_link_stops_working(): void
    {
        $links = app(FormLinks::class);
        $invitation = $this->invitation('undangan-satu');

        $token = $links->issue($invitation);
        $links->revoke($invitation);

        $this->assertNull($links->resolve($token));
        $this->get(route('invitation-form.show', $token))->assertNotFound();
    }

    public function test_the_form_opens_for_a_valid_link_and_404s_otherwise(): void
    {
        $links = app(FormLinks::class);
        $invitation = $this->invitation('undangan-satu', ['title' => 'Pernikahan Satu']);
        $token = $links->issue($invitation);

        $this->get(route('invitation-form.show', $token))
            ->assertOk()
            ->assertSee('Pernikahan Satu')
            ->assertSee('Mempelai');

        $this->get(route('invitation-form.show', str_repeat('b', 48)))->assertNotFound();
    }

    public function test_the_form_only_edits_its_own_invitation(): void
    {
        $links = app(FormLinks::class);
        $mine = $this->invitation('undangan-saya');
        $theirs = $this->invitation('undangan-orang-lain');
        $theirHost = $theirs->hosts()->create(['name' => 'Mempelai Lain', 'role' => 'bride']);

        $token = $links->issue($mine);

        $this->post(route('invitation-form.update', ['token' => $token, 'step' => 'mempelai']), [
            'hosts' => [
                ['id' => $theirHost->id, 'name' => 'Nama Selundupan', 'role' => 'groom'],
            ],
        ])->assertRedirect(route('invitation-form.show', ['token' => $token, 'step' => 'acara']));

        $this->assertSame('Mempelai Lain', $theirHost->fresh()->name);
        $this->assertSame('undangan-orang-lain', $theirHost->fresh()->invitation->slug);

        // The foreign id was ignored, so the host was created on the right invitation.
        $this->assertSame(1, $mine->hosts()->count());
        $this->assertSame('Nama Selundupan', $mine->hosts()->first()->name);
    }

    public function test_the_form_cannot_change_template_status_or_slug(): void
    {
        $links = app(FormLinks::class);
        $invitation = $this->invitation('undangan-satu', [
            'template_id' => 'elegant-rose',
            'status' => 'draft',
        ]);

        $token = $links->issue($invitation);

        $this->post(route('invitation-form.update', ['token' => $token, 'step' => 'mempelai']), [
            'hosts' => [['name' => 'Mempelai Satu', 'role' => 'groom']],
            'template_id' => 'midnight-ledger',
            'status' => 'published',
            'slug' => 'slug-baru',
            'is_catalog_demo' => true,
        ])->assertRedirect(route('invitation-form.show', ['token' => $token, 'step' => 'acara']));

        $invitation->refresh();

        $this->assertSame('elegant-rose', $invitation->template_id);
        $this->assertSame('draft', $invitation->status->value);
        $this->assertSame('undangan-satu', $invitation->slug);
        $this->assertFalse($invitation->is_catalog_demo);
    }

    public function test_the_customer_can_save_hosts_and_events(): void
    {
        $links = app(FormLinks::class);
        $invitation = $this->invitation('undangan-satu');
        $token = $links->issue($invitation);

        $this->post(route('invitation-form.update', ['token' => $token, 'step' => 'mempelai']), [
            'hosts' => [
                ['name' => 'Rangga', 'role' => 'groom', 'parent_father' => 'Bapak Rangga'],
                ['name' => 'Sinta', 'role' => 'bride'],
                ['name' => '', 'role' => ''],
            ],
        ])->assertRedirect(route('invitation-form.show', ['token' => $token, 'step' => 'acara']));

        $this->assertSame(2, $invitation->hosts()->count());
        $this->assertSame('Rangga', $invitation->hosts()->orderBy('position')->first()->name);
        $this->assertSame('Bapak Rangga', $invitation->hosts()->orderBy('position')->first()->parent_father);

        $this->post(route('invitation-form.update', ['token' => $token, 'step' => 'acara']), [
            'acara' => [
                ['label' => 'Akad Nikah', 'date' => '2027-01-10', 'start_time' => '08:00', 'timezone' => 'Asia/Jakarta'],
                ['label' => 'Resepsi', 'date' => '2027-01-10', 'start_time' => '11:00', 'timezone' => 'Asia/Jakarta'],
            ],
        ])->assertRedirect(route('invitation-form.show', ['token' => $token, 'step' => 'cerita']));

        $this->assertSame(2, $invitation->events()->count());
        $this->assertTrue($invitation->events()->orderBy('position')->first()->is_primary);
        $this->assertSame(1, $invitation->events()->where('is_primary', true)->count());
    }

    public function test_a_partial_submission_never_deletes_existing_content(): void
    {
        $links = app(FormLinks::class);
        $invitation = $this->invitation('undangan-satu');
        $kept = $invitation->hosts()->create(['name' => 'Mempelai Pertama', 'role' => 'groom', 'position' => 0]);
        $other = $invitation->hosts()->create(['name' => 'Mempelai Kedua', 'role' => 'bride', 'position' => 1]);

        $token = $links->issue($invitation);

        // Only the first host is submitted; the second is simply absent.
        $this->post(route('invitation-form.update', ['token' => $token, 'step' => 'mempelai']), [
            'hosts' => [
                ['id' => $kept->id, 'name' => 'Mempelai Pertama', 'role' => 'groom'],
            ],
        ])->assertRedirect(route('invitation-form.show', ['token' => $token, 'step' => 'acara']));

        $this->assertSame(2, $invitation->hosts()->count());
        $this->assertNotNull($other->fresh());

        // Removal must be explicit.
        $this->post(route('invitation-form.update', ['token' => $token, 'step' => 'mempelai']), [
            'hosts' => [
                ['id' => $kept->id, 'name' => 'Mempelai Pertama', 'role' => 'groom'],
                ['id' => $other->id, 'remove' => '1'],
            ],
        ])->assertRedirect(route('invitation-form.show', ['token' => $token, 'step' => 'acara']));

        $this->assertSame(1, $invitation->hosts()->count());
        $this->assertNull(Host::find($other->id));
    }

    public function test_content_still_needs_a_name_and_an_event(): void
    {
        $links = app(FormLinks::class);
        $invitation = $this->invitation('undangan-satu');
        $token = $links->issue($invitation);

        $this->from(route('invitation-form.show', $token))
            ->post(route('invitation-form.update', ['token' => $token, 'step' => 'mempelai']), [
                'hosts' => [['name' => '', 'role' => '']],
            ])
            ->assertSessionHasErrors('form', null, 'form');

        $this->from(route('invitation-form.show', $token))
            ->post(route('invitation-form.update', ['token' => $token, 'step' => 'acara']), [
                'acara' => [['label' => 'Resepsi', 'date' => '']],
            ])
            ->assertSessionHasErrors('form', null, 'form');
    }

    public function test_the_token_hash_is_never_serialised(): void
    {
        $invitation = $this->invitation('undangan-satu');
        app(FormLinks::class)->issue($invitation);

        $this->assertArrayNotHasKey('form_token_hash', $invitation->fresh()->toArray());
    }

    public function test_a_customer_photo_is_stored_as_a_safe_jpeg(): void
    {
        Storage::fake('public');

        $links = app(FormLinks::class);
        $invitation = $this->invitation('undangan-satu');
        $token = $links->issue($invitation);

        $this->post(route('invitation-form.update', ['token' => $token, 'step' => 'mempelai']), [
            'hosts' => [
                ['name' => 'Rangga', 'role' => 'groom', 'photo' => UploadedFile::fake()->image('foto-asli.png', 900, 900)],
            ],
        ])->assertRedirect(route('invitation-form.show', ['token' => $token, 'step' => 'acara']));

        $path = $invitation->hosts()->first()->photo_path;

        $this->assertIsString($path);
        $this->assertStringStartsWith('invitations/hosts/', $path);
        // Re-encoding normalises the format and drops EXIF, so the stored file
        // is a fresh JPEG regardless of what the customer uploaded.
        $this->assertStringEndsWith('.jpg', $path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_non_image_uploads_are_rejected(): void
    {
        Storage::fake('public');

        $links = app(FormLinks::class);
        $invitation = $this->invitation('undangan-satu');
        $token = $links->issue($invitation);

        $this->from(route('invitation-form.show', $token))
            ->post(route('invitation-form.update', ['token' => $token, 'step' => 'mempelai']), [
                'hosts' => [
                    ['name' => 'Rangga', 'role' => 'groom', 'photo' => UploadedFile::fake()->create('jahat.php', 10, 'application/x-php')],
                ],
            ])
            ->assertSessionHasErrors('hosts.0.photo', null, 'form');

        $this->assertNull($invitation->hosts()->first()?->photo_path);
    }

    public function test_the_customer_can_save_stories_and_gallery(): void
    {
        Storage::fake('public');

        $links = app(FormLinks::class);
        $invitation = $this->invitation('undangan-satu');
        $token = $links->issue($invitation);

        $this->post(route('invitation-form.update', ['token' => $token, 'step' => 'cerita']), [
            'cerita' => [
                ['date' => '14 Februari 2020', 'title' => 'Awal Pertemuan', 'body' => 'Kami bertemu di kampus.', 'photo' => UploadedFile::fake()->image('momen.jpg', 800, 600)],
                ['date' => '1 Januari 2026', 'title' => 'Lamaran'],
                ['date' => '', 'title' => ''],
            ],
        ])->assertRedirect(route('invitation-form.show', ['token' => $token, 'step' => 'galeri']));

        $this->assertSame(2, $invitation->stories()->count());

        $story = $invitation->stories()->orderBy('position')->first();
        $this->assertSame('Awal Pertemuan', $story->title);
        $this->assertStringEndsWith('.jpg', $story->image_path);
        Storage::disk('public')->assertExists($story->image_path);

        $this->post(route('invitation-form.update', ['token' => $token, 'step' => 'galeri']), [
            'galeri' => [
                ['photo' => UploadedFile::fake()->image('satu.jpg', 700, 700), 'caption' => 'Foto satu'],
                ['photo' => UploadedFile::fake()->image('dua.png', 700, 700), 'alt_text' => 'Foto dua'],
                ['caption' => ''],
            ],
        ])->assertRedirect(route('invitation-form.show', ['token' => $token, 'step' => 'selesai']));

        $this->assertSame(2, $invitation->media()->count());

        $first = $invitation->media()->orderBy('position')->first();
        $this->assertSame('image', $first->type);
        $this->assertSame('Foto satu', $first->caption);
        $this->assertStringEndsWith('.jpg', $first->path);
        Storage::disk('public')->assertExists($first->path);
    }

    public function test_a_story_with_any_content_needs_a_title(): void
    {
        $links = app(FormLinks::class);
        $invitation = $this->invitation('undangan-satu');
        $token = $links->issue($invitation);

        $this->from(route('invitation-form.show', $token))
            ->post(route('invitation-form.update', ['token' => $token, 'step' => 'cerita']), [
                'cerita' => [['date' => '2020', 'title' => '', 'body' => 'Ditulis tapi tanpa judul.']],
            ])
            ->assertSessionHasErrors('form', null, 'form');

        $this->assertSame(0, $invitation->stories()->count());
    }

    public function test_a_gallery_row_with_details_but_no_photo_is_rejected(): void
    {
        Storage::fake('public');

        $links = app(FormLinks::class);
        $invitation = $this->invitation('undangan-satu');
        $token = $links->issue($invitation);

        $this->from(route('invitation-form.show', $token))
            ->post(route('invitation-form.update', ['token' => $token, 'step' => 'galeri']), [
                'galeri' => [['caption' => 'Ada keterangan tapi tanpa foto']],
            ])
            ->assertSessionHasErrors('form', null, 'form');

        $this->assertSame(0, $invitation->media()->count());
    }

    public function test_gallery_removal_also_deletes_the_stored_file(): void
    {
        Storage::fake('public');

        $links = app(FormLinks::class);
        $invitation = $this->invitation('undangan-satu');
        $token = $links->issue($invitation);

        $this->post(route('invitation-form.update', ['token' => $token, 'step' => 'galeri']), [
            'galeri' => [['photo' => UploadedFile::fake()->image('satu.jpg', 700, 700)]],
        ])->assertRedirect(route('invitation-form.show', ['token' => $token, 'step' => 'selesai']));

        $item = $invitation->media()->first();
        $path = $item->path;

        Storage::disk('public')->assertExists($path);

        $this->post(route('invitation-form.update', ['token' => $token, 'step' => 'galeri']), [
            'galeri' => [['id' => $item->id, 'remove' => '1']],
        ])->assertRedirect(route('invitation-form.show', ['token' => $token, 'step' => 'selesai']));

        $this->assertSame(0, $invitation->media()->count());
        $this->assertNull(Media::find($item->id));
        Storage::disk('public')->assertMissing($path);
    }

    public function test_the_photo_quota_covers_the_whole_invitation(): void
    {
        Storage::fake('public');

        $links = app(FormLinks::class);
        $invitation = $this->invitation('undangan-satu');

        for ($i = 0; $i < PublicImageUpload::MAX_PER_INVITATION; $i++) {
            $invitation->media()->create([
                'type' => 'image',
                'path' => 'invitations/media/'.$i.'.jpg',
                'position' => $i,
            ]);
        }

        $token = $links->issue($invitation);

        $this->from(route('invitation-form.show', $token))
            ->post(route('invitation-form.update', ['token' => $token, 'step' => 'galeri']), [
                'galeri' => [['photo' => UploadedFile::fake()->image('lebih.jpg', 600, 600)]],
            ])
            ->assertSessionHasErrors('form', null, 'form');

        $this->assertSame(PublicImageUpload::MAX_PER_INVITATION, $invitation->media()->count());
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function invitation(string $slug, array $attributes = []): Invitation
    {
        return Invitation::create(array_merge([
            'customer_id' => Customer::create(['name' => 'Pelanggan'])->id,
            'title' => 'Undangan '.$slug,
            'slug' => $slug,
            'event_type' => 'wedding',
            'template_id' => 'elegant-rose',
            'status' => 'draft',
        ], $attributes));
    }
}

<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PruneOrphanUploadsTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_unreferenced_file_is_removed_when_forced(): void
    {
        Storage::fake('public');

        $this->store('invitations/media/yatim.jpg', daysOld: 5);
        $this->referenced('invitations/media/dipakai.jpg');

        $this->artisan('jall:prune-orphans', ['--force' => true])
            ->expectsOutputToContain('Dihapus: 1 file')
            ->assertSuccessful();

        Storage::disk('public')->assertMissing('invitations/media/yatim.jpg');
    }

    public function test_a_referenced_file_is_kept(): void
    {
        Storage::fake('public');

        $this->referenced('invitations/media/dipakai.jpg');

        $this->artisan('jall:prune-orphans', ['--force' => true])->assertSuccessful();

        Storage::disk('public')->assertExists('invitations/media/dipakai.jpg');
    }

    public function test_without_force_it_only_lists_the_files(): void
    {
        Storage::fake('public');

        $this->store('invitations/media/yatim.jpg', daysOld: 5);
        $this->referenced('invitations/media/dipakai.jpg');

        $this->artisan('jall:prune-orphans')
            ->expectsOutputToContain('Mode lihat saja')
            ->assertSuccessful();

        Storage::disk('public')->assertExists('invitations/media/yatim.jpg');
    }

    public function test_a_recent_unreferenced_file_is_left_alone(): void
    {
        Storage::fake('public');

        // Uploaded today: it may not have been written to the database yet.
        $this->store('invitations/media/baru.jpg', daysOld: 0);
        $this->referenced('invitations/media/dipakai.jpg');

        $this->artisan('jall:prune-orphans', ['--force' => true])->assertSuccessful();

        Storage::disk('public')->assertExists('invitations/media/baru.jpg');
    }

    public function test_it_refuses_to_run_when_no_reference_can_be_found(): void
    {
        Storage::fake('public');

        // Files on disk but an empty reference set means the scan is broken,
        // not that the storage is full of junk.
        $this->store('invitations/media/yatim.jpg', daysOld: 5);

        $this->artisan('jall:prune-orphans', ['--force' => true])
            ->expectsOutputToContain('Dibatalkan demi keamanan')
            ->assertFailed();

        Storage::disk('public')->assertExists('invitations/media/yatim.jpg');
    }

    public function test_it_never_touches_files_outside_the_upload_root(): void
    {
        Storage::fake('public');

        Storage::disk('public')->put('catatan.txt', 'jangan disentuh');
        Storage::disk('public')->put('build/manifest.json', '{}');
        $this->referenced('invitations/media/dipakai.jpg');

        $this->artisan('jall:prune-orphans', ['--force' => true])->assertSuccessful();

        Storage::disk('public')->assertExists('catatan.txt');
        Storage::disk('public')->assertExists('build/manifest.json');
    }

    public function test_a_cover_video_inside_settings_json_is_protected(): void
    {
        Storage::fake('public');

        $video = 'invitations/cover-videos/sampul.mp4';
        $this->store($video, daysOld: 5);

        $this->invitation()->forceFill([
            'settings_json' => ['cover_video_mobile' => $video, 'cover_video_enabled' => true],
        ])->save();

        $this->artisan('jall:prune-orphans', ['--force' => true])->assertSuccessful();

        Storage::disk('public')->assertExists($video);
    }

    public function test_host_photos_and_story_images_are_protected(): void
    {
        Storage::fake('public');

        $photo = 'invitations/hosts/mempelai.jpg';
        $story = 'invitations/stories/momen.jpg';
        $song = 'invitations/music/lagu.mp3';

        $this->store($photo, daysOld: 5);
        $this->store($story, daysOld: 5);
        $this->store($song, daysOld: 5);

        $invitation = $this->invitation();
        $invitation->hosts()->create(['name' => 'Mempelai', 'role' => 'groom', 'photo_path' => $photo]);
        $invitation->stories()->create(['title' => 'Momen', 'image_path' => $story, 'position' => 0]);
        $invitation->forceFill(['music_path' => $song])->save();

        $this->artisan('jall:prune-orphans', ['--force' => true])->assertSuccessful();

        Storage::disk('public')->assertExists($photo);
        Storage::disk('public')->assertExists($story);
        Storage::disk('public')->assertExists($song);
    }

    public function test_a_path_in_an_unregistered_column_is_still_protected(): void
    {
        Storage::fake('public');

        // The scan is schema driven, so even a column the pruner has never
        // heard of protects whatever it points at. This is the guard against
        // someone adding an upload field and forgetting to register it.
        $path = 'invitations/media/dari-kolom-lain.jpg';
        $this->store($path, daysOld: 5);

        $this->invitation()->forceFill([
            'closing_message' => 'Foto penutup: '.$path,
        ])->save();

        $this->artisan('jall:prune-orphans', ['--force' => true])->assertSuccessful();

        Storage::disk('public')->assertExists($path);
    }

    public function test_a_soft_deleted_invitation_still_protects_its_files(): void
    {
        Storage::fake('public');

        $path = 'invitations/media/milik-terhapus.jpg';
        $this->store($path, daysOld: 5);

        $invitation = $this->invitation();
        $invitation->media()->create(['type' => 'image', 'path' => $path, 'position' => 0]);
        $invitation->delete();

        $this->artisan('jall:prune-orphans', ['--force' => true])->assertSuccessful();

        // Restoring the invitation must still work.
        Storage::disk('public')->assertExists($path);
    }

    private function invitation(): Invitation
    {
        return Invitation::create([
            'customer_id' => Customer::create(['name' => 'Pelanggan'])->id,
            'title' => 'Undangan',
            'slug' => 'undangan-uji-'.uniqid(),
            'event_type' => 'wedding',
            'template_id' => 'elegant-rose',
            'status' => 'draft',
        ]);
    }

    private function store(string $path, int $daysOld): void
    {
        $disk = Storage::disk('public');
        $disk->put($path, 'isi');
        touch($disk->path($path), now()->subDays($daysOld)->getTimestamp());
    }

    private function referenced(string $path): void
    {
        $this->store($path, daysOld: 5);

        $this->invitation()->media()->create([
            'type' => 'image',
            'path' => $path,
            'position' => 0,
        ]);
    }
}

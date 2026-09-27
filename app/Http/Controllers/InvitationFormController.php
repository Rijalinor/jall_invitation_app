<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Services\FormLinks;
use App\Services\PublicImageUpload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * The customer-facing content form, reached through a link issued from the
 * admin panel.
 *
 * The invitation is always resolved from the token, never from the request
 * body, so a valid link can only ever edit its own invitation. The form writes
 * content only (hosts, events, stories, gallery); template, status, slug and
 * publication dates stay under the operator's control.
 */
class InvitationFormController extends Controller
{
    public const STEP_HOSTS = 'mempelai';

    public const STEP_EVENTS = 'acara';

    public const STEP_STORIES = 'cerita';

    public const STEP_GALLERY = 'galeri';

    public const STEP_DONE = 'selesai';

    private const STEPS = [
        self::STEP_HOSTS => 'Mempelai',
        self::STEP_EVENTS => 'Acara',
        self::STEP_STORIES => 'Cerita',
        self::STEP_GALLERY => 'Galeri',
        self::STEP_DONE => 'Selesai',
    ];

    private const HOST_ROLES = [
        'groom' => 'Mempelai Pria',
        'bride' => 'Mempelai Wanita',
        'host' => 'Tuan Rumah',
        'co_host' => 'Pendamping Tuan Rumah',
    ];

    private const TIMEZONES = [
        'Asia/Jakarta' => 'WIB (Jakarta)',
        'Asia/Makassar' => 'WITA (Makassar)',
        'Asia/Jayapura' => 'WIT (Jayapura)',
    ];

    private const HOST_PHOTO_DIRECTORY = 'invitations/hosts';

    private const STORY_PHOTO_DIRECTORY = 'invitations/stories';

    private const GALLERY_PHOTO_DIRECTORY = 'invitations/media';

    private const MAX_HOSTS = 4;

    private const MAX_EVENTS = 5;

    private const MAX_STORIES = 8;

    private const MAX_GALLERY = 30;

    public function show(
        FormLinks $links,
        string $token,
        ?string $step = null,
    ): View {
        $invitation = $this->invitation($links, $token);
        $links->touch($invitation);

        $step = $step !== null && array_key_exists($step, self::STEPS) ? $step : self::STEP_HOSTS;

        return view('invitation-form.show', [
            'token' => $token,
            'invitation' => $invitation,
            'step' => $step,
            'steps' => self::STEPS,
            'hosts' => $invitation->hosts()->orderBy('position')->orderBy('id')->get(),
            'events' => $invitation->events()->orderBy('position')->orderBy('id')->get(),
            'stories' => $invitation->stories()->orderBy('position')->orderBy('id')->get(),
            'gallery' => $invitation->media()->orderBy('position')->orderBy('id')->get(),
            'hostRoles' => self::HOST_ROLES,
            'timezones' => self::TIMEZONES,
            'maxHosts' => self::MAX_HOSTS,
            'maxEvents' => self::MAX_EVENTS,
            'maxStories' => self::MAX_STORIES,
            'maxGallery' => self::MAX_GALLERY,
            'remainingPhotoSlots' => $this->remainingPhotoSlots($invitation),
        ]);
    }

    public function update(
        Request $request,
        FormLinks $links,
        PublicImageUpload $uploads,
        string $token,
        string $step,
    ): RedirectResponse {
        $invitation = $this->invitation($links, $token);

        return match ($step) {
            self::STEP_HOSTS => $this->saveHosts($request, $invitation, $uploads, $token),
            self::STEP_EVENTS => $this->saveEvents($request, $invitation, $token),
            self::STEP_STORIES => $this->saveStories($request, $invitation, $uploads, $token),
            self::STEP_GALLERY => $this->saveGallery($request, $invitation, $uploads, $token),
            default => $this->toStep($token, self::STEP_DONE),
        };
    }

    /**
     * @return array<string, string>
     */
    public static function steps(): array
    {
        return self::STEPS;
    }

    private function saveHosts(
        Request $request,
        Invitation $invitation,
        PublicImageUpload $uploads,
        string $token,
    ): RedirectResponse {
        $data = $request->validateWithBag('form', [
            'hosts' => ['array', 'max:'.self::MAX_HOSTS],
            'hosts.*.id' => ['nullable', 'integer'],
            'hosts.*.remove' => ['nullable', 'boolean'],
            'hosts.*.name' => ['nullable', 'string', 'max:150'],
            'hosts.*.role' => ['nullable', Rule::in(array_keys(self::HOST_ROLES))],
            'hosts.*.nickname' => ['nullable', 'string', 'max:100'],
            'hosts.*.birth_order' => ['nullable', 'string', 'max:100'],
            'hosts.*.parent_father' => ['nullable', 'string', 'max:150'],
            'hosts.*.parent_mother' => ['nullable', 'string', 'max:150'],
            'hosts.*.bio' => ['nullable', 'string', 'max:600'],
            'hosts.*.social_instagram' => ['nullable', 'string', 'max:100'],
            'hosts.*.social_tiktok' => ['nullable', 'string', 'max:100'],
            'hosts.*.photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:'.PublicImageUpload::MAX_KILOBYTES,
            ],
        ]);

        $rows = collect($data['hosts'] ?? [])
            ->filter(fn (array $row): bool => filled($row['name'] ?? null))
            // A row the customer ticked for removal is never also saved.
            ->reject(fn (array $row): bool => filter_var($row['remove'] ?? false, FILTER_VALIDATE_BOOL))
            ->take(self::MAX_HOSTS)
            ->values();

        if ($rows->isEmpty()) {
            $this->fail('Isi minimal satu nama mempelai.');
        }

        // Store new photos before touching the database, so a rejected image
        // leaves the saved content intact.
        [$photos] = $this->storeUploads($rows, $invitation, $uploads, self::HOST_PHOTO_DIRECTORY);

        DB::transaction(function () use ($rows, $data, $photos, $invitation, $uploads): void {
            $keptIds = [];

            foreach ($rows as $index => $row) {
                $host = filled($row['id'] ?? null)
                    ? $invitation->hosts()->find($row['id'])
                    : null;

                $host ??= $invitation->hosts()->make();

                $host->fill([
                    'role' => ($row['role'] ?? null) ?: 'groom',
                    'name' => $row['name'],
                    'nickname' => $row['nickname'] ?? null,
                    'birth_order' => $row['birth_order'] ?? null,
                    'parent_father' => $row['parent_father'] ?? null,
                    'parent_mother' => $row['parent_mother'] ?? null,
                    'bio' => $row['bio'] ?? null,
                    'social_instagram' => $row['social_instagram'] ?? null,
                    'social_tiktok' => $row['social_tiktok'] ?? null,
                    'position' => $index,
                ]);

                if (isset($photos[$index])) {
                    $uploads->delete($host->photo_path, self::HOST_PHOTO_DIRECTORY);
                    $host->photo_path = $photos[$index];
                }

                $host->save();
                $keptIds[] = $host->id;
            }

            // Deletion is explicit: a row missing from the payload is left
            // alone, so a partially submitted form can never wipe content.
            $this->applyRemovals($invitation->hosts(), $data['hosts'] ?? [], $keptIds, $uploads, self::HOST_PHOTO_DIRECTORY, 'photo_path');

            // Thrown inside the transaction so a submission that would empty the
            // invitation rolls back instead of leaving it without any host.
            if ($invitation->hosts()->count() === 0) {
                $this->fail('Isi minimal satu mempelai.');
            }
        });

        return $this->toStep($token, self::STEP_EVENTS);
    }

    private function saveEvents(Request $request, Invitation $invitation, string $token): RedirectResponse
    {
        $data = $request->validateWithBag('form', [
            'acara' => ['array', 'max:'.self::MAX_EVENTS],
            'acara.*.id' => ['nullable', 'integer'],
            'acara.*.remove' => ['nullable', 'boolean'],
            'acara.*.label' => ['nullable', 'string', 'max:150'],
            'acara.*.date' => ['nullable', 'date'],
            'acara.*.start_time' => ['nullable', 'date_format:H:i'],
            'acara.*.end_time' => ['nullable', 'date_format:H:i'],
            'acara.*.timezone' => ['nullable', Rule::in(array_keys(self::TIMEZONES))],
            'acara.*.venue_name' => ['nullable', 'string', 'max:150'],
            'acara.*.address' => ['nullable', 'string', 'max:500'],
            'acara.*.map_url' => ['nullable', 'string', 'max:500', 'regex:/^https?:\/\//i'],
            'acara.*.dress_code' => ['nullable', 'string', 'max:100'],
        ]);

        $rows = collect($data['acara'] ?? [])
            ->filter(fn (array $row): bool => filled($row['label'] ?? null))
            ->reject(fn (array $row): bool => filter_var($row['remove'] ?? false, FILTER_VALIDATE_BOOL))
            ->take(self::MAX_EVENTS)
            ->values();

        foreach ($rows as $row) {
            if (! filled($row['date'] ?? null)) {
                $this->fail('Setiap acara perlu tanggal pelaksanaan.');
            }
        }

        DB::transaction(function () use ($rows, $data, $invitation): void {
            $keptIds = [];

            foreach ($rows as $index => $row) {
                $event = filled($row['id'] ?? null)
                    ? $invitation->events()->find($row['id'])
                    : null;

                $event ??= $invitation->events()->make();

                $event->fill([
                    'label' => $row['label'],
                    'date' => $row['date'],
                    'start_time' => $row['start_time'] ?? null,
                    'end_time' => $row['end_time'] ?? null,
                    'timezone' => ($row['timezone'] ?? null) ?: 'Asia/Jakarta',
                    'venue_name' => $row['venue_name'] ?? null,
                    'address' => $row['address'] ?? null,
                    'map_url' => $row['map_url'] ?? null,
                    'dress_code' => $row['dress_code'] ?? null,
                    'is_primary' => $index === 0,
                    'position' => $index,
                ]);

                $event->save();
                $keptIds[] = $event->id;
            }

            $this->applyRemovals($invitation->events(), $data['acara'] ?? [], $keptIds);

            // Rolled back by the transaction when it fails, so the invitation is
            // never left without a single event.
            if ($invitation->events()->count() === 0) {
                $this->fail('Isi minimal satu acara.');
            }

            // Keep exactly one primary event, matching the admin panel.
            $primary = $invitation->events()->orderBy('position')->orderBy('id')->first();

            $invitation->events()->update(['is_primary' => false]);

            if ($primary) {
                $invitation->events()->whereKey($primary->id)->update(['is_primary' => true]);
            }
        });

        return $this->toStep($token, self::STEP_STORIES);
    }

    private function saveStories(
        Request $request,
        Invitation $invitation,
        PublicImageUpload $uploads,
        string $token,
    ): RedirectResponse {
        $data = $request->validateWithBag('form', [
            'cerita' => ['array', 'max:'.self::MAX_STORIES],
            'cerita.*.id' => ['nullable', 'integer'],
            'cerita.*.remove' => ['nullable', 'boolean'],
            'cerita.*.date' => ['nullable', 'string', 'max:100'],
            'cerita.*.title' => ['nullable', 'string', 'max:255'],
            'cerita.*.body' => ['nullable', 'string', 'max:1200'],
            'cerita.*.photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:'.PublicImageUpload::MAX_KILOBYTES,
            ],
        ]);

        $rows = collect($data['cerita'] ?? [])->take(self::MAX_STORIES)->values();

        // A row that carries anything at all must also carry a title, otherwise
        // the customer's typing would be silently discarded.
        foreach ($rows as $row) {
            $touched = filled($row['id'] ?? null)
                || filled($row['date'] ?? null)
                || filled($row['body'] ?? null)
                || ($row['photo'] ?? null) instanceof UploadedFile;

            if ($touched && ! filled($row['title'] ?? null)) {
                $this->fail('Setiap momen perlu judul.');
            }
        }

        $rows = $rows
            ->filter(fn (array $row): bool => filled($row['title'] ?? null))
            ->reject(fn (array $row): bool => filter_var($row['remove'] ?? false, FILTER_VALIDATE_BOOL))
            ->values();

        [$photos, $uploaded] = $this->storeUploads($rows, $invitation, $uploads, self::STORY_PHOTO_DIRECTORY);

        DB::transaction(function () use ($rows, $data, $photos, $invitation, $uploads): void {
            $keptIds = [];

            foreach ($rows as $index => $row) {
                $story = filled($row['id'] ?? null)
                    ? $invitation->stories()->find($row['id'])
                    : null;

                $story ??= $invitation->stories()->make();

                $story->fill([
                    'date' => $row['date'] ?? null,
                    'title' => $row['title'],
                    'body' => $row['body'] ?? null,
                    'position' => $index,
                ]);

                if (isset($photos[$index])) {
                    $uploads->delete($story->image_path, self::STORY_PHOTO_DIRECTORY);
                    $story->image_path = $photos[$index];
                }

                $story->save();
                $keptIds[] = $story->id;
            }

            $this->applyRemovals($invitation->stories(), $data['cerita'] ?? [], $keptIds, $uploads, self::STORY_PHOTO_DIRECTORY, 'image_path');
        });

        return $this->toStep($token, self::STEP_GALLERY);
    }

    private function saveGallery(
        Request $request,
        Invitation $invitation,
        PublicImageUpload $uploads,
        string $token,
    ): RedirectResponse {
        $data = $request->validateWithBag('form', [
            'galeri' => ['array', 'max:'.self::MAX_GALLERY],
            'galeri.*.id' => ['nullable', 'integer'],
            'galeri.*.remove' => ['nullable', 'boolean'],
            'galeri.*.caption' => ['nullable', 'string', 'max:500'],
            'galeri.*.alt_text' => ['nullable', 'string', 'max:255'],
            'galeri.*.photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:'.PublicImageUpload::MAX_KILOBYTES,
            ],
        ]);

        $rows = collect($data['galeri'] ?? [])->take(self::MAX_GALLERY)->values();

        foreach ($rows as $row) {
            $hasPhoto = ($row['photo'] ?? null) instanceof UploadedFile;

            if (! $hasPhoto && ! filled($row['id'] ?? null)
                && (filled($row['caption'] ?? null) || filled($row['alt_text'] ?? null))) {
                $this->fail('Pilih foto untuk setiap baris galeri yang diisi.');
            }
        }

        // Gallery rows only exist when they have a file, so a row without an id
        // and without an upload is simply an untouched spare slot. A row ticked
        // for removal is dropped here so it can be deleted, not re-saved.
        $rows = $rows
            ->reject(fn (array $row): bool => filter_var($row['remove'] ?? false, FILTER_VALIDATE_BOOL))
            ->filter(fn (array $row): bool => filled($row['id'] ?? null)
                || ($row['photo'] ?? null) instanceof UploadedFile)
            ->values();

        [$photos, $uploaded] = $this->storeUploads($rows, $invitation, $uploads, self::GALLERY_PHOTO_DIRECTORY);

        DB::transaction(function () use ($rows, $data, $photos, $invitation, $uploads): void {
            $keptIds = [];

            foreach ($rows as $index => $row) {
                $item = filled($row['id'] ?? null)
                    ? $invitation->media()->find($row['id'])
                    : null;

                if ($item === null && ! isset($photos[$index])) {
                    continue;
                }

                $item ??= $invitation->media()->make(['type' => 'image']);

                $item->fill([
                    'type' => $item->type ?: 'image',
                    'caption' => $row['caption'] ?? null,
                    'alt_text' => $row['alt_text'] ?? null,
                    'position' => $index,
                ]);

                if (isset($photos[$index])) {
                    $uploads->delete($item->path, self::GALLERY_PHOTO_DIRECTORY);
                    $item->path = $photos[$index];
                }

                $item->save();
                $keptIds[] = $item->id;
            }

            $this->applyRemovals($invitation->media(), $data['galeri'] ?? [], $keptIds, $uploads, self::GALLERY_PHOTO_DIRECTORY, 'path');
        });

        return $this->toStep($token, self::STEP_DONE);
    }

    /**
     * Store every uploaded photo up front so a rejected image leaves the saved
     * content untouched, and so the per-invitation quota is applied once.
     *
     * @param  Collection<int, array<string, mixed>>  $rows
     * @return array{0: array<int, string>, 1: int}
     */
    private function storeUploads(
        Collection $rows,
        Invitation $invitation,
        PublicImageUpload $uploads,
        string $directory,
    ): array {
        $photos = [];
        $uploaded = 0;

        foreach ($rows as $index => $row) {
            $file = $row['photo'] ?? null;

            if (! $file instanceof UploadedFile) {
                continue;
            }

            if ($this->remainingPhotoSlots($invitation) - $uploaded < 1) {
                $this->fail('Kuota foto undangan ini sudah penuh.');
            }

            $photos[$index] = $uploads->store($file, $directory);
            $uploaded++;
        }

        return [$photos, $uploaded];
    }

    /**
     * Delete only the rows the customer explicitly ticked for removal, and their
     * files. Rows missing from the payload are always left alone.
     *
     * @param  \Illuminate\Database\Eloquent\Relations\HasMany<*>  $relation
     * @param  array<int, array<string, mixed>>  $submitted
     * @param  list<int>  $keptIds
     */
    private function applyRemovals(
        $relation,
        array $submitted,
        array $keptIds,
        ?PublicImageUpload $uploads = null,
        ?string $directory = null,
        ?string $pathColumn = null,
    ): void {
        $removals = collect($submitted)
            ->filter(fn (array $row): bool => filter_var($row['remove'] ?? false, FILTER_VALIDATE_BOOL)
                && filled($row['id'] ?? null))
            ->pluck('id')
            ->all();

        if ($removals === []) {
            return;
        }

        $doomed = $relation->whereIn('id', $removals)->whereNotIn('id', $keptIds)->get();

        foreach ($doomed as $record) {
            if ($uploads !== null && $directory !== null && $pathColumn !== null) {
                $uploads->delete($record->{$pathColumn}, $directory);
            }

            $record->delete();
        }
    }

    private function invitation(FormLinks $links, string $token): Invitation
    {
        return $links->resolve($token) ?? abort(404);
    }

    private function toStep(string $token, string $step): RedirectResponse
    {
        return redirect()
            ->route('invitation-form.show', ['token' => $token, 'step' => $step])
            ->with('form_saved', true);
    }

    private function remainingPhotoSlots(Invitation $invitation): int
    {
        $used = $invitation->media()->count()
            + $invitation->hosts()->whereNotNull('photo_path')->count()
            + $invitation->stories()->whereNotNull('image_path')->count();

        return max(0, PublicImageUpload::MAX_PER_INVITATION - $used);
    }

    /**
     * @return never
     */
    private function fail(string $message): void
    {
        throw ValidationException::withMessages(['form' => $message])->errorBag('form');
    }
}

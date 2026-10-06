<?php

namespace App\Http\Controllers;

use App\Enums\InvitationStatus;
use App\Models\Guest;
use App\Models\Invitation;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class InvitationInteractionController extends Controller
{
    public function rsvp(Request $request, string $slug): RedirectResponse|JsonResponse
    {
        $invitation = $this->publishedInvitation($slug);
        $guest = $request->filled('guest_token')
            ? $invitation->guests()->where('token', $request->string('guest_token'))->first()
            : null;
        $limit = $guest?->invitation_limit ?? 2;
        $data = $request->validateWithBag('rsvp', [
            'guest_token' => ['nullable', 'string', 'max:64'],
            'website' => ['nullable', 'size:0'],
            'name' => ['required', 'string', 'max:150'],
            'status' => ['required', Rule::in(['attending', 'not_attending', 'tentative'])],
            'party_size' => ['required', 'integer', 'min:0', 'max:'.$limit],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        if ($data['status'] === 'attending' && $data['party_size'] < 1) {
            $message = 'Jumlah tamu hadir minimal 1.';

            if ($request->wantsJson()) {
                return response()->json(['message' => $message, 'errors' => ['party_size' => [$message]]], 422);
            }

            return back()->withErrors(['party_size' => $message], 'rsvp')->withInput();
        }

        if ($data['status'] !== 'attending') {
            $data['party_size'] = 0;
        }

        $this->storeRsvp($invitation, $guest, [
            'name' => $guest?->display_name ?? Str::squish(strip_tags($data['name'])),
            'status' => $data['status'],
            'party_size' => $data['party_size'],
            'note' => $this->cleanText($data['note'] ?? null, 500),
            'ip_address' => $request->ip(),
        ]);

        return $this->respond($request, 'rsvp_success', 'Konfirmasi kehadiran berhasil disimpan.');
    }

    public function guestbook(Request $request, string $slug): RedirectResponse|JsonResponse
    {
        $invitation = $this->publishedInvitation($slug);
        $data = $request->validateWithBag('guestbook', [
            'guest_token' => ['nullable', 'string', 'max:64'],
            'website' => ['nullable', 'size:0'],
            'name' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:1000'],
        ]);
        $guest = $request->filled('guest_token')
            ? $invitation->guests()->where('token', $request->string('guest_token'))->first()
            : null;

        $invitation->guestbookEntries()->create([
            'guest_id' => $guest?->id,
            'name' => $guest?->display_name ?? Str::squish(strip_tags($data['name'])),
            'message' => Str::squish(strip_tags($data['message'])),
            'moderation_status' => 'pending',
            'ip_address' => $request->ip(),
        ]);

        return $this->respond($request, 'guestbook_success', 'Ucapan terkirim dan menunggu moderasi.');
    }

    /**
     * The combined "konfirmasi & ucapan" form used by invitations that ask for
     * it: the guest states attendance and leaves one note that doubles as their
     * wish. A single submit writes the RSVP and a moderated guestbook entry.
     */
    public function confirmation(Request $request, string $slug): RedirectResponse|JsonResponse
    {
        $invitation = $this->publishedInvitation($slug);
        $guest = $request->filled('guest_token')
            ? $invitation->guests()->where('token', $request->string('guest_token'))->first()
            : null;
        $data = $request->validateWithBag('rsvp', [
            'guest_token' => ['nullable', 'string', 'max:64'],
            'website' => ['nullable', 'size:0'],
            'name' => ['required', 'string', 'max:150'],
            'status' => ['required', Rule::in(['attending', 'not_attending', 'tentative'])],
            'message' => ['required', 'string', 'max:1000'],
        ]);
        $name = $guest?->display_name ?? Str::squish(strip_tags($data['name']));
        $message = Str::squish(strip_tags($data['message']));

        $this->storeRsvp($invitation, $guest, [
            'name' => $name,
            'status' => $data['status'],
            'party_size' => $data['status'] === 'attending' ? 1 : 0,
            'note' => $this->cleanText($message, 500),
            'ip_address' => $request->ip(),
        ]);

        $invitation->guestbookEntries()->create([
            'guest_id' => $guest?->id,
            'name' => $name,
            'message' => $message,
            'moderation_status' => 'pending',
            'ip_address' => $request->ip(),
        ]);

        return $this->respond($request, 'rsvp_success', 'Konfirmasi & ucapan terkirim. Ucapan tampil setelah moderasi.');
    }

    /**
     * A submit from one of the public forms. A plain post reloads the page with
     * a flash message; the AJAX script asks for JSON and gets the same message
     * back, so it can show it in place without a reload.
     */
    private function respond(Request $request, string $flashKey, string $message): RedirectResponse|JsonResponse
    {
        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'message' => $message]);
        }

        return back()->with($flashKey, $message);
    }

    /**
     * Persist one RSVP, atomically for a known guest.
     *
     * updateOrCreate is a SELECT-then-INSERT, so two concurrent submits can both
     * insert; the unique index on (invitation_id, guest_id) turns that into a
     * constraint violation, which we recover from by updating the row the other
     * request just created.
     *
     * @param  array{name: string, status: string, party_size: int, note: ?string, ip_address: ?string}  $attributes
     */
    private function storeRsvp(Invitation $invitation, ?Guest $guest, array $attributes): void
    {
        $identity = $guest
            ? ['guest_id' => $guest->id]
            : ['guest_id' => null, 'name' => $attributes['name'], 'ip_address' => $attributes['ip_address']];

        $values = $attributes + ['guest_id' => $guest?->id, 'submitted_at' => now()];

        try {
            $invitation->rsvps()->updateOrCreate($identity, $values);
        } catch (UniqueConstraintViolationException) {
            $invitation->rsvps()->updateOrCreate($identity, $values);
        }

        // Keep the audience roster's attendance flag in step with the RSVP.
        if ($guest) {
            $guest->update(['has_attended' => $attributes['status'] === 'attending']);
        }
    }

    /**
     * Free text from a guest, stripped of markup and capped like every other
     * user-controlled field before it is stored.
     */
    private function cleanText(?string $value, int $limit): ?string
    {
        if ($value === null) {
            return null;
        }

        $clean = Str::limit(Str::squish(strip_tags($value)), $limit, '');

        return $clean !== '' ? $clean : null;
    }

    private function publishedInvitation(string $slug): Invitation
    {
        return Invitation::query()
            ->where('slug', $slug)
            ->where('status', InvitationStatus::PUBLISHED)
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->firstOrFail();
    }
}

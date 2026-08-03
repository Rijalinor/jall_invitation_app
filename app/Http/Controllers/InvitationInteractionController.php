<?php

namespace App\Http\Controllers;

use App\Enums\InvitationStatus;
use App\Models\Invitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class InvitationInteractionController extends Controller
{
    public function rsvp(Request $request, string $slug): RedirectResponse
    {
        $invitation = $this->publishedInvitation($slug);
        $guest = $request->filled('guest_token')
            ? $invitation->guests()->where('token', $request->string('guest_token'))->first()
            : null;
        $limit = $guest?->invitation_limit ?? 2;
        $data = $request->validateWithBag('rsvp', [
            'guest_token' => ['nullable', 'string'],
            'name' => ['required', 'string', 'max:150'],
            'status' => ['required', Rule::in(['attending', 'not_attending', 'tentative'])],
            'party_size' => ['required', 'integer', 'min:0', 'max:'.$limit],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        if ($data['status'] === 'attending' && $data['party_size'] < 1) {
            return back()->withErrors(['party_size' => 'Jumlah tamu hadir minimal 1.'], 'rsvp')->withInput();
        }

        if ($data['status'] !== 'attending') {
            $data['party_size'] = 0;
        }

        $identity = $guest
            ? ['guest_id' => $guest->id]
            : ['guest_id' => null, 'name' => Str::squish(strip_tags($data['name'])), 'ip_address' => $request->ip()];

        $invitation->rsvps()->updateOrCreate($identity, [
            'guest_id' => $guest?->id,
            'name' => $guest?->display_name ?? Str::squish(strip_tags($data['name'])),
            'status' => $data['status'],
            'party_size' => $data['party_size'],
            'note' => $data['note'] ?? null,
            'ip_address' => $request->ip(),
            'submitted_at' => now(),
        ]);

        return back()->with('rsvp_success', 'Konfirmasi kehadiran berhasil disimpan.');
    }

    public function guestbook(Request $request, string $slug): RedirectResponse
    {
        $invitation = $this->publishedInvitation($slug);
        $data = $request->validateWithBag('guestbook', [
            'guest_token' => ['nullable', 'string'],
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

        return back()->with('guestbook_success', 'Ucapan terkirim dan menunggu moderasi.');
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

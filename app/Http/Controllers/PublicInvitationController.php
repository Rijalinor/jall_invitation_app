<?php

namespace App\Http\Controllers;

use App\Enums\InvitationStatus;
use App\Models\Invitation;
use App\Services\InvitationRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PublicInvitationController extends Controller
{
    public function show(Request $request, InvitationRenderer $renderer, string $slug, ?string $token = null): View
    {
        $invitation = Invitation::query()
            ->where('slug', $slug)
            ->where('status', InvitationStatus::PUBLISHED)
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->with([
                'hosts', 'events', 'sections', 'stories', 'media', 'contacts', 'giftMethods',
                'guestbookEntries' => fn ($query) => $query->where('moderation_status', 'approved')->latest()->limit(20),
            ])
            ->firstOrFail();

        $guest = $token ? $invitation->guests()->where('token', $token)->first() : null;
        if ($guest && ! $guest->link_opened_at) {
            $guest->updateQuietly(['link_opened_at' => now()]);
        }
        $recipient = $guest?->display_name ?? $request->query('to') ?? 'Bapak/Ibu/Saudara/i';
        $recipient = Str::limit(Str::squish(strip_tags((string) $recipient)), 150, '');

        try {
            return $renderer->render($invitation, $recipient ?: 'Bapak/Ibu/Saudara/i', $guest);
        } catch (\RuntimeException) {
            throw new NotFoundHttpException;
        }
    }
}

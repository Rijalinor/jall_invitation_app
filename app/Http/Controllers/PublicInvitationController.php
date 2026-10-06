<?php

namespace App\Http\Controllers;

use App\Enums\InvitationStatus;
use App\Models\Invitation;
use App\Services\InvitationRenderer;
use App\Services\TemplateRegistry;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PublicInvitationController extends Controller
{
    public function show(Request $request, InvitationRenderer $renderer, TemplateRegistry $templates, string $slug, ?string $token = null): View
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
        $requestedRecipient = $request->query('to');
        $recipient = $guest?->display_name ?? (is_string($requestedRecipient) ? $requestedRecipient : null);
        $recipient = $recipient !== null ? Str::limit(Str::squish(strip_tags($recipient)), 150, '') : null;
        // A name that sanitises to nothing is treated as no name at all, so a
        // general link shows no greeting rather than an empty one.
        $recipient = $recipient ?: null;

        // The catalogue shows one sample in every template's design, so a sample
        // invitation accepts a registered `template` override. A normal
        // invitation ignores it: the stored template is the customer's choice,
        // not something a query string can change.
        $templateId = null;
        $requested = $request->query('template');

        if ($invitation->is_catalog_demo && is_string($requested) && $templates->find($requested)) {
            $templateId = $requested;
        }

        try {
            return $renderer->render($invitation, $recipient, $guest, $templateId);
        } catch (\RuntimeException) {
            throw new NotFoundHttpException;
        }
    }
}

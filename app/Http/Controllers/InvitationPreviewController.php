<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Services\InvitationRenderer;
use Illuminate\Contracts\View\View;

class InvitationPreviewController extends Controller
{
    public function show(Invitation $invitation, InvitationRenderer $renderer): View
    {
        $invitation->load([
            'hosts', 'events', 'sections', 'stories', 'media', 'contacts', 'giftMethods',
            'guestbookEntries' => fn ($query) => $query->where('moderation_status', 'approved')->latest()->limit(20),
        ]);

        return $renderer->render($invitation, 'Bapak/Ibu/Saudara/i');
    }
}

<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\Invitation;
use App\ViewModels\InvitationViewModel;
use Illuminate\Contracts\View\View;
use RuntimeException;

class InvitationRenderer
{
    public function __construct(private TemplateRegistry $templates) {}

    public function render(Invitation $invitation, string $recipient, ?Guest $guest = null): View
    {
        $manifest = $this->templates->find($invitation->template_id);

        if (! $manifest) {
            throw new RuntimeException('Template undangan tidak tersedia.');
        }

        return view($manifest['entry_view'], InvitationViewModel::from($invitation, $recipient, $manifest, $guest)->data);
    }
}

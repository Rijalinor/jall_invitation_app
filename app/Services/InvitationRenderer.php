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

    /**
     * Render an invitation through a template.
     *
     * The template is normally the one stored on the invitation. The public
     * catalogue passes an explicit `$templateId` so one sample invitation can
     * be shown in every registered design without changing what is stored. When
     * that design differs from the sample's own, the design's default colours
     * are used instead of the sample's saved palette, so each card shows itself.
     */
    public function render(Invitation $invitation, ?string $recipient, ?Guest $guest = null, ?string $templateId = null): View
    {
        $manifest = $this->templates->find($templateId ?? $invitation->template_id);

        if (! $manifest) {
            throw new RuntimeException('Template undangan tidak tersedia.');
        }

        $useTemplateDefaults = $templateId !== null && $templateId !== $invitation->template_id;

        return view($manifest['entry_view'], InvitationViewModel::from(
            $invitation,
            $recipient,
            $manifest,
            $guest,
            $useTemplateDefaults,
        )->data);
    }
}

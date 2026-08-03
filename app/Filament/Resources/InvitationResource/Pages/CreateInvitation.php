<?php

namespace App\Filament\Resources\InvitationResource\Pages;

use App\Filament\Resources\InvitationResource;
use App\Models\Invitation;
use Filament\Resources\Pages\CreateRecord;

class CreateInvitation extends CreateRecord
{
    protected static string $resource = InvitationResource::class;

    protected function afterCreate(): void
    {
        /** @var Invitation $invitation */
        $invitation = $this->record;

        $defaultSections = [
            'opening', 'hosts', 'events', 'countdown', 'calendar',
            'map', 'story', 'gallery', 'rsvp', 'guestbook',
            'gifts', 'contacts', 'livestream', 'sharing', 'closing',
        ];

        foreach ($defaultSections as $index => $key) {
            $invitation->sections()->create([
                'key' => $key,
                'enabled' => true,
                'position' => $index,
            ]);
        }
    }
}

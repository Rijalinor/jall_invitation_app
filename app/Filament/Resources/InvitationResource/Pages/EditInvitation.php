<?php

namespace App\Filament\Resources\InvitationResource\Pages;

use App\Enums\InvitationStatus;
use App\Filament\Resources\InvitationResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\URL;

class EditInvitation extends EditRecord
{
    protected static string $resource = InvitationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('preview')
                ->label('Preview Undangan')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url(fn () => URL::temporarySignedRoute('invitations.preview', now()->addMinutes(15), $this->record))
                ->openUrlInNewTab(),

            Actions\Action::make('publish')
                ->label('Publish Undangan')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->status !== InvitationStatus::PUBLISHED)
                ->action(function () {
                    if (! $this->ensureReady()) {
                        return;
                    }

                    $this->record->update([
                        'status' => InvitationStatus::PUBLISHED->value,
                        'published_at' => $this->record->published_at ?? now(),
                    ]);
                    $this->refreshFormData(['status', 'published_at']);
                    Notification::make()
                        ->title('Undangan Berhasil Dipublikasikan')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('setPreview')
                ->label('Set ke Preview')
                ->icon('heroicon-o-eye')
                ->color('warning')
                ->visible(fn () => $this->record->status !== InvitationStatus::PREVIEW)
                ->action(function () {
                    if (! $this->ensureReady()) {
                        return;
                    }

                    $this->record->update(['status' => InvitationStatus::PREVIEW->value]);
                    $this->refreshFormData(['status', 'published_at']);
                    Notification::make()
                        ->title('Status diubah ke Preview')
                        ->warning()
                        ->send();
                }),

            Actions\DeleteAction::make(),
        ];
    }

    private function ensureReady(): bool
    {
        $missing = $this->record->missingPreviewRequirements();

        if ($missing === []) {
            return true;
        }

        Notification::make()->title('Konten undangan belum lengkap')
            ->body('Lengkapi '.implode(' dan ', $missing).' sebelum melanjutkan.')
            ->danger()->send();

        return false;
    }
}

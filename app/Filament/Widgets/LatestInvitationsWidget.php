<?php

namespace App\Filament\Widgets;

use App\Enums\InvitationStatus;
use App\Filament\Resources\InvitationResource;
use App\Models\Invitation;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

/**
 * A short list of the newest invitations, so the dashboard opens on something
 * actionable instead of only counters.
 */
class LatestInvitationsWidget extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(Invitation::query()->latest('created_at'))
            ->heading('Undangan Terbaru')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul Undangan')
                    ->weight('bold')
                    ->description(fn (Invitation $record): string => url('/'.$record->slug))
                    ->searchable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Pelanggan')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('template_id')
                    ->label('Template')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof InvitationStatus ? $state->label() : InvitationStatus::tryFrom($state)?->label() ?? $state)
                    ->color(fn ($state) => match ($state instanceof InvitationStatus ? $state->value : $state) {
                        'draft' => 'gray',
                        'preview' => 'warning',
                        'published' => 'success',
                        'expired' => 'danger',
                        'archived' => 'secondary',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordUrl(fn (Invitation $record): string => InvitationResource::getUrl('edit', ['record' => $record]))
            ->paginated([5, 10, 25]);
    }
}

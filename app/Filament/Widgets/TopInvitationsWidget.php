<?php

namespace App\Filament\Widgets;

use App\Enums\InvitationStatus;
use App\Filament\Resources\InvitationResource;
use App\Models\Invitation;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

/**
 * Which invitations guests actually open, so the dashboard shows where the
 * attention is instead of only how many invitations exist.
 */
class TopInvitationsWidget extends TableWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(Invitation::query()->withCount('visits')->latest('id'))
            ->heading('Undangan Paling Banyak Dilihat')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul Undangan')
                    ->weight('bold')
                    ->description(fn (Invitation $record): string => url('/'.$record->slug)),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Pelanggan')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('visits_count')
                    ->label('Kunjungan')
                    ->numeric()
                    ->alignCenter(),

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
            ])
            ->defaultSort('visits_count', 'desc')
            ->recordUrl(fn (Invitation $record): string => InvitationResource::getUrl('edit', ['record' => $record]))
            ->paginated([5, 10, 25]);
    }
}

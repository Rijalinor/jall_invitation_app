<?php

namespace App\Filament\Resources\InvitationResource\RelationManagers;

use App\Models\InvitationVisit;
use BackedEnum;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Read-only analytics for one invitation: how many times the link was opened,
 * how many distinct visitors, and the raw log with the guest link and device
 * behind each open. This is a report, not content, so nothing here is editable.
 */
class VisitsRelationManager extends RelationManager
{
    protected static string $relationship = 'visits';

    protected static ?string $title = 'Analitik Kunjungan';

    protected static string|BackedEnum|null $icon = 'heroicon-o-chart-bar';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function canCreate(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        $visits = $this->getOwnerRecord()->visits();

        $total = (clone $visits)->count();
        $unique = (clone $visits)->whereNotNull('ip_hash')->distinct()->count('ip_hash');
        $personal = (clone $visits)->whereNotNull('guest_id')->count();
        $latest = (clone $visits)->latest('created_at')->first();

        return $table
            ->heading($total === 0
                ? 'Belum ada kunjungan. Angka muncul setelah tamu membuka link undangan.'
                : $total.' kunjungan • '.$unique.' pengunjung unik • '.$personal.' dari link pribadi • terakhir '.$latest->created_at->diffForHumans())
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('guest.display_name')
                    ->label('Tamu')
                    ->placeholder('Link umum')
                    ->searchable(),

                Tables\Columns\TextColumn::make('source')
                    ->label('Sumber')
                    ->state(fn (InvitationVisit $record): string => $record->source())
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Link pribadi' ? 'success' : 'gray'),

                Tables\Columns\TextColumn::make('device')
                    ->label('Perangkat')
                    ->state(fn (InvitationVisit $record): string => $record->device())
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('referrer')
                    ->label('Dari')
                    ->placeholder('Langsung / WhatsApp')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('guest_id')
                    ->label('Sumber')
                    ->placeholder('Semua')
                    ->trueLabel('Link pribadi')
                    ->falseLabel('Link umum')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('guest_id'),
                        false: fn ($query) => $query->whereNull('guest_id'),
                    ),
            ]);
    }
}

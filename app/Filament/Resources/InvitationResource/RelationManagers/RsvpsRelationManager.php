<?php

namespace App\Filament\Resources\InvitationResource\RelationManagers;

use App\Enums\RsvpStatus;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RsvpsRelationManager extends RelationManager
{
    protected static string $relationship = 'rsvps';

    protected static ?string $title = 'Konfirmasi Kehadiran';

    protected static string|BackedEnum|null $icon = 'heroicon-o-clipboard-document-check';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Nama')->required()->maxLength(150),
            Select::make('status')->options(collect(RsvpStatus::cases())->mapWithKeys(fn (RsvpStatus $status) => [$status->value => $status->label()]))->required(),
            TextInput::make('party_size')->label('Jumlah Tamu')->numeric()->minValue(0)->required(),
            Textarea::make('note')->label('Catatan')->maxLength(500)->columnSpanFull(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable(),
                Tables\Columns\TextColumn::make('status')->label('Status')->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof RsvpStatus ? $state->label() : RsvpStatus::from($state)->label())
                    ->color(fn ($state) => $state instanceof RsvpStatus ? $state->color() : RsvpStatus::from($state)->color()),
                Tables\Columns\TextColumn::make('party_size')->label('Jumlah'),
                Tables\Columns\TextColumn::make('submitted_at')->label('Dikirim')->dateTime('d M Y H:i')->sortable(),
            ])
            ->defaultSort('submitted_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('Status Kehadiran')
                    ->options(collect(RsvpStatus::cases())->mapWithKeys(fn (RsvpStatus $status) => [$status->value => $status->label()])),
            ])
            ->headerActions([
                Actions\Action::make('export')->label('Ekspor CSV')->icon('heroicon-o-arrow-down-tray')
                    ->action(fn (): StreamedResponse => response()->streamDownload(function (): void {
                        $stream = fopen('php://output', 'w');
                        fputcsv($stream, ['Nama', 'Status', 'Jumlah Tamu', 'Catatan', 'Dikirim']);
                        $this->getOwnerRecord()->rsvps()->latest('submitted_at')->each(function ($rsvp) use ($stream): void {
                            fputcsv($stream, [$rsvp->name, $rsvp->status->label(), $rsvp->party_size, $rsvp->note, $rsvp->submitted_at?->format('Y-m-d H:i:s')]);
                        });
                        fclose($stream);
                    }, 'rsvp-'.$this->getOwnerRecord()->slug.'.csv')),
            ])
            ->actions([Actions\EditAction::make(), Actions\DeleteAction::make()]);
    }
}

<?php

namespace App\Filament\Resources\InvitationResource\RelationManagers;

use App\Enums\ModerationStatus;
use App\Models\GuestbookEntry;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class GuestbookEntriesRelationManager extends RelationManager
{
    protected static string $relationship = 'guestbookEntries';

    protected static ?string $title = 'Buku Ucapan';

    protected static string|BackedEnum|null $icon = 'heroicon-o-chat-bubble-left-right';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Nama')->required()->maxLength(150),
            Select::make('moderation_status')->label('Moderasi')->options(collect(ModerationStatus::cases())->mapWithKeys(fn (ModerationStatus $status) => [$status->value => $status->label()]))->required(),
            Textarea::make('message')->label('Ucapan')->required()->maxLength(1000)->columnSpanFull(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable(),
                Tables\Columns\TextColumn::make('message')->label('Ucapan')->limit(70)->wrap(),
                Tables\Columns\TextColumn::make('moderation_status')->label('Moderasi')->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof ModerationStatus ? $state->label() : ModerationStatus::from($state)->label())
                    ->color(fn ($state) => $state instanceof ModerationStatus ? $state->color() : ModerationStatus::from($state)->color()),
                Tables\Columns\TextColumn::make('created_at')->label('Dikirim')->dateTime('d M Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Actions\Action::make('approve')->label('Setujui')->color('success')->icon('heroicon-o-check')
                    ->visible(fn (GuestbookEntry $record) => $record->moderation_status !== ModerationStatus::APPROVED)
                    ->action(fn (GuestbookEntry $record) => $record->update(['moderation_status' => ModerationStatus::APPROVED])),
                Actions\Action::make('reject')->label('Tolak')->color('danger')->icon('heroicon-o-x-mark')
                    ->visible(fn (GuestbookEntry $record) => $record->moderation_status !== ModerationStatus::REJECTED)
                    ->action(fn (GuestbookEntry $record) => $record->update(['moderation_status' => ModerationStatus::REJECTED])),
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ]);
    }
}

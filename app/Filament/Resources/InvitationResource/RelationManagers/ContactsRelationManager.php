<?php

namespace App\Filament\Resources\InvitationResource\RelationManagers;

use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ContactsRelationManager extends RelationManager
{
    protected static string $relationship = 'contacts';

    protected static ?string $title = 'Kontak Penanggung Jawab';

    protected static string|BackedEnum|null $icon = 'heroicon-o-phone';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Kontak Panitia / WO')
                    ->schema([
                        TextInput::make('label')
                            ->label('Label / Peran Kontak')
                            ->placeholder('CP Keluarga Pria / WO / Panitia')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('name')
                            ->label('Nama Kontak')
                            ->placeholder('Bapak Ahmad')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('phone')
                            ->label('Nomor WhatsApp / Telepon')
                            ->placeholder('081234567890')
                            ->tel()
                            ->required()
                            ->rule('regex:/^\+?[0-9\s\-()]{8,20}$/')
                            ->maxLength(50),

                        TextInput::make('position')
                            ->label('Urutan Tampilan')
                            ->numeric()
                            ->default(0),
                    ])->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('label')
                    ->label('Peran / Label')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Kontak')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('phone')
                    ->label('WhatsApp')
                    ->copyable()
                    ->icon('heroicon-m-phone')
                    ->searchable(),

                Tables\Columns\TextColumn::make('position')
                    ->label('Urutan')
                    ->sortable(),
            ])
            ->defaultSort('position', 'asc')
            ->reorderable('position')
            ->headerActions([
                Actions\CreateAction::make(),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}

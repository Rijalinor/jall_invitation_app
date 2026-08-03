<?php

namespace App\Filament\Resources\InvitationResource\RelationManagers;

use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class StoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'stories';

    protected static ?string $title = 'Kisah Cinta / Timeline';

    protected static string|BackedEnum|null $icon = 'heroicon-o-heart';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Kisah Cinta / Momen Penting')
                    ->schema([
                        TextInput::make('date')
                            ->label('Tanggal / Momen')
                            ->placeholder('14 Februari 2020')
                            ->maxLength(100),

                        TextInput::make('title')
                            ->label('Judul Momen')
                            ->placeholder('Awal Pertemuan')
                            ->required()
                            ->maxLength(255),

                        FileUpload::make('image_path')
                            ->disk('public')
                            ->label('Foto Kenangan (Opsional)')
                            ->directory('invitations/stories')
                            ->image()
                            ->imageEditor()
                            ->imageEditorViewportWidth(560)
                            ->imageEditorViewportHeight(360)
                            ->imageEditorAspectRatios(['16:9', '4:5', '1:1'])
                            ->imageResizeMode('contain')
                            ->imageResizeTargetWidth('1200')
                            ->imageResizeTargetHeight('1200')
                            ->imageResizeUpscale(false)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(3072)
                            ->columnSpanFull(),

                        Textarea::make('body')
                            ->label('Cerita Singkat')
                            ->rows(4)
                            ->columnSpanFull(),

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
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->disk('public')
                    ->label('Foto'),

                Tables\Columns\TextColumn::make('date')
                    ->label('Momen')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('title')
                    ->label('Judul Cerita')
                    ->searchable()
                    ->weight('bold'),

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

<?php

namespace App\Filament\Resources\InvitationResource\RelationManagers;

use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class MediaRelationManager extends RelationManager
{
    protected static string $relationship = 'media';

    protected static ?string $title = 'Galeri Foto & Media';

    protected static string|BackedEnum|null $icon = 'heroicon-o-photo';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Upload Media Galeri')
                    ->schema([
                        Select::make('type')
                            ->label('Tipe Media')
                            ->options([
                                'image' => 'Foto Galeri',
                                'banner' => 'Foto Cover / Header Utama',
                            ])
                            ->default('image')
                            ->required(),

                        FileUpload::make('path')
                            ->disk('public')
                            ->label('File Foto / Media')
                            ->directory('invitations/media')
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
                            ->maxSize(5120)
                            ->required()
                            ->columnSpanFull(),

                        TextInput::make('alt_text')
                            ->label('Deskripsi Singkat (Alt Text)')
                            ->placeholder('Foto Prewedding 1'),

                        TextInput::make('caption')
                            ->label('Keterangan / Caption Foto')
                            ->placeholder('Foto diambil di Bali, 2024'),

                        TextInput::make('position')
                            ->label('Urutan Galeri')
                            ->numeric()
                            ->default(0),
                    ])->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('alt_text')
            ->columns([
                Tables\Columns\ImageColumn::make('path')
                    ->disk('public')
                    ->label('Pratinjau Foto'),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'banner' => 'success',
                        default => 'info',
                    }),

                Tables\Columns\TextColumn::make('caption')
                    ->label('Caption')
                    ->placeholder('-'),

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

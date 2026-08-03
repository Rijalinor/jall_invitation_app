<?php

namespace App\Filament\Resources\InvitationResource\RelationManagers;

use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class HostsRelationManager extends RelationManager
{
    protected static string $relationship = 'hosts';

    protected static ?string $title = 'Mempelai / Tuan Rumah';

    protected static string|BackedEnum|null $icon = 'heroicon-o-user-group';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Tuan Rumah / Mempelai')
                    ->schema([
                        Select::make('role')
                            ->label('Peran / Posisi')
                            ->options([
                                'groom' => 'Mempelai Pria (Groom)',
                                'bride' => 'Mempelai Wanita (Bride)',
                                'host' => 'Tuan Rumah / Penyelenggara Utama',
                                'co_host' => 'Pendamping Tuan Rumah',
                            ])
                            ->required(),

                        TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('nickname')
                            ->label('Nama Panggilan')
                            ->maxLength(100),

                        TextInput::make('birth_order')
                            ->label('Urutan Kelahiran')
                            ->placeholder('Putra Pertama dari...')
                            ->maxLength(100),

                        TextInput::make('parent_father')
                            ->label('Nama Ayah')
                            ->maxLength(255),

                        TextInput::make('parent_mother')
                            ->label('Nama Ibu')
                            ->maxLength(255),

                        FileUpload::make('photo_path')
                            ->label('Foto Profil')
                            ->disk('public')
                            ->directory('invitations/hosts')
                            ->image()
                            ->imageEditor()
                            ->imageEditorViewportWidth(400)
                            ->imageEditorViewportHeight(400)
                            ->imageEditorAspectRatios(['1:1'])
                            ->imageResizeMode('cover')
                            ->imageResizeTargetWidth('800')
                            ->imageResizeTargetHeight('800')
                            ->imageResizeUpscale(false)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(3072)
                            ->columnSpanFull(),

                        Textarea::make('bio')
                            ->label('Biodata / Profil Singkat')
                            ->rows(3)
                            ->columnSpanFull(),

                        TextInput::make('social_instagram')
                            ->label('Username Instagram')
                            ->placeholder('@username')
                            ->prefix('@'),

                        TextInput::make('social_tiktok')
                            ->label('Username TikTok')
                            ->placeholder('@username')
                            ->prefix('@'),

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
                Tables\Columns\ImageColumn::make('photo_path')
                    ->label('Foto')
                    ->disk('public')
                    ->circular(),

                Tables\Columns\TextColumn::make('role')
                    ->label('Peran')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'groom' => 'Mempelai Pria',
                        'bride' => 'Mempelai Wanita',
                        'host' => 'Tuan Rumah',
                        'co_host' => 'Pendamping',
                        default => $state
                    })
                    ->color(fn ($state) => match ($state) {
                        'groom' => 'info',
                        'bride' => 'danger',
                        'host' => 'success',
                        default => 'gray'
                    }),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Lengkap')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('nickname')
                    ->label('Panggilan')
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

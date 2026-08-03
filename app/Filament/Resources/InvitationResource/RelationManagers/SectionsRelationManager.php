<?php

namespace App\Filament\Resources\InvitationResource\RelationManagers;

use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class SectionsRelationManager extends RelationManager
{
    protected static string $relationship = 'sections';

    protected static ?string $title = 'Pengaturan Section Tampilan';

    protected static string|BackedEnum|null $icon = 'heroicon-o-queue-list';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                SchemaSection::make('Konfigurasi Section Undangan')
                    ->schema([
                        Select::make('key')
                            ->label('Identifikasi Section')
                            ->options([
                                'opening' => 'Pembuka / Cover',
                                'hosts' => 'Mempelai / Host',
                                'events' => 'Rangkaian Acara',
                                'countdown' => 'Hitung Mundur (Countdown)',
                                'calendar' => 'Simpan Kalender (Add to Calendar)',
                                'map' => 'Peta Lokasi (Google Maps)',
                                'story' => 'Kisah Cinta (Love Story)',
                                'gallery' => 'Galeri Foto & Video',
                                'rsvp' => 'Form Konfirmasi Kehadiran (RSVP)',
                                'guestbook' => 'Buku Tamu & Ucapan',
                                'gifts' => 'Amplop Digital & Kado',
                                'contacts' => 'Kontak Panitia / WO',
                                'livestream' => 'Live Streaming',
                                'sharing' => 'Tombol Bagikan WA',
                                'closing' => 'Penutup Undangan',
                            ])
                            ->required()
                            ->disabledOn('edit'),

                        Toggle::make('enabled')
                            ->label('Status Aktif Tampil di Undangan')
                            ->default(true),

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
            ->recordTitleAttribute('key')
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->label('Nama Section')
                    ->weight('bold')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'opening' => 'Pembuka / Cover',
                        'hosts' => 'Mempelai / Host',
                        'events' => 'Rangkaian Acara',
                        'countdown' => 'Hitung Mundur (Countdown)',
                        'calendar' => 'Simpan Kalender',
                        'map' => 'Peta Lokasi',
                        'story' => 'Kisah Cinta',
                        'gallery' => 'Galeri Foto',
                        'rsvp' => 'Form RSVP',
                        'guestbook' => 'Buku Tamu & Ucapan',
                        'gifts' => 'Amplop Digital',
                        'contacts' => 'Kontak Panitia',
                        'livestream' => 'Live Streaming',
                        'sharing' => 'Bagikan WA',
                        'closing' => 'Penutup Undangan',
                        default => $state
                    }),

                Tables\Columns\IconColumn::make('enabled')
                    ->label('Status Tampil')
                    ->boolean(),

                Tables\Columns\TextColumn::make('position')
                    ->label('Urutan Tampilan')
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

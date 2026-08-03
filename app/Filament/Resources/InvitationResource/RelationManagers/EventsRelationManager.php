<?php

namespace App\Filament\Resources\InvitationResource\RelationManagers;

use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class EventsRelationManager extends RelationManager
{
    protected static string $relationship = 'events';

    protected static ?string $title = 'Rangkaian Acara';

    protected static string|BackedEnum|null $icon = 'heroicon-o-calendar-days';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Acara & Lokasi')
                    ->schema([
                        TextInput::make('label')
                            ->label('Nama / Label Acara')
                            ->placeholder('Akad Nikah / Resepsi')
                            ->required()
                            ->maxLength(255),

                        DatePicker::make('date')
                            ->label('Tanggal Acara')
                            ->required(),

                        TimePicker::make('start_time')
                            ->label('Waktu Mulai')
                            ->seconds(false),

                        TimePicker::make('end_time')
                            ->label('Waktu Selesai')
                            ->seconds(false),

                        Select::make('timezone')
                            ->label('Zona Waktu')
                            ->options([
                                'Asia/Jakarta' => 'WIB (Asia/Jakarta)',
                                'Asia/Makassar' => 'WITA (Asia/Makassar)',
                                'Asia/Jayapura' => 'WIT (Asia/Jayapura)',
                            ])
                            ->default('Asia/Jakarta')
                            ->required(),

                        TextInput::make('venue_name')
                            ->label('Nama Tempat / Lokasi')
                            ->placeholder('Gedung / Masjid / Kediaman Mempelai')
                            ->maxLength(255),

                        Textarea::make('address')
                            ->label('Alamat Lengkap')
                            ->rows(3)
                            ->columnSpanFull(),

                        TextInput::make('map_url')
                            ->label('Link Google Maps')
                            ->placeholder('https://maps.google.com/?q=...')
                            ->url()
                            ->columnSpanFull(),

                        TextInput::make('latitude')
                            ->label('Latitude')
                            ->numeric()
                            ->minValue(-90)
                            ->maxValue(90),

                        TextInput::make('longitude')
                            ->label('Longitude')
                            ->numeric()
                            ->minValue(-180)
                            ->maxValue(180),

                        TextInput::make('dress_code')
                            ->label('Dress Code / Pakaian')
                            ->placeholder('Batik / Formal White')
                            ->maxLength(255),

                        Toggle::make('is_primary')
                            ->label('Acara Utama (Hitung Mundur Countdown)')
                            ->default(false),

                        Textarea::make('parking_notes')
                            ->label('Petunjuk Parkir')
                            ->rows(2),

                        Textarea::make('landmark_notes')
                            ->label('Patokan Lokasi')
                            ->rows(2),

                        Textarea::make('entrance_notes')
                            ->label('Petunjuk Pintu Masuk')
                            ->rows(2),

                        TextInput::make('position')
                            ->label('Urutan')
                            ->numeric()
                            ->default(0),
                    ])->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('label')
            ->columns([
                Tables\Columns\TextColumn::make('label')
                    ->label('Nama Acara')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('start_time')
                    ->label('Waktu')
                    ->formatStateUsing(fn ($record) => ($record->start_time ? substr($record->start_time, 0, 5) : 'Selesai').($record->end_time ? ' - '.substr($record->end_time, 0, 5) : ' s/d Selesai')),

                Tables\Columns\TextColumn::make('venue_name')
                    ->label('Lokasi')
                    ->placeholder('-'),

                Tables\Columns\IconColumn::make('is_primary')
                    ->label('Utama')
                    ->boolean(),

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

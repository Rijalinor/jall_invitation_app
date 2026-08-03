<?php

namespace App\Filament\Resources\InvitationResource\RelationManagers;

use App\Enums\GiftMethodType;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class GiftMethodsRelationManager extends RelationManager
{
    protected static string $relationship = 'giftMethods';

    protected static ?string $title = 'Amplop Digital / Hadiah';

    protected static string|BackedEnum|null $icon = 'heroicon-o-gift';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Hadiah / Rekening Digital')
                    ->schema([
                        Select::make('type')
                            ->label('Tipe Hadiah')
                            ->options(collect(GiftMethodType::cases())->mapWithKeys(fn (GiftMethodType $type) => [$type->value => $type->label()]))
                            ->default(GiftMethodType::BANK_TRANSFER->value)
                            ->required(),

                        TextInput::make('provider')
                            ->label('Nama Bank / E-Wallet / Kurir')
                            ->placeholder('BCA / Mandiri / GoPay / OVO / JNE')
                            ->required()
                            ->maxLength(100),

                        TextInput::make('account_name')
                            ->label('Atas Nama (A/N)')
                            ->placeholder('Budi Santoso')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('account_number')
                            ->label('Nomor Rekening / Nomor E-Wallet')
                            ->placeholder('1234567890')
                            ->maxLength(255),

                        Textarea::make('delivery_address')
                            ->label('Alamat Pengiriman Hadiah Fisik')
                            ->rows(3)
                            ->columnSpanFull(),

                        Textarea::make('notes')
                            ->label('Catatan Tambahan')
                            ->placeholder('Mohon sertakan nama saat konfirmasi transfer...')
                            ->rows(2)
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
            ->recordTitleAttribute('provider')
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof GiftMethodType ? $state->label() : GiftMethodType::tryFrom($state)?->label() ?? $state),

                Tables\Columns\TextColumn::make('provider')
                    ->label('Bank / E-Wallet')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('account_number')
                    ->label('No. Rekening')
                    ->copyable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('account_name')
                    ->label('Atas Nama')
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

<?php

namespace App\Filament\Resources\InvitationResource\RelationManagers;

use App\Enums\BlockType;
use App\Services\TemplateRegistry;
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
use Illuminate\Database\Eloquent\Model;

class BlocksRelationManager extends RelationManager
{
    protected static string $relationship = 'blocks';

    protected static ?string $title = 'Seksi Tambahan (Blok Bebas)';

    protected static string|BackedEnum|null $icon = 'heroicon-o-squares-plus';

    /**
     * Only templates that render blocks should offer this tab. Otherwise an
     * operator could fill it in for a template that ignores it and never see the
     * result, which is worse than not offering it at all.
     */
    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        $manifest = app(TemplateRegistry::class)->find((string) $ownerRecord->template_id);

        return in_array('blocks', $manifest['sections'] ?? [], true);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Isi Elemen')
                    ->description('Susun dari atas ke bawah. "Judul Seksi Baru" membuka seksi tersendiri, dan elemen di bawahnya masuk ke seksi itu.')
                    ->schema([
                        Select::make('type')
                            ->label('Jenis Elemen')
                            ->options(collect(BlockType::cases())->mapWithKeys(fn (BlockType $type) => [$type->value => $type->label()]))
                            ->required()
                            ->live(),

                        TextInput::make('position')
                            ->label('Urutan Tampilan')
                            ->numeric()
                            ->default(0),

                        TextInput::make('content_json.title')
                            ->label(fn ($get) => $get('type') === BlockType::SECTION->value ? 'Judul Seksi' : 'Sub-judul (opsional)')
                            ->maxLength(120)
                            ->required(fn ($get) => $get('type') === BlockType::SECTION->value)
                            ->visible(fn ($get) => in_array($get('type'), [BlockType::SECTION->value, BlockType::TEXT->value], true))
                            ->columnSpanFull(),

                        Textarea::make('content_json.body')
                            ->label(fn ($get) => $get('type') === BlockType::NOTE->value ? 'Isi Catatan' : 'Isi Teks')
                            ->rows(5)
                            ->required(fn ($get) => in_array($get('type'), [BlockType::TEXT->value, BlockType::NOTE->value], true))
                            ->visible(fn ($get) => in_array($get('type'), [BlockType::TEXT->value, BlockType::NOTE->value], true))
                            ->helperText('Baris baru yang kamu ketik akan tampil sebagai baris baru di undangan.')
                            ->columnSpanFull(),

                        Textarea::make('content_json.quote')
                            ->label('Isi Kutipan')
                            ->rows(3)
                            ->required(fn ($get) => $get('type') === BlockType::QUOTE->value)
                            ->visible(fn ($get) => $get('type') === BlockType::QUOTE->value)
                            ->columnSpanFull(),

                        TextInput::make('content_json.source')
                            ->label('Sumber / Atas Nama (opsional)')
                            ->maxLength(120)
                            ->visible(fn ($get) => $get('type') === BlockType::QUOTE->value),

                        FileUpload::make('content_json.path')
                            ->label('Foto')
                            ->disk('public')
                            ->directory('invitations/blocks')
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(3072)
                            ->required(fn ($get) => $get('type') === BlockType::IMAGE->value)
                            ->visible(fn ($get) => $get('type') === BlockType::IMAGE->value)
                            ->columnSpanFull(),

                        TextInput::make('content_json.caption')
                            ->label('Keterangan Foto (opsional)')
                            ->maxLength(160)
                            ->visible(fn ($get) => $get('type') === BlockType::IMAGE->value)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('type')
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn ($state) => ($state instanceof BlockType ? $state : BlockType::from($state))->label()),

                Tables\Columns\TextColumn::make('content_json')
                    ->label('Isi')
                    ->placeholder('—')
                    ->getStateUsing(fn ($record) => collect(is_array($record->content_json) ? $record->content_json : [])
                        ->except('path')
                        ->first(fn ($value) => is_string($value) && trim($value) !== ''))
                    ->limit(60),

                Tables\Columns\TextColumn::make('position')
                    ->label('Urutan')
                    ->sortable(),
            ])
            ->defaultSort('position', 'asc')
            ->reorderable('position')
            ->headerActions([
                Actions\CreateAction::make()->label('Tambah Elemen'),
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

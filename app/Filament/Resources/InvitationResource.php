<?php

namespace App\Filament\Resources;

use App\Enums\EventType;
use App\Enums\InvitationStatus;
use App\Filament\Resources\InvitationResource\Pages;
use App\Models\Invitation;
use App\Services\TemplateRegistry;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class InvitationResource extends Resource
{
    protected static ?string $model = Invitation::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-duplicate';

    protected static string|UnitEnum|null $navigationGroup = 'Manajemen Utama';

    protected static ?string $modelLabel = 'Undangan';

    protected static ?string $pluralModelLabel = 'Daftar Undangan';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        /** @var TemplateRegistry $templateRegistry */
        $templateRegistry = app(TemplateRegistry::class);

        return $schema
            ->components([
                Group::make()
                    ->schema([
                        Section::make('Informasi Dasar Undangan')
                            ->schema([
                                Select::make('customer_id')
                                    ->label('Pelanggan Pemesan')
                                    ->relationship('customer', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->createOptionForm([
                                        TextInput::make('name')
                                            ->label('Nama Lengkap')
                                            ->required(),
                                        TextInput::make('phone')
                                            ->label('Nomor WhatsApp')
                                            ->tel(),
                                        TextInput::make('email')
                                            ->email(),
                                    ]),

                                TextInput::make('title')
                                    ->label('Judul Undangan')
                                    ->placeholder('Pernikahan Budi & Ani')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($set, ?string $state) => $set('slug', Str::slug($state ?? ''))),

                                TextInput::make('slug')
                                    ->label('URL Slug')
                                    ->placeholder('budi-ani-wedding')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->prefix(url('/').'/')
                                    ->alphaDash(),

                                Select::make('event_type')
                                    ->label('Jenis Acara')
                                    ->options(collect(EventType::cases())->mapWithKeys(fn (EventType $type) => [$type->value => $type->label()]))
                                    ->default(EventType::WEDDING->value)
                                    ->required(),

                                Select::make('template_id')
                                    ->label('Pilihan Template')
                                    ->options($templateRegistry->getOptions())
                                    ->required()
                                    ->helperText('Dapat diganti kapan saja tanpa kehilangan data undangan.'),

                                View::make('filament.forms.template-gallery')
                                    ->columnSpanFull(),
                            ])->columns(2),

                        Section::make('Pengaturan Teks & Pesan')
                            ->schema([
                                Textarea::make('opening_text')
                                    ->label('Teks Pembuka / Kalimat Mutiara')
                                    ->placeholder('Dengan memohon rahmat dan ridho Allah SWT...')
                                    ->rows(3),

                                Textarea::make('closing_message')
                                    ->label('Pesan Penutup')
                                    ->placeholder('Merupakan suatu kehormatan dan kebahagiaan bagi kami...')
                                    ->rows(3),

                                Textarea::make('share_message')
                                    ->label('Template Pesan WhatsApp Share')
                                    ->placeholder("Kepada Yth. Bapak/Ibu/Saudara/i [nama],\nKami mengundang Anda untuk menghadiri acara kami...")
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ])->columns(2),

                        Section::make('Tema Template')
                            ->schema([
                                ColorPicker::make('settings_json.accent_color')
                                    ->label('Warna Aksen')
                                    ->default('#7b2639')
                                    ->regex('/^#[0-9a-f]{6}$/i'),
                                Select::make('settings_json.motion')
                                    ->label('Intensitas Gerak')
                                    ->options(['calm' => 'Tenang', 'expressive' => 'Ekspresif', 'off' => 'Tanpa Animasi'])
                                    ->default('calm')
                                    ->required(),
                            ])->columns(2),
                    ])->columnSpan(['lg' => 2]),

                Group::make()
                    ->schema([
                        Section::make('Status & Publikasi')
                            ->schema([
                                Select::make('status')
                                    ->label('Status Lifecycle')
                                    ->options(collect(InvitationStatus::cases())->mapWithKeys(fn (InvitationStatus $status) => [$status->value => $status->label()]))
                                    ->default(InvitationStatus::DRAFT->value)
                                    ->required(),

                                DateTimePicker::make('published_at')
                                    ->label('Tanggal Published'),

                                DateTimePicker::make('expires_at')
                                    ->label('Tanggal Kadaluarsa')
                                    ->helperText('Kosongkan jika tidak ada batas waktu.'),
                            ]),

                        Section::make('Media & Fitur Tambahan')
                            ->schema([
                                FileUpload::make('music_path')
                                    ->label('Musik Latar (.mp3)')
                                    ->disk('public')
                                    ->directory('invitations/music')
                                    ->acceptedFileTypes(['audio/mpeg', 'audio/mp3', 'audio/ogg', 'audio/m4a'])
                                    ->maxSize(10240),

                                Toggle::make('music_autoplay')
                                    ->label('Autoplay setelah buka cover')
                                    ->default(true),

                                TextInput::make('livestream_url')
                                    ->label('URL Live Streaming (opsional)')
                                    ->placeholder('https://youtube.com/live/...'),

                                TextInput::make('livestream_label')
                                    ->label('Label Tombol Livestream')
                                    ->placeholder('Saksikan via YouTube'),
                            ]),
                    ])->columnSpan(['lg' => 1]),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul Undangan')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (Invitation $record): string => url('/'.$record->slug)),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Pelanggan')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('event_type')
                    ->label('Jenis Acara')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof EventType ? $state->label() : EventType::tryFrom($state)?->label() ?? $state),

                Tables\Columns\TextColumn::make('template_id')
                    ->label('Template')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => match ($state instanceof InvitationStatus ? $state->value : $state) {
                        'draft' => 'gray',
                        'preview' => 'warning',
                        'published' => 'success',
                        'expired' => 'danger',
                        'archived' => 'secondary',
                        default => 'gray'
                    })
                    ->formatStateUsing(fn ($state) => $state instanceof InvitationStatus ? $state->label() : InvitationStatus::tryFrom($state)?->label() ?? $state),

                Tables\Columns\TextColumn::make('published_at')
                    ->label('Dipublikasi')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(collect(InvitationStatus::cases())->mapWithKeys(fn (InvitationStatus $status) => [$status->value => $status->label()])),

                Tables\Filters\SelectFilter::make('event_type')
                    ->label('Jenis Acara')
                    ->options(collect(EventType::cases())->mapWithKeys(fn (EventType $type) => [$type->value => $type->label()])),
            ])
            ->actions([
                Actions\ActionGroup::make([
                    Actions\EditAction::make(),
                    Actions\Action::make('publish')
                        ->label('Publish Undangan')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn (Invitation $record) => $record->status !== InvitationStatus::PUBLISHED)
                        ->action(function (Invitation $record) {
                            if (! static::ensureReadyForPreview($record)) {
                                return;
                            }

                            $record->update([
                                'status' => InvitationStatus::PUBLISHED->value,
                                'published_at' => $record->published_at ?? now(),
                            ]);
                            Notification::make()
                                ->title('Undangan Berhasil Dipublikasikan')
                                ->success()
                                ->send();
                        }),

                    Actions\Action::make('setPreview')
                        ->label('Set ke Preview')
                        ->icon('heroicon-o-eye')
                        ->color('warning')
                        ->visible(fn (Invitation $record) => $record->status !== InvitationStatus::PREVIEW)
                        ->action(function (Invitation $record) {
                            if (! static::ensureReadyForPreview($record)) {
                                return;
                            }

                            $record->update(['status' => InvitationStatus::PREVIEW->value]);
                            Notification::make()
                                ->title('Status diubah ke Preview')
                                ->warning()
                                ->send();
                        }),

                    Actions\Action::make('setDraft')
                        ->label('Kembalikan ke Draft')
                        ->icon('heroicon-o-arrow-path')
                        ->color('gray')
                        ->visible(fn (Invitation $record) => $record->status !== InvitationStatus::DRAFT)
                        ->action(function (Invitation $record) {
                            $record->update(['status' => InvitationStatus::DRAFT->value]);
                            Notification::make()
                                ->title('Status dikembalikan ke Draft')
                                ->info()
                                ->send();
                        }),

                    Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function ensureReadyForPreview(Invitation $invitation): bool
    {
        $missing = $invitation->missingPreviewRequirements();

        if ($missing === []) {
            return true;
        }

        Notification::make()
            ->title('Konten undangan belum lengkap')
            ->body('Lengkapi '.implode(' dan ', $missing).' sebelum melanjutkan.')
            ->danger()
            ->send();

        return false;
    }

    public static function getRelations(): array
    {
        return [
            InvitationResource\RelationManagers\HostsRelationManager::class,
            InvitationResource\RelationManagers\EventsRelationManager::class,
            InvitationResource\RelationManagers\StoriesRelationManager::class,
            InvitationResource\RelationManagers\MediaRelationManager::class,
            InvitationResource\RelationManagers\GiftMethodsRelationManager::class,
            InvitationResource\RelationManagers\ContactsRelationManager::class,
            InvitationResource\RelationManagers\SectionsRelationManager::class,
            InvitationResource\RelationManagers\GuestsRelationManager::class,
            InvitationResource\RelationManagers\RsvpsRelationManager::class,
            InvitationResource\RelationManagers\GuestbookEntriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvitations::route('/'),
            'create' => Pages\CreateInvitation::route('/create'),
            'edit' => Pages\EditInvitation::route('/{record}/edit'),
        ];
    }
}

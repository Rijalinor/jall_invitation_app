<?php

namespace App\Filament\Resources;

use App\Enums\EventType;
use App\Enums\InvitationStatus;
use App\Filament\Resources\InvitationResource\Pages;
use App\Models\Invitation;
use App\Services\FormLinks;
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
use Filament\Schemas\Components\Fieldset;
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

        // The panel used to offer every setting to every template, so an operator
        // could fill in a field the chosen template silently ignores. Show only
        // what the selected template declares, the same way section labels work.
        $hasSetting = fn ($get, string ...$keys): bool => array_intersect(
            $keys,
            array_keys((array) ($templateRegistry->find((string) $get('template_id'))['settings_schema'] ?? [])),
        ) !== [];

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
                                    ->live()
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

                        Section::make('Pengaturan Visual')
                            ->description('Hanya pengaturan yang didukung template terpilih yang ditampilkan. Kosongkan nilai yang ingin mengikuti bawaan dari template.')
                            ->schema([
                                ColorPicker::make('settings_json.accent_color')
                                    ->label('Warna Aksen (opsional)')
                                    ->regex('/^#[0-9a-f]{6}$/i'),
                                Select::make('settings_json.motion')
                                    ->label('Intensitas Gerak (opsional)')
                                    ->options(['calm' => 'Tenang', 'expressive' => 'Ekspresif', 'off' => 'Tanpa Animasi'])
                                    ->placeholder('Ikuti bawaan template'),
                                Select::make('settings_json.cover_video_enabled')
                                    ->label('Video cover')
                                    ->options(['true' => 'Aktif', 'false' => 'Nonaktif'])
                                    ->placeholder('Ikuti bawaan template')
                                    ->helperText('Video autoplay akan dimute, loop, dan memakai poster/foto sebagai fallback.')
                                    ->visible(fn ($get) => $hasSetting($get, 'cover_video_enabled')),
                                Select::make('settings_json.opening_video_enabled')
                                    ->label('Video di seksi pembuka (opsional)')
                                    ->options(['true' => 'Aktif', 'false' => 'Nonaktif'])
                                    ->placeholder('Ikuti bawaan template')
                                    ->helperText('Memakai video yang sama seperti sampul, hanya dipasang sebagai latar seksi pembuka.')
                                    ->visible(fn ($get) => $hasSetting($get, 'opening_video_enabled')),
                                FileUpload::make('settings_json.cover_video_desktop')
                                    ->label('Video Cover')
                                    ->helperText('Cukup satu video di sini. Video ini juga dipakai di HP selama kolom versi HP dibiarkan kosong.')
                                    ->imagePreviewHeight('200')
                                    ->disk('public')
                                    ->directory('invitations/cover-videos')
                                    ->acceptedFileTypes(['video/mp4', 'video/webm'])
                                    ->maxSize(51200)
                                    ->visible(fn ($get) => $hasSetting($get, 'cover_video_desktop')),

                                Fieldset::make('Pengaturan lanjutan (jarang diubah)')
                                    ->visible(fn ($get) => $hasSetting($get, 'cover_video_mobile', 'cover_poster_image', 'cover_focal_x', 'cover_focal_y', 'cover_overlay_opacity', 'cover_text_position'))
                                    ->schema([
                                        FileUpload::make('settings_json.cover_video_mobile')
                                            ->label('Video Cover versi HP (opsional)')
                                            ->helperText('Hanya bila ingin versi lebih ringan untuk HP. Kosongkan untuk memakai video yang sama seperti di atas.')
                                            ->imagePreviewHeight('200')
                                            ->disk('public')
                                            ->directory('invitations/cover-videos')
                                            ->acceptedFileTypes(['video/mp4', 'video/webm'])
                                            ->maxSize(51200)
                                            ->visible(fn ($get) => $hasSetting($get, 'cover_video_mobile')),
                                        FileUpload::make('settings_json.cover_poster_image')
                                            ->label('Poster / fallback cover')
                                            ->helperText('Dipakai sebelum video termuat. Kosongkan untuk memakai foto galeri/mempelai pertama.')
                                            ->imagePreviewHeight('240')
                                            ->disk('public')
                                            ->directory('invitations/cover-posters')
                                            ->image()
                                            ->maxSize(8192)
                                            ->visible(fn ($get) => $hasSetting($get, 'cover_poster_image'))
                                            ->columnSpanFull(),
                                        TextInput::make('settings_json.cover_focal_x')
                                            ->label('Focal point horizontal')
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(100)
                                            ->suffix('%')
                                            ->visible(fn ($get) => $hasSetting($get, 'cover_focal_x')),
                                        TextInput::make('settings_json.cover_focal_y')
                                            ->label('Focal point vertikal')
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(100)
                                            ->suffix('%')
                                            ->visible(fn ($get) => $hasSetting($get, 'cover_focal_y')),
                                        TextInput::make('settings_json.cover_overlay_opacity')
                                            ->label('Gelap overlay')
                                            ->numeric()
                                            ->minValue(30)
                                            ->maxValue(78)
                                            ->suffix('%')
                                            ->visible(fn ($get) => $hasSetting($get, 'cover_overlay_opacity')),
                                        Select::make('settings_json.cover_text_position')
                                            ->label('Posisi teks cover')
                                            ->options(['left' => 'Kiri', 'center' => 'Tengah', 'right' => 'Kanan'])
                                            ->placeholder('Ikuti bawaan template')
                                            ->visible(fn ($get) => $hasSetting($get, 'cover_text_position')),
                                    ])
                                    ->columns(2)
                                    ->columnSpanFull(),
                            ])->columns(2),

                        Section::make('Tulisan Seksi (opsional)')
                            ->description('Mengganti tulisan bawaan template untuk undangan ini saja; undangan lain tidak terpengaruh. Kosongkan bila ingin memakai bawaan.')
                            ->visible(fn ($get) => $templateRegistry->labels((string) $get('template_id')) !== [])
                            ->collapsible()
                            ->collapsed()
                            ->schema([
                                TextInput::make('settings_json.labels.cover_eyebrow')
                                    ->label('Sampul — tulisan kecil')
                                    ->placeholder('Undangan')
                                    ->maxLength(120),

                                TextInput::make('settings_json.labels.cover_recipient_label')
                                    ->label('Sampul — tulisan "Kepada Yth."')
                                    ->placeholder('Kepada Yth.')
                                    ->maxLength(120),

                                TextInput::make('settings_json.labels.cover_countdown_label')
                                    ->label('Sampul — tulisan di atas hitung mundur')
                                    ->placeholder('Menuju acara')
                                    ->maxLength(120),

                                TextInput::make('settings_json.labels.cover_cta')
                                    ->label('Sampul — tombol buka undangan')
                                    ->placeholder('Buka Undangan')
                                    ->maxLength(120),

                                TextInput::make('settings_json.labels.opening_eyebrow')
                                    ->label('Pembuka — tulisan kecil')
                                    ->placeholder('Dengan penuh kebahagiaan')
                                    ->maxLength(120),

                                TextInput::make('settings_json.labels.hosts_eyebrow')
                                    ->label('Mempelai — tulisan kecil')
                                    ->placeholder('Yang Berbahagia')
                                    ->maxLength(120),

                                TextInput::make('settings_json.labels.hosts_title')
                                    ->label('Mempelai — judul seksi')
                                    ->placeholder('Mempelai & Keluarga')
                                    ->maxLength(120),

                                TextInput::make('settings_json.labels.events_eyebrow')
                                    ->label('Acara — tulisan kecil')
                                    ->placeholder('Save the Date')
                                    ->maxLength(120),

                                TextInput::make('settings_json.labels.events_title')
                                    ->label('Acara — judul seksi')
                                    ->placeholder('Rangkaian Acara')
                                    ->maxLength(120),

                                TextInput::make('settings_json.labels.countdown_eyebrow')
                                    ->label('Hitung mundur — tulisan kecil')
                                    ->placeholder('Menuju Hari Bahagia')
                                    ->maxLength(120),

                                TextInput::make('settings_json.labels.map_eyebrow')
                                    ->label('Lokasi — tulisan kecil')
                                    ->placeholder('Lokasi Acara')
                                    ->maxLength(120),

                                TextInput::make('settings_json.labels.map_title')
                                    ->label('Lokasi — judul seksi')
                                    ->placeholder('Petunjuk Lokasi')
                                    ->maxLength(120),

                                TextInput::make('settings_json.labels.story_eyebrow')
                                    ->label('Cerita — tulisan kecil')
                                    ->placeholder('Jejak Cerita')
                                    ->maxLength(120),

                                TextInput::make('settings_json.labels.story_title')
                                    ->label('Cerita — judul seksi')
                                    ->placeholder('Kisah Kami')
                                    ->maxLength(120),

                                TextInput::make('settings_json.labels.gallery_eyebrow')
                                    ->label('Galeri — tulisan kecil')
                                    ->placeholder('Galeri')
                                    ->maxLength(120),

                                TextInput::make('settings_json.labels.gallery_title')
                                    ->label('Galeri — judul seksi')
                                    ->placeholder('Momen Pilihan')
                                    ->maxLength(120),

                                TextInput::make('settings_json.labels.gifts_eyebrow')
                                    ->label('Hadiah — tulisan kecil')
                                    ->placeholder('Tanda Kasih')
                                    ->maxLength(120),

                                TextInput::make('settings_json.labels.gifts_title')
                                    ->label('Hadiah — judul seksi')
                                    ->placeholder('Hadiah Digital')
                                    ->maxLength(120),

                                Textarea::make('settings_json.labels.gifts_intro')
                                    ->label('Hadiah — paragraf pembuka')
                                    ->placeholder('Doa dan kehadiran Anda adalah hadiah terindah. Detail berikut tersedia bila Anda ingin mengirim tanda kasih.')
                                    ->rows(3)
                                    ->maxLength(400)
                                    ->helperText('Baris baru yang kamu ketik tampil sebagai baris baru.')
                                    ->columnSpanFull(),

                                Textarea::make('settings_json.labels.rsvp_intro')
                                    ->label('RSVP — paragraf pembuka')
                                    ->placeholder('Mohon berikan konfirmasi kehadiran Anda.')
                                    ->rows(2)
                                    ->maxLength(400)
                                    ->columnSpanFull(),

                                TextInput::make('settings_json.labels.contacts_eyebrow')
                                    ->label('Kontak — tulisan kecil')
                                    ->placeholder('Hubungi Kami')
                                    ->maxLength(120),

                                TextInput::make('settings_json.labels.contacts_title')
                                    ->label('Kontak — judul seksi')
                                    ->placeholder('Kontak')
                                    ->maxLength(120),

                                Textarea::make('settings_json.labels.contacts_intro')
                                    ->label('Kontak — paragraf pembuka')
                                    ->placeholder('Jika membutuhkan informasi lebih lanjut, silakan hubungi kontak berikut.')
                                    ->rows(2)
                                    ->maxLength(400)
                                    ->columnSpanFull(),

                                TextInput::make('settings_json.labels.sharing_eyebrow')
                                    ->label('Bagikan — tulisan kecil')
                                    ->placeholder('Sebarkan Kabar Bahagia')
                                    ->maxLength(120),

                                TextInput::make('settings_json.labels.sharing_title')
                                    ->label('Bagikan — judul seksi')
                                    ->placeholder('Bagikan Undangan')
                                    ->maxLength(120),

                                Textarea::make('settings_json.labels.sharing_intro')
                                    ->label('Bagikan — paragraf pembuka')
                                    ->placeholder('Bagikan undangan ini kepada keluarga dan orang terdekat.')
                                    ->rows(2)
                                    ->maxLength(400)
                                    ->columnSpanFull(),

                                TextInput::make('settings_json.labels.closing_eyebrow')
                                    ->label('Penutup — tulisan kecil')
                                    ->placeholder('Terima Kasih')
                                    ->maxLength(120)
                                    ->columnSpanFull(),
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

                                Toggle::make('is_catalog_demo')
                                    ->label('Tampilkan sebagai contoh di katalog')
                                    ->helperText('Calon pelanggan bisa membuka undangan ini dari halaman katalog sebagai contoh desain. Undangan harus berstatus Published dan belum kedaluwarsa agar tombol "Lihat contoh" muncul.')
                                    ->columnSpanFull(),
                            ]),

                        Section::make('Media & Fitur Tambahan')
                            ->description('Musik latar dan live streaming. Biarkan tertutup bila tidak dipakai.')
                            ->collapsible()
                            ->collapsed()
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

                Tables\Columns\IconColumn::make('form_link')
                    ->label('Form pelanggan')
                    ->getStateUsing(fn (Invitation $record): bool => app(FormLinks::class)->isActive($record))
                    ->boolean()
                    ->trueIcon('heroicon-o-link')
                    ->falseIcon('heroicon-o-no-symbol')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->tooltip(fn (Invitation $record): string => $record->form_token_used_at
                        ? 'Terakhir dibuka '.$record->form_token_used_at->diffForHumans()
                        : 'Belum pernah dibuka pelanggan'),
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

                    Actions\Action::make('formLink')
                        ->label('Link pengisian')
                        ->icon('heroicon-o-link')
                        ->color('info')
                        ->schema([
                            Select::make('days')
                                ->label('Masa berlaku tautan')
                                ->options([7 => '7 hari', 30 => '30 hari', 90 => '90 hari'])
                                ->default(FormLinks::DEFAULT_DAYS)
                                ->required(),
                        ])
                        ->modalHeading('Buat link pengisian pelanggan')
                        ->modalDescription('Kirimkan tautan ini ke pelanggan lewat WhatsApp. Tautan yang lama otomatis tidak berlaku lagi.')
                        ->modalSubmitActionLabel('Buat tautan')
                        ->action(function (Invitation $record, array $data): void {
                            $links = app(FormLinks::class);
                            $token = $links->issue($record, (int) ($data['days'] ?? FormLinks::DEFAULT_DAYS));

                            Notification::make()
                                ->title('Tautan pengisian dibuat')
                                ->body($links->url($token))
                                ->persistent()
                                ->success()
                                ->send();
                        }),

                    Actions\Action::make('revokeFormLink')
                        ->label('Cabut link pengisian')
                        ->icon('heroicon-o-link-slash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalDescription('Pelanggan tidak akan bisa membuka form pengisian lewat tautan lama.')
                        ->visible(fn (Invitation $record): bool => app(FormLinks::class)->isActive($record))
                        ->action(function (Invitation $record): void {
                            app(FormLinks::class)->revoke($record);

                            Notification::make()
                                ->title('Tautan pengisian dicabut')
                                ->success()
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
            InvitationResource\RelationManagers\BlocksRelationManager::class,
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

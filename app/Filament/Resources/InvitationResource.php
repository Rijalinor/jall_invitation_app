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
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Js;
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

        // Captured so the preset swatches we drop under each colour field can
        // write to exactly the same form state, whatever the state path is.
        $accentPicker = ColorPicker::make('settings_json.accent_color')
            ->label('Warna Aksen (opsional)')
            ->regex('/^#[0-9a-f]{6}$/i');

        $bgColorPicker = ColorPicker::make('settings_json.bg_color')
            ->label('Warna Latar (opsional)')
            ->regex('/^#[0-9a-f]{6}$/i')
            ->visible(fn ($get) => $hasSetting($get, 'bg_color'));

        $colorPresets = fn ($get, string $key): array => $templateRegistry->colorPresets((string) $get('template_id'), $key);

        return $schema
            ->components([
                Group::make()
                    ->schema([
                        Section::make('Informasi Undangan')
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

                        Section::make('Tulisan & Pesan')
                            ->schema([
                                Textarea::make('opening_text')
                                    ->label('Teks Pembuka / Kalimat Mutiara')
                                    ->placeholder('Dengan memohon rahmat dan ridho Allah SWT...')
                                    ->rows(3),

                                Textarea::make('closing_message')
                                    ->label('Pesan Penutup')
                                    ->placeholder('Merupakan suatu kehormatan dan kebahagiaan bagi kami...')
                                    ->rows(3),

                                Repeater::make('settings_json.closing_families')
                                    ->label('Keluarga di Penutup (tampil berdampingan)')
                                    ->helperText('Tiap baris menjadi satu kolom. Dua baris tampil berdampingan, dan menumpuk di layar sempit. Kosongkan bila tidak perlu.')
                                    ->schema([
                                        TextInput::make('label')
                                            ->label('Judul')
                                            ->placeholder('Keluarga besar Mempelai Pria')
                                            ->maxLength(120),
                                        Textarea::make('names')
                                            ->label('Nama-nama')
                                            ->placeholder('Bapak Anang Asrani & Ibu Hj. Rahimah')
                                            ->rows(2)
                                            ->maxLength(400),
                                    ])
                                    ->columns(2)
                                    ->addActionLabel('Tambah keluarga')
                                    ->columnSpanFull(),

                                Textarea::make('settings_json.closing_footer')
                                    ->label('Turut Mengundang / Teks Bawah (opsional)')
                                    ->placeholder("Turut mengundang:\nKeluarga besar dari Mempelai pria dan wanita")
                                    ->rows(2)
                                    ->columnSpanFull(),

                                Textarea::make('share_message')
                                    ->label('Template Pesan WhatsApp Share')
                                    ->placeholder("Kepada Yth. Bapak/Ibu/Saudara/i [nama],\nKami mengundang Anda untuk menghadiri acara kami...")
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ])->columns(2),

                        Section::make('Tampilan')
                            ->description('Hanya pengaturan yang didukung template terpilih yang ditampilkan. Kosongkan nilai yang ingin mengikuti bawaan dari template.')
                            ->schema([
                                $accentPicker,
                                View::make('filament.forms.color-presets')
                                    ->viewData(fn ($get): array => [
                                        'presets' => $colorPresets($get, 'accent_color'),
                                        'statePath' => $accentPicker->getStatePath(),
                                    ])
                                    ->visible(fn ($get): bool => $colorPresets($get, 'accent_color') !== [])
                                    ->columnSpanFull(),
                                $bgColorPicker,
                                View::make('filament.forms.color-presets')
                                    ->viewData(fn ($get): array => [
                                        'presets' => $colorPresets($get, 'bg_color'),
                                        'statePath' => $bgColorPicker->getStatePath(),
                                    ])
                                    ->visible(fn ($get): bool => $colorPresets($get, 'bg_color') !== [])
                                    ->columnSpanFull(),
                                Select::make('settings_json.motion')
                                    ->label('Intensitas Gerak (opsional)')
                                    ->options(['calm' => 'Tenang', 'expressive' => 'Ekspresif', 'off' => 'Tanpa Animasi'])
                                    ->placeholder('Ikuti bawaan template'),
                                Select::make('settings_json.cover_video_enabled')
                                    ->label('Video cover')
                                    ->options(['true' => 'Aktif', 'false' => 'Nonaktif'])
                                    ->placeholder('Ikuti bawaan template')
                                    ->helperText('Video autoplay akan dimute, loop, dan memakai poster/foto sebagai fallback. Bila diunggah, video ini juga dipakai sebagai latar seksi pembuka.')
                                    ->visible(fn ($get) => $hasSetting($get, 'cover_video_enabled')),
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
                                Fieldset::make('Opsi Tampilan Khusus (opsional)')
                                    ->visible(fn ($get) => $hasSetting($get, 'hide_timezone', 'merge_rsvp_guestbook', 'show_rsvp_summary', 'auto_scroll'))
                                    ->schema([
                                        Toggle::make('settings_json.hide_timezone')
                                            ->label('Sembunyikan zona waktu')
                                            ->helperText('Jam acara tidak menampilkan WIB/WITA/WIT.')
                                            ->visible(fn ($get) => $hasSetting($get, 'hide_timezone')),
                                        Toggle::make('settings_json.merge_rsvp_guestbook')
                                            ->label('Gabung RSVP & Buku Ucapan')
                                            ->helperText('Satu form: nama, status kehadiran, dan catatan yang sekaligus menjadi ucapan.')
                                            ->visible(fn ($get) => $hasSetting($get, 'merge_rsvp_guestbook')),
                                        Toggle::make('settings_json.show_rsvp_summary')
                                            ->label('Tampilkan rekap kehadiran')
                                            ->helperText('Menampilkan jumlah hadir / ragu-ragu / tidak hadir di bawah form konfirmasi — tanpa nama tamu.')
                                            ->visible(fn ($get) => $hasSetting($get, 'show_rsvp_summary')),
                                        Toggle::make('settings_json.auto_scroll')
                                            ->label('Auto-scroll halus')
                                            ->helperText('Undangan bergeser pelan otomatis setelah dibuka, dan langsung berhenti begitu tamu menyentuh atau men-scroll sendiri.')
                                            ->visible(fn ($get) => $hasSetting($get, 'auto_scroll')),
                                    ])
                                    ->columns(2)
                                    ->columnSpanFull(),
                            ])->columns(2),

                        Section::make('Tulisan Seksi (opsional)')
                            ->description('Mengganti tulisan bawaan template untuk undangan ini saja; undangan lain tidak terpengaruh. Kosongkan bila ingin memakai bawaan.')
                            ->visible(fn ($get) => $templateRegistry->labels((string) $get('template_id')) !== [])
                            ->collapsible()
                            ->collapsed()
                            ->schema(function () use ($templateRegistry): array {
                                // One field per label any template declares, shown only when the
                                // chosen template declares it. A fixed list would offer wording
                                // that every other template silently ignores.
                                $names = [
                                    'cover_eyebrow' => 'Sampul — tulisan kecil',
                                    'cover_recipient_label' => 'Sampul — tulisan "Kepada Yth."',
                                    'cover_countdown_label' => 'Sampul — tulisan di atas hitung mundur',
                                    'cover_cta' => 'Sampul — tombol buka undangan',
                                    'opening_eyebrow' => 'Pembuka — tulisan kecil',
                                    'hosts_eyebrow' => 'Mempelai — tulisan kecil',
                                    'hosts_title' => 'Mempelai — judul seksi',
                                    'events_eyebrow' => 'Acara — tulisan kecil',
                                    'events_title' => 'Acara — judul seksi',
                                    'countdown_eyebrow' => 'Hitung mundur — tulisan kecil',
                                    'countdown_title' => 'Hitung mundur — judul seksi',
                                    'map_eyebrow' => 'Lokasi — tulisan kecil',
                                    'map_title' => 'Lokasi — judul seksi',
                                    'story_eyebrow' => 'Cerita — tulisan kecil',
                                    'story_title' => 'Cerita — judul seksi',
                                    'gallery_eyebrow' => 'Galeri — tulisan kecil',
                                    'gallery_title' => 'Galeri — judul seksi',
                                    'gifts_eyebrow' => 'Hadiah — tulisan kecil',
                                    'gifts_title' => 'Hadiah — judul seksi',
                                    'gifts_intro' => 'Hadiah — paragraf pembuka',
                                    'rsvp_intro' => 'RSVP — paragraf pembuka',
                                    'contacts_eyebrow' => 'Kontak — tulisan kecil',
                                    'contacts_title' => 'Kontak — judul seksi',
                                    'contacts_intro' => 'Kontak — paragraf pembuka',
                                    'sharing_eyebrow' => 'Bagikan — tulisan kecil',
                                    'sharing_title' => 'Bagikan — judul seksi',
                                    'sharing_intro' => 'Bagikan — paragraf pembuka',
                                    'closing_eyebrow' => 'Penutup — tulisan kecil',
                                    'closing_script' => 'Penutup — tulisan kecil kedua',
                                    'closing_kicker' => 'Penutup — kalimat penutup',
                                ];

                                $byTemplate = $templateRegistry->labelsByTemplate();
                                $declared = [];

                                foreach ($byTemplate as $labels) {
                                    $declared += $labels;
                                }

                                $hasLabel = fn ($get, string $key): bool => array_key_exists(
                                    $key,
                                    $byTemplate[(string) $get('template_id')] ?? [],
                                );

                                $fields = [];

                                foreach (array_keys($declared) as $key) {
                                    $name = $names[$key] ?? $key;

                                    $fields[] = str_ends_with($key, '_intro')
                                        ? Textarea::make('settings_json.labels.'.$key)
                                            ->label($name)
                                            ->rows(3)
                                            ->maxLength(400)
                                            ->helperText('Baris baru yang kamu ketik tampil sebagai baris baru.')
                                            ->visible(fn ($get) => $hasLabel($get, $key))
                                            ->columnSpanFull()
                                        : TextInput::make('settings_json.labels.'.$key)
                                            ->label($name)
                                            ->maxLength(120)
                                            ->visible(fn ($get) => $hasLabel($get, $key));
                                }

                                return $fields;
                            })->columns(2),
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
                                    ->helperText('Satu undangan contoh mewakili seluruh katalog: setiap desain menampilkan data undangan ini dengan gayanya sendiri. Cukup tandai satu undangan, pastikan berstatus Published dan belum kedaluwarsa. Bila ada beberapa, yang terbaru dipakai.')
                                    ->columnSpanFull(),
                            ]),

                        Section::make('Form Pelanggan')
                            ->description('Pengaturan untuk tautan pengisian yang dikirim ke mempelai.')
                            ->schema([
                                TextInput::make('settings_json.guest_limit')
                                    ->label('Batas maksimum tamu')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(Invitation::MAX_GUESTS)
                                    ->default(Invitation::MAX_GUESTS)
                                    ->helperText('Jumlah maksimum tamu yang boleh ditambahkan atau diimpor oleh mempelai dari form mereka. Kosongkan untuk memakai '.Invitation::MAX_GUESTS.'.')
                                    ->columnSpanFull(),
                            ]),

                        Section::make('Preview Link & Bagikan')
                            ->description('Gambar yang muncul saat link undangan dibagikan ke WhatsApp dan media sosial.')
                            ->schema([
                                Select::make('settings_json.share_image')
                                    ->label('Foto preview link (opsional)')
                                    ->helperText('Pilih dari foto undangan ini. Kosongkan untuk otomatis: poster cover → foto galeri pertama → foto mempelai pertama. Pakai JPG agar aman di WhatsApp.')
                                    ->options(function (?Invitation $record): array {
                                        if ($record === null) {
                                            return [];
                                        }

                                        $options = [];
                                        $poster = $record->settings_json['cover_poster_image'] ?? null;

                                        if (is_string($poster) && $poster !== '') {
                                            $options[$poster] = 'Poster cover';
                                        }

                                        foreach ($record->hosts as $host) {
                                            if ($host->photo_path) {
                                                $options[$host->photo_path] = 'Mempelai — '.$host->name;
                                            }
                                        }

                                        foreach ($record->media as $index => $media) {
                                            if ($media->path) {
                                                $options[$media->path] = 'Galeri '.($index + 1);
                                            }
                                        }

                                        return $options;
                                    })
                                    ->placeholder('Otomatis')
                                    ->searchable()
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
                // One click copies the public invitation URL, ready to paste into
                // WhatsApp for the customer. Copying happens in the browser, so it
                // needs no server round-trip.
                Actions\Action::make('copyLink')
                    ->label('Salin link')
                    ->icon('heroicon-o-clipboard-document')
                    ->color('gray')
                    ->tooltip('Salin link undangan untuk dikirim ke pelanggan')
                    ->alpineClickHandler(function (Invitation $record): string {
                        $link = Js::from(url('/'.$record->slug));
                        $message = Js::from('Link undangan disalin');

                        return "window.navigator.clipboard.writeText({$link}); \$tooltip({$message}, { theme: \$store.theme, timeout: 2000 })";
                    }),

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
                            $url = $links->url($token);

                            Notification::make()
                                ->title('Tautan pengisian dibuat')
                                ->body($url)
                                ->persistent()
                                ->success()
                                // The plaintext token is shown once and never stored, so
                                // the notification carries the copy control too.
                                ->actions([
                                    Actions\Action::make('copyFormLink')
                                        ->label('Salin tautan')
                                        ->alpineClickHandler('window.navigator.clipboard.writeText('.Js::from($url).'); $tooltip('.Js::from('Tautan disalin').', { theme: $store.theme, timeout: 2000 })'),
                                ])
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
        // Eleven managers in one tab bar is a lot to hunt through, so they are
        // grouped into a few themed tabs by reading flow: the couple, then the
        // stories around them, then the gifts, then how the page is built, then
        // the audience and their responses. A group hides itself when every
        // manager inside it is hidden for the record (e.g. blocks for a template
        // that does not render them).
        return [
            RelationGroup::make('Mempelai & Acara', [
                InvitationResource\RelationManagers\HostsRelationManager::class,
                InvitationResource\RelationManagers\EventsRelationManager::class,
            ])->icon('heroicon-o-user-group'),

            RelationGroup::make('Kisah & Galeri', [
                InvitationResource\RelationManagers\StoriesRelationManager::class,
                InvitationResource\RelationManagers\MediaRelationManager::class,
            ])->icon('heroicon-o-photo'),

            RelationGroup::make('Hadiah & Kontak', [
                InvitationResource\RelationManagers\GiftMethodsRelationManager::class,
                InvitationResource\RelationManagers\ContactsRelationManager::class,
            ])->icon('heroicon-o-gift'),

            RelationGroup::make('Struktur Seksi', [
                InvitationResource\RelationManagers\SectionsRelationManager::class,
                InvitationResource\RelationManagers\BlocksRelationManager::class,
            ])->icon('heroicon-o-queue-list'),

            RelationGroup::make('Tamu & Interaksi', [
                InvitationResource\RelationManagers\GuestsRelationManager::class,
                InvitationResource\RelationManagers\RsvpsRelationManager::class,
                InvitationResource\RelationManagers\GuestbookEntriesRelationManager::class,
            ])
                ->icon('heroicon-o-users')
                ->badge(fn (Invitation $record): ?string => ($count = $record->guestbookEntries()->where('moderation_status', 'pending')->count()) > 0 ? (string) $count : null)
                ->badgeColor('warning'),
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

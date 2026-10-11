<?php

namespace App\Filament\Resources\InvitationResource\RelationManagers;

use App\Models\Guest;
use App\Services\GuestCsvImporter;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class GuestsRelationManager extends RelationManager
{
    protected static string $relationship = 'guests';

    protected static ?string $title = 'Daftar Tamu';

    protected static string|BackedEnum|null $icon = 'heroicon-o-users';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('display_name')->label('Nama Tamu')->required()->helperText('Nama yang disapa di link pribadi tamu.')->maxLength(255),
            TextInput::make('group')->label('Grup')->placeholder('Keluarga / Teman / Kantor')->helperText('Untuk pengelompokan dan pencarian.')->maxLength(255),
            TextInput::make('phone')->label('Nomor WhatsApp')->tel()->helperText('Dipakai tombol "WhatsApp" saat mengirim link.')->maxLength(50),
            TextInput::make('invitation_limit')->label('Batas Undangan')->numeric()->minValue(1)->maxValue(20)->default(2)->required()->helperText('Jumlah maksimum orang untuk tamu ini.'),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        // The owner record is already loaded, so the personal link and share
        // message read from it instead of lazy-loading the invitation again for
        // every guest row.
        $invitation = $this->getOwnerRecord();

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('display_name')->label('Nama')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('group')->label('Grup')->searchable()->placeholder('-'),
                Tables\Columns\TextColumn::make('invitation_limit')->label('Kuota'),
                Tables\Columns\TextColumn::make('personal_link')->label('Link Personal')
                    ->state(fn (Guest $record) => route('invitations.guest', [$invitation->slug, $record->token]))
                    ->copyable()->limit(38),
                Tables\Columns\TextColumn::make('link_opened_at')->label('Dibuka')->dateTime('d M Y H:i')->placeholder('Belum')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                Actions\CreateAction::make(),
                Actions\Action::make('importCsv')
                    ->label('Impor CSV')->icon('heroicon-o-arrow-up-tray')
                    ->modalDescription('Gunakan header: name,group,phone,invitation_limit')
                    ->schema([
                        FileUpload::make('file')->label('File CSV')->disk('local')->directory('guest-imports')
                            ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel'])->maxSize(2048)->required(),
                    ])
                    ->action(function (array $data): void {
                        $stream = Storage::disk('local')->readStream($data['file']);
                        $count = $stream ? app(GuestCsvImporter::class)->import($this->getOwnerRecord(), $stream) : -1;

                        if ($count < 0) {
                            if (is_resource($stream)) {
                                fclose($stream);
                            }
                            Storage::disk('local')->delete($data['file']);
                            Notification::make()->title('CSV tidak memiliki kolom name')->danger()->send();

                            return;
                        }

                        fclose($stream);
                        Storage::disk('local')->delete($data['file']);
                        Notification::make()->title("{$count} tamu berhasil diimpor")->success()->send();
                    }),
            ])
            ->actions([
                Actions\Action::make('whatsapp')->label('WhatsApp')->icon('heroicon-o-paper-airplane')
                    ->url(function (Guest $record) use ($invitation): string {
                        $link = route('invitations.guest', [$invitation->slug, $record->token]);
                        $message = str_replace('[nama]', $record->display_name, $invitation->share_message ?: 'Kepada Yth. [nama], kami mengundang Anda ke acara kami.');

                        return 'https://wa.me/?text='.rawurlencode($message."\n".$link);
                    })->openUrlInNewTab(),
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([Actions\DeleteBulkAction::make()]);
    }
}

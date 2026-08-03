<?php

namespace App\Filament\Widgets;

use App\Enums\InvitationStatus;
use App\Models\Customer;
use App\Models\GuestbookEntry;
use App\Models\Invitation;
use App\Models\Rsvp;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Pelanggan', Customer::count())
                ->description('Pelanggan terdaftar')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('Total Undangan', Invitation::count())
                ->description('Semua jenis undangan')
                ->descriptionIcon('heroicon-m-document-duplicate')
                ->color('info'),

            Stat::make('Undangan Published', Invitation::where('status', InvitationStatus::PUBLISHED)->count())
                ->description('Aktif dan dapat diakses publik')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Undangan Draft / Preview', Invitation::whereIn('status', [InvitationStatus::DRAFT, InvitationStatus::PREVIEW])->count())
                ->description('Dalam proses pembuatan / revisi')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Tamu Akan Hadir', Rsvp::where('status', 'attending')->sum('party_size'))
                ->description('Total orang dari RSVP hadir')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('success'),

            Stat::make('Ucapan Menunggu', GuestbookEntry::where('moderation_status', 'pending')->count())
                ->description('Perlu dimoderasi')
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color('warning'),
        ];
    }
}

<?php

namespace App\Enums;

enum GiftMethodType: string
{
    case BANK_TRANSFER = 'bank_transfer';
    case EWALLET = 'ewallet';
    case PHYSICAL_GIFT = 'physical_gift';

    public function label(): string
    {
        return match ($this) {
            self::BANK_TRANSFER => 'Transfer Bank',
            self::EWALLET => 'E-Wallet',
            self::PHYSICAL_GIFT => 'Kirim Hadiah Fisik',
        };
    }
}

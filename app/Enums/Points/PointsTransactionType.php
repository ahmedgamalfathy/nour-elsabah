<?php

namespace App\Enums\Points;

enum PointsTransactionType: string
{
    case EARNED = 'earned';
    case REDEEMED = 'redeemed';
    case EXPIRED = 'expired';
    case REFUNDED = 'refunded';
    case ADMIN_ADJUSTMENT = 'admin_adjustment';

    public function label(): string
    {
        return match($this) {
            self::EARNED => 'نقاط مكتسبة',
            self::REDEEMED => 'نقاط مستخدمة',
            self::EXPIRED => 'نقاط منتهية',
            self::REFUNDED => 'نقاط مسترجعة',
            self::ADMIN_ADJUSTMENT => 'تعديل إداري',
        };
    }
}

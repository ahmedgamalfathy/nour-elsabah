<?php
namespace App\Models\Points;

use Illuminate\Database\Eloquent\Model;

class PointsSetting extends Model
{
    protected $fillable = [
        'points_per_currency',
        'currency_per_point',
        'min_points_to_redeem',
        'is_active',
    ];

    protected $casts = [
        'points_per_currency' => 'decimal:2',
        'currency_per_point' => 'decimal:2',
        'min_points_to_redeem' => 'integer',
        'is_active' => 'boolean',
    ];

    public static function getSettings()
    {
        return self::firstOrCreate(
            ['id' => 1],
            [
                'points_per_currency' => 1,
                'currency_per_point' => 1,
                'min_points_to_redeem' => 100,
                'is_active' => true,
            ]
        );
    }
}

<?php

namespace App\Modules\Identity\Domain\Enums;

enum UserStatus: string
{
    case AKTIF = 'Aktif';
    case NON_AKTIF = 'Non Aktif';

    /**
     * Get all status values as array
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all status names as array
     *
     * @return array<string>
     */
    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    /**
     * Check if status is active
     */
    public function isActive(): bool
    {
        return $this === self::AKTIF;
    }
}

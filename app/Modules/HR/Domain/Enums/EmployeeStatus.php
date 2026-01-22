<?php

namespace App\Modules\HR\Domain\Enums;

enum EmployeeStatus: string
{
    case AKTIF = 'aktif';
    case NON_AKTIF = 'nonaktif';
    case CUTI = 'cuti';
    case KELUAR = 'keluar';

    public function label(): string
    {
        return match ($this) {
            self::AKTIF => 'Aktif',
            self::NON_AKTIF => 'Non Aktif',
            self::CUTI => 'Cuti',
            self::KELUAR => 'Keluar',
        };
    }
}

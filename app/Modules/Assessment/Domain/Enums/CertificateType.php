<?php

namespace App\Modules\Assessment\Domain\Enums;

enum CertificateType: string
{
    case ENGLISH = 'english';
    case COMPUTER = 'computer';

    public function label(): string
    {
        return match ($this) {
            self::ENGLISH => 'English Course',
            self::COMPUTER => 'Computer Course',
        };
    }
}

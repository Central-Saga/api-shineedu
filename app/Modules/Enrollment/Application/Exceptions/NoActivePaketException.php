<?php

namespace App\Modules\Enrollment\Application\Exceptions;

use Exception;

class NoActivePaketException extends Exception
{
    protected $code = 422;

    public function __construct($message = "Tidak ada paket aktif untuk enrollment ini", $code = 422, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

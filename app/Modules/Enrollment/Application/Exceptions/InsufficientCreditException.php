<?php

namespace App\Modules\Enrollment\Application\Exceptions;

use Exception;

class InsufficientCreditException extends Exception
{
    protected $code = 422;

    public function __construct($message = "Saldo pertemuan tidak mencukupi", $code = 422, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

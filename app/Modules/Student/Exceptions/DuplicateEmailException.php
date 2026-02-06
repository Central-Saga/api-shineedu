<?php

namespace App\Modules\Student\Exceptions;

class DuplicateEmailException extends \Exception
{
    public function __construct(string $message = 'Email sudah digunakan.', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

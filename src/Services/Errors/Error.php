<?php

namespace App\Services\Errors;

class Error
{
    public readonly string $id;
    public readonly string $message;

    public function __construct(string $id, string $message)
    {
        $this->id = $id;
        $this->message = $message;
    }
}
<?php

namespace App\Model\Question\Login;

readonly class LoginData
{
    public function __construct(
        public ?string $email = null,
        public ?string $password = null,
    ) {
    }
}
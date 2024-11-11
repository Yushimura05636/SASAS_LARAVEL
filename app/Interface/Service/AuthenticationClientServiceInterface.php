<?php

namespace App\Interface\Service;

interface AuthenticationClientServiceInterface
{
    public function authenticate(object $payload);

    public function unauthenticate(object $payload);
}

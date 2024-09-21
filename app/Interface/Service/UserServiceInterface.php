<?php

namespace App\Interface\Service;

interface UserServiceInterface
{
    public function findUser();

    public function findUserById(int $id);

    // public function findUserByEmail(string $email);

    public function createUser(object $payload);

    public function updateUser(object $payload, int $id);

    public function deleteUser(int $id);
}

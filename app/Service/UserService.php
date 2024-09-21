<?php

namespace App\Service;

use App\Http\Resources\UserResource;
use App\Interface\Repository\UserRepositoryInterface;
use App\Interface\Service\UserServiceInterface;

class UserService implements UserServiceInterface
{
    private $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function findUser()
    {
        $user = $this->userRepository->findMany();
        return UserResource::collection($user);
    }

    public function findUserById(int $id)
    {
        $user = $this->userRepository->findOneById($id);
        return new UserResource($user);
    }

    // public function findUserByEmail(string $email)
    // {
    //     $user = $this->userRepository->findByEmail($email);
    //     return new UserResource($user);
    // }

    public function createUser(object $payload)
    {
        $user = $this->userRepository->create($payload);
        return new UserResource($user);
    }

    public function updateUser(object $payload, int $id)
    {
        $user = $this->userRepository->update($payload, $id);
        return new UserResource($user);
    }

    public function deleteUser(int $id)
    {
        return $this->userRepository->delete($id);
    }
}

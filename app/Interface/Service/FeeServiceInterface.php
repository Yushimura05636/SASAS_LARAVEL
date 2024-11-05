<?php

namespace App\Interface\Service;

interface FeeServiceInterface
{
    public function findFees();

    public function findFeeById(int $id);

    public function createFee(object $payload);

    public function updateFee(object $payload, int $id);

    public function deleteFee(int $id);
}

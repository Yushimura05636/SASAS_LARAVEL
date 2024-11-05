<?php 

namespace App\Interface\Service;

interface FactorRateServiceInterface
{
    public function findFactorRate();

    public function findFactorRateById(int $id);

    public function createFactorRate(object $payload);

    public function updateFactorRate(object $payload, int $id);

    public function deleteFactorRate(int $id);

    public function getFreqandDuraDescription();
}
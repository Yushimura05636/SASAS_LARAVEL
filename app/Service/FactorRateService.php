<?php

namespace App\Service;

use App\Http\Resources\FactorRateResource;
use App\Interface\Repository\FactorRateRepositoryInterface;
use App\Interface\Service\FactorRateServiceInterface;

class FactorRateService implements FactorRateServiceInterface
{

    
    private $factorRate;

    public function __construct(FactorRateRepositoryInterface $factorRate)
    {
        $this->factorRate = $factorRate;
    }

    public function findFactorRate()
    {
        $factorRate = $this->factorRate->findMany();
        return FactorRateResource::collection($factorRate);

    }

    public function findFactorRateById(int $id)
    {
        $factorRate = $this->factorRate->findOneById($id);
        return new FactorRateResource($factorRate);

    }

    public function createFactorRate(object $payload)
    {
        $factorRate = $this->factorRate->create($payload);
        return new FactorRateResource($factorRate);

    }

    public function updateFactorRate(object $payload, int $id)
    {
        $factorRate = $this->factorRate->update($payload, $id);
        return new FactorRateResource($factorRate);

    }

    public function deleteFactorRate(int $id)
    {
        return $this->factorRate->delete($id);
    }

    public function getFreqandDuraDescription(){
        
    }
}
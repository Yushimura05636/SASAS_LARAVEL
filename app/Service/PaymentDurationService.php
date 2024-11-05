<?php

namespace App\Service;

use App\Http\Resources\PaymentDurationResource;
use App\Interface\Repository\PaymentDurationRepositoryInterface;
use App\Interface\Service\PaymentDurationServiceInterface;

class PaymentDurationService implements PaymentDurationServiceInterface
{

    
    private $paymentDuration;

    public function __construct(PaymentDurationRepositoryInterface $paymentDuration)
    {
        $this->paymentDuration = $paymentDuration;
    }

    public function findPaymentDuration()
    {
        $paymentDuration = $this->paymentDuration->findMany();

        return PaymentDurationResource::collection($paymentDuration);

    }

    public function findPaymentDurationById(int $id)
    {
        $paymentDuration = $this->paymentDuration->findOneById($id);
        return new PaymentDurationResource($paymentDuration);

    }

    public function createPaymentDuration(object $payload)
    {
        $paymentDuration = $this->paymentDuration->create($payload);
        return new PaymentDurationResource($paymentDuration);

    }

    public function updatePaymentDuration(object $payload, int $id)
    {
        $paymentDuration = $this->paymentDuration->update($payload, $id);
        return new PaymentDurationResource($paymentDuration);

    }

    public function deletePaymentDuration(int $id)
    {
        return $this->paymentDuration->delete($id);

    }
}
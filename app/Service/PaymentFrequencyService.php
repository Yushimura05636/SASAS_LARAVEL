<?php

namespace App\Service;

use App\Http\Resources\PaymentFrequencyResource;
use App\Interface\Repository\PaymentFrequencyRepositoryInterface;
use App\Interface\Service\PaymentFrequencyServiceInterface;

class PaymentFrequencyService implements PaymentFrequencyServiceInterface
{
    private $paymentFrequencyRepository;

    public function __construct(PaymentFrequencyRepositoryInterface $paymentFrequencyRepository)
    {
        $this->paymentFrequencyRepository = $paymentFrequencyRepository;
    }

    public function findPaymentFrequencies()
    {
        $paymentFrequency = $this->paymentFrequencyRepository->findMany();

        return PaymentFrequencyResource::collection($paymentFrequency);
    }

    public function findPaymentFrequencyById(int $id)
    {
        $paymentFrequency = $this->paymentFrequencyRepository->findOneById($id);

        return new PaymentFrequencyResource($paymentFrequency);
    }

    public function createPaymentFrequency(object $payload)
    {
        $paymentFrequency = $this->paymentFrequencyRepository->create($payload);

        return new PaymentFrequencyResource($paymentFrequency);
    }

    public function updatePaymentFrequency(object $payload, int $id)
    {
        $paymentFrequency = $this->paymentFrequencyRepository->update($payload, $id);

        return new PaymentFrequencyResource($paymentFrequency);
    }

    public function deletePaymentFrequency(int $id)
    {
        return $this->paymentFrequencyRepository->delete($id);
    }
}

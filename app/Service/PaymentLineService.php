<?php 

namespace App\Service;

use App\Http\Resources\PaymentLineResource;
use App\Interface\Repository\PaymentLineRepositoryInterface;
use App\Interface\Service\PaymentLineServiceInterface;

class PaymentLineService implements PaymentLineServiceInterface
{
    private $paymentLineRepository;

    public function __construct(PaymentLineRepositoryInterface $paymentLineRepository)
    {
        $this->paymentLineRepository = $paymentLineRepository;
    }

    public function findPaymentLine()
    {
        $paymentLine = $this->paymentLineRepository->findMany();
        return PaymentLineResource::collection($paymentLine);
    }

    public function findPaymentLineById(int $id)
    {
        $paymentLine = $this->paymentLineRepository->findOneById($id);
        return new PaymentLineResource($paymentLine);

    }

    public function createPaymentLine(object $payload)
    {
        $paymentLine = $this->paymentLineRepository->create($payload);
        return new PaymentLineResource($paymentLine);
    }

    public function updatePaymentLine(object $payload, int $id)
    {
        $paymentLine = $this->paymentLineRepository->update($payload, $id);
        return new PaymentLineResource($paymentLine);
    }

    public function deletePaymentLine(int $id)
    {
        return $this->paymentLineRepository->delete($id);

    }
}
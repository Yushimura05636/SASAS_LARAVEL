<?php
namespace App\Service;

use App\Http\Resources\PaymentScheduleResource;
use App\Interface\Repository\PaymentScheduleRepositoryInterface;
use App\Interface\Service\PaymentScheduleServiceInterface;

class PaymentScheduleService implements PaymentScheduleServiceInterface
{
    private $paymentScheduleRepository;

    public function __construct(PaymentScheduleRepositoryInterface $paymentScheduleRepository)
    {
        $this->paymentScheduleRepository=$paymentScheduleRepository;
    }
    public function findPaymentSchedule()
    {
        $paymentScheduleRepository = $this->paymentScheduleRepository->findMany();

        return PaymentScheduleResource::collection($paymentScheduleRepository);
    }

    public function findPaymentScheduleById(int $id)
    {
        $paymentScheduleRepository = $this->paymentScheduleRepository->findOneById($id);

        return new PaymentScheduleResource($paymentScheduleRepository);

    }

    public function createPaymentSchedule(object $payload)
    {
        $paymentScheduleRepository = $this->paymentScheduleRepository->create($payload);

        return new PaymentScheduleResource($paymentScheduleRepository);
    }

    public function updatePaymentSchedule(object $payload, int $id)
    {
        $paymentScheduleRepository = $this->paymentScheduleRepository->update($payload, $id);

        return new PaymentScheduleResource($paymentScheduleRepository);
    }

    public function deletePaymentSchedule(int $id)
    {
        return $this->paymentScheduleRepository->delete($id);

    }

}

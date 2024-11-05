<?php

namespace App\Interface\Service;

interface PaymentScheduleServiceInterface
{
    public function findPaymentSchedule();

    public function findPaymentScheduleById(int $id);

    public function createPaymentSchedule(object $payload);

    public function updatePaymentSchedule(object $payload, int $id);

    public function deletePaymentSchedule(int $id);
}

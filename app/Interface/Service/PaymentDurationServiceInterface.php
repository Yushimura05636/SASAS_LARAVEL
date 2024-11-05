<?php 

namespace App\Interface\Service;

interface PaymentDurationServiceInterface
{
    public function findPaymentDuration();

    public function findPaymentDurationById(int $id);

    public function createPaymentDuration(object $payload);

    public function updatePaymentDuration(object $payload, int $id);

    public function deletePaymentDuration(int $id);
}
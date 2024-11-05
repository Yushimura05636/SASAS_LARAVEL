<?php

namespace App\Interface\Service;

interface PaymentFrequencyServiceInterface
{
    public function findPaymentFrequencies();

    public function findPaymentFrequencyById(int $id);

    public function createPaymentFrequency(object $payload);

    public function updatePaymentFrequency(object $payload, int $id);

    public function deletePaymentFrequency(int $id);
}

<?php

namespace App\Interface\Service;


interface PaymentLineServiceInterface
{
    public function findPaymentLine();

    public function findPaymentLineById(int $id);

    public function createPaymentLine(object $payload);

    public function updatePaymentLine(object $payload, int $id);

    public function deletePaymentLine(int $id);

}
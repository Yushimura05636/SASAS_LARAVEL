<?php 

namespace App\Interface\Service;

interface PaymentServiceInterface
{
    public function findPayment();

    public function findPaymentById(int $id);

    public function createPayment(object $payload);

    public function updatePayment(object $payload, int $id);

    public function deletePayment(int $id);
}
<?php

namespace App\Interface\Service;

interface LoanApplicationFeeServiceInterface
{
    public function findLoanFees();

    public function findLoanFeeById($id);

    public function createLoanFee(object $payload);

    public function updateLoanFee(object $payload, int $id);

    public function deleteLoanFee(int $id);
}

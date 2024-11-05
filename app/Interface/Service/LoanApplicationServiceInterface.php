<?php

namespace App\Interface\Service;

interface LoanApplicationServiceInterface
{
    public function findLoanApplication();

    public function findLoanApplicationById(int $id);

    public function createLoanApplication(object $payload);

    public function updateLoanApplication(object $payload, int $id);
    
    public function deleteLoanApplication(int $id);
}

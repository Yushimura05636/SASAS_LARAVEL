<?php

namespace App\Interface\Service;

interface LoanCountServiceInterface
{
    public function findLoanCounts();

    public function findLoanCountById(int $id);

    public function createLoanCount(object $payload);

    public function updateLoanCount(object $payload, int $id);

    public function deleteLoanCount(int $id);
}

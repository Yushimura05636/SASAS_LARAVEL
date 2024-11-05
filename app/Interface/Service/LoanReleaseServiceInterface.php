<?php
namespace App\Interface\Service;

interface LoanReleaseServiceInterface
{
    public function findLoanRelease();

    public function findLoanReleaseById(int $id);

    public function createLoanRelease(object $payload);

    public function updateLoanRelease(object $payload, int $id);
    
    public function deleteLoanRelease(int $id);
}
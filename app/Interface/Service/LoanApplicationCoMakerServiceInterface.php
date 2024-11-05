<?php

namespace App\Interface\Service;

interface LoanApplicationCoMakerServiceInterface
{

    public function findCoMakers();

    public function findLoanCoMakerById($id);

    public function createLoanCoMaker(object $payload);

    public function updateLoanCoMaker(object $payload, int $id);

    public function deleteLoanCoMaker(int $id);
}

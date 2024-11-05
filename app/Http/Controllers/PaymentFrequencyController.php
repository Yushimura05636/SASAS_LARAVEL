<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentFrequencyStoreRequest;
use App\Http\Requests\PaymentFrequencyUpdateRequest;
use App\Interface\Service\PaymentFrequencyServiceInterface;

class PaymentFrequencyController extends Controller
{
    private $paymentFrequencyService;

    public function __construct(PaymentFrequencyServiceInterface $paymentFrequencyService)
    {
        $this->paymentFrequencyService = $paymentFrequencyService;
    }

    public function index()
    {
        return $this->paymentFrequencyService->findPaymentFrequencies();
    }

    public function store(PaymentFrequencyStoreRequest $request)
    {
        return $this->paymentFrequencyService->createPaymentFrequency($request);
    }

    public function show(int $id)
    {
        return $this->paymentFrequencyService->findPaymentFrequencyById($id);
    }

    public function update(PaymentFrequencyUpdateRequest $request, int $id)
    {
        return $this->paymentFrequencyService->updatePaymentFrequency($request, $id);
    }

    public function destroy(int $id)
    {
        return $this->paymentFrequencyService->deletePaymentFrequency($id);
    }
}

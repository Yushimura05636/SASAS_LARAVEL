<?php

namespace App\Http\Controllers;


use App\Http\Requests\PaymentDurationStoreRequest;
use App\Http\Requests\PaymentDurationUpdateRequest;
use App\Interface\Service\PaymentDurationServiceInterface;
use Illuminate\Http\Request;

class PaymentDurationController extends Controller
{
    private $paymentDurationService;

    public function __construct(PaymentDurationServiceInterface $paymentDurationService)
    {
        $this->paymentDurationService=$paymentDurationService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->paymentDurationService->findPaymentDuration();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PaymentDurationStoreRequest $request)
    {
        return $this->paymentDurationService->createPaymentDuration($request);
        
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        return $this->paymentDurationService->findPaymentDurationById($id);
        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PaymentDurationUpdateRequest $request, int $id)
    {
        return $this->paymentDurationService->updatePaymentDuration($request, $id);
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        return $this->paymentDurationService->deletePaymentDuration($id);

    }
}

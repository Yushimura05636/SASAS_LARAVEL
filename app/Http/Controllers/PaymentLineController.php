<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentLineStoreRequest;
use App\Http\Requests\PaymentLineUpdateRequest;
use App\Interface\Service\PaymentLineServiceInterface;
use Illuminate\Http\Request;

class PaymentLineController extends Controller
{

    private $paymentLineService;

    public function __construct(PaymentLineServiceInterface $paymentLineService)
    {
        $this->paymentLineService=$paymentLineService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->paymentLineService->findPaymentLine();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        return $this->paymentLineService->createPaymentLine($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        return $this->paymentLineService->findPaymentLineById($id);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PaymentLineUpdateRequest $request, int $id)
    {
        return $this->paymentLineService->updatePaymentLine($request, $id);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        return $this->paymentLineService->deletePaymentLine($id);

    }
}

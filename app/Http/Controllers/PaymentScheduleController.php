<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentScheduleStoreRequest;
use App\Http\Requests\PaymentScheduleUpdateRequest;
use App\Interface\Service\PaymentScheduleServiceInterface;
use Illuminate\Http\Request;

class PaymentScheduleController extends Controller
{

    private $paymentScheduleService;

    public function __construct(PaymentScheduleServiceInterface $paymentScheduleService)
    {
        $this->paymentScheduleService = $paymentScheduleService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->paymentScheduleService->findPaymentSchedule();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        return $this->paymentScheduleService->createPaymentSchedule($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        return $this->paymentScheduleService->findPaymentScheduleById($id);

    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id)
    {
        return $this->paymentScheduleService->updatePaymentSchedule($request,  $id);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        return $this->paymentScheduleService->deletePaymentSchedule($id);

    }
}

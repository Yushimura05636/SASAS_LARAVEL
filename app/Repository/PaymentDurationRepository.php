<?php

namespace App\Repository;

use App\Interface\Repository\PaymentDurationRepositoryInterface;
use App\Models\Payment_Duration;
use Illuminate\Http\Response;

class PaymentDurationRepository implements PaymentDurationRepositoryInterface
{
    public function findMany()
    {
        return Payment_Duration::paginate(10);
    }

    public function findOneById($id)
    {
        return Payment_Duration::findOrFail($id);
    }

    public function create(object $payload)
    {
        $paymentDuration = new Payment_Duration();
        $paymentDuration->description = $payload->description;
        $paymentDuration->number_of_payments = $payload->number_of_payments;
        $paymentDuration->notes = $payload->notes;
        $paymentDuration->save();
        return $paymentDuration->refresh();
    }

    public function update(object $payload, int $id)
    {
        $paymentDuration = Payment_Duration::findOrFail($id);
        $paymentDuration->description = $payload->description;
        $paymentDuration->number_of_payments = $payload->number_of_payments;
        $paymentDuration->notes = $payload->notes;
        $paymentDuration->save();
        return $paymentDuration->refresh();
    }

    public function delete(int $id)
    {
        $paymentDuration = Payment_Duration::findOrFail($id);
        $paymentDuration->delete();

        return response()->json([
            'message' => 'Success'
        ], Response::HTTP_OK);
    }
}
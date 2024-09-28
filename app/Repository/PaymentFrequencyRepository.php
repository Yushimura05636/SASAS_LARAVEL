<?php

namespace App\Repository;
use App\Interface\Repository\PaymentFrequencyRepositoryInterface;
use App\Models\Factor_Rate;
use App\Models\Payment_Frequency;
use Illuminate\Http\Response;

class PaymentFrequencyRepository implements PaymentFrequencyRepositoryInterface
{
    public function findMany()
    {
        return Payment_Frequency::paginate(10);
    }

    public function findOneById($id)
    {
        return Payment_Frequency::find($id);
    }

    public function create(object $payload)
    {
        $paymentFrequency = new Payment_Frequency();
        $paymentFrequency->description = $payload->description;
        $paymentFrequency->days_interval = $payload->days_interval;
        $paymentFrequency->notes = $payload->notes;
        $paymentFrequency->save();

        return $paymentFrequency->fresh();
    }

    public function update(object $payload, int $id)
    {
        $paymentFrequency = Payment_Frequency::findOrFail($id);
        $paymentFrequency->description = $payload->description;
        $paymentFrequency->days_interval = $payload->days_interval;
        $paymentFrequency->notes = $payload->notes;
        $paymentFrequency->save();

        return $paymentFrequency->fresh();
    }

    public function delete(int $id)
    {
        $paymentFrequency = Payment_Frequency::findOrFail($id);
        $paymentFrequency->delete();

        return response()->json([
            'message' => 'Success'
        ], Response::HTTP_OK);
    }
}

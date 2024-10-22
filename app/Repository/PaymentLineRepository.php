<?php

namespace App\Repository;

use App\Interface\Repository\PaymentLineRepositoryInterface;
use App\Models\Payment_Line;
use Illuminate\Http\Response;

class PaymentLineRepository implements PaymentLineRepositoryInterface
{
    public function findMany()
    {
        return Payment_Line::paginate(100);
    }

    public function findOneById($id)
    {
        return Payment_Line::find($id);
    }

    public function create(object $payload)
    {
        $paymentLine = new Payment_Line();
        $paymentLine->payment_id= $payload->payment_id;
        $paymentLine->payment_schedule_id= $payload->payment_schedule_id;
        $paymentLine->balance= $payload->balance;
        $paymentLine->amount_paid= $payload->amount_paid;
        $paymentLine->remarks= $payload->remarks;

        $paymentLine->save();

        return $paymentLine->fresh();
    }

    public function update(object $payload, int $id)
    {
        $paymentLine = Payment_Line::findOrFail($id);
        $paymentLine->payment_id= $payload->payment_id;
        $paymentLine->payment_schedule_id= $payload->payment_schedule_id;
        $paymentLine->balance= $payload->balance;
        $paymentLine->amount_paid= $payload->amount_paid;
        $paymentLine->remarks= $payload->remarks;

        $paymentLine->save();

        return $paymentLine->fresh();
    }

    public function delete(int $id)
    {
        $paymentLine = Payment_Line::findOrFail($id);
        $paymentLine->delete();

        return response()->json([
            'message' => 'Success'
        ], Response::HTTP_OK);
    }
}

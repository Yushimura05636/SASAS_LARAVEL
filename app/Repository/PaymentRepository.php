<?php

namespace App\Repository;

use App\Interface\Repository\PaymentRepositoryInterface;
use App\Models\Payment;
use Illuminate\Http\Response;

class PaymentRepository implements PaymentRepositoryInterface
{
    public function findMany()
    {
        return Payment::get(); //no limit
    }

    public function findOneById($id)
    {
        return Payment::find($id);
    }

    public function create(object $payload)
    {
        $payment = new Payment();
        $payment->customer_id= $payload->customer_id;
        $payment->prepared_at= $payload->prepared_at;
        $payment->document_status_code= $payload->document_status_code;
        $payment->prepared_by_id= $payload->prepared_by_id;
        $payment->amount_paid= $payload->amount_paid;
        $payment->notes= $payload->notes;
        $payment->save();
        return $payment->fresh();
    }

    public function update(object $payload, int $id)
    {
        $payment = Payment::findOrFail($id);
        $payment->customer_id= $payload->customer_id;
        $payment->prepared_at= $payload->prepared_at;
        $payment->document_status_code= $payload->document_status_code;
        $payment->prepared_by_id= $payload->prepared_by_id;
        $payment->amount_paid= $payload->amount_paid;
        $payment->notes= $payload->notes;
        $payment->save();
        return $payment->fresh();
    }

    public function delete(int $id)
    {
        $payment = Payment::findOrFail($id);
        $payment->delete();

        return response()->json([
            'message' => 'Success'
        ], Response::HTTP_OK);
    }
}

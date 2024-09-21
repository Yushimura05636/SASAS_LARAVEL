<?php

namespace App\Repository;

use App\Interface\Repository\PaymentScheduleRepositoryInterface;
use App\Models\Payment_Schedule;
use Illuminate\Http\Response;

class PaymentScheduleRepository implements PaymentScheduleRepositoryInterface
{

    public function findMany()
    {
        return Payment_Schedule::paginate(10);
    }

    public function findOneById($id)
    {
        return Payment_Schedule::find($id);
    }

    public function create(object $payload)
    {
        $paymentSchedule = new Payment_Schedule();

        $paymentSchedule->datetime_created =$payload->datetime_created;
        $paymentSchedule->customer_id =$payload->customer_id;
        $paymentSchedule->loan_released_id =$payload->loan_released_id;
        $paymentSchedule->datetime_due =$payload->datetime_due;
        $paymentSchedule->amount_due =$payload->amount_due;
        $paymentSchedule->amount_interest =$payload->amount_interest;
        $paymentSchedule->amount_paid =$payload->amount_paid;
        $paymentSchedule->payment_status_code =$payload->payment_status_code;
        $paymentSchedule->remarks =$payload->remarks;
        $paymentSchedule->save();

        return $paymentSchedule->fresh();

    }

    public function update(object $payload, int $id)
    {
        $paymentSchedule = Payment_Schedule::findOrFail($id);

        $paymentSchedule->datetime_created =$payload->datetime_created;
        $paymentSchedule->customer_id =$payload->customer_id;
        $paymentSchedule->loan_released_id =$payload->loan_released_id;
        $paymentSchedule->datetime_due =$payload->datetime_due;
        $paymentSchedule->amount_due =$payload->amount_due;
        $paymentSchedule->amount_interest =$payload->amount_interest;
        $paymentSchedule->amount_paid =$payload->amount_paid;
        $paymentSchedule->payment_status_code =$payload->payment_status_code;
        $paymentSchedule->remarks =$payload->remarks;
        $paymentSchedule->save();

        return $paymentSchedule->fresh();
    }

    public function delete(int $id)
    {
        $paymentSchedule = Payment_Schedule::findOrFail($id);
        $paymentSchedule->delete();

        return response()->json([
            'message' => 'Success'
        ], Response::HTTP_OK);

    }

}

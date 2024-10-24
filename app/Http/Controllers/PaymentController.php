<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentStoreRequest;
use App\Http\Requests\PaymentUpdateRequest;
use App\Interface\Service\PaymentLineServiceInterface;
use App\Interface\Service\PaymentScheduleServiceInterface;
use App\Interface\Service\PaymentServiceInterface;
use App\Models\Loan_Release;
use App\Models\Payment_Line;
use App\Models\Payment_Schedule;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{

    private $paymentService;

    public function __construct(PaymentServiceInterface $paymentService)
    {
        $this->paymentService=$paymentService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(CustomerPersonalityController $customerPersonalityController)
    {
        $payment = $this->paymentService->findPayment();

        for($i = 0; $i < count($payment); $i++)
        {

            $customerPersonality = $customerPersonalityController->show($payment[$i]['customer_id']);

            $payment[$i]['family_name'] = " " . $customerPersonality->original['personality']['family_name'];
            $payment[$i]['first_name'] = " " . $customerPersonality->original['personality']['first_name'];
            $payment[$i]['middle_name'] = " " . $customerPersonality->original['personality']['middle_name'];
        }

        return response()->json([
            'data' => $payment,
        ]);

    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, PaymentServiceInterface $paymentService, PaymentLineServiceInterface $paymentLineService, PaymentScheduleServiceInterface $paymentScheduleService)
{
    // Start DB Transaction
    DB::beginTransaction();

    try {
        $customerId = $request['customer.id'];
        $amountPaid = $request['schedule.amount_paid'];
        $notes = $request['schedule.remarks'] ?? null;


        // Create the payment record
        $paymentData = [
            'customer_id' => $customerId,
            'prepared_at' => now(),
            'document_status_code' => 'APPROVED',
            'prepared_by_id' => auth()->user()->id,
            'amount_paid' => $amountPaid,
            'notes' => $notes
        ];

        // return response()->json([
        //     'data' => $paymentData,
        //     'request' => $request['customer.passbook_no']
        // ], Response::HTTP_INTERNAL_SERVER_ERROR);

        if($amountPaid > 0)
        {
            $paymentData = new Request($paymentData);

            $payment = $paymentService->createPayment($paymentData); // Save payment

            // Step 2: Apply payment to schedule(s)
            $this->applyPaymentToSchedules($payment, $amountPaid, $request, $paymentLineService, $paymentScheduleService);
        }
        else
        {
            throw new \Exception('The amount should not be less than or equal zero');
        }


        DB::commit();

        return response()->json(['message' => 'Payment created successfully'], 200);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

protected function applyPaymentToSchedules($payment, $totalAmountPaid, Request $request, PaymentLineServiceInterface $paymentLineService, PaymentScheduleServiceInterface $paymentScheduleService)
{

    //get first the loan app id
    $loanReleaseId = Loan_Release::where('loan_application_id', $request['loan.loan_applications.id'])->where('passbook_number', $request['customer.passbook_no'])->get();

    //Empty schedule
    $schedules = '';


    if(!$loanReleaseId->isEmpty()){
        $loanReleaseId = Loan_Release::where('loan_application_id', $request['loan.loan_applications.id'])->where('passbook_number', $request['customer.passbook_no'])
        ->first()
        ->id;
    }
    else
    {
        $loanReleaseId = null;
    }



    //then release

    $schedules = Payment_Schedule::where('customer_id', $payment->customer_id)->where('loan_released_id', $loanReleaseId)->where('payment_status_code', 'UNPAID')
    ->orWhere('payment_status_code', 'PARTIALLY PAID')
    ->get();

    foreach ($schedules as $schedule) {
        if ($totalAmountPaid <= 0) {
            break; // No more payment left to allocate
        }


        $amountDue = $schedule->amount_due - $schedule->amount_paid; // Remaining balance for this schedule

        if ($totalAmountPaid >= $amountDue) {

            $this->createPaymentLine($request, $payment, $schedule, $amountDue, 'Full payment', $paymentLineService);
            $schedule->amount_paid = $schedule->amount_paid + $amountDue;
            $schedule->payment_status_code = 'PAID';
            $schedule->save();
            $schedule->fresh();

            // $schedule->update([
                //     'amount_paid' => $schedule->amount_paid + $amountDue,
                //     'payment_status_code' => 'PAID',
                // ]);
                $totalAmountPaid -= $amountDue;
                // return response()->json([
                //             'data' => $schedule,
                //             ], Response::HTTP_INTERNAL_SERVER_ERROR);
        } else {
            // Partial payment
            $this->createPaymentLine($request, $payment, $schedule, $totalAmountPaid, 'Partial payment', $paymentLineService);
            $schedule->amount_paid = $schedule->amount_paid + $totalAmountPaid;
            $schedule->payment_status_code = 'PARTIALLY PAID';
            $schedule->save();
            $schedule->fresh();
            // $schedule->update([
            //     'amount_paid' => $schedule->amount_paid + $totalAmountPaid,
            //     'payment_status_code' => 'PARTIALLY PAID',
            // ]);
            $totalAmountPaid = 0; // All payment has been allocated
        }
    }

    return response()->json([
        'data' => 'end',
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
}

protected function createPaymentLine($request, $payment, $schedule, $amountPaid, $remarks, PaymentLineServiceInterface $paymentLineService)
{

    //get the total balance of all the payment schedules
    $totals = Payment_Schedule::where('customer_id', $request['customer.id'])
        ->selectRaw('(SUM(amount_due) - SUM(amount_paid)) AS balance')
        ->first();

    (float) $balance = $totals->balance;

    $paymentLineData = [
        'payment_id' => $payment->id,
        'payment_schedule_id' => $schedule->id,
        'balance' => $balance - $amountPaid,
        'amount_paid' => $amountPaid,
        'remarks' => $remarks,
    ];

    // return response()->json([
    //             'data' => $paymentLineData,
    //             'pay' => $schedule->amount_paid,
    //             'due' => $schedule->amount_due,
    //             'new Balance if zero' => $totals,
    //             'all' => $totals,
    //         ], Response::HTTP_INTERNAL_SERVER_ERROR);



    //convert object
    $paymentLineData = new Request($paymentLineData);

    // Insert or update the record as necessary
    // Payment_Line::updateOrCreate(
    //     ['payment_id' => $payment->id, 'payment_schedule_id' => $schedule->id, 'balance' => $schedule->amount_due - $schedule->amount_paid, 'amount_paid' => $amountPaid, 'remarks' => $remarks],
    //     $paymentLineData
    // );
    // Save payment line
    $paymentLineService->createPaymentLine($paymentLineData);
}




    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        return $this->paymentService->findPaymentById($id);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PaymentUpdateRequest $request, int $id)
    {
        return $this->paymentService->updatePayment($request, $id);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        return $this->paymentService->deletePayment($id);

    }
}

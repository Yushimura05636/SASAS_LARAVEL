<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentStoreRequest;
use App\Http\Requests\PaymentUpdateRequest;
use App\Http\Resources\PaymentScheduleResource;
use App\Interface\Service\PaymentLineServiceInterface;
use App\Interface\Service\PaymentScheduleServiceInterface;
use App\Interface\Service\PaymentServiceInterface;
use App\Models\Customer;
use App\Models\Loan_Application;
use App\Models\Loan_Release;
use App\Models\Payment;
use App\Models\Payment_Line;
use App\Models\Payment_Schedule;
use App\Models\User_Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
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
            //payment line
            $payment_line = Payment_Line::where('payment_id', $payment[$i]['id'])->first();

            //payment schedule
            $payment_schedule = Payment_Schedule::where('id', $payment_line->payment_schedule_id)->first();

            $customerPersonality = $customerPersonalityController->show($payment[$i]['customer_id']);

            $payment[$i]['family_name'] = " " . $customerPersonality->original['personality']['family_name'];
            $payment[$i]['first_name'] = " " . $customerPersonality->original['personality']['first_name'];
            $payment[$i]['middle_name'] = " " . $customerPersonality->original['personality']['middle_name'];
            $payment[$i]['loan_application_no'] = $payment_schedule->loan_application_no;
        }

        return response()->json([
            'data' => $payment,
        ]);

    }

    public function store(Request $request, PaymentLineController $paymentLineController, PaymentServiceInterface $paymentService)
    {
        // Start DB Transaction
        DB::beginTransaction();

        try {

            // Loop through the decoded array to get the ids
            foreach ($request['payment'] as $key => $item) {
                if (isset($item)) {

                    // Create the payment record
                    $paymentData = [
                        'customer_id' => $request->customer_id,
                        'prepared_at' => now(),
                        'document_status_code' => 'PENDING',
                        'prepared_by_id' => auth()->user()->id,
                        'amount_paid' => $item['amount_paid'],
                        'notes' => ''
                    ];



                    if($paymentData['amount_paid'] > 0)
                    {
                        $paymentData = new Request($paymentData);

                        $payment = $paymentService->createPayment($paymentData); // Save payment

                        //get the loan application no
                        $payment_schedule_id = $item['id'];

                        $payment_id = $payment->id;

                        $payload = [
                            'payment_id' => $payment_id,
                            'payment_schedule_id' => $payment_schedule_id,
                            'balance' => $item['balance'],
                            'amount_paid' => 0,
                            'remarks' => 'PENDING',
                        ];

                        $payload = new Request($payload);

                        $payment_line = $paymentLineController->store($payload);
                    }
                    else
                    {
                        throw new \Exception('The amount should not be less than or equal zero');
                    }
                }
            }

            DB::commit();

            return response()->json(['message' => 'Payment created successfully'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
        return response()->json(['error' => $e->getMessage()], 500);
    }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function paymentApprove(Request $request, int $id, PaymentServiceInterface $paymentService, PaymentLineServiceInterface $paymentLineService, PaymentScheduleServiceInterface $paymentScheduleService, PaymentScheduleController $paymentScheduleController, CustomerPersonalityController $customerPersonalityController)
    {
        // Start DB Transaction
        DB::beginTransaction();

        try {
            $payment = $request->input('state.payment');

            // Check for pending payments before approving the current payment
            $hasPendingPayments = $this->hasPendingPayments($payment['customer_id'], $id);

            if ($hasPendingPayments) {
                throw new \Exception('Cannot approve payment. There are pending payments before this one.');
            }

            // Create the payment record
            $paymentData = [
                'customer_id' => $payment['customer_id'],
                'prepared_at' => now(),
                'document_status_code' => 'APPROVED',
                'prepared_by_id' => auth()->user()->id,
                'amount_paid' => $payment['amount_paid'],
                'notes' => $payment['notes'],
            ];

            if ($payment['amount_paid'] > 0) {
                $paymentData = new Request($paymentData);
                $paymentConfirm = $paymentService->updatePayment($paymentData, $id); // Save payment

                $amount_paid = $payment['amount_paid'];

                // Step 2: Apply payment to schedule(s)
                $this->applyPaymentToSchedules($paymentConfirm, $amount_paid, $request, $paymentLineService, $paymentScheduleService, $paymentScheduleController, $customerPersonalityController);
            } else {
                throw new \Exception('The amount should not be less than or equal zero');
            }

            DB::commit();

            return response()->json(['message' => 'Payment created successfully'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

private function hasPendingPayments(int $customerId, int $currentPaymentId)
{
    // Check if there are any pending payments for the customer before the current payment
    $pendingPayments = Payment::where('customer_id', $customerId)
        ->where('document_status_code', 'PENDING') // Assuming 'PENDING' is the status code for pending payments
        ->where('id', '<', $currentPaymentId) // Ensure that we check for payments with an ID less than the current payment's ID
        ->exists();

    return $pendingPayments;
}

protected function applyPaymentToSchedules($payment, $totalAmountPaid, Request $request, PaymentLineServiceInterface $paymentLineService, PaymentScheduleServiceInterface $paymentScheduleService, PaymentScheduleController $paymentScheduleController, CustomerPersonalityController $customerPersonalityController)
{

    $customer = Customer::where('id', $payment->customer_id)->first();

    //get the loan application
    $payment_line = $paymentLineService->findPaymentLine();

    $payment_schedule_id = null;
    $loan_application_no = null;

    foreach($payment_line as $line)
    {
        if($line->payment_id == $payment->id)
        {
            $payment_schedule_id = $line->payment_schedule_id;
            break;
        }
    }

    // $payment_schedule = $paymentScheduleService->findPaymentScheduleById($payment_schedule_id);
    $payment_schedule = $paymentScheduleController->index($customerPersonalityController);

    // Convert the array into an associative array keyed by 'id'
    $indexedSchedules = array_values($payment_schedule->original);


    foreach ($indexedSchedules as $id => $schedule) {
        $debug = $schedule;
        if (!is_null($schedule)) {
            $debug = count($schedule);
            foreach($schedule as $sched)
            {
                if(!is_null($sched))
                {
                    if($sched['id'] == $payment_schedule_id)
                    {
                        $loan_application_no = $sched['loan_application_no'];
                    }
                }
            }
        }
    }

    if($loan_application_no == null || is_null($loan_application_no))
    {
        foreach($indexedSchedules as $id => $schedule)
        {
            if(!is_null($schedule))
            {
                $payment_schedule_id = $schedule[0]['id'];
                $paymentScheduleId = $schedule[0]->id;

                if($payment_schedule_id == $payment_schedule_id || $paymentScheduleId == $payment_schedule_id)
                {
                    //return response()->json(['message create' => $schedule], Response::HTTP_INTERNAL_SERVER_ERROR);
                    $loan_application_no = $schedule[0]->loan_application_no;

                    if(is_null($loan_application_no))
                    {
                        $loan_application_no = $schedule[0]['loan_application_no'];
                    }
                }
            }
        }
    }

    //return response()->json(['message' => $schedule, 'messabe data 2' => $altId, 'message data 3' => $altId2], Response::HTTP_INTERNAL_SERVER_ERROR);

    //return response()->json(['message' => $loan_application_no], Response::HTTP_INTERNAL_SERVER_ERROR);




    $loan_application = Loan_Application::where('loan_application_no', $loan_application_no)->first();

    //get first the loan app id
    $loanReleaseId = Loan_Release::where('loan_application_id', $loan_application->loan_application_no)->where('passbook_number', $customer->passbook_no)->get();

    //Empty schedule
    $schedules = '';



    if(!$loanReleaseId->isEmpty()){
        $loanReleaseId = Loan_Release::where('loan_application_id', $loan_application->id)->where('passbook_number', $request['customer.passbook_no'])
        ->first()
        ->id;
    }
    else
    {
        $loanReleaseId = null;
    }

    $schedules = Payment_Schedule::where('customer_id', $payment->customer_id)
    ->where('loan_released_id', $loanReleaseId)
    ->whereIn('payment_status_code', ['UNPAID', 'PARTIALLY PAID']) // Include UNPAID and PARTIALLY PAID
    ->orWhere('payment_status_code', 'not like', '%FORWARDED%') // Exclude any that have FORWARDED
    ->orWhere('payment_status_code', 'not like', '%PAID%') // Exclude any that have PAID
    ->get();




    foreach ($schedules as $index => $schedule) {
        // Skip any schedule marked as "FORWARDED"
        if (strpos($schedule->payment_status_code, 'FORWARDED') !== false) {
            continue;
        }

        if ($totalAmountPaid <= 0) {
            break; // No more payment left to allocate
        }

        $amountDue = $schedule->amount_due - $schedule->amount_paid; // Calculate remaining balance


        if ($totalAmountPaid >= $amountDue) {
            // Full payment case
            $schedule->amount_paid += $amountDue;
            $schedule->payment_status_code = 'PAID';
            $schedule->save();

            $totalAmountPaid -= $amountDue; // Deduct the paid amount from total

            // Create a payment line for full payment
            $this->createPaymentLine($request, $payment, $schedule, $amountDue, 'Full payment', $paymentLineService);

        } else {
            // Partial payment case
            $remainingBalance = $amountDue - $totalAmountPaid; // Calculate remaining balance after partial payment

            //return response()->json(['message' => $remainingBalance], Response::HTTP_INTERNAL_SERVER_ERROR);
            // Update the schedule's amount paid with the partial amount
            $schedule->amount_paid += $totalAmountPaid;

            // Check if the schedule still has a balance
            if ($schedule->amount_due > 0) {
                // If there's still an amount due, mark as partially paid
                if ($index < count($schedules) - 1) {
                    $schedule->payment_status_code = 'PARTIALLY PAID, FORWARDED';
                } else {
                    $schedule->payment_status_code = 'PARTIALLY PAID';
                }
            } else {
                // If the amount_due is now zero, mark as paid
                $schedule->payment_status_code = 'PAID';
            }

            // Save and refresh the schedule to ensure updated state
            $schedule->save();
            $schedule->fresh();

            // Forward the remaining balance to the next schedule only if this is not the last schedule
            $nextSchedule = ($index < count($schedules) - 1)
            ? Payment_Schedule::where('id', '>', $schedule->id)
            ->orderBy('id')
            ->first()
            : null;

            if ($nextSchedule) {
                $nextSchedule->amount_due += $remainingBalance; // Forward remaining balance to the next schedule
                $nextSchedule->amount_interest += $remainingBalance;
                $nextSchedule->save();
            }

            //return response()->json(['message' => $nextSchedule], Response::HTTP_INTERNAL_SERVER_ERROR);
            // Create a payment line for the partial payment
            $this->createPaymentLine($request, $payment, $schedule, $totalAmountPaid, 'Partial payment', $paymentLineService);

            // Reset totalAmountPaid as all the payment has been allocated
            $totalAmountPaid = 0;
        }
    }


    // // Return updated schedules data
    // return response()->json([
    //     'data' => $schedules,
    // ], Response::HTTP_INTERNAL_SERVER_ERROR);

}

protected function createPaymentLine($request, $payment, $schedule, $amountPaid, $remarks, PaymentLineServiceInterface $paymentLineService)
{

    //get the total balance of all the payment schedules
    $totals = Payment_Schedule::where('customer_id', $payment->customer_id)
        ->selectRaw('(SUM(amount_due) - SUM(amount_paid)) AS balance, SUM(amount_paid) AS paid, SUM(amount_due) AS due')
        ->first();

    (float) $balance = $totals->balance;

    // return response()->json([
    //     'paid' => $totals->balance,
    //     'due' => $totals->due,
    // ], Response::HTTP_INTERNAL_SERVER_ERROR);

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
    public function show(int $id, PaymentScheduleController $paymentScheduleController, CustomerPersonalityController $customerPersonalityController)
    {
        //get the payment
        $payment = $this->paymentService->findPaymentById($id);

        //payment line
        $payment_line = Payment_Line::where('payment_id', $payment->id)->first();

        //payment schedule
        //$payment_schedule = Payment_Schedule::where('id', $payment_line->payment_schedule_id)->first();
        $payment_schedule = $paymentScheduleController->index($customerPersonalityController);

        //return response()->json(['message success' => $payment_schedule]);

        foreach($payment_schedule->original as $index=> $schedule)
        {
            foreach($schedule as $sched)
            {
                if($payment_line->payment_schedule_id == $sched->id)
                {
                    $payment->loan_application_no = $sched->loan_application_no;
                }
            }
        }

        $user = User_Account::where('id', $payment->prepared_by_id)->first();

        //return response()->json(['message success' => $payment->loan_application_no]);

        return response()->json([
            'data' => $payment,
            'user' => $user,
        ], Response::HTTP_OK);
    }

    public function paymentLoanNO(string $id, CustomerPersonalityController $customerPersonalityController, PaymentScheduleController $paymentScheduleController)
    {

// Call the method to get the payments response
$response = $paymentScheduleController->index($customerPersonalityController);

$payments = null;
// Check if the response is a JsonResponse
if ($response instanceof JsonResponse) {
    // Get the content of the response
    $payments = $response->getContent();
} else {
    // Handle the case where the response is not what you expect
    echo "Unexpected response type.";
    exit;
}

// Decode the JSON string into a PHP array
$paymentsArray = json_decode($payments, true);

// Check if decoding was successful
if ($paymentsArray === null) {
    echo "Failed to decode JSON: " . json_last_error_msg();
    exit;
}

// Access the loan_application_no values
$loanApplicationNos = [];
$i = 0;
$k = 0;

// Loop through the decoded array to get the ids
foreach ($paymentsArray as $item) {
    if (isset($item)) {
        foreach ($item as $data) {
            if (isset($data)) {
                if($data['loan_application_no'] == $id)
                {
                    $loanApplicationNos[] = $data;
                }
            }
        }
    }
}

// Output the loan_application_no values
if (empty($loanApplicationNos)) {
    echo "No loan application numbers found.\n";
    return [
        'data result' => $loanApplicationNos,
    ];
} else {
    return [
        'data' => $loanApplicationNos,
    ];
}
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

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
use App\Models\Personality;
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


        foreach($payment as $pay)
        {
            if(!is_null($pay))
            {

                //payment line
                $payment_line = Payment_Line::where('payment_id', $pay['id'])->first();

                //payment schedule
                $payment_schedule = Payment_Schedule::where('id', $payment_line['payment_schedule_id'])->first();

                $customerPersonality = $customerPersonalityController->show($pay['customer_id']);

                $pay['family_name'] = " " . $customerPersonality->original['personality']['family_name'];
                $pay['first_name'] = " " . $customerPersonality->original['personality']['first_name'];
                $pay['middle_name'] = " " . $customerPersonality->original['personality']['middle_name'];
                $pay['loan_application_no'] = $payment_schedule->loan_application_no;
            }
        }
        //return response()->json(['message' => $payment], Response::HTTP_INTERNAL_SERVER_ERROR);

        return response()->json([
            'data' => $payment,
        ]);

    }

    public function store(Request $request, PaymentLineController $paymentLineController, PaymentServiceInterface $paymentService)
    {
        // Start DB Transaction
        DB::beginTransaction();

        //check the customer payment dues
        $schedules = Payment_Schedule::where('customer_id', $request['customer_id'])
        ->where('payment_status_code', 'like', '%Unpaid%')
        ->orWhere('payment_status_code', 'PARTIALLY PAID')
        ->get();


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

                    if(!(count($schedules) > 1))
                    {
                        // Check if the amount paid is within the balance
                        if ($paymentData['amount_paid'] > $item['balance']) {
                            return response()->json(['message' => 'Payment amount exceeds remaining balance'], Response::HTTP_BAD_REQUEST);
                        }
                    }

                    //return response()->json(['message' => 'stop'], Response::HTTP_INTERNAL_SERVER_ERROR);


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
                            'amount_paid' => $item['amount_paid'],
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

            //return response()->json(['message' => $hasPendingPayments], Response::HTTP_INTERNAL_SERVER_ERROR);

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
        ->where('document_status_code', 'like', '%Pending%') // Assuming 'PENDING' is the status code for pending payments
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

    //$payment_schedule = $paymentScheduleService->findPaymentScheduleById($payment_schedule_id);
    //$payment_schedule = $paymentScheduleController->index($customerPersonalityController);

    $payment = Payment_Schedule::where('id', $payment_schedule_id)
    ->where('payment_status_code', 'like', '%UNPAID%')
    ->orWhere('payment_status_code' ,'PARTIALLY PAID')
    ->get();


    //$payment = PaymentScheduleResource::collection($payment);


    foreach($payment as $pay)
    {
        if(!is_null($pay))
        {

            $customerPersonality = $customerPersonalityController->show($pay['customer_id']);

            $pay['family_name'] = " " . $customerPersonality->original['personality']['family_name'];
            $pay['first_name'] = " " . $customerPersonality->original['personality']['first_name'];
            $pay['middle_name'] = " " . $customerPersonality->original['personality']['middle_name'];


            // Adjust balance calculation to account for forwarded amounts
            //$originalDue = $payment[$i]['amount_due'] + $payment[$i]['amount_paid']; // or replace with stored original_amount_due if available
            $balance = $pay['balance'] = $pay['amount_due'] - $pay['amount_paid'];

            // return response()->json([
            //     'data' => $balance,
            // ], Response::HTTP_INTERNAL_SERVER_ERROR);


            if($pay['loan_released_id'] && $pay['loan_released_id'] > 0)
            {

                //get the loan application id
                $loanAppId = Loan_Release::where('id', $pay['loan_released_id'])->first()->loan_application_id;

                //get the loan_application_no
                $loanApplicationNo = Loan_Application::where('id', $loanAppId)->first()->loan_application_no;

                if($loanApplicationNo && !is_null($loanApplicationNo))
                {
                    $pay['loan_application_no'] = $loanApplicationNo;
                }
            }
            else
            {
                $loanApplications = DB::table('loan_application_fees AS laf')
                ->join('loan_application AS la', 'laf.loan_application_id', '=', 'la.id')
                ->where('la.customer_id', $pay['customer_id'])
                ->select('la.loan_application_no', 'laf.amount', 'la.customer_id', 'laf.loan_application_id')
                ->first();

                if(!is_null($loanApplications))
                {
                    $pay['loan_application_no'] = $loanApplications->loan_application_no;
                }

            }


            //search the customer id
            $customerPersonality = $customerPersonalityController->show($pay['customer_id']);

            $pay['family_name'] = " " . $customerPersonality->original['personality']['family_name'];
            $pay['first_name'] = " " . $customerPersonality->original['personality']['first_name'];
            $pay['middle_name'] = " " . $customerPersonality->original['personality']['middle_name'];

            // Adjust balance calculation to account for forwarded amounts
            //$originalDue = $payment[$i]['amount_due'] + $payment[$i]['amount_paid']; // or replace with stored original_amount_due if available
            $balance = $pay['balance'] = $pay['amount_due'] - $pay['amount_paid'];

            // // Convert the array into an associative array keyed by 'id'
            // $indexedSchedules = array_values($payment_schedule->original);
        }
        $payment = $pay;
    }

        $loan_application_no = $payment['loan_application_no'];
        //throw new \Exception($loan_application_no);

    $loan_application = Loan_Application::where('loan_application_no', $loan_application_no)->first();

    $loanReleaseId = null;
    if(!is_null($loan_application))
    {
        //get first the loan app id
        $loanReleaseId = Loan_Release::where('loan_application_id', $loan_application->id)->where('passbook_number', $customer->passbook_no)->get();

    }

    //Empty schedule
    $schedules = '';

    if(!$loanReleaseId == null && !$loanReleaseId->isEmpty()){
        $loanReleaseId = Loan_Release::where('loan_application_id', $loan_application->id)->where('passbook_number', $customer->passbook_no)
        ->first();

        $loanReleaseId = (int)  $loanReleaseId['id'];
    }
    else
    {
        $loanReleaseId = null;
    }

    $schedules = Payment_Schedule::where('customer_id', $payment->customer_id)
    ->where('id', $payment_schedule_id)
    ->orWhere('loan_released_id', $loanReleaseId) //optional
    ->where('payment_status_code', 'like', '%Unpaid%')
    ->orWhere('payment_status_code', 'PARTIALLY PAID')
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

            //throw new \Exception($amountDue);

            // Full payment case
            $schedule->amount_paid += $amountDue;
            $schedule->payment_status_code = 'PAID';
            $schedule->save();

            $totalAmountPaid -= $amountDue; // Deduct the paid amount from total

            // Create a payment line for full payment
            $this->createPaymentLine($request, $payment, $schedule, $amountDue, 'APPROVED', $paymentLineService);

        } else {
            // Partial payment case
            $remainingBalance = $amountDue - $totalAmountPaid; // Calculate remaining balance after partial payment


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
}

protected function createPaymentLine($request, $payment, $schedule, $amountPaid, $remarks, PaymentLineServiceInterface $paymentLineService)
{

    //get the total balance of all the payment schedules
    $totals = Payment_Schedule::where('customer_id', $payment->customer_id)
        ->selectRaw('(SUM(amount_due) - SUM(amount_paid)) AS balance, SUM(amount_paid) AS paid, SUM(amount_due) AS due')
        ->first();

    (float) $balance = $totals->balance;

    $paymentLineData = [
        'payment_id' => $request['id'],
        'payment_schedule_id' => $schedule->id,
        'balance' => $balance - $amountPaid,
        'amount_paid' => $amountPaid,
        'remarks' => $remarks,
    ];


    // //convert object
    // $paymentLineData = new Request($paymentLineData);

    // // Save payment line
    // $payment = $paymentLineService->createPaymentLine($paymentLineData);

    // if (!$payment || !$payment->id) {
    //     throw new \Exception('Failed to create payment');
    // }

    // Define the conditions to identify the existing record
    $conditions = [
        'payment_id' => $request['id'],
        'payment_schedule_id' => $schedule->id,
    ];

    //throw new \Exception('' . $conditions['payment_id']);

    // Define the data to update or insert if the record does not exist
    $paymentLineData = [
        'balance' => $schedule->amount_due - $schedule->amount_paid,
        'amount_paid' => $amountPaid,
        'remarks' => $remarks, // Optional: Only include if you want to set remarks on create or update
    ];

    // Use updateOrCreate
    Payment_Line::updateOrCreate($conditions, $paymentLineData);

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

    public function paymentBarGraphData()
    {
        $payments = Payment::get();

        foreach($payments as $pay)
        {
            if(!is_null($pay))
            {

                //get the user and personality
                $personalityId = Customer::where('id', $pay['customer_id'])->first()->personality_id;
                $personality = Personality::where('id', $personalityId)->first();

                $pay['family_name'] = $personality['family_name'];
                $pay['first_name'] = $personality['first_name'];
                $pay['middle_name'] = $personality['middle_name'];
            }
        }

        //throw new \Exception($payments);


        return response()->json(['data' => $payments], Response::HTTP_OK);
    }

    public function paymentCustomerId(string $id)
    {
        $payment = Payment_Schedule::where('customer_id', $id)
        ->where('payment_status_code', 'like', '%Unpaid%')
        ->orWhere('payment_status_code', 'PARTIALLY PAID')
        ->get();



        foreach($payment as $pay)
        {
            if(!is_null($pay))
            {
                //get the total balance of all the payment schedules
                $totals = Payment_Schedule::where('customer_id', $id)
                ->where('id', $pay['id'])
                ->selectRaw('(SUM(amount_due) - SUM(amount_paid)) AS balance, SUM(amount_paid) AS paid, SUM(amount_due) AS due')
                ->first();

                $pay['balance'] = $pay['amount_due'] - $pay['amount_paid'];

                //throw new \Exception(($pay['amount_due'] - $pay['amount_paid']));
            }

            //throw new \Exception($pay);
        }


        if(is_null($payment))
        {
            throw new \Exception('There is no payments!');
        }

        return response()->json([
            'data' => $payment,
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

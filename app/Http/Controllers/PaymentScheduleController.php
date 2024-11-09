<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentScheduleStoreRequest;
use App\Http\Requests\PaymentScheduleUpdateRequest;
use App\Http\Resources\PaymentScheduleResource;
use App\Interface\Service\PaymentScheduleServiceInterface;
use App\Models\Loan_Application;
use App\Models\Loan_Release;
use App\Models\Payment_Schedule;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

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
    public function index(CustomerPersonalityController $customerPersonalityController)
    {
        // $payment = Payment_Schedule::where('payment_status_code', 'not like', '%FORWARDED%')
        // ->where('payment_status_code', 'not like', '%PAID%')
        // ->get(); // No arguments needed here

        $payment = Payment_Schedule::where('payment_status_code', 'like', '%UNPAID%')
        ->orWhere('payment_status_code', '=' ,'PARTIALLY PAID')
        ->get();



        $payment = PaymentScheduleResource::collection($payment);


        for($i = 0; $i < count($payment); $i++)
        {
            //search the customer id
            $customerPersonality = $customerPersonalityController->show($payment[$i]['customer_id']);

            $payment[$i]['family_name'] = " " . $customerPersonality->original['personality']['family_name'];
            $payment[$i]['first_name'] = " " . $customerPersonality->original['personality']['first_name'];
            $payment[$i]['middle_name'] = " " . $customerPersonality->original['personality']['middle_name'];

            // Adjust balance calculation to account for forwarded amounts
            //$originalDue = $payment[$i]['amount_due'] + $payment[$i]['amount_paid']; // or replace with stored original_amount_due if available
            $balance = $payment[$i]['balance'] = $payment[$i]['amount_due'] - $payment[$i]['amount_paid'];

            // return response()->json([
            //     'data' => $balance,
            // ], Response::HTTP_INTERNAL_SERVER_ERROR);


            if($payment[$i]['loan_released_id'] && $payment[$i]['loan_released_id'] > 0)
            {
                //get the loan application id
                $loanAppId = Loan_Release::where('id', $payment[$i]['loan_released_id'])->first()->loan_application_id;

                //get the loan_application_no
                $loanApplicationNo = Loan_Application::where('id', $loanAppId)->first()->loan_application_no;

                $payment[$i]['loan_application_no'] = $loanApplicationNo;
            }
            else
            {
                $loanApplications = DB::table('loan_application_fees AS laf')
                ->join('loan_application AS la', 'laf.loan_application_id', '=', 'la.id')
                ->where('la.customer_id', $payment[$i]['customer_id'])
                ->select('la.loan_application_no', 'laf.amount', 'la.customer_id', 'laf.loan_application_id')
                ->first();

                if(!is_null($loanApplications))
                {
                    $payment[$i]['loan_application_no'] = $loanApplications->loan_application_no;
                }

            }
        }

        return response()->json([
            'data' => $payment,
        ]);

        //'data' => $customerPersonality->original['customer']['id'],

    }

    public function indexAll(CustomerPersonalityController $customerPersonalityController)
    {
        // $payment = Payment_Schedule::where('payment_status_code', 'not like', '%FORWARDED%')
        // ->where('payment_status_code', 'not like', '%PAID%')
        // ->get(); // No arguments needed here

        $payment = Payment_Schedule::get();

        $payment = PaymentScheduleResource::collection($payment);

        // return response()->json([
        //         'data' => $payment,
        //     ], Response::HTTP_INTERNAL_SERVER_ERROR);

        for($i = 0; $i < count($payment); $i++)
        {
            //search the customer id
            $customerPersonality = $customerPersonalityController->show($payment[$i]['customer_id']);

            $payment[$i]['family_name'] = " " . $customerPersonality->original['personality']['family_name'];
            $payment[$i]['first_name'] = " " . $customerPersonality->original['personality']['first_name'];
            $payment[$i]['middle_name'] = " " . $customerPersonality->original['personality']['middle_name'];

            // Adjust balance calculation to account for forwarded amounts
            $originalDue = $payment[$i]['amount_due'] + $payment[$i]['amount_paid']; // or replace with stored original_amount_due if available
            $payment[$i]['balance'] = $originalDue - $payment[$i]['amount_paid'];


            if($payment[$i]['loan_released_id'] && $payment[$i]['loan_released_id'] > 0)
            {
                //get the loan application id
                $loanAppId = Loan_Release::where('id', $payment[$i]['loan_released_id'])->first()->loan_application_id;

                //get the loan_application_no
                $loanApplicationNo = Loan_Application::where('id', $loanAppId)->first()->loan_application_no;

                $payment[$i]['loan_application_no'] = $loanApplicationNo;
            }
            else
            {
                $loanApplications = DB::table('loan_application_fees AS laf')
                ->join('loan_application AS la', 'laf.loan_application_id', '=', 'la.id')
                ->where('la.customer_id', $payment[$i]['customer_id'])
                ->select('la.loan_application_no', 'laf.amount', 'la.customer_id', 'laf.loan_application_id')
                ->first();

                $payment[$i]['loan_application_no'] = $loanApplications->loan_application_no;

            }




        }

        return response()->json([
            'data' => $payment,
        ]);

        //'data' => $customerPersonality->original['customer']['id'],

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

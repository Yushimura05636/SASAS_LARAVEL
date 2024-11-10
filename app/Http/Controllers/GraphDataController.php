<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Payment;
use App\Models\Payment_Schedule;
use App\Models\Personality;
use Illuminate\Http\Response;

class GraphDataController extends Controller
{
    public function index()
    {
        $total_amount_receivables = Payment_Schedule::where('payment_status_code', 'like', '%Unpaid%')
            ->orWhere('payment_status_code', 'PARTIALLY PAID')
            ->selectRaw('SUM(amount_due) as amount_receivables')
            ->first(); // Use first() to get a single record

        // Access the sum value
        $total_amount_receivables = $total_amount_receivables->amount_receivables;

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

        //get the total borrowers
        $customers = Customer::get();

        //get the total customers
        $total_customers = count($customers);

        $total_groups = 0;
        $uniqueGroups = []; // Array to store unique group IDs

        foreach ($customers as $cus) {
            // Check if the customer and group_id are not null
            if (!is_null($cus) && isset($cus['group_id'])) {
                // Add group_id to the uniqueGroups array if it doesn't already exist
                if (!in_array($cus['group_id'], $uniqueGroups)) {
                    $uniqueGroups[] = $cus['group_id'];
                }
            }
        }

        // Count the total unique groups
        $total_groups = count($uniqueGroups);

        // throw new \Exception($total_amount_receivables);


        $data = [
            'total_customers' => $total_customers,
            'total_groups' => $total_groups,
            'payments' => $payments,
            'total_amount_recievables' => $total_amount_receivables,
        ];

        return response()->json(['data' => $data], Response::HTTP_OK);
    }
}

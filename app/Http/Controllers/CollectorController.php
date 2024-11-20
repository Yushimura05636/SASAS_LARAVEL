<?php

namespace App\Http\Controllers;

use App\Models\Loan_Application;
use App\Models\Payment_Schedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CollectorController extends Controller
{
    public function getCollectorIDAndGroupID()
    {
        $user = Auth::user(); // Get logged-in user

        if ($user) {
            // Log user details for debugging
            $debugData = [
                'user_id' => $user->id,
                'user_email' => $user->email,
            ];

            $customerGroup = $user->customerGroup;

            if ($customerGroup) {
                // Append fetched customer group data for debugging
                $debugData['customer_group'] = [
                    'collector_id' => $customerGroup->collector_id,
                    'group_id' => $customerGroup->id,
                    'description' => $customerGroup->description,
                ];

                return response()->json([
                    'success' => true,
                    'message' => 'Data fetched successfully',
                    'data' => $debugData,
                ]);
            }

            // No customer group found
            $debugData['customer_group'] = null;
            return response()->json([
                'success' => false,
                'message' => 'No customer group found',
                'data' => $debugData,
            ]);
        }

            // User not authenticated
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated',
                'data' => null,
            ]);
    }

    public function fetchToCollect(Request $request)
    {
            // Validate input
        $validatedData = $request->validate([
            'group_id' => 'required|exists:customer_group,id',
            'collector_id' => 'required|exists:user_account,id',
        ]);

        // Fetch payment schedules where payment_status_code is NOT "PAID"
        $paymentSchedules = Payment_Schedule::whereNotIn('payment_status_code', ['PAID', 'PARTIALLY PAID, FORWARDED']) // Exclude PAID
            ->whereHas('loanRelease.loanApplication', function ($query) use ($validatedData) {
                $query->where('group_id', $validatedData['group_id'])
                    ->whereHas('group', function ($subQuery) use ($validatedData) {
                        $subQuery->where('collector_id', $validatedData['collector_id']);
                    });
            })
            ->with([
                'loanRelease.loanApplication.group:id,description', // Fetch group details
                'loanRelease.loanApplication.customer.personality:id,first_name,middle_name,family_name', // Fetch customer details
            ])
            ->get()
            ->map(function ($schedule) {
                return [
                    'Group Name' => $schedule->loanRelease->loanApplication->group->description,
                    'Full Name' => trim("{$schedule->loanRelease->loanApplication->customer->personality->first_name} " .
                                        "{$schedule->loanRelease->loanApplication->customer->personality->middle_name} " .
                                        "{$schedule->loanRelease->loanApplication->customer->personality->family_name}"),
                    'Amount Due' => $schedule->amount_due,
                    'Due Date' => Carbon::parse($schedule->datetime_due)->format('F d Y'), // Format due date
                ];
            });

        $total_to_Collect = $paymentSchedules->sum('Amount Due');


        // Return response
        return response()->json([
            'success' => true,
            'data' => $paymentSchedules,
            'total_to_collect' => $total_to_Collect,

        ]);
    }





    public function fetchToCollected(Request $request)
    {
        $validatedData = $request->validate([
            'group_id' => 'required|exists:customer_group,id',
            'collector_id' => 'required|exists:user_account,id',
        ]);
        
        // Fetch payment schedules where payment_status_code is NOT "PAID"
        $paymentSchedules = Payment_Schedule::whereIn('payment_status_code', ['PAID', 'PARTIALLY PAID, FORWARDED']) // Exclude PAID
            ->whereHas('loanRelease.loanApplication', function ($query) use ($validatedData) {
                $query->where('group_id', $validatedData['group_id'])
                    ->whereHas('group', function ($subQuery) use ($validatedData) {
                        $subQuery->where('collector_id', $validatedData['collector_id']);
                    });
            })
            ->with([
                'loanRelease.loanApplication.group:id,description', // Fetch group details
                'loanRelease.loanApplication.customer.personality:id,first_name,middle_name,family_name', // Fetch customer details
            ])
            ->get()
            ->map(function ($schedule) {
                return [
                    'Payment Status' => $schedule->payment_status_code,
                    'Amount Paid' => $schedule->amount_paid,
                    'Customer Name' => trim("{$schedule->loanRelease->loanApplication->customer->personality->first_name} " .
                                            "{$schedule->loanRelease->loanApplication->customer->personality->middle_name} " .
                                            "{$schedule->loanRelease->loanApplication->customer->personality->family_name}"),
                    'Group Name' => $schedule->loanRelease->loanApplication->group->description,
                ];
            });
        
        $totalCollected = $paymentSchedules->sum('Amount Paid');

        // Return response
        return response()->json([
            'success' => true,
            'data' => $paymentSchedules,
            'total_collected' => $totalCollected,
        ]);

    }

}

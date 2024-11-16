<?php

namespace App\Http\Controllers;

use App\Models\Loan_Application;
use App\Models\Payment;
use App\Models\Payment_Schedule;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        // Fetch only approved payments with customer and personality information
        $approvedPayments = Payment::where('document_status_code', 'APPROVED')
            ->with('customer.personality:id,first_name,middle_name,family_name') // Eager load customer with personality data
            ->get(['id', 'customer_id', 'prepared_at', 'amount_paid']);
    
        // Calculate the total amount collected, ensuring only numeric values are included
        $totalAmount = $approvedPayments->sum(function($payment) {
            $amount = str_replace(',', '', $payment->amount_paid); // Remove commas
            return is_numeric($amount) ? $amount : 0; // Sum after ensuring it's numeric
        });
    
        // Transform data to include the full name from the personality (customer) in the output
        $approvedPayments->transform(function ($payment) {
            // Ensure prepared_at is a Carbon instance
            $preparedAt = Carbon::parse($payment->prepared_at);
    
            $firstName = $payment->customer->personality->first_name ?? '';
            $middleName = $payment->customer->personality->middle_name ?? '';
            $familyName = $payment->customer->personality->family_name ?? '';
    
            $fullName = trim("{$firstName} {$middleName} {$familyName}");
    
            // Remove commas from the amount and format it
            $amountPaid = str_replace(',', '', $payment->amount_paid);
            $amountPaidFormatted = number_format($amountPaid, 2);
    
            return [
                'id' => $payment->id,
                'customer_id' => $payment->customer_id,
                'customer_name' => $fullName ?: 'N/A', // Get the full name from personality relationship
                'prepared_at' => $payment->prepared_at,
                'amount_paid' => $amountPaidFormatted, // Correctly formatted amount
                'month' => $preparedAt->format('m-Y'), // Extract month and year (e.g., "2024-11")
            ];
        });
    
        // Group payments by customer
        $paymentsByCustomer = $approvedPayments->groupBy('customer_id');
    
        // Group payments by month
        $paymentsByMonth = $approvedPayments->groupBy('month');
    
        // Calculate total payments for each month
        $totalPaymentsByMonth = [];
        foreach ($paymentsByMonth as $month => $payments) {
            // Sum all the payments in the month after removing commas
            $totalPaymentsByMonth[$month] = number_format($payments->sum(function ($payment) {
                $amountPaid = str_replace(',', '', $payment['amount_paid']); // Remove commas
                return is_numeric($amountPaid) ? $amountPaid : 0; // Sum after ensuring it's numeric
            }), 2);
        }
    
        // Prepare report data
        $report = [
            'total_amount_collected' => number_format($totalAmount, 2),
            'total_payments_by_month' => $totalPaymentsByMonth, // Add total payments by month
            'payments_by_customer' => $paymentsByCustomer
        ];
    
        return response()->json($report);
    }
    

    public function feeReports()
{
    // Get the total amount paid for all relevant records
    $totalAmountCollected = Payment_Schedule::whereNull('loan_released_id')
        ->where('payment_status_code', 'PAID')
        ->selectRaw('SUM(amount_paid) as total_amount_paid')
        ->value('total_amount_paid');  // Use value() to get a single value directly

    // Get the customers with their payment details
    $customers = Payment_Schedule::whereNull('loan_released_id')
        ->where('payment_status_code', 'PAID')
        ->with('customer.personality:id,first_name,middle_name,family_name')
        ->select('id', 'customer_id', 'updated_at')
        ->selectRaw('SUM(amount_paid) as total_amount_paid')
        ->groupBy('id', 'customer_id', 'updated_at')
        ->get()
        ->map(function ($customer) {
            return [
                'id' => $customer->id,
                'customer_id' => $customer->customer_id,
                'total_amount_paid' => $customer->total_amount_paid,
                'updated_at' => $customer->updated_at,
                'first_name' => $customer->customer->personality->first_name,
                'middle_name' => $customer->customer->personality->middle_name,
                'family_name' => $customer->customer->personality->family_name,
            ];
        });

    // Get all payments to group by month
    $payments = Payment_Schedule::whereNull('loan_released_id')
        ->where('payment_status_code', 'PAID')
        ->with('customer.personality:id,first_name,middle_name,family_name')
        ->get(['id', 'customer_id', 'updated_at', 'amount_paid']);

    // Group payments by month
    $paymentsByMonth = $payments->groupBy(function($payment) {
        return Carbon::parse($payment->prepared_at)->format('m-Y'); // Format as "mm-yyyy"
    });

    // Calculate total payments for each month
    $totalPaymentsByMonth = [];
    foreach ($paymentsByMonth as $month => $payments) {
        // Sum all the payments in the month after removing commas
        $totalPaymentsByMonth[$month] = number_format($payments->sum(function ($payment) {
            $amountPaid = str_replace(',', '', $payment->amount_paid); // Remove commas
            return is_numeric($amountPaid) ? $amountPaid : 0; // Sum after ensuring it's numeric
        }), 2);
    }

    // Prepare the report data
    return [
        'total_amount_collected' => number_format($totalAmountCollected, 2),
        'customers' => $customers,
        'total_payments_by_month' => $totalPaymentsByMonth, // Include the total payments by month
    ];
}


public function Balances()
{
    $paymentSummary = Payment_Schedule::with('customer.personality:id,first_name,middle_name,family_name')
        ->selectRaw('
            customer_id, 
            datetime_due, 
            SUM(amount_due) as total_amount_due, 
            SUM(amount_paid) as total_amount_paid, 
            SUM(amount_due) - SUM(amount_paid) as balance_due
        ')
        ->groupBy('customer_id', 'datetime_due')
        ->get();

    $totalAmountToCollect = 0;
    $totalAmountPaid = 0;
    $totalUnpaidBalance = 0;
    $unpaidBalanceByMonth = [];
    $totalBalancePerCustomer = [];

    $paymentSummary->each(function ($payment) use (&$totalAmountToCollect, &$totalAmountPaid, &$totalUnpaidBalance, &$unpaidBalanceByMonth, &$totalBalancePerCustomer) {
        $totalAmountToCollect += $payment->total_amount_due;
        $totalAmountPaid += $payment->total_amount_paid;
        $totalUnpaidBalance += $payment->balance_due;

        $monthKey = Carbon::parse($payment->datetime_due)->format('m-Y');
        if (!isset($unpaidBalanceByMonth[$monthKey])) {
            $unpaidBalanceByMonth[$monthKey] = 0;
        }
        $unpaidBalanceByMonth[$monthKey] += $payment->balance_due;

        // Add customer-specific details
        $customer = $payment->customer;
        if (!isset($totalBalancePerCustomer[$payment->customer_id])) {
            $totalBalancePerCustomer[$payment->customer_id] = [
                'customer_id' => $payment->customer_id,
                'customer_name' => $customer && $customer->personality
                    ? "{$customer->personality->first_name} {$customer->personality->middle_name} {$customer->personality->family_name}"
                    : 'Unknown',
                'total_balance' => 0,
                'unsettled_balance' => 0,
                'latest_due_date' => Carbon::parse($payment->datetime_due)->format('d-m-Y H:i:s'),
            ];
        }

        $totalBalancePerCustomer[$payment->customer_id]['total_balance'] += $payment->balance_due;

        // Add to unsettled balance only if the balance_due is greater than zero
        if ($payment->balance_due > 0) {
            $totalBalancePerCustomer[$payment->customer_id]['unsettled_balance'] += $payment->balance_due;
        }

        // Update the latest_due_date if necessary
        $existingDate = Carbon::parse($totalBalancePerCustomer[$payment->customer_id]['latest_due_date']);
        $currentDate = Carbon::parse($payment->datetime_due);
        if ($currentDate->greaterThan($existingDate)) {
            $totalBalancePerCustomer[$payment->customer_id]['latest_due_date'] = $currentDate->format('d-m-Y H:i:s');
        }
    });

    $data = [
        'total_amount_to_collect' => number_format($totalAmountToCollect, 2),
        'total_amount_collected' => number_format($totalAmountPaid, 2),
        'total_unpaid_balance' => number_format($totalUnpaidBalance, 2),
        'unpaid_balance_by_month' => $unpaidBalanceByMonth,
        'total_balance_per_customer' => array_values($totalBalancePerCustomer), // Flatten associative array
    ];

    return response()->json($data);
}


public function getLoanDisbursementSummary()
    {
        $loanDisbursements = Loan_Application::with('customer.personality:id,first_name,middle_name,family_name')
            ->selectRaw('
                customer_id, 
                datetime_approved, 
                SUM(amount_loan) as total_loan_amount, 
                SUM(amount_interest) as total_amount_interest
            ')
            ->where('document_status_code', 2) // Filter by document_status_code = 2
            ->groupBy('customer_id', 'datetime_approved') // Group by customer_id and approval datetime
            ->get();

        // Initialize variables
        $totalLoanAmount = 0;
        $totalInterestAmount = 0;
        $disbursedByMonth = [];
        $totalLoanPerCustomer = [];

        $loanDisbursements->each(function ($loan) use (&$totalLoanAmount, &$totalInterestAmount, &$disbursedByMonth, &$totalLoanPerCustomer) {
            // Update total amounts
            $totalLoanAmount += $loan->total_loan_amount;
            $totalInterestAmount += $loan->total_amount_interest;

            // Format the approval month (MM-YYYY)
            $monthKey = Carbon::parse($loan->datetime_approved)->format('m-Y');
            if (!isset($disbursedByMonth[$monthKey])) {
                $disbursedByMonth[$monthKey] = 0;
            }
            $disbursedByMonth[$monthKey] += $loan->total_loan_amount;

            // Add customer-specific details
            $customer = $loan->customer;
            if (!isset($totalLoanPerCustomer[$loan->customer_id])) {
                $totalLoanPerCustomer[$loan->customer_id] = [
                    'customer_id' => $loan->customer_id,
                    'customer_name' => $customer && $customer->personality
                        ? "{$customer->personality->first_name} {$customer->personality->middle_name} {$customer->personality->family_name}"
                        : 'Unknown',
                    'total_loan_amount' => 0,
                    'total_amount_interest' => 0,
                ];
            }

            // Add the loan amount and interest to the customer's totals
            $totalLoanPerCustomer[$loan->customer_id]['total_loan_amount'] += $loan->total_loan_amount;
            $totalLoanPerCustomer[$loan->customer_id]['total_amount_interest'] += $loan->total_amount_interest;
        });

        $data = [
            'total_loan_amount' => number_format($totalLoanAmount, 2),
            'total_amount_interest' => number_format($totalInterestAmount, 2),
            'disbursed_by_month' => $disbursedByMonth,
            'total_loan_per_customer' => array_values($totalLoanPerCustomer), // Flatten associative array
        ];

        return response()->json($data);
    }

}

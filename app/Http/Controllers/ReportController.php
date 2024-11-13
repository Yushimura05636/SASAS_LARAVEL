<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        // Fetch only approved payments with customer and personality information
        $approvedPayments = Payment::where('document_status_code', 'APPROVED')
        ->with('customer.personality:id,first_name,middle_name,family_name') // Eager load customer with personality data
        ->get(['id', 'customer_id', 'prepared_at', 'amount_paid']);

        // Calculate the total amount collected
        $totalAmount = $approvedPayments->sum('amount_paid');

        // Transform data to include the full name from the personality (customer) in the output
        $approvedPayments->transform(function ($payment) {
        // Ensure prepared_at is a Carbon instance
        $preparedAt = Carbon::parse($payment->prepared_at);

        $firstName = $payment->customer->personality->first_name ?? '';
        $middleName = $payment->customer->personality->middle_name ?? '';
        $familyName = $payment->customer->personality->family_name ?? '';

        $fullName = trim("{$firstName} {$middleName} {$familyName}");

        return [
            'id' => $payment->id,
            'customer_id' => $payment->customer_id,
            'customer_name' => $fullName ?: 'N/A', // Get the full name from personality relationship
            'prepared_at' => $payment->prepared_at,
            'amount_paid' => number_format($payment->amount_paid, 2),
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
        $totalPaymentsByMonth[$month] = number_format($payments->sum('amount_paid'), 2);
        }

        // Prepare report data
        $report = [
        'total_amount_collected' => number_format($totalAmount, 2),
        'total_payments_by_month' => $totalPaymentsByMonth, // Add total payments by month
        'payments_by_customer' => $paymentsByCustomer
        ];

        return response()->json($report);

    }
}

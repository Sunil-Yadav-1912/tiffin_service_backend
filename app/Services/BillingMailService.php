<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\User;
use App\Models\Notification;
use App\Mail\MonthlyBillingSummaryMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BillingMailService
{
    /**
     * Normalize month-year string to standard MM-YYYY format.
     */
    public static function normalizeMonthYear(?string $monthYear = null): string
    {
        if (!$monthYear) {
            return Carbon::today()->format('m-Y');
        }

        $cleaned = str_replace('/', '-', trim($monthYear));
        $parts = explode('-', $cleaned);

        if (count($parts) === 2) {
            if (strlen($parts[0]) === 4) {
                // YYYY-MM -> MM-YYYY
                return str_pad($parts[1], 2, '0', STR_PAD_LEFT) . '-' . $parts[0];
            } else {
                return str_pad($parts[0], 2, '0', STR_PAD_LEFT) . '-' . $parts[1];
            }
        }

        return $monthYear;
    }

    /**
     * Generate or update monthly bills for all active mess customers.
     */
    public static function generateBillsForMonth(string $monthYear)
    {
        $monthYear = self::normalizeMonthYear($monthYear);

        // Process only mess subscribers
        $customers = User::where('role', 'Customer')
            ->whereHas('tiffins', function ($q) {
                $q->where('category', 'Monthly')->orWhere('price', '>=', 500);
            })
            ->with(['tiffins' => function ($q) {
                $q->where('category', 'Monthly')->orWhere('price', '>=', 500);
            }])
            ->get();

        foreach ($customers as $customer) {
            $totalAmount = 0;

            foreach ($customer->tiffins as $tiffin) {
                // Fixed monthly mess subscription fee
                $totalAmount += (float) $tiffin->price;
            }

            if ($totalAmount > 0) {
                $existingBill = Bill::where('user_id', $customer->id)
                    ->where('month_year', $monthYear)
                    ->first();

                $availableAdvance = (float) $customer->advance_payment + ($existingBill ? (float) $existingBill->advance_deducted : 0);

                $advanceDeducted = min($totalAmount, $availableAdvance);
                $payableAmount = $totalAmount - $advanceDeducted;

                Bill::updateOrCreate(
                    [
                        'user_id' => $customer->id,
                        'month_year' => $monthYear,
                    ],
                    [
                        'total_amount' => $totalAmount,
                        'advance_deducted' => $advanceDeducted,
                        'payable_amount' => $payableAmount,
                    ]
                );

                // Update customer's advance balance
                $customer->advance_payment = max(0, $availableAdvance - $advanceDeducted);
                $customer->save();
            }
        }

        return Bill::with(['user.tiffins'])
            ->where('month_year', $monthYear)
            ->orderBy('id', 'asc')
            ->get();
    }

    /**
     * Generate and dispatch the monthly billing summary email to all admins.
     */
    public static function sendMonthlyBillingSummary(?string $monthYear = null, ?string $targetEmail = null): array
    {
        $monthYear = self::normalizeMonthYear($monthYear);

        try {
            $readableMonth = Carbon::createFromFormat('m-Y', $monthYear)->format('F Y');
        } catch (\Throwable $e) {
            $readableMonth = $monthYear;
        }

        // 1. Ensure bills are generated/updated
        $bills = self::generateBillsForMonth($monthYear);

        // 2. Compute financial summary KPIs
        $totalCustomers = $bills->count();
        $totalGross = (float) $bills->sum('total_amount');
        $totalAdvanceDeducted = (float) $bills->sum('advance_deducted');
        $totalPayable = (float) $bills->sum('payable_amount');

        $paidBills = $bills->filter(fn($b) => strtolower($b->status) === 'paid');
        $unpaidBills = $bills->filter(fn($b) => strtolower($b->status) !== 'paid');

        $summary = [
            'total_customers' => $totalCustomers,
            'total_gross' => $totalGross,
            'total_advance_deducted' => $totalAdvanceDeducted,
            'total_payable' => $totalPayable,
            'paid_count' => $paidBills->count(),
            'unpaid_count' => $unpaidBills->count(),
            'paid_amount' => (float) $paidBills->sum('payable_amount'),
            'unpaid_amount' => (float) $unpaidBills->sum('payable_amount'),
        ];

        // 3. Determine recipient list
        $recipients = [];
        if (!empty($targetEmail)) {
            $recipients[] = trim($targetEmail);
        } else {
            // Find all admin users from DB
            $adminEmails = User::where('role', 'Admin')
                ->whereNotNull('email')
                ->pluck('email')
                ->map(fn($e) => trim($e))
                ->filter()
                ->unique()
                ->values()
                ->all();

            $recipients = array_merge($recipients, $adminEmails);

            // Add fallback from .env if present
            $envAdminEmail = env('ADMIN_EMAIL');
            if ($envAdminEmail && !in_array($envAdminEmail, $recipients)) {
                $recipients[] = trim($envAdminEmail);
            }

            // Ultimate fallback to mail from address
            if (empty($recipients)) {
                $recipients[] = config('mail.from.address', 'admin@crownoragroup.co.in');
            }
        }

        $recipients = array_values(array_unique(array_filter($recipients)));

        // 4. Send emails
        $sentTo = [];
        $failedTo = [];

        foreach ($recipients as $recipient) {
            try {
                Mail::to($recipient)->send(new MonthlyBillingSummaryMail($monthYear, $bills, $summary));
                $sentTo[] = $recipient;
            } catch (\Throwable $e) {
                Log::error("Failed to send monthly billing email to {$recipient}: " . $e->getMessage());
                $failedTo[] = ['email' => $recipient, 'error' => $e->getMessage()];
            }
        }

        // 5. Create in-app Admin Notification
        try {
            Notification::create([
                'type' => 'monthly_bills_summary',
                'title' => "Monthly Bills Emailed ({$readableMonth})",
                'message' => "Automated monthly billing statement for {$readableMonth} sent to " . implode(', ', $sentTo) . " with {$totalCustomers} customer bills totaling ₹" . number_format($totalPayable, 2),
                'read' => false,
                'meta' => [
                    'month_year' => $monthYear,
                    'recipients' => $sentTo,
                    'bills_count' => $totalCustomers,
                    'total_payable' => $totalPayable,
                ],
            ]);

            // Also send Web Push to admin devices
            WebPushService::notifyAdmins(
                "Monthly Bills Generated ({$readableMonth})",
                "Bills for {$totalCustomers} customers totaling ₹" . number_format($totalPayable, 2) . " have been generated and emailed to admin.",
                [
                    'url' => '/admin/dashboard',
                    'type' => 'monthly_bills_summary',
                ]
            );
        } catch (\Throwable $e) {
            Log::warning('Error creating notification for monthly bills email: ' . $e->getMessage());
        }

        Log::info("Monthly billing summary for {$monthYear} dispatched to: " . implode(', ', $sentTo));

        return [
            'success' => count($sentTo) > 0,
            'message' => count($sentTo) > 0
                ? "Monthly bills summary for {$readableMonth} successfully emailed to: " . implode(', ', $sentTo)
                : "No emails could be delivered. Please verify mail configuration.",
            'recipients_sent' => $sentTo,
            'recipients_failed' => $failedTo,
            'month_year' => $monthYear,
            'readable_month' => $readableMonth,
            'bills_count' => $totalCustomers,
            'summary' => $summary,
        ];
    }
}


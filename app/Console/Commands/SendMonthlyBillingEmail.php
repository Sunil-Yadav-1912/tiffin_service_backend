<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BillingMailService;

class SendMonthlyBillingEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bills:send-monthly-summary 
                            {--month= : Specific month-year in MM-YYYY format (defaults to current month)}
                            {--email= : Specific email address to send the summary to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate monthly customer bills and trigger automated email summary to admin';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $month = $this->option('month');
        $email = $this->option('email');

        $this->info('Starting monthly customer bills generation and email trigger...');

        $result = BillingMailService::sendMonthlyBillingSummary($month, $email);

        if ($result['success']) {
            $this->info("✓ " . $result['message']);
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Billing Period', $result['readable_month'] . ' (' . $result['month_year'] . ')'],
                    ['Subscribers Billed', $result['bills_count']],
                    ['Gross Total', '₹' . number_format($result['summary']['total_gross'], 2)],
                    ['Advance Adjusted', '-₹' . number_format($result['summary']['total_advance_deducted'], 2)],
                    ['Net Payable Amount', '₹' . number_format($result['summary']['total_payable'], 2)],
                    ['Unpaid Count', $result['summary']['unpaid_count']],
                    ['Paid Count', $result['summary']['paid_count']],
                    ['Recipients', implode(', ', $result['recipients_sent'])],
                ]
            );
            return Command::SUCCESS;
        } else {
            $this->error("✗ Failed to send billing email.");
            if (!empty($result['recipients_failed'])) {
                foreach ($result['recipients_failed'] as $f) {
                    $this->error(" - {$f['email']}: {$f['error']}");
                }
            }
            return Command::FAILURE;
        }
    }
}


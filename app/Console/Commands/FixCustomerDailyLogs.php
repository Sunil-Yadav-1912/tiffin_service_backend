<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\DailyLog;

class FixCustomerDailyLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:cleanup-prejoining {--dry-run : Preview which logs would be deleted without deleting them}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up daily logs erroneously created for dates before each customer joined/registered';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');

        $this->info($isDryRun 
            ? '🔍 Running pre-joining logs check in DRY-RUN mode (no records will be modified)...'
            : '🧹 Cleaning up pre-joining daily logs for existing customers...'
        );

        $customers = User::where('role', 'Customer')->get();

        if ($customers->isEmpty()) {
            $this->warn('No customers found in the database.');
            return 0;
        }

        $totalPurged = 0;
        $affectedCustomers = 0;
        $tableData = [];

        foreach ($customers as $customer) {
            if (!$customer->created_at) {
                continue;
            }

            $joinDate = $customer->created_at->toDateString();

            // Find logs with date strictly before the join date
            $query = DailyLog::where('user_id', $customer->id)
                ->where('date', '<', $joinDate);

            $count = $query->count();

            if ($count > 0) {
                $affectedCustomers++;
                $totalPurged += $count;

                $datesSample = $query->pluck('date')->unique()->sort()->values();
                $dateRange = $datesSample->first() . ' to ' . $datesSample->last();

                if (!$isDryRun) {
                    $query->delete();
                }

                $tableData[] = [
                    $customer->id,
                    $customer->name,
                    $customer->email,
                    $joinDate,
                    $count,
                    $dateRange,
                    $isDryRun ? 'Would Delete' : 'Deleted'
                ];
            }
        }

        if (count($tableData) > 0) {
            $this->table(
                ['User ID', 'Name', 'Email', 'Joined Date', 'Invalid Logs', 'Affected Dates', 'Status'],
                $tableData
            );

            if ($isDryRun) {
                $this->warn("Found {$totalPurged} pre-joining logs across {$affectedCustomers} customer(s) that should be removed.");
                $this->info("To delete them, run: php artisan logs:cleanup-prejoining");
            } else {
                $this->info("✓ Successfully purged {$totalPurged} pre-joining logs across {$affectedCustomers} customer(s).");
            }
        } else {
            $this->info("✓ All clean! No customers have daily logs created prior to their joining date.");
        }

        return 0;
    }
}

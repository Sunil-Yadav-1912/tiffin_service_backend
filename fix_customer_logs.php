<?php

// Standalone script to clean up pre-joining daily logs for existing customers
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\DailyLog;

echo "========================================================\n";
echo "   Crownora Group - Pre-Joining Logs Cleanup Utility     \n";
echo "========================================================\n\n";

$customers = User::where('role', 'Customer')->get();

if ($customers->isEmpty()) {
    echo "No customers found in the database.\n";
    exit(0);
}

$totalPurged = 0;
$affectedCustomers = 0;

foreach ($customers as $customer) {
    if (!$customer->created_at) {
        continue;
    }

    $joinDate = $customer->created_at->toDateString();

    $query = DailyLog::where('user_id', $customer->id)
        ->where('date', '<', $joinDate);

    $count = $query->count();

    if ($count > 0) {
        $affectedCustomers++;
        $totalPurged += $count;

        $datesSample = $query->pluck('date')->unique()->sort()->values();
        $dateRange = $datesSample->first() . ' to ' . $datesSample->last();

        $query->delete();

        echo "[FIXED] Customer #{$customer->id} ({$customer->name} - {$customer->email})\n";
        echo "        Joined Date: {$joinDate}\n";
        echo "        Purged {$count} invalid daily log(s) spanning {$dateRange}\n\n";
    }
}

if ($totalPurged > 0) {
    echo "--------------------------------------------------------\n";
    echo "SUCCESS: Cleaned {$totalPurged} pre-joining logs across {$affectedCustomers} customer(s).\n";
} else {
    echo "ALL CLEAN: No existing customers have daily logs prior to their joining date.\n";
}
echo "========================================================\n";

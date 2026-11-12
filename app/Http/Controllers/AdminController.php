<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Tiffin;
use App\Models\DailyLog;
use App\Models\Bill;
use App\Models\Notification;
use App\Models\PushSubscription;
use App\Services\WebPushService;
use App\Services\BillingMailService;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function index() // lists customers
    {
        return response()->json(User::where('role', 'Customer')->with(['tiffins', 'leaves'])->get());
    }

    public function store(Request $request) // create customer
    {
        // simplistic implementation
        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'advance_payment' => 'nullable|numeric|min:0',
            'tiffin_ids' => 'nullable|array',
            'tiffin_ids.*' => 'exists:tiffins,id'
        ]);

        $validated['password'] = bcrypt($validated['password']);
        $validated['role'] = 'Customer';

        $user = User::create($validated);

        if (!empty($validated['tiffin_ids'])) {
            $user->tiffins()->sync($validated['tiffin_ids']);
        }

        Notification::create([
            'user_id' => $user->id,
            'type' => 'new_customer',
            'title' => 'New Customer Added',
            'message' => "{$user->name} ({$user->email}) was added by Admin.",
            'read' => false,
            'meta' => [
                'phone' => $user->phone,
                'address' => $user->address,
                'email' => $user->email,
                'name' => $user->name,
            ],
        ]);

        WebPushService::notifyAdmins(
            'New Customer Added',
            "{$user->name} ({$user->email}) was added by Admin.",
            ['phone' => $user->phone, 'email' => $user->email]
        );

        return response()->json(['message' => 'Customer created', 'user' => $user->load('tiffins')]);
    }

    public function show($id)
    {
        return response()->json(User::with('tiffins', 'leaves', 'dailyLogs', 'bills')->findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->update($request->except('tiffin_ids'));

        if ($request->has('tiffin_ids')) {
            $user->tiffins()->sync($request->tiffin_ids);
        }
        return response()->json(['message' => 'Customer updated', 'user' => $user->load('tiffins')]);
    }

    public function destroy($id)
    {
        User::destroy($id);
        return response()->json(['message' => 'Customer deleted']);
    }

    // Daily Logs
    public function getLogsByDate(Request $request)
    {
        $date = $request->query('date', Carbon::today()->toDateString());

        // For all customers who have mess subscriptions, ensure a log exists for this date.
        // Default to 'Cancelled' if they are on leave, or 'Delivered' by default.
        $messCustomers = User::where('role', 'Customer')
            ->whereHas('tiffins', function ($q) {
                $q->where('category', 'Monthly')->orWhere('price', '>=', 500);
            })
            ->with(['tiffins' => function ($q) {
                $q->where('category', 'Monthly')->orWhere('price', '>=', 500);
            }, 'leaves'])
            ->get();

        foreach ($messCustomers as $customer) {
            $isOnLeave = $customer->leaves->contains(function ($leave) use ($date) {
                return $date >= $leave->from_date && $date <= $leave->to_date;
            });

            foreach ($customer->tiffins as $tiffin) {
                DailyLog::firstOrCreate(
                    [
                        'user_id' => $customer->id,
                        'tiffin_id' => $tiffin->id,
                        'date' => $date,
                    ],
                    [
                        'status' => $isOnLeave ? 'Cancelled' : 'Delivered',
                    ]
                );
            }
        }

        return response()->json(DailyLog::with('user', 'tiffin')->whereDate('date', $date)->get());
    }

    public function bulkUpdateLogs(Request $request)
    {
        // Now handling single update-or-create for a user-tiffin-date combination
        $validated = $request->validate([
            'user_id' => 'required',
            'tiffin_id' => 'required',
            'date' => 'required|date',
            'status' => 'required|in:Delivered,Cancelled,Pending'
        ]);

        DailyLog::updateOrCreate(
            [
                'user_id' => $validated['user_id'],
                'tiffin_id' => $validated['tiffin_id'],
                'date' => $validated['date']
            ],
            [
                'status' => $validated['status']
            ]
        );
        return response()->json(['message' => 'Log updated successfully']);
    }

    // Billing
    public function generateBills(Request $request)
    {
        $inputMonthYear = $request->input('month_year');
        $monthYear = BillingMailService::normalizeMonthYear($inputMonthYear);
        $generatedBills = BillingMailService::generateBillsForMonth($monthYear);

        return response()->json([
            'message' => "Bills successfully generated for $monthYear.",
            'bills' => $generatedBills
        ]);
    }

    public function sendMonthlyBillingEmail(Request $request)
    {
        $monthYear = $request->input('month_year');
        $email = $request->input('email');

        $result = BillingMailService::sendMonthlyBillingSummary($monthYear, $email);

        return response()->json($result);
    }

    public function previewMonthlyBillingEmail(Request $request)
    {
        $monthYear = BillingMailService::normalizeMonthYear($request->input('month_year'));
        $bills = BillingMailService::generateBillsForMonth($monthYear);

        $summary = [
            'total_customers' => $bills->count(),
            'total_gross' => (float) $bills->sum('total_amount'),
            'total_advance_deducted' => (float) $bills->sum('advance_deducted'),
            'total_payable' => (float) $bills->sum('payable_amount'),
            'paid_count' => $bills->filter(fn($b) => strtolower($b->status) === 'paid')->count(),
            'unpaid_count' => $bills->filter(fn($b) => strtolower($b->status) !== 'paid')->count(),
            'paid_amount' => (float) $bills->filter(fn($b) => strtolower($b->status) === 'paid')->sum('payable_amount'),
            'unpaid_amount' => (float) $bills->filter(fn($b) => strtolower($b->status) !== 'paid')->sum('payable_amount'),
        ];

        return new \App\Mail\MonthlyBillingSummaryMail($monthYear, $bills, $summary);
    }

    public function getBills(Request $request)
    {
        $monthYear = $request->query('month_year');
        $query = Bill::with(['user.tiffins'])->orderBy('created_at', 'desc');

        if ($monthYear) {
            $parts = explode('-', $monthYear);
            if (count($parts) === 2) {
                $normalizedMonthYear = str_pad($parts[0], 2, '0', STR_PAD_LEFT) . '-' . $parts[1];
                $query->where('month_year', $normalizedMonthYear);
            } else {
                $query->where('month_year', $monthYear);
            }
        }

        return response()->json($query->get());
    }

    public function updateBillStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:Paid,Unpaid'
        ]);

        $bill = Bill::findOrFail($id);
        $bill->status = $validated['status'];
        $bill->save();

        return response()->json(['message' => 'Bill status updated successfully', 'bill' => $bill]);
    }

    // Notifications
    public function getNotifications(Request $request)
    {
        $notifications = Notification::with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($notifications);
    }

    public function markNotificationRead($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->read = true;
        $notification->save();

        return response()->json([
            'message' => 'Notification marked as read',
            'notification' => $notification
        ]);
    }

    public function markAllNotificationsRead()
    {
        Notification::where('read', false)->update(['read' => true]);

        return response()->json(['message' => 'All notifications marked as read']);
    }

    // Push Notifications
    public function getVapidPublicKey()
    {
        return response()->json(['public_key' => WebPushService::getPublicKey()]);
    }

    public function savePushSubscription(Request $request)
    {
        $validated = $request->validate([
            'endpoint' => 'required|string',
            'keys' => 'required|array',
            'keys.p256dh' => 'required|string',
            'keys.auth' => 'required|string',
        ]);

        $sub = PushSubscription::updateOrCreate(
            ['endpoint' => $validated['endpoint']],
            [
                'user_id' => $request->user()->id,
                'public_key' => $request->input('keys.p256dh'),
                'auth_token' => $request->input('keys.auth'),
                'content_encoding' => $request->input('content_encoding', 'aesgcm'),
            ]
        );

        return response()->json(['message' => 'Push subscription saved successfully', 'subscription' => $sub]);
    }
}

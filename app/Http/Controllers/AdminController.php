<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Tiffin;
use App\Models\DailyLog;
use App\Models\Bill;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function index() // lists customers
    {
        return response()->json(User::where('role', 'Customer')->with('tiffins')->get());
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
        $validated = $request->validate([
            'month_year' => 'nullable|date_format:m-Y',
        ]);

        $monthYear = $validated['month_year'] ?? Carbon::today()->format('m-Y');
        $date = Carbon::createFromFormat('m-Y', $monthYear);

        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        $customers = User::where('role', 'Customer')->has('tiffins')->with('tiffins')->get();

        $generatedBills = [];

        foreach ($customers as $customer) {
            $totalAmount = 0;

            foreach ($customer->tiffins as $tiffin) {
                // If daily_logs has tiffin_id, use it. But since older logs might not have it,
                // and the user might just want a flat monthly calculation if logs aren't perfect,
                // wait, the prompt says "When i am marking cancelled or delivered... nothing changes".
                // If a user has multiple tiffins, the daily log NEEDS a tiffin_id.
                // Let's count delivered logs for this specific tiffin.
                $deliveredCount = DailyLog::where('user_id', $customer->id)
                    ->where('tiffin_id', $tiffin->id)
                    ->where('status', 'Delivered')
                    ->whereBetween('date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
                    ->count();

                $totalAmount += ($deliveredCount * $tiffin->price);
            }

            if ($totalAmount > 0) {
                $advancePayment = $customer->advance_payment;

                $advanceDeducted = min($totalAmount, $advancePayment);
                $payableAmount = $totalAmount - $advanceDeducted;

                $bill = Bill::updateOrCreate(
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

                // Deduct the advance from user
                if ($advanceDeducted > 0 && $bill->wasRecentlyCreated) {
                    $customer->advance_payment -= $advanceDeducted;
                    $customer->save();
                }

                $generatedBills[] = $bill;
            }
        }

        return response()->json([
            'message' => "Bills successfully generated for $monthYear.",
            'bills' => $generatedBills
        ]);
    }
}

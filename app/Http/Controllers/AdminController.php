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
        if ($inputMonthYear) {
            $parts = explode('-', $inputMonthYear);
            if (count($parts) === 2) {
                $monthYear = str_pad($parts[0], 2, '0', STR_PAD_LEFT) . '-' . $parts[1];
            } else {
                $monthYear = $inputMonthYear;
            }
        } else {
            $monthYear = Carbon::today()->format('m-Y');
        }

        $date = Carbon::createFromFormat('m-Y', $monthYear);
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        // Process only mess subscribers
        $customers = User::where('role', 'Customer')
            ->whereHas('tiffins', function ($q) {
                $q->where('category', 'Monthly')->orWhere('price', '>=', 500);
            })
            ->with(['tiffins' => function ($q) {
                $q->where('category', 'Monthly')->orWhere('price', '>=', 500);
            }])
            ->get();

        $generatedBills = [];

        foreach ($customers as $customer) {
            $totalAmount = 0;

            foreach ($customer->tiffins as $tiffin) {
                // Fixed monthly mess subscription fee (No refund policy for skipping)
                $totalAmount += $tiffin->price;
            }

            if ($totalAmount > 0) {
                $existingBill = Bill::where('user_id', $customer->id)
                    ->where('month_year', $monthYear)
                    ->first();
                
                $availableAdvance = $customer->advance_payment + ($existingBill ? $existingBill->advance_deducted : 0);

                $advanceDeducted = min($totalAmount, $availableAdvance);
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

                // Update the customer's balance accurately
                $customer->advance_payment = $availableAdvance - $advanceDeducted;
                $customer->save();

                $generatedBills[] = $bill;
            }
        }

        return response()->json([
            'message' => "Bills successfully generated for $monthYear.",
            'bills' => $generatedBills
        ]);
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
}

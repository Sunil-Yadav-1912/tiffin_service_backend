<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Leave;
use App\Models\DailyLog;
use App\Models\Bill;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class CustomerController extends Controller
{
    public function getProfile(Request $request)
    {
        return response()->json($request->user()->load('tiffins'));
    }

    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'tiffin_ids' => 'nullable|array',
            'tiffin_ids.*' => 'exists:tiffins,id'
        ]);

        $request->user()->update($request->except('tiffin_ids'));

        if ($request->has('tiffin_ids')) {
            $request->user()->tiffins()->sync($validated['tiffin_ids']);
        }

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $request->user()->fresh('tiffins')
        ]);
    }

    public function index(Request $request) // For leaves
    {
        return response()->json($request->user()->leaves);
    }

    public function store(Request $request) // Store leave
    {
        $validated = $request->validate([
            'from_date' => 'required|date|after_or_equal:today',
            'to_date' => 'required|date|after_or_equal:from_date',
        ]);

        $user = $request->user();
        $leave = $user->leaves()->create($validated);

        // Explictly create 'Cancelled' logs for all days in the range so they appear immediately in UI
        $period = CarbonPeriod::create($validated['from_date'], $validated['to_date']);
        $tiffinIds = $user->tiffins()->pluck('tiffins.id');

        foreach ($period as $date) {
            foreach ($tiffinIds as $tiffinId) {
                DailyLog::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'tiffin_id' => $tiffinId,
                        'date' => $date->toDateString(),
                    ],
                    [
                        'status' => 'Cancelled'
                    ]
                );
            }
        }

        return response()->json(['message' => 'Leave scheduled successfully', 'leave' => $leave]);
    }

    public function destroy(Request $request, $id) // Delete leave
    {
        $leave = $request->user()->leaves()->findOrFail($id);
        
        // Delete the Cancelled daily logs that were created for this leave
        DailyLog::where('user_id', $request->user()->id)
            ->whereBetween('date', [$leave->from_date, $leave->to_date])
            ->where('status', 'Cancelled')
            ->delete();

        $leave->delete();

        return response()->json(['message' => 'Leave cancelled successfully']);
    }

    public function getLogs(Request $request)
    {
        $user = $request->user()->load(['tiffins' => function ($q) {
            $q->where('category', 'Monthly')->orWhere('price', '>=', 500);
        }, 'leaves']);

        $today = Carbon::today();
        $startOfMonth = $today->copy()->startOfMonth();

        // Ensure logs exist from start of month up to today for subscribed mess tiffins
        if ($user->tiffins->isNotEmpty()) {
            for ($date = $startOfMonth->copy(); $date->lte($today); $date->addDay()) {
                $dateStr = $date->toDateString();
                $isOnLeave = $user->leaves->contains(function ($leave) use ($dateStr) {
                    return $dateStr >= $leave->from_date && $dateStr <= $leave->to_date;
                });

                foreach ($user->tiffins as $tiffin) {
                    DailyLog::firstOrCreate(
                        [
                            'user_id' => $user->id,
                            'tiffin_id' => $tiffin->id,
                            'date' => $dateStr,
                        ],
                        [
                            'status' => $isOnLeave ? 'Cancelled' : 'Delivered',
                        ]
                    );
                }
            }
        }

        return response()->json($request->user()->dailyLogs()->orderBy('date', 'desc')->get());
    }

    public function getBills(Request $request)
    {
        return response()->json($request->user()->bills()->orderBy('month_year', 'desc')->get());
    }
}

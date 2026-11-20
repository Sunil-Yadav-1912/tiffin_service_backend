<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Notification;
use App\Services\WebPushService;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'tiffin_ids' => 'nullable|array',
            'tiffin_ids.*' => 'exists:tiffins,id',
            'tiffin_id' => 'nullable|exists:tiffins,id',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'role' => 'Customer',
            'advance_payment' => 0,
        ]);

        $tiffinIds = [];
        if (!empty($validated['tiffin_ids'])) {
            $tiffinIds = $validated['tiffin_ids'];
        } elseif (!empty($validated['tiffin_id'])) {
            $tiffinIds = [$validated['tiffin_id']];
        }

        if (!empty($tiffinIds)) {
            $user->tiffins()->sync($tiffinIds);
            $user->update(['selected_tiffin_id' => $tiffinIds[0]]);
        }

        $planNames = $user->tiffins()->pluck('name')->join(', ');
        $planDetail = $planNames ? " (Plan: {$planNames})" : "";

        // Create Admin Notification for newly registered customer
        Notification::create([
            'user_id' => $user->id,
            'type' => 'new_customer',
            'title' => 'New Customer Registered',
            'message' => "{$user->name} ({$user->email}) has registered on Crownora Group{$planDetail}.",
            'read' => false,
            'meta' => [
                'phone' => $user->phone,
                'address' => $user->address,
                'email' => $user->email,
                'name' => $user->name,
                'meal_plan' => $planNames,
                'tiffin_ids' => $tiffinIds,
            ],
        ]);

        // Send instant Web Push Notification to all subscribed admin devices (wakes up mobile/desktop even if browser is closed)
        WebPushService::notifyAdmins(
            'New Customer Registered',
            "{$user->name} ({$user->email}) has registered on Crownora Group{$planDetail}.",
            [
                'url' => '/admin/dashboard',
                'phone' => $user->phone,
                'email' => $user->email,
                'type' => 'new_customer',
                'meal_plan' => $planNames,
            ]
        );

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful',
            'access_token' => $token,
            'user' => $user->load('tiffins')
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::with('tiffins')->where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'access_token' => $token,
            'user' => $user
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Tiffin;
use App\Models\DailyLog;
use App\Models\Leave;
use App\Models\Bill;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Tiffins
        $vegBreakfast = Tiffin::create([
            'name' => 'Veg Breakfast',
            'price' => 50.00,
            'description' => 'Healthy vegetarian breakfast',
            'long_description' => 'Includes Poha, Upma, or Idli with Chutney.',
            'image' => '/images/veg_breakfast.jpg',
            'meal_type' => 'Breakfast',
            'diet_type' => 'Veg',
            'category' => 'Daily',
            'items_included' => ['Poha/Upma/Idli', 'Chutney'],
            'is_available' => true,
            'featured' => true,
        ]);

        $nonVegBreakfast = Tiffin::create([
            'name' => 'Non-Veg Breakfast',
            'price' => 70.00,
            'description' => 'High protein non-veg breakfast',
            'long_description' => 'Includes Omelette, Bread toasts, and Sausages.',
            'meal_type' => 'Breakfast',
            'diet_type' => 'Non-Veg',
            'category' => 'Premium',
            'items_included' => ['Omelette', 'Bread', 'Sausages'],
            'is_available' => true,
            'featured' => false,
        ]);

        $vegLunch = Tiffin::create([
            'name' => 'Veg Lunch',
            'price' => 100.00,
            'description' => 'Standard vegetarian lunch',
            'long_description' => 'Includes 4 rotis, rice, dal, and sabzi.',
            'image' => '/images/veg_lunch.jpg',
            'meal_type' => 'Lunch',
            'diet_type' => 'Veg',
            'category' => 'Daily',
            'items_included' => ['4 Rotis', 'Rice', 'Dal', 'Sabzi'],
            'is_available' => true,
            'featured' => true,
        ]);

        $nonVegLunch = Tiffin::create([
            'name' => 'Non-Veg Lunch',
            'price' => 130.00,
            'description' => 'Delicious non-veg lunch',
            'long_description' => 'Includes 3 rotis, chicken curry, and rice.',
            'image' => '/images/non_veg_lunch.jpg',
            'meal_type' => 'Lunch',
            'diet_type' => 'Non-Veg',
            'category' => 'Premium',
            'items_included' => ['3 Rotis', 'Chicken Curry', 'Rice'],
            'is_available' => true,
            'featured' => false,
        ]);

        $vegDinner = Tiffin::create([
            'name' => 'Veg Dinner',
            'price' => 110.00,
            'description' => 'Light vegetarian dinner',
            'long_description' => 'Includes 3 rotis, paneer sabzi, dal, and salad.',
            'image' => '/images/veg_dinner.jpg',
            'meal_type' => 'Dinner',
            'diet_type' => 'Veg',
            'category' => 'Daily',
            'items_included' => ['3 Rotis', 'Paneer Sabzi', 'Dal', 'Salad'],
            'is_available' => true,
            'featured' => true,
        ]);

        $nonVegDinner = Tiffin::create([
            'name' => 'Non-Veg Dinner',
            'price' => 150.00,
            'description' => 'Hearty non-veg dinner',
            'long_description' => 'Includes 3 rotis, mutton curry, rice, and salad.',
            'image' => '/images/non_veg_dinner.jpg',
            'meal_type' => 'Dinner',
            'diet_type' => 'Non-Veg',
            'category' => 'Premium',
            'items_included' => ['3 Rotis', 'Mutton Curry', 'Rice', 'Salad'],
            'is_available' => true,
            'featured' => false,
        ]);

        // 2. Create Admin User
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'Admin',
            'phone' => '9999999999',
            'address' => 'Admin Office, City Center',
        ]);

        // 3. Create Customers mapped to Tiffins
        $customer1 = User::create([
            'name' => 'John Doe (Veg Lunch)',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'role' => 'Customer',
            'phone' => '1111111111',
            'address' => 'Apt 1, Building A',
            'advance_payment' => 500,
        ]);
        $customer1->tiffins()->sync([$vegLunch->id]);

        $customer2 = User::create([
            'name' => 'Alice Smith (Non-Veg Dinner)',
            'email' => 'alice@example.com',
            'password' => bcrypt('password'),
            'role' => 'Customer',
            'phone' => '2222222222',
            'address' => 'Apt 2, Building B',
            'advance_payment' => 0,
        ]);
        $customer2->tiffins()->sync([$nonVegDinner->id]);
        
        $customer3 = User::create([
            'name' => 'Bob (Veg Breakfast)',
            'email' => 'bob@example.com',
            'password' => bcrypt('password'),
            'role' => 'Customer',
            'phone' => '3333333333',
            'address' => 'Apt 3, Building C',
            'advance_payment' => 1000,
        ]);
        $customer3->tiffins()->sync([$vegBreakfast->id, $vegLunch->id]);

        $customers = [$customer1, $customer2, $customer3];

        // 4. Create Leaves
        Leave::create([
            'user_id' => $customer1->id,
            'from_date' => Carbon::today()->addDays(2),
            'to_date' => Carbon::today()->addDays(4),
        ]);
        Leave::create([
            'user_id' => $customer2->id,
            'from_date' => Carbon::today()->addDays(5),
            'to_date' => Carbon::today()->addDays(5),
        ]);

        // 5. Create Daily Logs for the past 7 days (and today)
        foreach ($customers as $customer) {
            foreach ($customer->tiffins as $tiffin) {
                for ($i = 7; $i >= 0; $i--) {
                    DailyLog::create([
                        'user_id' => $customer->id,
                        'tiffin_id' => $tiffin->id,
                        'date' => Carbon::today()->subDays($i)->toDateString(),
                        'status' => $i == 0 ? 'Pending' : ($i % 3 == 0 ? 'Cancelled' : 'Delivered'), // Mix of statuses
                    ]);
                }
            }
        }

        // 6. Create Bills for last month
        $lastMonth = Carbon::today()->subMonth()->format('m-Y');
        
        foreach ($customers as $customer) {
            $mockTotalAmount = 0;
            foreach ($customer->tiffins as $tiffin) {
                $mockTotalAmount += $tiffin->price * 20; // 20 days delivered mock
            }
            $advancePayment = $customer->advance_payment ?? 0;
            $advanceDeducted = min($mockTotalAmount, $advancePayment);
            $payableAmount = $mockTotalAmount - $advanceDeducted;

            Bill::create([
                'user_id' => $customer->id,
                'month_year' => $lastMonth,
                'total_amount' => $mockTotalAmount,
                'advance_deducted' => $advanceDeducted,
                'payable_amount' => $payableAmount,
                'status' => $customer->id % 2 == 0 ? 'Unpaid' : 'Paid',
            ]);
        }
    }
}

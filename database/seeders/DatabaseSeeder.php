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
        // 1. Create Hostel Monthly Mess Plans (Curated for Hostel Students)
        $monthlyBreakfast = Tiffin::create([
            'name' => 'Breakfast (Monthly Plan)',
            'price' => 1200.00,
            'description' => 'Hostel monthly breakfast plan (8:30 AM - 9:30 AM)',
            'long_description' => 'Fresh hot nutritious breakfast served daily between 8:30 AM to 9:30 AM. Rotating menu: Poha, Upma, Idli Sambhar, Parathas, and hot tea.',
            'image' => '/images/veg_breakfast.jpg',
            'meal_type' => 'Breakfast',
            'diet_type' => 'Veg',
            'category' => 'Monthly',
            'items_included' => ['Poha/Upma/Idli/Paratha', 'Chutney & Sambhar', 'Hot Tea'],
            'is_available' => true,
            'featured' => true,
        ]);

        $monthlyLunch = Tiffin::create([
            'name' => 'Lunch (Monthly Plan)',
            'price' => 1800.00,
            'description' => 'Hostel monthly lunch plan (12:00 PM - 2:00 PM)',
            'long_description' => 'Fresh homemade lunch delivered between 12:00 PM to 2:00 PM. Includes 4 fresh phulkas, seasonal vegetable curry, dal tadka, steamed basmati rice, salad, and pickle.',
            'image' => '/images/veg_lunch.jpg',
            'meal_type' => 'Lunch',
            'diet_type' => 'Veg',
            'category' => 'Monthly',
            'items_included' => ['4 Fresh Phulkas', 'Seasonal Sabzi', 'Dal Tadka', 'Steamed Rice', 'Salad & Pickle'],
            'is_available' => true,
            'featured' => true,
        ]);

        $monthlyDinner = Tiffin::create([
            'name' => 'Dinner (Monthly Plan)',
            'price' => 2100.00,
            'description' => 'Hostel monthly dinner plan (8:00 PM - 9:30 PM)',
            'long_description' => 'Satisfying dinner served warm between 8:00 PM to 9:30 PM. Includes 4 hot rotis, special evening curry (Paneer / Kofta / Dal Makhani rotations), rice, and salad.',
            'image' => '/images/veg_dinner.jpg',
            'meal_type' => 'Dinner',
            'diet_type' => 'Veg',
            'category' => 'Monthly',
            'items_included' => ['4 Hot Rotis', 'Special Evening Curry', 'Dal / Gravy', 'Steamed Rice', 'Salad'],
            'is_available' => true,
            'featured' => true,
        ]);

        $monthlyBreakfastLunch = Tiffin::create([
            'name' => 'Breakfast + Lunch Combo (Monthly)',
            'price' => 2800.00,
            'description' => 'Hostel monthly combo for morning & afternoon meals',
            'long_description' => 'Complete morning and afternoon nourishment for students. Breakfast (8:30 AM - 9:30 AM) + Full Lunch (12:00 PM - 2:00 PM) everyday.',
            'image' => '/images/veg_lunch.jpg',
            'meal_type' => 'Both',
            'diet_type' => 'Veg',
            'category' => 'Monthly',
            'items_included' => ['Morning Breakfast + Tea', 'Full Afternoon Lunch Thali', '4 Phulkas + Rice + Dal + Sabzi'],
            'is_available' => true,
            'featured' => true,
        ]);

        $monthlyBreakfastDinner = Tiffin::create([
            'name' => 'Breakfast + Dinner Combo (Monthly)',
            'price' => 2800.00,
            'description' => 'Hostel monthly combo for breakfast & evening dinner',
            'long_description' => 'Ideal for college students: Breakfast (8:30 AM - 9:30 AM) + Dinner (8:00 PM - 9:30 PM) daily.',
            'image' => '/images/veg_dinner.jpg',
            'meal_type' => 'Both',
            'diet_type' => 'Veg',
            'category' => 'Monthly',
            'items_included' => ['Hot Breakfast + Beverage', 'Full Evening Dinner Thali (4 Rotis, Special Sabzi, Dal, Rice)'],
            'is_available' => true,
            'featured' => false,
        ]);

        $monthlyLunchDinner = Tiffin::create([
            'name' => 'Lunch + Dinner Combo (Monthly)',
            'price' => 3300.00,
            'description' => 'Hostel two-meal power combo for Lunch & Dinner',
            'long_description' => 'Most popular hostel package! Full Lunch (12:00 PM - 2:00 PM) and Dinner (8:00 PM - 9:30 PM) delivered fresh to your hostel room.',
            'image' => '/images/veg_lunch.jpg',
            'meal_type' => 'Both',
            'diet_type' => 'Veg',
            'category' => 'Monthly',
            'items_included' => ['Full Lunch Thali (Phulkas, Sabzi, Dal, Rice)', 'Full Dinner Thali (Rotis, Paneer/Curry, Dal, Rice)'],
            'is_available' => true,
            'featured' => true,
        ]);

        $monthlyFullMess = Tiffin::create([
            'name' => 'Full Mess Plan (Breakfast + Lunch + Dinner)',
            'price' => 3800.00,
            'description' => 'All-inclusive 3 meals daily monthly mess subscription',
            'long_description' => 'Total hostel meal coverage! Breakfast (8:30-9:30 AM), Lunch (12:00-2:00 PM), and Dinner (8:00-9:30 PM) everyday. Complete peace of mind.',
            'image' => '/images/veg_dinner.jpg',
            'meal_type' => 'Both',
            'diet_type' => 'Veg',
            'category' => 'Monthly',
            'items_included' => ['Daily Breakfast + Tea (8:30-9:30 AM)', 'Afternoon Lunch Thali (12:00-2:00 PM)', 'Evening Dinner Thali (8:00-9:30 PM)', 'Weekend Special Sweets'],
            'is_available' => true,
            'featured' => true,
        ]);

        // Daily Tiffin Options
        $vegBreakfast = Tiffin::create([
            'name' => 'Daily Veg Breakfast',
            'price' => 50.00,
            'description' => 'Healthy vegetarian breakfast (8:30 AM - 9:30 AM)',
            'long_description' => 'Includes Poha, Upma, or Idli with Chutney.',
            'image' => '/images/veg_breakfast.jpg',
            'meal_type' => 'Breakfast',
            'diet_type' => 'Veg',
            'category' => 'Daily',
            'items_included' => ['Poha/Upma/Idli', 'Chutney'],
            'is_available' => true,
            'featured' => false,
        ]);

        $vegLunch = Tiffin::create([
            'name' => 'Daily Veg Lunch',
            'price' => 100.00,
            'description' => 'Standard vegetarian lunch (12:00 PM - 2:00 PM)',
            'long_description' => 'Includes 4 rotis, rice, dal, and sabzi.',
            'image' => '/images/veg_lunch.jpg',
            'meal_type' => 'Lunch',
            'diet_type' => 'Veg',
            'category' => 'Daily',
            'items_included' => ['4 Rotis', 'Rice', 'Dal', 'Sabzi'],
            'is_available' => true,
            'featured' => false,
        ]);

        $vegDinner = Tiffin::create([
            'name' => 'Daily Veg Dinner',
            'price' => 110.00,
            'description' => 'Light vegetarian dinner (8:00 PM - 9:30 PM)',
            'long_description' => 'Includes 3 rotis, paneer sabzi, dal, and salad.',
            'image' => '/images/veg_dinner.jpg',
            'meal_type' => 'Dinner',
            'diet_type' => 'Veg',
            'category' => 'Daily',
            'items_included' => ['3 Rotis', 'Paneer Sabzi', 'Dal', 'Salad'],
            'is_available' => true,
            'featured' => false,
        ]);

        // 2. Create Admin User
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('Crownora@Admin2026!'),
            'role' => 'Admin',
            'phone' => '9999999999',
            'address' => 'Admin Office, City Center',
        ]);

        // 3. Create Customers mapped to Monthly Mess Options
        $customer1 = User::create([
            'name' => 'John Doe (Hostel Lunch Mess)',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'role' => 'Customer',
            'phone' => '1111111111',
            'address' => 'Hostel Block A, Room 101',
            'advance_payment' => 500,
        ]);
        $customer1->tiffins()->sync([$monthlyLunch->id]);

        $customer2 = User::create([
            'name' => 'Alice Smith (Hostel Dinner Mess)',
            'email' => 'alice@example.com',
            'password' => bcrypt('password'),
            'role' => 'Customer',
            'phone' => '2222222222',
            'address' => 'Hostel Block B, Room 204',
            'advance_payment' => 0,
        ]);
        $customer2->tiffins()->sync([$monthlyDinner->id]);

        $customer3 = User::create([
            'name' => 'Bob (Hostel Breakfast + Lunch Mess)',
            'email' => 'bob@example.com',
            'password' => bcrypt('password'),
            'role' => 'Customer',
            'phone' => '3333333333',
            'address' => 'Hostel Block C, Room 305',
            'advance_payment' => 1000,
        ]);
        $customer3->tiffins()->sync([$monthlyBreakfastLunch->id]);

        $customers = [$customer1, $customer2, $customer3];

        // 4. Create Leaves
        Leave::create([
            'user_id' => $customer1->id,
            'from_date' => Carbon::today()->addDays(2)->toDateString(),
            'to_date' => Carbon::today()->addDays(4)->toDateString(),
        ]);
        Leave::create([
            'user_id' => $customer2->id,
            'from_date' => Carbon::today()->addDays(5)->toDateString(),
            'to_date' => Carbon::today()->addDays(5)->toDateString(),
        ]);

        // 5. Create Daily Logs for the past 7 days (and today) - Default Delivered, Cancelled only for skips
        foreach ($customers as $customer) {
            foreach ($customer->tiffins as $tiffin) {
                for ($i = 7; $i >= 0; $i--) {
                    DailyLog::create([
                        'user_id' => $customer->id,
                        'tiffin_id' => $tiffin->id,
                        'date' => Carbon::today()->subDays($i)->toDateString(),
                        'status' => ($i == 3) ? 'Cancelled' : 'Delivered', // Default Delivered, occasional Cancelled skip
                    ]);
                }
            }
        }

        // 6. Create Bills for last month (Monthly Mess Plan amounts)
        $lastMonth = Carbon::today()->subMonth()->format('m-Y');

        foreach ($customers as $customer) {
            $mockTotalAmount = 0;
            foreach ($customer->tiffins as $tiffin) {
                $mockTotalAmount += $tiffin->price; // Full monthly mess subscription fee
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

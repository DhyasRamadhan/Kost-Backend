<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Room;
use App\Models\RentalContract;
use App\Models\Payment;
use App\Models\ElectricityUsage;
use Carbon\Carbon;

class DummyOperationalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // =========================
        // OWNER ACCOUNT
        // =========================

        $owner1 = User::create([
            'name' => 'Budi Santoso',
            'email' => 'owner.pandanwangi@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'owner',
            'phone' => '081234567801',
            'verification_status' => 'approved',
            'verified_at' => now(),
        ]);

        $owner2 = User::create([
            'name' => 'Rina Maharani',
            'email' => 'owner.griyatropodo@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'owner',
            'phone' => '081234567802',
            'verification_status' => 'approved',
            'verified_at' => now(),
        ]);

        // =========================
        // ROOM DATA
        // =========================

        $rooms = [];

        // Owner 1 rooms
        for ($i = 1; $i <= 12; $i++) {
            $rooms[] = Room::create([
                'owner_id' => $owner1->id,
                'room_number' => 'PW-' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'price' => rand(700000, 1200000),
                'status' => 'occupied',
            ]);
        }

        // Owner 2 rooms
        for ($i = 1; $i <= 13; $i++) {
            $rooms[] = Room::create([
                'owner_id' => $owner2->id,
                'room_number' => 'GT-' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'price' => rand(850000, 1500000),
                'status' => 'occupied',
            ]);
        }

        // =========================
        // TENANT DATA
        // =========================

        $tenantNames = [
            'Ahmad Fauzan',
            'Dewi Lestari',
            'Rizky Maulana',
            'Kevin Wijaya',
            'Nabila Putri',
            'Anisa Ramadhani',
            'Yoga Pratama',
            'Farhan Akbar',
            'Meylina Sari',
            'Bagas Saputra',
            'Rina Oktavia',
            'Dimas Kurniawan',
            'Shinta Aulia',
            'Fikri Hidayat',
            'Aldo Saputro',
            'Gina Maharani',
            'Salsa Amelia',
            'Rafi Ramadhan',
            'Taufik Hidayat',
            'Nadya Permata',
            'Iqbal Ramadhan',
            'Mochammad Reza',
            'Putri Maharani',
            'Vania Larasati',
            'Arga Pranata',
        ];

        $addresses = [
            'Surabaya',
            'Sidoarjo',
            'Malang',
            'Jombang',
            'Mojokerto',
            'Lamongan',
            'Gresik',
            'Pasuruan',
        ];

        foreach ($rooms as $index => $room) {

            $name = $tenantNames[$index];

            $user = User::create([
                'name' => $name,
                'email' => Str::slug($name) . '@gmail.com',
                'password' => Hash::make('password123'),
                'role' => 'tenant',
                'phone' => '08' . rand(1111111111, 9999999999),
                'verification_status' => 'approved',
                'verified_at' => now(),
            ]);

            $tenant = Tenant::create([
                'user_id' => $user->id,
                'address' => $addresses[array_rand($addresses)],
            ]);

            if (rand(1, 4) == 1) {
                \App\Models\TenantUpdateRequest::create([
                    'tenant_id' => $tenant->id,
                    'field_name' => 'address',
                    'old_value' => $tenant->address,
                    'new_value' => 'Surabaya Barat',
                    'status' => collect([
                        'pending',
                        'approved',
                        'rejected'
                    ])->random(),
                    'approved_at' => now(),
                ]);
            }

            $startDate = Carbon::now()->subMonths(rand(2, 8));
            if ($index < 5) {
                $endDate = Carbon::now()->addDays(rand(3, 14));
            } else {
                $endDate = (clone $startDate)->addMonths(12);
            }

            $contract = RentalContract::create([
                'tenant_id' => $tenant->id,
                'owner_id' => $room->owner_id,
                'room_id' => $room->id,
                'monthly_rent' => $room->price,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => 'active',
            ]);


            // =========================
            // PAYMENT HISTORY
            // =========================

            $paymentCount = rand(2, 4);

            for ($p = 1; $p <= $paymentCount; $p++) {

                $paymentDate = Carbon::now()->subMonths($paymentCount - $p);

                $statuses = [
                    'paid',
                    'paid',
                    'paid',
                    'pending',
                    'pending',
                    'failed'
                ];

                $status = $statuses[array_rand($statuses)];
                $dueDate = $status == 'pending' ? Carbon::now()->subDays(rand(2, 10)) : (clone $paymentDate)->addDays(7);

                Payment::create([
                    'contract_id' => $contract->id,
                    'owner_id' => $room->owner_id,
                    'tenant_id' => $tenant->id,
                    'amount' => $room->price,
                    'payment_date' => $paymentDate,
                    'due_date' => $dueDate,
                    'status' => $status,
                    'midtrans_order_id' => 'ORDER-' . strtoupper(Str::random(10)),
                    'midtrans_transaction_id' => 'TRX-' . strtoupper(Str::random(12)),
                    'payment_type' => collect([
                        'bank_transfer',
                        'qris',
                        'gopay',
                        'shopeepay'
                    ])->random(),
                    'paid_at' => $status == 'paid'
                        ? (clone $paymentDate)->addDays(rand(1, 5))
                        : null,
                ]);
            }

            // =========================
            // ELECTRICITY USAGE
            // =========================

            $meterStart = rand(1000, 5000);

            if ($index < 3) {
                $usageKwh = rand(400, 700);
            } else {
                $usageKwh = rand(50, 300);
            }

            $meterEnd = $meterStart + $usageKwh;
            $estimateBill = $usageKwh * 1500;

            for ($e = 1; $e <= 4; $e++) {
                ElectricityUsage::create([
                    'room_id' => $room->id,
                    'owner_id' => $room->owner_id,
                    'usage_date' => Carbon::now()->subMonths($e),
                    'meter_start' => $meterStart,
                    'meter_end' => $meterEnd,
                    'usage_kwh' => $usageKwh,
                    'estimate_bill' => $estimateBill,
                    'token_amount' => rand(50000, 200000),
                ]);
            }
        }

        // =========================
        // VACANT ROOM SAMPLE
        // =========================

        Room::create([
            'owner_id' => $owner1->id,
            'room_number' => 'PW-13',
            'price' => 950000,
            'status' => 'available',
        ]);

        Room::create([
            'owner_id' => $owner2->id,
            'room_number' => 'GT-14',
            'price' => 1100000,
            'status' => 'available',
        ]);
    }
}
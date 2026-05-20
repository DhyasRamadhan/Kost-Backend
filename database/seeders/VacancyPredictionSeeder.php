<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Room;
use App\Models\RentalContract;
use App\Models\Payment;
use Carbon\Carbon;

class VacancyPredictionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $owner = User::where('role', 'owner')->first();

        if (!$owner) {
            return;
        }

        // =========================
        // ROOM POTENTIAL VACANCY
        // =========================

        for ($i = 1; $i <= 5; $i++) {

            $room = Room::create([
                'owner_id' => $owner->id,
                'room_number' => 'VAC-' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'price' => rand(850000, 1500000),
                'status' => 'occupied',
            ]);

            $tenantUser = User::create([
                'name' => 'Tenant Vacancy ' . $i,
                'email' => 'vacancytenant' . $i . '@gmail.com',
                'password' => bcrypt('password123'),
                'role' => 'tenant',
                'phone' => '08123' . rand(100000, 999999),
                'verification_status' => 'approved',
                'verified_at' => now(),
            ]);

            $tenant = Tenant::create([
                'user_id' => $tenantUser->id,
                'address' => 'Surabaya',
            ]);

            // =========================
            // CONTRACT ALMOST EXPIRED
            // =========================

            $contract = RentalContract::create([
                'tenant_id' => $tenant->id,
                'owner_id' => $owner->id,
                'room_id' => $room->id,
                'monthly_rent' => $room->price,
                'start_date' => Carbon::now()->subMonths(11),
                'end_date' => Carbon::now()->addDays(rand(2, 10)),
                'status' => 'active',
            ]);

            // =========================
            // PAYMENT HISTORY
            // =========================

            // Histori pembayaran telat
            for ($p = 1; $p <= 3; $p++) {

                $paymentDate = Carbon::now()->subMonths($p);

                Payment::create([
                    'contract_id' => $contract->id,
                    'owner_id' => $owner->id,
                    'tenant_id' => $tenant->id,
                    'amount' => $room->price,

                    // dibuat overdue
                    'payment_date' => Carbon::now()->subDays(rand(10, 30)),

                    'due_date' => Carbon::now()->subDays(rand(5, 20)),

                    'status' => collect([
                        'pending',
                        'failed',
                        'pending'
                    ])->random(),

                    'midtrans_order_id' => 'VACANCY-ORDER-' . rand(1000, 9999),

                    'midtrans_transaction_id' => 'VACANCY-TRX-' . rand(1000, 9999),

                    'payment_type' => collect([
                        'bank_transfer',
                        'gopay',
                        'qris'
                    ])->random(),

                    'paid_at' => null,
                ]);
            }
        }
    }
}

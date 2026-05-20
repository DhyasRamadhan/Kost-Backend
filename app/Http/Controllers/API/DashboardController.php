<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ElectricityUsage;
use App\Models\Payment;
use App\Models\RentalContract;
use App\Models\Room;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'owner') {
            return $this->ownerDashboard($user);
        }

        if ($user->role === 'tenant') {
            return $this->tenantDashboard($user);
        }

        return response()->json([
            'message' => 'Peran tidak valid'
        ], 403);
    }

    private function ownerDashboard($user)
    {
        $ownerId = $user->id;

        $totalRooms = Room::where('owner_id', $ownerId)->count();

        $occupiedRooms = Room::where('owner_id', $ownerId)
            ->where('status', 'occupied')
            ->count();

        $availableRooms = Room::where('owner_id', $ownerId)
            ->where('status', 'available')
            ->count();

        $totalTenants = RentalContract::where('owner_id', $ownerId)
            ->where('status', 'active')
            ->distinct('tenant_id')
            ->count('tenant_id');

        $activeContracts = RentalContract::where('owner_id', $ownerId)
            ->where('status', 'active')
            ->count();

        $totalIncome = Payment::where('owner_id', $ownerId)
            ->where('status', 'paid')
            ->sum('amount');

        $pendingPayments = Payment::where('owner_id', $ownerId)
            ->where('status', 'pending')
            ->count();

        $totalElectricityBill = ElectricityUsage::where('owner_id', $ownerId)
            ->sum('estimate_bill');

        $recentPayments = Payment::with([
            'tenant.user',
            'contract.room'
        ])
            ->where('owner_id', $ownerId)
            ->latest()
            ->take(5)
            ->get();

        $expiringContracts = RentalContract::with([
            'tenant.user',
            'room'
        ])
            ->where('owner_id', $ownerId)
            ->where('status', 'active')
            ->whereDate('end_date', '<=', Carbon::now()->addDays(14))
            ->orderBy('end_date', 'asc')
            ->get()
            ->map(function ($contract) {

                $daysRemaining = now()->startOfDay()->diffInDays(
                    Carbon::parse($contract->end_date)->startOfDay(),
                    false
                );

                return [
                    'contract_id' => $contract->id,
                    'tenant_name' => $contract->tenant->user->name ?? '-',
                    'room_number' => $contract->room->room_number ?? '-',
                    'end_date' => $contract->end_date,
                    'days_remaining' => $daysRemaining,
                ];
            });

        $alerts = [];

        if ($expiringContracts->count() > 0) {
            $alerts[] = [
                'type' => 'expiring_contract',
                'message' => $expiringContracts->count() . ' kontrak akan segera berakhir'
            ];
        }

        if ($totalRooms > 0 && $availableRooms == 0) {
            $alerts[] = [
                'type' => 'full_occupancy',
                'message' => 'Semua kamar sudah terisi'
            ];
        }

        $overduePayments = Payment::with([
            'tenant.user',
            'contract.room'
        ])
            ->where('owner_id', $ownerId)
            ->where('status', 'pending')
            ->whereDate('due_date', '<=', now())
            ->orderBy('due_date', 'asc')
            ->get()
            ->map(function ($payment) {

                $daysLate = now()->startOfDay()->diffInDays(
                    Carbon::parse($payment->due_date)->startOfDay(),
                    false
                );

                return [
                    'payment_id' => $payment->id,
                    'tenant_name' => $payment->tenant->user->name ?? '-',
                    'room_number' => $payment->contract->room->room_number ?? '-',
                    'amount' => $payment->amount,
                    'due_date' => $payment->due_date,
                    'days_late' => abs($daysLate),
                ];
            });

        if ($overduePayments->count() > 0) {
            $alerts[] = [
                'type' => 'overdue_payment',
                'message' => $overduePayments->count() . ' pembayaran terlambat'
            ];
        }

        $potentialVacantRooms = RentalContract::with([
            'tenant.user',
            'room'
        ])
            ->where('owner_id', $ownerId)
            ->where('status', 'active')
            ->whereDate('end_date', '<=', now()->addDays(7))
            ->get()
            ->filter(function ($contract) {

                $hasOverduePayment = Payment::where(
                    'contract_id',
                    $contract->id
                )
                    ->where('status', 'pending')
                    ->whereDate('due_date', '<=', now())
                    ->exists();

                return $hasOverduePayment;
            })
            ->map(function ($contract) {

                $daysRemaining = now()->startOfDay()
                    ->diffInDays(
                        Carbon::parse($contract->end_date)
                            ->startOfDay(),
                        false
                    );

                return [
                    'contract_id' => $contract->id,
                    'room_number' => $contract->room->room_number ?? '-',
                    'tenant_name' => $contract->tenant->user->name ?? '-',
                    'days_remaining' => abs($daysRemaining),
                ];
            })
            ->values();

        if ($potentialVacantRooms->count() > 0) {
            $alerts[] = [
                'type' => 'potential_vacant',
                'message' => $potentialVacantRooms->count() . ' potensi kamar kosong'
            ];
        }

        $occupancyRate = $totalRooms > 0
            ? round(($occupiedRooms / $totalRooms) * 100)
            : 0;

        return response()->json([
            'role' => 'owner',

            'owner' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'verification_status' => $user->verification_status,
            ],

            'rooms' => [
                'total' => $totalRooms,
                'occupied' => $occupiedRooms,
                'available' => $availableRooms,
                'occupancy_rate' => $occupancyRate,
            ],

            'tenants' => [
                'total' => $totalTenants,
            ],

            'contracts' => [
                'active' => $activeContracts,
            ],

            'payments' => [
                'total_income' => $totalIncome,
                'pending_payments' => $pendingPayments,
            ],

            'electricity' => [
                'total_estimated_bill' => $totalElectricityBill,
            ],

            'expiring_contracts' => $expiringContracts,

            'alerts' => $alerts,

            'overdue_payments' => $overduePayments,

            'potential_vacant_rooms' => $potentialVacantRooms,

            'recent_payments' => $recentPayments,
        ]);
    }

    private function tenantDashboard($user)
    {
        $tenant = $user->tenantProfile;

        if (!$tenant) {
            return response()->json([
                'message' => 'Profile penyewa tidak ditemukan'
            ], 404);
        }

        $contract = RentalContract::with('room')
            ->where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->latest()
            ->first();

        $pendingPayments = Payment::where('tenant_id', $tenant->id)
            ->where('status', 'pending')
            ->count();

        $paidPayments = Payment::where('tenant_id', $tenant->id)
            ->where('status', 'paid')
            ->count();

        $recentPayments = Payment::where('tenant_id', $tenant->id)
            ->latest()
            ->take(5)
            ->get();

        $electricityUsage = null;

        if ($contract && $contract->room_id) {
            $electricityUsage = ElectricityUsage::where('room_id', $contract->room_id)
                ->latest()
                ->first();
        }

        return response()->json([
            'role' => 'tenant',

            'tenant' => [
                'id' => $tenant->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'address' => $tenant->address,
            ],

            'room' => $contract?->room,

            'contract' => $contract,

            'payments' => [
                'pending_count' => $pendingPayments,
                'paid_count' => $paidPayments,
                'recent' => $recentPayments,
            ],

            'electricity_usage' => $electricityUsage,
        ]);
    }
}

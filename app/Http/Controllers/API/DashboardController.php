<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ElectricityUsage;
use App\Models\Payment;
use App\Models\RentalContract;
use App\Models\Room;
use App\Models\Tenant;
use Illuminate\Http\Request;

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
            'message' => 'Invalid role'
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

        $totalTenants = Tenant::where('owner_id', $ownerId)->count();

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

        return response()->json([
            'role' => 'owner',

            'rooms' => [
                'total' => $totalRooms,
                'occupied' => $occupiedRooms,
                'available' => $availableRooms,
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

            'recent_payments' => $recentPayments,
        ]);
    }

    private function tenantDashboard($user)
    {
        $tenant = $user->tenantProfile;

        if (!$tenant) {
            return response()->json([
                'message' => 'Tenant profile not found'
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

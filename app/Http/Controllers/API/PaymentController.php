<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\RentalContract;
use App\Services\MidtransService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $payments = Payment::with([
            'tenant.user',
            'contract.room'
        ])
            ->where(
                'owner_id',
                $request->user()->id
            )
            ->latest()
            ->get();

        return response()->json([
            'data' => $payments
        ]);
    }

    public function create(Request $request)
    {
        MidtransService::init();

        $request->validate([
            'contract_id' => 'required|exists:rental_contracts,id'
        ]);

        $contract = RentalContract::findOrFail($request->contract_id);

        $orderId = 'PAY-' . time();

        $payment = Payment::create([
            'contract_id' => $contract->id,
            'owner_id' => $contract->owner_id,
            'tenant_id' => $contract->tenant_id,
            'amount' => $contract->monthly_rent,
            'payment_date' => now(),
            'status' => 'pending',
            'midtrans_order_id' => $orderId
        ]);

        $snapToken = \Midtrans\Snap::getSnapToken([
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $contract->monthly_rent
            ],
            'customer_details' => [
                'first_name' => $request->user()->name,
                'email' => $request->user()->email
            ]
        ]);

        return response()->json([
            'message' => 'Payment created',
            'data' => $payment,
            'snap_token' => $snapToken
        ]);
    }

    public function tenantPayments(Request $request)
    {
        $tenant = $request->user()
            ->tenantProfile;

        if (!$tenant) {

            return response()->json([
                'message' =>
                'Tenant profile not found'
            ], 404);
        }

        $payments = Payment::with([
            'contract.room'
        ])
            ->where(
                'tenant_id',
                $tenant->id
            )
            ->latest()
            ->get();

        return response()->json([
            'data' => $payments
        ]);
    }

    public function cancel(Request $request, $id)
    {
        $payment = Payment::where(
            'owner_id',
            $request->user()->id
        )->findOrFail($id);

        if ($payment->status === 'paid') {

            return response()->json([
                'message' =>
                'Paid payment cannot be cancelled'
            ], 400);
        }

        if ($payment->status === 'cancelled') {

            return response()->json([
                'message' =>
                'Payment already cancelled'
            ], 400);
        }

        $payment->update([
            'status' => 'cancelled'
        ]);

        return response()->json([
            'message' => 'Payment cancelled',
            'data' => $payment
        ]);
    }
}

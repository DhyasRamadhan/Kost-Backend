<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\RentalContract;
use App\Services\MidtransService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
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
}

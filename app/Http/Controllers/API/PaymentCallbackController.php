<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentCallbackController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->all();

        $orderId = $payload['order_id'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? null;
        $fraudStatus = $payload['fraud_status'] ?? null;
        $paymentType = $payload['payment_type'] ?? null;
        $transactionId = $payload['transaction_id'] ?? null;

        if (!$orderId) {
            return response()->json([
                'message' => 'Order ID not found'
            ], 400);
        }

        $payment = Payment::where('midtrans_order_id', $orderId)->first();

        if (!$payment) {
            return response()->json([
                'message' => 'Payment not found'
            ], 404);
        }

        if ($transactionStatus == 'capture') {

            if ($fraudStatus == 'challenge') {
                $payment->status = 'pending';
            } else {
                $payment->status = 'paid';
                $payment->paid_at = now();
            }
        } elseif ($transactionStatus == 'settlement') {

            $payment->status = 'paid';
            $payment->paid_at = now();
        } elseif ($transactionStatus == 'pending') {

            $payment->status = 'pending';
        } elseif (
            $transactionStatus == 'deny' ||
            $transactionStatus == 'expire' ||
            $transactionStatus == 'cancel'
        ) {

            $payment->status = 'failed';
        }

        $payment->payment_type = $paymentType;
        $payment->midtrans_transaction_id = $transactionId;

        $payment->save();

        return response()->json([
            'message' => 'Callback processed successfully'
        ]);
    }
}

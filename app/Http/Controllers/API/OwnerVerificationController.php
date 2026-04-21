<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class OwnerVerificationController extends Controller
{
    public function pending()
    {
        $owners = User::where('role', 'owner')
            ->where('verification_status', 'pending')
            ->get();

        return response()->json([
            'message' => 'Pending owners fetched',
            'data' => $owners
        ]);
    }

    public function approve($id)
    {
        $user = User::where('role', 'owner')->findOrFail($id);

        $user->update([
            'verification_status' => 'approved',
            'verified_at' => now(),
            'rejected_reason' => null
        ]);

        return response()->json([
            'message' => 'Owner approved successfully'
        ]);
    }

    public function reject(Request $request, $id)
    {
        $user = User::where('role', 'owner')->findOrFail($id);

        $user->update([
            'verification_status' => 'rejected',
            'rejected_reason' => $request->reason,
            'verified_at' => null
        ]);

        return response()->json([
            'message' => 'Owner rejected successfully'
        ]);
    }
}

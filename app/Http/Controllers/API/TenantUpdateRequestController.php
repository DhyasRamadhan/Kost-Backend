<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TenantUpdateRequest;
use App\Models\Tenant;

class TenantUpdateRequestController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user();
        $tenant = $user->tenantProfile;

        $request->validate([
            'field_name' => 'required|in:phone,address',
            'new_value' => 'required'
        ]);

        TenantUpdateRequest::create([
            'tenant_id' => $tenant->id,
            'field_name' => $request->field_name,
            'old_value' => $request->field_name === 'phone' ? $tenant->user->phone : $tenant->{$request->field_name},
            'new_value' => $request->new_value,
            'status' => 'pending'
        ]);

        return response()->json([
            'message' => 'Request submitted, waiting approval'
        ]);
    }

    public function index(Request $request)
    {
        $ownerId = $request->user()->id;

        $requests = TenantUpdateRequest::with([
            'tenant.user'
        ])
            ->whereHas('tenant.contracts', function ($query) use ($ownerId) {

                $query->where('owner_id', $ownerId);

            })
            ->latest()
            ->get();

        return response()->json([
            'data' => $requests
        ]);
    }

    public function approve(Request $request, $id)
    {
        $ownerId = $request->user()->id;

        $updateRequest = TenantUpdateRequest::with('tenant')
            ->whereHas('tenant.contracts', function ($query) use ($ownerId) {
                $query->where('owner_id', $ownerId);
            })
            ->findOrFail($id);

        if ($updateRequest->status !== 'pending') {
            return response()->json([
                'message' => 'Request already processed'
            ], 400);
        }

        $tenant = $updateRequest->tenant;

        if ($updateRequest->field_name === 'phone') {
            $tenant->user->update([
                'phone' => $updateRequest->new_value
            ]);
        }

        if ($updateRequest->field_name === 'address') {
            $tenant->update([
                'address' => $updateRequest->new_value
            ]);
        }

        $updateRequest->update([
            'status' => 'approved',
            'approved_at' => now()
        ]);

        return response()->json([
            'message' => 'Update request approved',
            'data' => $updateRequest
        ]);
    }

    public function reject(Request $request, $id)
    {
        $ownerId = $request->user()->id;

        $updateRequest = TenantUpdateRequest::whereHas('tenant.contracts', function ($query) use ($ownerId) {
            $query->where('owner_id', $ownerId);
        })->findOrFail($id);

        if ($updateRequest->status !== 'pending') {
            return response()->json([
                'message' => 'Request already processed'
            ], 400);
        }

        $updateRequest->update([
            'status' => 'rejected'
        ]);

        return response()->json([
            'message' => 'Update request rejected',
            'data' => $updateRequest
        ]);
    }
}

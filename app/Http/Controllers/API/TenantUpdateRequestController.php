<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TenantUpdateRequest;

class TenantUpdateRequestController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user();
        $tenant = $user->tenant;

        $request->validate([
            'field_name' => 'required|in:phone,address',
            'new_value' => 'required'
        ]);

        TenantUpdateRequest::create([
            'tenant_id' => $tenant->id,
            'field_name' => $request->field_name,
            'old_value' => $tenant->{$request->field_name},
            'new_value' => $request->new_value
        ]);

        return response()->json([
            'message' => 'Request submitted, waiting approval'
        ]);
    }

    public function index()
    {
        return TenantUpdateRequest::with('tenant.user')
            ->where('status', 'pending')
            ->get();
    }

    public function approve($id)
    {
        $requestUpdate = TenantUpdateRequest::findOrFail($id);
        $tenant = $requestUpdate->tenant;

        // update data tenant
        $tenant->update([
            $requestUpdate->field_name => $requestUpdate->new_value
        ]);

        // update status request
        $requestUpdate->update([
            'status' => 'approved',
            'approved_at' => now()
        ]);

        return response()->json([
            'message' => 'Update approved'
        ]);
    }

    public function reject($id)
    {
        $requestUpdate = TenantUpdateRequest::findOrFail($id);

        $requestUpdate->update([
            'status' => 'rejected'
        ]);

        return response()->json([
            'message' => 'Update rejected'
        ]);
    }
}

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
}

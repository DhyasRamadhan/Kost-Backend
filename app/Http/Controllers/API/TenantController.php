<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    // GET /tenants
    public function index()
    {
        return Tenant::with('user')->get();
    }

    // POST /tenants (tambah tenant)
    public function store(Request $request)
    {
        $request->validate([
            'user_id'  => 'required|exists:users,id',
            'address'  => 'required|string|max:255'
        ]);

        $user = User::findOrFail($request->user_id);

        if ($user->role !== 'tenant') {
            return response()->json([
                'message' => 'Selected user is not a tenant account'
            ], 422);
        }

        $alreadyTenant = Tenant::where('user_id', $user->id)->exists();

        if ($alreadyTenant) {
            return response()->json([
                'message' => 'User already registered as tenant'
            ], 422);
        }

        $tenant = Tenant::create([
            'user_id'  => $user->id,
            'owner_id' => $request->user()->id,
            'address'  => $request->address
        ]);

        return response()->json([
            'message' => 'Tenant added successfully',
            'data'    => $tenant
        ], 201);
    }

    // GET /tenants/{id}
    public function show($id)
    {
        return Tenant::with('user')->findOrFail($id);
    }

    // PUT /tenants/{id} (edit tenant)
    public function update(Request $request, $id)
    {
        $tenant = Tenant::findOrFail($id);
        $user = $tenant->user;

        $user->update([
            'name' => $request->name ?? $user->name,
            'email' => $request->email ?? $user->email
        ]);

        $tenant->update([
            'phone' => $request->phone ?? $tenant->phone,
            'address' => $request->address ?? $tenant->address
        ]);

        return response()->json([
            'message' => 'Tenant updated',
            'data' => $tenant
        ]);
    }

    // DELETE /tenants/{id}
    public function destroy($id)
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->user->delete();
        $tenant->delete();

        return response()->json([
            'message' => 'Tenant deleted'
        ]);
    }
}

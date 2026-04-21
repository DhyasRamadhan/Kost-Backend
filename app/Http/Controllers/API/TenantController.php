<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    // GET /tenants
    public function index(Request $request)
    {
        $tenants = Tenant::with('user')
            ->where('owner_id', $request->user()->id)
            ->get();

        return response()->json([
            'message' => 'Tenants fetched successfully',
            'data' => $tenants
        ]);
    }

    // POST /tenants
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'address' => 'required'
        ]);

        $tenant = Tenant::create([
            'user_id' => $request->user_id,
            'owner_id' => $request->user()->id,
            'address' => $request->address
        ]);

        return response()->json([
            'message' => 'Tenant added',
            'data' => $tenant
        ]);
    }

    // GET /tenants/{id}
    public function show(Request $request, $id)
    {
        $tenant = Tenant::with('user')->where('owner_id', $request->user()->id)->findOrFail($id);

        return response()->json($tenant);
    }

    // PUT /tenants/{id}
    public function update(Request $request, $id)
    {
        $tenant = Tenant::where('owner_id', $request->user()->id)->findOrFail($id);
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
    public function destroy(Request $request, $id)
    {
        $tenant = Tenant::where('owner_id', $request->user()->id)->findOrFail($id);
        $tenant->user->delete();
        $tenant->delete();

        return response()->json([
            'message' => 'Tenant deleted'
        ]);
    }
}

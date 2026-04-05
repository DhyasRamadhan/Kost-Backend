<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'phone' => 'required',
            'address' => 'required'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'tenant'
        ]);

        $tenant = Tenant::create([
            'user_id' => $user->id,
            'phone' => $request->phone,
            'address' => $request->address
        ]);

        return response()->json([
            'message' => 'Tenant created',
            'data' => $tenant
        ]);
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

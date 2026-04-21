<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\RentalContract;
use App\Models\Room;
use App\Models\Tenant;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    public function index(Request $request)
    {
        $contracts = RentalContract::with(['tenant.user', 'room'])
            ->where('owner_id', $request->user()->id)
            ->get();

        return response()->json([
            'data' => $contracts
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'room_id' => 'required|exists:rooms,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date'
        ]);

        $user = $request->user();

        $tenant = Tenant::where('owner_id', $user->id)
            ->findOrFail($request->tenant_id);

        $room = Room::where('owner_id', $user->id)
            ->findOrFail($request->room_id);

        if ($room->status === 'occupied') {
            return response()->json([
                'message' => 'Room already occupied'
            ], 400);
        }

        $contract = RentalContract::create([
            'tenant_id' => $tenant->id,
            'room_id' => $room->id,
            'owner_id' => $user->id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status' => 'active'
        ]);

        $room->update([
            'status' => 'occupied'
        ]);

        return response()->json([
            'message' => 'Contract created',
            'data' => $contract
        ]);
    }

    public function show(Request $request, $id)
    {
        $contract = RentalContract::with(['tenant.user', 'room'])
            ->where('owner_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json($contract);
    }

    public function destroy(Request $request, $id)
    {
        $contract = RentalContract::where('owner_id', $request->user()->id)
            ->findOrFail($id);

        // balikin status room
        $contract->room->update([
            'status' => 'available'
        ]);

        $contract->delete();

        return response()->json([
            'message' => 'Contract deleted'
        ]);
    }
}

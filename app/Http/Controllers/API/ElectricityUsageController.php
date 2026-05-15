<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ElectricityUsage;
use App\Models\Room;
use Illuminate\Http\Request;

class ElectricityUsageController extends Controller
{
    public function index(Request $request)
    {
        $data = ElectricityUsage::with('room')->where('owner_id', $request->user()->id)->latest()->get();

        return response()->json([
            'data' => $data
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'room_id'       => 'required|exists:rooms,id',
            'token_amount'  => 'nullable|integer|min:0',
            'meter_start'   => 'nullable|numeric|min:0',
            'meter_end'     => 'nullable|numeric|min:0',
            'usage_date'    => 'required|date',
        ]);

        $ownerId = $request->user()->id;

        $room = Room::where('owner_id', $ownerId)->findOrFail($request->room_id);

        $usageKwh = null;
        $estimateBill = null;

        if (!is_null($request->meter_start) && !is_null($request->meter_end)) {
            if ($request->meter_end < $request->meter_start) {
                return response()->json([
                    'message' => 'meter_end must be greater than meter_start'
                ], 422);
            }

            $usageKwh = $request->meter_end - $request->meter_start;

            $tariff = 1500;

            $estimateBill = $usageKwh * $tariff;
        }

        $data = ElectricityUsage::create([
            'room_id'       => $room->id,
            'owner_id'      => $ownerId,
            'token_amount'  => $request->token_amount,
            'meter_start'   => $request->meter_start,
            'meter_end'     => $request->meter_end,
            'usage_kwh'     => $usageKwh,
            'estimate_bill' => $estimateBill,
            'usage_date'    => $request->usage_date,
        ]);

        return response()->json([
            'message' => 'Electricity usage created',
            'data' => $data
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $data = ElectricityUsage::with('room')->where('owner_id', $request->user()->id)->findOrFail($id);

        return response()->json($data);
    }

    public function update(Request $request, $id)
    {
        $data = ElectricityUsage::where('owner_id', $request->user()->id)
            ->findOrFail($id);

        $request->validate([
            'token_amount' => 'nullable|integer|min:0',
            'meter_start'  => 'nullable|numeric|min:0',
            'meter_end'    => 'nullable|numeric|min:0',
            'usage_date'   => 'nullable|date',
        ]);

        $meterStart = $request->meter_start ?? $data->meter_start;
        $meterEnd   = $request->meter_end ?? $data->meter_end;

        $usageKwh = $data->usage_kwh;
        $estimateBill = $data->estimate_bill;

        if (!is_null($meterStart) && !is_null($meterEnd)) {
            if ($meterEnd < $meterStart) {
                return response()->json([
                    'message' => 'meter_end must be greater than meter_start'
                ], 422);
            }

            $usageKwh = $meterEnd - $meterStart;
            $estimateBill = $usageKwh * 1500;
        }

        $data->update([
            'token_amount'  => $request->token_amount ?? $data->token_amount,
            'meter_start'   => $meterStart,
            'meter_end'     => $meterEnd,
            'usage_kwh'     => $usageKwh,
            'estimate_bill' => $estimateBill,
            'usage_date'    => $request->usage_date ?? $data->usage_date,
        ]);

        return response()->json([
            'message' => 'Electricity usage updated',
            'data' => $data
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $data = ElectricityUsage::where('owner_id', $request->user()->id)->findOrFail($id);

        $data->delete();

        return response()->json([
            'message' => 'Electricity usage deleted'
        ]);
    }
}

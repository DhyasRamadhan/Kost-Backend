<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $rooms = Room::where('owner_id', $request->user()->id)->get();

        return response()->json($rooms);
    }

    public function store(Request $request)
    {
        $request->validate([
            'room_number' => 'required|string|max:50',
            'price' => 'required|numeric|min:0'
        ]);

        $room = Room::create([
            'owner_id' => $request->user()->id,
            'room_number' => $request->room_number,
            'price' => $request->price,
            'status' => 'available'
        ]);

        return response()->json([
            'message' => 'Room created',
            'data' => $room
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $room = Room::where('owner_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json($room);
    }

    public function update(Request $request, $id)
    {
        $room = Room::where('owner_id', $request->user()->id)
            ->findOrFail($id);

        $room->update($request->only([
            'room_number',
            'price',
            'status'
        ]));

        return response()->json([
            'message' => 'Room updated',
            'data' => $room
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $room = Room::where('owner_id', $request->user()->id)
            ->findOrFail($id);

        $room->delete();

        return response()->json([
            'message' => 'Room deleted'
        ]);
    }
}

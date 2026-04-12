<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    // GET /rooms
    public function index(Request $request)
    {
        $rooms = Room::where('owner_id', $request->user()->id)->get();

        return response()->json([
            'message' => 'Rooms fetched successfully',
            'data' => $rooms
        ]);
    }

    // POST /rooms
    public function store(Request $request)
    {
        $request->validate([
            'room_number' => 'required',
            'price' => 'required|numeric',
            'status' => 'in:available,occupied'
        ]);

        $room = Room::create([
            'room_number' => $request->room_number,
            'price' => $request->price,
            'status' => $request->status ?? 'available',
            'owner_id' => $request->user()->id
        ]);

        return response()->json([
            'message' => 'Room created',
            'data' => $room
        ]);
    }

    // GET /rooms/{id}
    public function show(Request $request, $id)
    {
        $room = Room::where('owner_id', $request->user()->id)->findOrFail($id);

        return response()->json($room);
    }

    // PUT /rooms/{id}
    public function update(Request $request, $id)
    {
        $room = Room::where('owner_id', $request->user()->id)->findOrFail($id);
        $room->update($request->only(['room_number', 'price', 'status']));

        return response()->json([
            'message' => 'Room updated',
            'data' => $room
        ]);
    }

    // DELETE /rooms/{id}
    public function destroy(Request $request, $id)
    {
        $room = Room::where('owner_id', $request->user()->id)->findOrFail($id);
        $room->delete();

        return response()->json([
            'message' => 'Room deleted'
        ]);
    }
}

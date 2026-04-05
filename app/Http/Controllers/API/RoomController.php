<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    // GET /rooms
    public function index()
    {
        return response()->json(Room::all());
    }

    // POST /rooms
    public function store(Request $request)
    {
        $request->validate([
            'room_number' => 'required',
            'price' => 'required|numeric',
            'status' => 'in:available,occupied'
        ]);

        $room = Room::create($request->all());

        return response()->json([
            'message' => 'Room created',
            'data' => $room
        ]);
    }

    // GET /rooms/{id}
    public function show($id)
    {
        $room = Room::findOrFail($id);

        return response()->json($room);
    }

    // PUT /rooms/{id}
    public function update(Request $request, $id)
    {
        $room = Room::findOrFail($id);

        $room->update($request->all());

        return response()->json([
            'message' => 'Room updated',
            'data' => $room
        ]);
    }

    // DELETE /rooms/{id}
    public function destroy($id)
    {
        Room::destroy($id);

        return response()->json([
            'message' => 'Room deleted'
        ]);
    }
}

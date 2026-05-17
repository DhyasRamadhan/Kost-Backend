<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Room;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class RoomTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_room_successfully()
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'verification_status' => 'approved'
        ]);

        Sanctum::actingAs($owner);

        $response = $this->postJson('/api/rooms', [
            'room_number' => 'A-01',
            'price' => 750000
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Room created'
            ]);

        $this->assertDatabaseHas('rooms', [
            'room_number' => 'A-01',
            'price' => 750000,
            'status' => 'available'
        ]);
    }

    public function test_get_room_list()
    {
        $owner = User::factory()->create(['role' => 'owner']);

        Room::create([
            'owner_id' => $owner->id,
            'room_number' => 'A-01',
            'price' => 750000,
            'status' => 'available'
        ]);

        Sanctum::actingAs($owner);

        $response = $this->getJson('/api/rooms');

        $response->assertStatus(200);
    }

    public function test_update_room_successfully()
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $room = Room::create([
            'owner_id' => $owner->id,
            'room_number' => 'A-01',
            'price' => 750000,
            'status' => 'available'
        ]);

        Sanctum::actingAs($owner);

        $response = $this->putJson("/api/rooms/{$room->id}", [
            'room_number' => 'A-02',
            'price' => 900000,
            'status' => 'occupied'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Room updated'
            ]);

        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
            'room_number' => 'A-02',
            'price' => 900000,
            'status' => 'occupied'
        ]);
    }

    public function test_delete_room_successfully()
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $room = Room::create([
            'owner_id' => $owner->id,
            'room_number' => 'A-01',
            'price' => 750000,
            'status' => 'available'
        ]);

        Sanctum::actingAs($owner);

        $response = $this->deleteJson("/api/rooms/{$room->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Room deleted'
            ]);

        $this->assertDatabaseMissing('rooms', [
            'id' => $room->id
        ]);
    }
}

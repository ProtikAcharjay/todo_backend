<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Todo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TodoApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_user_can_view_their_todos()
    {
        Todo::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->getJson('/api/todos');

        $response->assertStatus(200)
                 ->assertJsonStructure(['status', 'todos']);
    }

    public function test_user_can_create_a_todo()
    {
        $data = [
            'title' => 'Test Todo',
            'description' => 'Testing description',
        ];

        $response = $this->actingAs($this->user)->postJson('/api/todos/create', $data);

        $response->assertStatus(201)
                 ->assertJsonFragment(['title' => 'Test Todo']);
    }

    public function test_user_can_update_a_todo()
    {
        $todo = Todo::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->putJson("/api/todos/update/{$todo->id}", [
            'title' => 'Updated title',
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['title' => 'Updated title']);
    }

    public function test_user_can_delete_a_todo()
    {
        $todo = Todo::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->deleteJson("/api/todos/delete/{$todo->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('todos', ['id' => $todo->id]);
    }

    public function test_user_can_reorder_todos()
    {
        $todos = Todo::factory()->count(2)->create(['user_id' => $this->user->id]);

        $data = [
            'todos' => [
                ['id' => $todos[0]->id, 'order' => 2],
                ['id' => $todos[1]->id, 'order' => 1],
            ],
        ];

        $response = $this->actingAs($this->user)->postJson('/api/todos/reorder', $data);

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'Order updated successfully.']);
    }

}

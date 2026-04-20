<?php

namespace Tests\Feature\Api\v1;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_get_list_of_tasks() : void {
        // Arrange
        Task::factory()->count(2)->create();

        // Act: make a get response
        $response = $this->getJson('/api/v1/tasks');

        // Assert
        $response->assertOk();
        $response->assertJsonCount(2, 'data');
        $response->assertJsonStructure([
            'data' => [
                ['id','name','completed']
            ]
        ]);
    }

    public function test_user_can_get_single_task() : void {
        //Arrange:
        $task = Task::factory()->create();

        // Act: Make a GET request to the end point with the task ID
        $response = $this->getJson("/api/v1/tasks/{$task->id}");

        //Assert:
        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'id','name','completed'
            ]
        ]);

        $response->assertJson([
            'data' => [
                'id' => $task->id,
                'name' => $task->name,
                'completed' => $task->completed
            ]
        ]);
    }

    // POST /tasks -> create a new task
    public function test_user_can_create_task() : void {
        $response = $this->postJson('/api/v1/tasks', [
            'name' => 'test new task',
        ]);

        $response->assertCreated();
        $response->assertJsonStructure([
            'data' => ['id','name','completed']
        ]);

        $this->assertDatabaseHas('tasks', [
            'name' => 'test new task',
        ]);
    }

    // `POST /tasks -> user cannot create an invalid task
    public function test_user_cannot_create_invalid_task() : void {
        // Arrange

        // Act
        $response = $this->postJson('/api/v1/tasks', [
            'name' => '',
        ]);

        // Assert
        $response->assertStatus(422);
        $response->assertJsonValidationErrorFor('name');
    }

    // `PUT /tasks/{id}` -> update existing task
    public function test_user_can_update_task() : void {
        // Arrange
        $task = Task::factory()->create();

        // Act
        $response = $this->putJson("/api/v1/tasks/{$task->id}", [
            'name' => 'updated task',
        ]);

        // Assert
        $response->assertOk();
        $response->assertJsonFragment([
            'name' => 'updated task',
        ]);
    }

    public function test_user_cannot_update_invalid_task() : void {
        // Arrange
        $task = Task::factory()->create();

        // Act
        $response = $this->putJson("/api/v1/tasks/{$task->id}", [
            'name' => '',
        ]);

        // Assert
        $response->assertStatus(422);
        $response->assertJsonValidationErrorFor('name');
    }

    // `PATCH /tasks/{id}/complete` -> mark the task as complete
    public function test_user_can_mark_task_complete() : void {
        $task = Task::factory()->create([
            'completed' => false,
        ]);

        $response = $this->patchJson("/api/v1/tasks/{$task->id}/complete", [
            'completed' => true,
        ]);

        // Assert
        $response->assertOk();
        $response->assertJsonFragment(['completed' => true]);
    }

    // `PATCH /tasks/{id}/complete` -> cannot update task with invalid complete
    public function test_user_cannot_mark_task_complete_with_invalid_data() : void {
        $task = Task::factory()->create([
            'completed' => false,
        ]);
        $response = $this->patchJson("/api/v1/tasks/{$task->id}/complete", [
            'completed' => 'true',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrorFor('completed');
    }

    // `PATCH /tasks/{id}/complete` -> mark the task as incomplete
    public function test_user_can_mark_task_incomplete() : void {
        $task = Task::factory()->create(['completed' => true]);
        $response = $this->patchJson("/api/v1/tasks/{$task->id}/complete", [
            'completed' => false,
        ]);
        $response->assertOk();
        $response->assertJsonFragment(['completed' => false]);
    }

    // `PATCH /tasks/{id}/complete` -> cannot mark the task as incomplete using invalid data
    public function test_user_cannot_mark_task_incomplete_with_invalid_data() : void {
        $task = Task::factory()->create(['completed' => true]);
        $response = $this->patchJson("/api/v1/tasks/{$task->id}/complete", [
            'completed' => 'no sir',
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrorFor('completed');
    }

    // `DELETE /tasks/{id}` -> user can delete task
    public function test_user_can_delete_task() : void {
        $task = Task::factory()->create();
        $response = $this->deleteJson("/api/v1/tasks/{$task->id}");
        $response->assertNoContent();
        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id,
        ]);
    }

}

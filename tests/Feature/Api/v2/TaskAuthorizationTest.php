<?php

namespace Tests\Feature\Api\v2;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use App\Models\Task;
use App\Models\User;

class TaskAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $otherUser;

    public function setUp(): void {
        parent::setUp();
        $this->owner = User::factory()->create();
        $this->otherUser = User::factory()->create();
    }

    public function test_user_can_view_own_tasks() : void {
        $task = Task::factory()->for($this->owner)->create();

        $this->actingAs($this->owner)
            ->getJson("/api/v2/tasks/{$task->id}")
            ->assertOk();
    }

    public function test_user_cannot_view_tasks_owned_by_others() : void {
        $task = Task::factory()->for($this->owner)->create();

        $this->actingAs($this->otherUser)
            ->getJson("/api/v2/tasks/{$task->id}")
            ->assertForbidden();
    }

    public function test_user_can_update_own_task() : void {
        $task = Task::factory()->for($this->owner)->create();

        $payload = ['name' => 'new task name'];

        $this->actingAs($this->owner)
            ->putJson("/api/v2/tasks/{$task->id}", $payload)
            ->assertOk();
    }

    public function test_user_cannot_update_tasks_owned_by_others() : void {
        $task = Task::factory()->for($this->owner)->create();

        $payload = ['name' => 'Unauthorized Update'];

        $this->actingAs($this->otherUser)
            ->putJson("/api/v2/tasks/{$task->id}", $payload)
            ->assertForbidden();
    }

    public function test_user_can_delete_own_task() : void {
        $task = Task::factory()->for($this->owner)->create();

        $this->actingAs($this->owner)
            ->deleteJson("/api/v2/tasks/{$task->id}")
            ->assertNoContent();
    }

    public function test_user_cannot_delete_tasks_owned_by_others() : void {
        $task = Task::factory()->for($this->owner)->create();
        $this->actingAs($this->otherUser)
            ->deleteJson("/api/v2/tasks/{$task->id}")
            ->assertForbidden();
    }

    public function test_user_can_complete_own_task() : void {
        $task = Task::factory()->for($this->owner)->create();

        $payload = ['completed' => true];

        $this->actingAs($this->owner)
            ->patchJson("/api/v2/tasks/{$task->id}/complete", $payload)
            ->assertOk();
    }

    public function test_user_cannot_complete_tasks_owned_by_others() : void {
        $task = Task::factory()->for($this->owner)->create();

        $payload = ['completed' => true];

        $this->actingAs($this->otherUser)
            ->patchJson("/api/v2/tasks/{$task->id}/complete", $payload)
            ->assertForbidden();
    }
}

<?php

namespace Tests\Feature;

use App\Models\TodoList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TodoListDuplicateFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_duplicate_todo_list_with_todos()
    {
        // Create a user
        $user = User::factory()->create();

        // Create a todo list owned by the user
        $originalList = $user->todoLists()->create([
            'name' => 'My Original List',
            'description' => 'Original description',
            'refresh_daily' => true,
        ]);

        // Create some todos for the list
        $originalList->todos()->create([
            'title' => 'First Todo',
            'completed' => true,
            'priority' => 'high',
        ]);

        $originalList->todos()->create([
            'title' => 'Second Todo',
            'completed' => false,
            'priority' => 'medium',
        ]);

        // Act as the user and duplicate the list
        $response = $this->actingAs($user)
            ->post(route('todo-lists.duplicate', $originalList->id));

        // Assert the response
        $response->assertRedirect();
        $response->assertSessionHas('success', 'List duplicated successfully!');

        // Assert the duplicated list exists
        $this->assertDatabaseHas('todo_lists', [
            'name' => 'My Original List (Copy)',
            'description' => 'Original description',
            'refresh_daily' => true,
        ]);

        // Get the duplicated list
        $duplicatedList = TodoList::where('name', 'My Original List (Copy)')->first();
        $this->assertNotNull($duplicatedList);

        // Assert the todos were duplicated
        $this->assertEquals(2, $duplicatedList->todos()->count());

        // Assert the duplicated todos have the correct attributes
        $duplicatedTodos = $duplicatedList->todos()->get();

        $this->assertEquals('First Todo', $duplicatedTodos[0]->title);
        $this->assertEquals(false, $duplicatedTodos[0]->completed); // Should be reset to false
        $this->assertEquals('high', $duplicatedTodos[0]->priority);

        $this->assertEquals('Second Todo', $duplicatedTodos[1]->title);
        $this->assertEquals(false, $duplicatedTodos[1]->completed); // Should be reset to false
        $this->assertEquals('medium', $duplicatedTodos[1]->priority);

        // Assert original list is unchanged
        $this->assertEquals(2, $originalList->todos()->count());
        $this->assertEquals('My Original List', $originalList->name);
    }

    public function test_duplicate_preserves_refresh_daily_setting()
    {
        $user = User::factory()->create();

        // Test with refresh_daily = false
        $list1 = $user->todoLists()->create([
            'name' => 'Non-Daily List',
            'refresh_daily' => false,
        ]);

        $response = $this->actingAs($user)
            ->post(route('todo-lists.duplicate', $list1->id));

        $response->assertRedirect();
        $this->assertDatabaseHas('todo_lists', [
            'name' => 'Non-Daily List (Copy)',
            'refresh_daily' => false,
        ]);

        // Test with refresh_daily = true
        $list2 = $user->todoLists()->create([
            'name' => 'Daily List',
            'refresh_daily' => true,
        ]);

        $response = $this->actingAs($user)
            ->post(route('todo-lists.duplicate', $list2->id));

        $response->assertRedirect();
        $this->assertDatabaseHas('todo_lists', [
            'name' => 'Daily List (Copy)',
            'refresh_daily' => true,
        ]);
    }
}

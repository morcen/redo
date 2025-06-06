<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For existing todo lists, assign them to the user who has the most todos in that list
        // If no todos exist for a list, assign to the first user (or delete the list)
        
        $todoLists = DB::table('todo_lists')->get();
        
        foreach ($todoLists as $todoList) {
            // Find the user who has the most todos in this list
            $userWithMostTodos = DB::table('todos')
                ->select('user_id', DB::raw('COUNT(*) as todo_count'))
                ->where('todo_list_id', $todoList->id)
                ->groupBy('user_id')
                ->orderBy('todo_count', 'desc')
                ->first();
            
            if ($userWithMostTodos) {
                // Assign the list to the user with the most todos
                DB::table('todo_lists')
                    ->where('id', $todoList->id)
                    ->update(['user_id' => $userWithMostTodos->user_id]);
            } else {
                // If no todos exist for this list, assign to the first user or delete
                $firstUser = DB::table('users')->first();
                if ($firstUser) {
                    DB::table('todo_lists')
                        ->where('id', $todoList->id)
                        ->update(['user_id' => $firstUser->id]);
                } else {
                    // No users exist, delete the orphaned list
                    DB::table('todo_lists')->where('id', $todoList->id)->delete();
                }
            }
        }

        // After populating data, make the user_id column non-nullable
        Schema::table('todo_lists', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove user_id from todo_lists (this will be handled by the schema migration rollback)
        // This migration is primarily for data population, so no specific rollback needed
    }
};

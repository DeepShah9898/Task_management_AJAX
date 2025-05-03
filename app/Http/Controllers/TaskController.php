<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;

class TaskController extends Controller
{
    // Show the main view
    public function index()
    {
        return view('tasks.index');
    }

    // AJAX: Fetch tasks with search and pagination
    public function fetchTasks(Request $request)
    {
        $search = $request->input('search');

        $query = Task::query();

        if ($search) {
            $query->where('title', 'like', '%' . $search . '%');
        }

        $tasks = $query->orderBy('created_at', 'desc')->paginate(5);

        return response()->json([
            'tasks' => $tasks
        ]);
    }

    // AJAX: Store a new task
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        $task = Task::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
        ]);

        return response()->json([
            'message' => 'Task created successfully!',
            'task' => $task
        ]);
    }

    // AJAX: Update an existing task
    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        $task->update($validated);

        return response()->json([
            'message' => 'Task updated successfully!',
            'task' => $task
        ]);
    }

    // AJAX: Delete a task
    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        $task->delete();

        return response()->json([
            'message' => 'Task deleted successfully!'
        ]);
    }

    // AJAX: Toggle task completion status
    public function toggleComplete($id)
    {
        $task = Task::findOrFail($id);
        $task->completed = !$task->completed;
        $task->save();

        return response()->json([
            'message' => 'Task completion status updated!',
            'completed' => $task->completed
        ]);
    }
}

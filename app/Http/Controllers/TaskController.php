<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;

class TaskController extends Controller
{
    
    public function index()
    {
        return view('tasks.index');
    }

    
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

    
    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        $task->delete();

        return response()->json([
            'message' => 'Task deleted successfully!'
        ]);
    }

    
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

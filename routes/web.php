<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;

// Route::get('/', function () {
//     return view('welcome');
// });


Route::get('/', [TaskController::class, 'index']);
Route::get('/fetch-tasks', [TaskController::class, 'fetchTasks']);
Route::post('/tasks', [TaskController::class, 'store']);
Route::put('/tasks/{id}', [TaskController::class, 'update']);
Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);
Route::patch('/tasks/{id}/toggle', [TaskController::class, 'toggleComplete']); // optional

<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\AuthController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::post('/login', [AuthController::class, 'login']);
Route::post('/tasks/{taskId}/assign/{userId}', [TaskController::class, 'assignUser']);
Route::match(['post', 'delete'], '/tasks/{taskId}/assign/{userId}', [TaskController::class, 'unassignUser']);
Route::patch('/tasks/{taskId}/change-status', [TaskController::class, 'changeStatus']);
Route::resource('tasks', TaskController::class);
Route::get('/user/{userId}/assigned-tasks', [TaskController::class, 'getAssignedTasks']); 
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {

    return $request->user();
});
?>
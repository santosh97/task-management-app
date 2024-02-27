<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::query();
    
        // Filter by status
        if ($request->has('status')) {
            $status = $request->input('status');
    
            // Ensure the provided status is valid (in:pending,in progress,completed)
            if (in_array($status, ['pending', 'in progress', 'completed'])) {
                $query->where('status', $status);
            } else {
                return response()->json(['error' => 'Invalid status value.'], 400);
            }
        }
    
        // Filter by date
        if ($request->has('date')) {
            $date = $request->input('date');
    
            // Ensure the provided date is in a valid format (Y-m-d)
            if (strtotime($date) === false) {
                return response()->json(['error' => 'Invalid date format. Use Y-m-d.'], 400);
            }
    
            $query->whereDate('due_date', $date);
        }
    
        // Filter by assigned user
        if ($request->has('user_id')) {
            $userId = $request->input('user_id');
    
            // Ensure the provided user ID is numeric
            if (!is_numeric($userId)) {
                return response()->json(['error' => 'Invalid user ID.'], 400);
            }
    
            $query->whereHas('assignedUsers', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            });
        }
    
        $tasks = $query->get();
    
        return response()->json(['tasks' => $tasks], 200);
    }


    public function show($id)
    {
        $task = Task::findOrFail($id);
        return response()->json(['task' => $task], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'status' => 'required|string|in:pending,in progress,completed',
        ]);

        $task = Task::create($request->all());
        return response()->json(['task' => $task], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'status' => 'required|string|in:pending,in progress,completed',
        ]);

        $task = Task::findOrFail($id);
        $task->update($request->all());
        return response()->json(['task' => $task], 200);
    }

    public function destroy($id)
    {
        Task::destroy($id);
        return response()->json(['message' => 'Task deleted successfully'], 200);
    }

    public function assignUser(Request $request, $taskId, $userId)
    {
        $task = Task::findOrFail($taskId);
        $user = User::findOrFail($userId);

        $task->assignedUsers()->syncWithoutDetaching([$userId]);

        return response()->json(['message' => 'User assigned to the task successfully'], 200);
    }

    public function getAssignedTasks($userId)
    {
        $user = User::findOrFail($userId);

        $assignedTasks = $user->assignedTasks;

        return response()->json(['assignedTasks' => $assignedTasks]);
    }

    public function unassignUser($taskId, $userId)
    {
        try {
            // Find the task
            $task = Task::findOrFail($taskId);

            // Find the user
            $user = User::findOrFail($userId);

            // Detach the user from the task
            $task->assignedUsers()->detach($user);

            return response()->json(['message' => 'User unassigned from the task successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error unassigning user from the task'], 500);
        }
    }

    public function changeStatus(Request $request, $taskId)
    {
        $task = Task::findOrFail($taskId);

        $request->validate([
            'status' => 'required|in:pending,in progress,completed',
        ]);

        $task->update(['status' => $request->input('status')]);

        return response()->json(['message' => 'Task status changed successfully'], 200);
    }


}

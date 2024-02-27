<?php
// app/Http/Controllers/AuthController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {
            $user = Auth::user();

            // Check if the user is not null before accessing relationships
            if ($user) {
                $assignedTasks = $user->assignedTasks;
                $taskIds = $assignedTasks ? $assignedTasks->pluck('id')->all() : [];

                $token = $user->createToken('authToken')->plainTextToken;

                return response()->json(['token' => $token, 'user' => $user, 'assignedTasksIDs' => $taskIds], 200);
            } else {
                // Handle the case where the user is null
                return response()->json(['error' => 'User not found'], 404);
            }
        }

        return response()->json(['error' => 'Invalid credentials'], 401);
    }
}

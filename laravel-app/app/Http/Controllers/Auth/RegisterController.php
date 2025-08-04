<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function userRegister(UserRegisterRequest $request)
    {
        // Validated data from the request
        $validated = $request->validated();

        // Create the user
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Return a response
        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user
        ], 201);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class ApiTokenController extends Controller
{
    // Show the form to create a token for a user
    public function create()
    {
        $users = User::all();
        return view('admin.api_tokens.create', compact('users'));
    }

    // Store and display the generated token
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'token_name' => 'required|string|max:255',
        ]);

        $user = User::findOrFail($request->user_id);
        $token = $user->createToken($request->token_name);

        // Show the token only once after creation
        return view('admin.api_tokens.show', [
            'token' => $token->plainTextToken,
            'user' => $user,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthenticationController extends Controller
{
    public function viewLogin()
    {
        if (!auth()->check()) {
            return view('login'); // or any page
        } else {
            return view('dashboard'); // login/home page
        }
    }

    public function viewRegister()
    {
        return view('register');
    }
    public function createUser(Request $request)
    {
        User::create([
            'name' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'WOW ' . $request->username
        ]);
    }
    public function loginUser(Request $request)
    {
        // Find the user by email
        $user = User::where('name', $request->email_username)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Check if password matches
        if (Hash::check($request->password, $user->password)) {

            auth()->login($user);
            return response()->json(['success' => true]);
        } else {

            return response()->json(['message' => 'Invalid password'], 401);
        }
    }
    public function logoutUser(Request $request)
    {
        auth()->logout(); // Log the current user out

        // Optional: invalidate the session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // OR if redirecting normally:
        return redirect('/login');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthenticationController extends Controller
{
	public function viewLogin()
	{
		return view('auth.login');
	}

	public function loginUser(Request $request)
	{
		$credentials = $request->validate([
			'email' => 'required|email',
			'password' => 'required|string'
		]);

		if (Auth::attempt($credentials, $request->boolean('remember'))) {
			$request->session()->regenerate();
			return redirect()->intended('/');
		}

		return back()->withErrors([
			'email' => 'The provided credentials do not match our records.',
		]);
	}

	public function viewRegister()
	{
		return view('auth.register');
	}

	public function createUser(Request $request)
	{
		$validated = $request->validate([
			'name' => 'required|string|max:255',
			'email' => 'required|email|unique:users,email',
			'password' => 'required|string|min:6|confirmed'
		]);

		$user = User::create([
			'name' => $validated['name'],
			'email' => $validated['email'],
			'password' => Hash::make($validated['password']),
		]);

		// Assign Customer role if Spatie is installed and role exists
		try {
			if (method_exists($user, 'assignRole')) {
				$user->assignRole('Customer');
			}
		} catch (\Exception $e) {
			// ignore
		}

		Auth::login($user);

		return redirect('/');
	}

	public function logoutUser(Request $request)
	{
		Auth::logout();
		$request->session()->invalidate();
		$request->session()->regenerateToken();

		return redirect('/');
	}
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required'],
        ]);

        $attempt = [
            'username' => $credentials['username'],
            'password' => $credentials['password'],
        ];

        if (Auth::attempt($attempt, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'username' => 'The provided credentials do not match our records.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::logout();
    
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    
        return redirect()->route('login');
    }

    // ✅ FORGOT PASSWORD: Show form
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    // ✅ FORGOT PASSWORD: Send OTP to email
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        $email = $request->email;
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $token = Str::random(64);

        // Store OTP in database
        DB::table('password_resets')->updateOrInsert(
            ['email' => $email],
            [
                'token' => $token,
                'otp' => bcrypt($otp),
                'otp_verified' => false,
                'otp_expires_at' => now()->addMinutes(15),
                'created_at' => now(),
            ]
        );

        // Send OTP via email
        try {
            Mail::send('emails.otp-email', ['otp' => $otp, 'email' => $email], function ($message) use ($email) {
                $message->to($email)
                    ->subject('Password Reset OTP - Quotation System');
            });

            return redirect()->route('verify.otp.form', ['email' => $email])
                ->with('status', 'OTP has been sent to your email. It will expire in 15 minutes.');
        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Failed to send OTP. Please try again.']);
        }
    }

    // ✅ FORGOT PASSWORD: Show OTP verification form
    public function showVerifyOtpForm(Request $request)
    {
        $email = $request->query('email');
        
        if (!$email) {
            return redirect()->route('forgot.password');
        }

        return view('auth.verify-otp', ['email' => $email]);
    }

    // ✅ FORGOT PASSWORD: Verify OTP
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'numeric', 'digits:6'],
        ]);

        $email = $request->email;
        $otp = $request->otp;

        $resetRecord = DB::table('password_resets')
            ->where('email', $email)
            ->first();

        if (!$resetRecord) {
            return back()->withErrors(['otp' => 'No password reset request found. Please try again.']);
        }

        // Check if OTP has expired
        if (now()->isAfter($resetRecord->otp_expires_at)) {
            DB::table('password_resets')->where('email', $email)->delete();
            return back()->withErrors(['otp' => 'OTP has expired. Please request a new one.']);
        }

        // Verify OTP
        if (Hash::check($otp, $resetRecord->otp)) {
            // Mark OTP as verified
            DB::table('password_resets')
                ->where('email', $email)
                ->update(['otp_verified' => true]);

            return redirect()->route('reset.password.form', ['token' => $resetRecord->token, 'email' => $email])
                ->with('status', 'OTP verified successfully. Please set your new password.');
        }

        return back()->withErrors(['otp' => 'Invalid OTP. Please try again.']);
    }

    // ✅ FORGOT PASSWORD: Show reset password form
    public function showResetPasswordForm(Request $request)
    {
        $token = $request->query('token');
        $email = $request->query('email');

        $resetRecord = DB::table('password_resets')
            ->where('email', $email)
            ->where('token', $token)
            ->first();

        if (!$resetRecord || !$resetRecord->otp_verified) {
            return redirect()->route('forgot.password')->withErrors(['error' => 'Invalid reset link or OTP not verified.']);
        }

        return view('auth.reset-password', ['token' => $token, 'email' => $email]);
    }

    // ✅ FORGOT PASSWORD: Reset password
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => [
                'required',
                'string',
                'min:12',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
            ],
        ], [
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character (@$!%*?&).',
        ]);

        $resetRecord = DB::table('password_resets')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$resetRecord) {
            return back()->withErrors(['email' => 'Invalid reset token.']);
        }

        // Update user password
        $user = \App\Models\User::where('email', $request->email)->first();
        
        if (!$user) {
            return back()->withErrors(['email' => 'User not found.']);
        }

        $user->update(['password' => bcrypt($request->password)]);

        // Delete password reset record
        DB::table('password_resets')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('status', 'Password has been reset successfully. Please login with your new password.');
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check() && Auth::user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            if (Auth::user()->role !== 'admin') {
                Auth::logout();
                return back()->withErrors(['email' => 'عذراً، الوصول للمسؤولين فقط.']);
            }
            
            $request->session()->regenerate();
            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors([
            'email' => 'بيانات الاعتماد المقدمة غير متطابقة مع سجلاتنا.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }

    // --- Admin Password Reset ---

    public function showForgotPasswordForm()
    {
        return view('admin.auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = \App\Models\User::where('email', $request->email)->first();

        if (! $user || $user->role !== 'admin') {
            // Security: We can still say it's sent to avoid enumeration or just redirect back with success
            return back()->with('success', 'إذا كان هذا البريد مسجلاً لدينا كمسؤول، فقد تم إرسال رابط الاستعادة.');
        }

        $token = \Illuminate\Support\Facades\Password::broker()->createToken($user);
        $url = route('admin.password.reset', ['token' => $token, 'email' => $user->email]);

        try {
            \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\PasswordResetMail($url));
            return back()->with('success', 'تم إرسال رابط استعادة كلمة المرور إلى بريدك الإلكتروني.');
        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'حدث خطأ أثناء إرسال البريد الإلكتروني.']);
        }
    }

    public function showResetPasswordForm(Request $request, $token)
    {
        return view('admin.auth.reset-password')->with([
            'token' => $token,
            'email' => $request->email
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        $status = \Illuminate\Support\Facades\Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => \Illuminate\Support\Facades\Hash::make($password)
                ])->setRememberToken(\Illuminate\Support\Str::random(60));

                $user->save();

                // Clear other sessions/tokens
                $user->tokens()->delete();

                try {
                    \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\PasswordResetConfirmationMail());
                } catch (\Exception $e) {
                    // Ignore mail errors on success
                }
            }
        );

        return $status === \Illuminate\Support\Facades\Password::PASSWORD_RESET
            ? redirect()->route('admin.login')->with('success', 'تم تغيير كلمة المرور بنجاح.')
            : back()->withErrors(['email' => 'حدث خطأ أو أن الرابط منتهي الصلاحية.']);
    }
}

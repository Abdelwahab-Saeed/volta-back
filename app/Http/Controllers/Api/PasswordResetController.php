<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ForgotPasswordRequest;
use App\Http\Requests\Api\ResetPasswordRequest;
use App\Models\User;
use App\Mail\PasswordResetMail;
use App\Mail\PasswordResetConfirmationMail;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    use ApiResponse;

    /**
     * Generate token and send reset email.
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        // Security: Return generic success even if user doesn't exist to prevent enumeration
        // The ForgotPasswordRequest already checks 'exists:users,email' but returns a generic message in case of failure.

        $token = Password::broker()->createToken($user);
        $url = url(config('app.frontend_url').'/reset-password?token='.$token.'&email='.urlencode($user->email));

        try {
            Mail::to($user->email)->send(new PasswordResetMail($url));
            
            Log::info("Password reset link sent to: {$user->email}");

            return $this->successResponse(
                null,
                'إذا كان هذا البريد موجوداً في نظامنا، فقد تم إرسال رابط استعادة كلمة المرور.'
            );
        } catch (\Exception $e) {
            Log::error("Failed to send password reset email to {$user->email}: " . $e->getMessage());
            return $this->errorResponse('حدث خطأ أثناء إرسال البريد الإلكتروني. يرجى المحاولة لاحقاً.', 500);
        }
    }

    /**
     * Handle password reset.
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $status = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                // Invalidate all existing tokens (sessions) for this user
                $user->tokens()->delete();

                try {
                    Mail::to($user->email)->send(new PasswordResetConfirmationMail());
                } catch (\Exception $e) {
                    Log::error("Failed to send password reset confirmation email to {$user->email}: " . $e->getMessage());
                }
                
                Log::info("Password reset successful for user: {$user->email}");
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return $this->successResponse(null, 'تم إعادة تعيين كلمة المرور بنجاح.');
        }

        // Handle errors (invalid token, expired, etc.)
        $message = match($status) {
            Password::INVALID_TOKEN => 'رمز التحقق غير صحيح أو منتهي الصلاحية.',
            Password::INVALID_USER => 'لا يمكن العثور على مستخدم بهذا البريد الإلكتروني.',
            default => 'حدث خطأ غير متوقع أثناء إعادة تعيين كلمة المرور.'
        };

        return $this->errorResponse($message, 400);
    }
}

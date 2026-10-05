<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ImageUploader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use App\Traits\ApiResponse;

class AuthController extends Controller
{
    use ApiResponse;

    protected $metaService;

    public function __construct(\App\Services\MetaService $metaService)
    {
        $this->metaService = $metaService;
    }

    // REGISTER
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            // A deleted account's email can register again (as a new, empty account)
            'email' => ['required', 'email', Rule::unique('users', 'email')->withoutTrashed()],
            'password' => 'required|min:8|confirmed',
            'phone_number' => 'required|string|max:11',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone_number' => $data['phone_number'],
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        // $this->metaService->sendRegistration($user);

        return $this->successResponse([
            'user' => $user,
            'token' => $token,
        ], 'تم إنشاء الحساب بنجاح', 201);
    }

    // LOGIN
    public function login(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string|max:255',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->identifier)->orWhere('phone_number', $request->identifier)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return $this->errorResponse('بيانات الدخول غير صحيحة', 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'user' => $user,
            'token' => $token,
        ], 'تم تسجيل الدخول بنجاح');
    }

    // LOGOUT
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'تم تسجيل الخروج بنجاح');
    }

    // AUTH USER
    public function me(Request $request)
    {
        return $this->successResponse($request->user(), 'تم جلب بيانات المستخدم بنجاح');
    }

    // UPDATE PROFILE
    public function updateProfile(Request $request, ImageUploader $images)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => ['nullable', 'email', Rule::unique('users')->ignore($user->id)->withoutTrashed()],
            'phone_number' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'image' => 'nullable|image|max:10240', // 10MB max; stored as a resized WebP
        ]);

        if ($request->hasFile('image')) {
            if ($user->image) {
                Storage::disk('public')->delete($user->image);
            }

            $data['image'] = $images->store($request->file('image'), 'profile_images');
        }

        $user->fill($data);
        $user->save();

        return $this->successResponse($user, 'تم تحديث الملف الشخصي بنجاح');
    }

    // CHANGE PASSWORD
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|string|min:8|confirmed|max:255',
        ]);

        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return $this->successResponse(null, 'تم تغيير كلمة المرور بنجاح');
    }
}

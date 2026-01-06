<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->paginate(15);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'role' => 'required|in:user,admin',
            'phone_number' => 'nullable|string|max:20',
            'image' => 'nullable|image|max:2048',
        ]);

        $data = $request->all();
        $data['password'] = Hash::make($request->password);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('uploads/users', 'public');
        }

        User::create($data);

        return redirect()->route('admin.users.index')->with('success', 'تم إضافة المستخدم بنجاح.');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:8',
            'role' => 'required|in:user,admin',
            'phone_number' => 'nullable|string|max:20',
            'image' => 'nullable|image|max:2048',
        ]);

        $data = $request->except('password');
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('image')) {
            if ($user->image) {
                Storage::disk('public')->delete($user->image);
            }
            $data['image'] = $request->file('image')->store('uploads/users', 'public');
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'تم تحديث بيانات المستخدم بنجاح.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')->with('error', 'لا يمكنك حذف حسابك الخاص.');
        }

        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'تم حذف المستخدم بنجاح.');
    }
}

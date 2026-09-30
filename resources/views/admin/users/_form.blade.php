{{-- Shared by create and edit. $user is null on create (then the password is required). --}}
@php($user = $user ?? null)
<div class="space-y-8">
    <section class="space-y-5">
        <h2 class="section-title">البيانات الأساسية</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label for="name" class="label required">الاسم الكامل</label>
                <input type="text" name="name" id="name" value="{{ old('name', $user?->name) }}" placeholder="أحمد محمد" class="input @error('name') input-error @enderror">
                @error('name')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="email" class="label required">البريد الإلكتروني</label>
                <input type="email" name="email" id="email" value="{{ old('email', $user?->email) }}" placeholder="example@mail.com" dir="ltr" class="input text-left @error('email') input-error @enderror">
                @error('email')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="phone_number" class="label">رقم الهاتف</label>
                <input type="text" name="phone_number" id="phone_number" value="{{ old('phone_number', $user?->phone_number) }}" placeholder="01xxxxxxxxx" dir="ltr" class="input text-left @error('phone_number') input-error @enderror">
            </div>
            <div>
                <label for="role" class="label">الصلاحية</label>
                <select name="role" id="role" class="input">
                    <option value="user" {{ old('role', $user?->role) == 'user' ? 'selected' : '' }}>مستخدم (عميل)</option>
                    <option value="admin" {{ old('role', $user?->role) == 'admin' ? 'selected' : '' }}>مسؤول (دخول لوحة التحكم)</option>
                </select>
            </div>
        </div>
    </section>

    <section class="space-y-5 pt-6 border-t border-slate-100">
        <div>
            <h2 class="section-title"><x-admin.icon name="lock" class="w-4 h-4 text-slate-400" /> {{ $user ? 'تغيير كلمة المرور (اختياري)' : 'كلمة المرور' }}</h2>
            @if($user)<p class="hint mt-1">اتركهما فارغين للإبقاء على كلمة المرور الحالية.</p>@endif
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label for="password" class="label {{ $user ? '' : 'required' }}">{{ $user ? 'كلمة المرور الجديدة' : 'كلمة المرور' }}</label>
                <input type="password" name="password" id="password" autocomplete="new-password" class="input @error('password') input-error @enderror">
                @error('password')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="password_confirmation" class="label {{ $user ? '' : 'required' }}">تأكيد كلمة المرور</label>
                <input type="password" name="password_confirmation" id="password_confirmation" autocomplete="new-password" class="input">
            </div>
        </div>
    </section>

    <section class="pt-6 border-t border-slate-100">
        @include('admin.partials.image-upload', [
            'name' => 'image',
            'label' => $user ? 'تغيير صورة الحساب' : 'صورة الحساب',
            'current' => $user?->image,
            'accept' => 'image/*',
            'hint' => 'اختيارية',
            'previewClass' => 'w-20 h-20 object-cover rounded-full',
        ])
    </section>
</div>

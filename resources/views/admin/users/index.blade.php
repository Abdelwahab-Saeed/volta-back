@extends('admin.layouts.app')

@section('title', 'المستخدمون')
@section('subtitle', 'حسابات العملاء والمسؤولين وصلاحياتهم.')

@section('actions')
    <a href="{{ route('admin.users.create') }}" class="btn-primary">
        <x-admin.icon name="plus" class="w-4 h-4" />
        إضافة مستخدم
    </a>
@endsection

@section('content')
<div class="card overflow-hidden">
    @if($users->isEmpty())
        <x-admin.empty-state icon="users" title="لا يوجد مستخدمون حالياً" />
    @else
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>المستخدم</th>
                        <th>الصلاحية</th>
                        <th>الهاتف</th>
                        <th>تاريخ الانضمام</th>
                        <th class="text-left">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3 min-w-[14rem]">
                                @if($user->image)
                                    <img class="w-10 h-10 rounded-full object-cover bg-slate-100" src="{{ asset('storage/' . $user->image) }}" alt="">
                                @else
                                    <span class="w-10 h-10 rounded-full bg-gradient-to-br from-brand-100 to-navy-100 text-navy-800 flex items-center justify-center font-extrabold">{{ mb_substr($user->name, 0, 1) }}</span>
                                @endif
                                <div class="min-w-0">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="font-bold text-navy-900 hover:text-brand-700 truncate block">{{ $user->name }} @if($user->id === auth()->id())<span class="text-xs font-semibold text-slate-400">(أنت)</span>@endif</a>
                                    <p class="text-xs text-slate-400 truncate" dir="ltr">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($user->role === 'admin')
                                <span class="badge-navy"><x-admin.icon name="shield" class="w-3.5 h-3.5" /> مسؤول</span>
                            @else
                                <span class="badge-neutral">مستخدم</span>
                            @endif
                        </td>
                        <td class="text-slate-600 whitespace-nowrap" dir="ltr">{{ $user->phone_number ?? '—' }}</td>
                        <td class="text-slate-500 whitespace-nowrap">{{ $user->created_at?->format('Y/m/d') ?? 'غير متوفر' }}</td>
                        <td>
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('admin.users.edit', $user) }}" class="icon-btn-primary" data-tip="تعديل" aria-label="تعديل"><x-admin.icon name="edit" class="w-[18px] h-[18px]" /></a>
                                @if($user->id !== auth()->id())
                                    <form id="delete-user-{{ $user->id }}" action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" onclick="confirmAction('delete-user-{{ $user->id }}', 'هل أنت متأكد من أرشفة هذا المستخدم؟')" class="icon-btn-danger" data-tip="حذف" aria-label="حذف">
                                            <x-admin.icon name="trash" class="w-[18px] h-[18px]" />
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div class="card-footer">{{ $users->links() }}</div>
        @endif
    @endif
</div>
@endsection

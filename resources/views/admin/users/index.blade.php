@extends('admin.layouts.app')

@section('title', 'إدارة المستخدمين')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between mb-8 space-y-4 sm:space-y-0 text-right">
    <div>
        <p class="text-gray-500 font-medium">تحكم في صلاحيات مستخدمي المنصة وبياناتهم.</p>
    </div>
    <a href="{{ route('admin.users.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl shadow-md hover:shadow-lg transition-all font-bold flex items-center justify-center sm:w-auto">
        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
        إضافة مستخدم جديد
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-right whitespace-nowrap">
            <thead class="bg-gray-50/50 text-gray-400 text-xs uppercase font-bold border-b border-gray-100">
                <tr>
                    <th class="px-6 py-4">المستخدم</th>
                    <th class="px-6 py-4 text-center">الصلاحية</th>
                    <th class="px-6 py-4 text-center">الهاتف</th>
                    <th class="px-6 py-4 text-center">تاريخ الانضمام</th>
                    <th class="px-6 py-4 text-left">العمليات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($users as $user)
                <tr class="hover:bg-gray-50/80 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10 ml-4">
                                @if($user->image)
                                    <img class="h-10 w-10 rounded-xl object-cover bg-gray-100 shadow-sm" src="{{ asset('storage/' . $user->image) }}" alt="">
                                @else
                                    <div class="h-10 w-10 bg-gradient-to-br from-blue-50 to-blue-100 text-blue-600 rounded-xl flex items-center justify-center font-black shadow-sm">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                @endif
                            </div>
                            <div>
                                <div class="text-sm font-black text-gray-900">{{ $user->name }}</div>
                                <div class="text-[10px] text-gray-400 font-medium">{{ $user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold 
                            {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-slate-100 text-slate-600' }}">
                            @if($user->role === 'admin')
                                <svg class="w-3 h-3 ml-1.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.9L9.03 9.069a1.003 1.003 0 001.94 0L17.834 4.9a1 1 0 00-1.168-1.564l-6.666 4.032-6.666-4.032A1 1 0 002.166 4.9z" clip-rule="evenodd"></path><path d="M11 11.53l6.834-4.133a1.001 1.001 0 011.166 1.564l-7.483 4.532a1.003 1.003 0 01-1.034 0L2.983 8.961a1.003 1.003 0 011.034-1.732l7.483 4.532a1.001 1.001 0 01.5 0z"></path></svg>
                            @endif
                            {{ $user->role === 'admin' ? 'مسؤول' : 'مستخدم' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-sm font-bold text-gray-600 bg-gray-50 px-3 py-1 rounded-lg">
                            {{ $user->phone_number ?? 'غير مسجل' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-sm text-gray-400 font-bold">
                            {{ $user->created_at->format('Y/m/d') }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-left">
                        <div class="flex items-center justify-start space-x-reverse space-x-2">
                            <a href="{{ route('admin.users.edit', $user) }}" class="inline-flex p-2 text-blue-600 hover:bg-blue-50 rounded-xl transition-colors border border-transparent hover:border-blue-100" title="تعديل">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </a>
                            @if($user->id !== auth()->id())
                            <form id="delete-user-{{ $user->id }}" action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="button" onclick="confirmAction('delete-user-{{ $user->id }}', 'هل أنت متأكد من أرشفة هذا المستخدم؟')" 
                                    class="p-2 text-red-600 hover:bg-red-50 rounded-xl transition-colors border border-transparent hover:border-red-100" title="حذف">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-gray-500 font-bold">لا يوجد مستخدمين حالياً.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
    <div class="p-6 bg-gray-50/50 border-t border-gray-100">
        {{ $users->links() }}
    </div>
    @endif
</div>
@endsection

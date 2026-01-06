@extends('admin.layouts.app')

@section('title', 'إدارة البانرات')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between mb-8 space-y-4 sm:space-y-0 text-right">
    <div>
        <h1 class="text-2xl font-black text-gray-900">البانرات</h1>
        <p class="text-gray-500 font-medium">إدارة واجهة المتجر واللوحات الإعلانية.</p>
    </div>
    <a href="{{ route('admin.banners.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl shadow-md hover:shadow-lg transition-all font-bold flex items-center justify-center sm:w-auto">
        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
        إضافة بانر جديد
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-right whitespace-nowrap">
            <thead class="bg-gray-50/50 text-gray-500 text-xs uppercase font-bold border-b border-gray-100">
                <tr>
                    <th class="px-6 py-4">البانر</th>
                    <th class="px-6 py-4">العنوان</th>
                    <th class="px-6 py-4 text-center">الحالة</th>
                    <th class="px-6 py-4 text-center">تاريخ الإضافة</th>
                    <th class="px-6 py-4 text-left">العمليات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($banners as $banner)
                <tr class="hover:bg-gray-50/80 transition-colors">
                    <td class="px-6 py-4">
                        <div class="w-32 h-16 rounded-lg overflow-hidden bg-gray-100 border border-gray-100">
                            @if($banner->image)
                                <img src="{{ asset('storage/' . $banner->image) }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-300">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="max-w-xs">
                            <p class="font-black text-gray-900 text-sm truncate">{{ $banner->title }}</p>
                            <p class="text-[10px] text-gray-400 truncate">{{ $banner->description }}</p>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $banner->status ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $banner->status ? 'نشط' : 'غير نشط' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center text-gray-500 text-sm">
                        {{ $banner->created_at->format('Y-m-d') }}
                    </td>
                    <td class="px-6 py-4 text-left">
                        <div class="flex items-center justify-start space-x-reverse space-x-2">
                            <a href="{{ route('admin.banners.edit', $banner) }}" class="inline-flex p-2 text-blue-600 hover:bg-blue-50 rounded-xl transition-colors border border-transparent hover:border-blue-100" title="تعديل">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </a>
                            <form id="delete-banner-{{ $banner->id }}" action="{{ route('admin.banners.destroy', $banner) }}" method="POST" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="button" onclick="confirmAction('delete-banner-{{ $banner->id }}', 'هل أنت متأكد من حذف هذا البانر؟')" 
                                    class="p-2 text-red-600 hover:bg-red-50 rounded-xl transition-colors border border-transparent hover:border-red-100" title="حذف">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                        <div class="flex flex-col items-center">
                            <svg class="w-12 h-12 text-gray-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path></svg>
                            <p class="text-lg font-black mb-2">لا توجد بانرات حالياً</p>
                            <a href="{{ route('admin.banners.create') }}" class="text-blue-600 hover:text-blue-700 font-bold underline transition-colors">أضف أول بانر الآن</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($banners->hasPages())
    <div class="p-6 bg-gray-50/50 border-t border-gray-100">
        {{ $banners->links() }}
    </div>
    @endif
</div>
@endsection

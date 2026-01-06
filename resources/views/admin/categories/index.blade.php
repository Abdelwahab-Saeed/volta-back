@extends('admin.layouts.app')

@section('title', 'إدارة الأقسام')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between mb-8 space-y-4 sm:space-y-0 text-right">
    <div>
        <p class="text-gray-500 font-medium">نظم أقسام منتجاتك بسهولة وفعالية.</p>
    </div>
    <a href="{{ route('admin.categories.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl shadow-md hover:shadow-lg transition-all font-bold flex items-center justify-center sm:w-auto">
        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
        إضافة قسم جديد
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-right whitespace-nowrap">
            <thead class="bg-gray-50/50 text-gray-500 text-xs uppercase font-bold border-b border-gray-100">
                <tr>
                    <th class="px-6 py-4">القسم</th>
                    <th class="px-6 py-4 text-center">الحالة</th>
                    <th class="px-6 py-4 text-center">المنتجات</th>
                    <th class="px-6 py-4 text-left">العمليات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($categories as $category)
                <tr class="hover:bg-gray-50/80 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-12 w-12 ml-4">
                                @if($category->image)
                                    <img src="{{ asset('storage/' . $category->image) }}" class="w-12 h-12 rounded-xl object-cover bg-gray-100 shadow-sm">
                                @else
                                    <div class="w-12 h-12 bg-gray-50 rounded-xl flex items-center justify-center text-gray-300 border border-dashed border-gray-200">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    </div>
                                @endif
                            </div>
                            <div class="max-w-[200px] sm:max-w-xs">
                                <p class="font-black text-gray-900 text-sm truncate">{{ $category->name }}</p>
                                <p class="text-[10px] text-gray-400 truncate">{{ $category->description }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $category->status ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                            <span class="w-1.5 h-1.5 rounded-full ml-2 {{ $category->status ? 'bg-emerald-500 animate-pulse' : 'bg-gray-400' }}"></span>
                            {{ $category->status ? 'نشط' : 'غير نشط' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-sm font-bold text-gray-600 bg-gray-50 px-3 py-1 rounded-lg">
                            {{ $category->products_count ?? 0 }} <span class="text-xs opacity-75">منتج</span>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-left">
                        <div class="flex items-center justify-start space-x-reverse space-x-2">
                            <a href="{{ route('admin.categories.edit', $category) }}" class="inline-flex p-2 text-blue-600 hover:bg-blue-50 rounded-xl transition-colors border border-transparent hover:border-blue-100" title="تعديل">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </a>
                            <form id="delete-category-{{ $category->id }}" action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="button" onclick="confirmAction('delete-category-{{ $category->id }}', 'هل أنت متأكد من أرشفة هذا القسم؟ قد يؤثر ذلك على المنتجات التابعة له.')" 
                                    class="p-2 text-red-600 hover:bg-red-50 rounded-xl transition-colors border border-transparent hover:border-red-100" title="حذف">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                        <div class="flex flex-col items-center">
                            <svg class="w-12 h-12 text-gray-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            <p class="text-lg font-black mb-2">لا توجد أقسام حالياً</p>
                            <a href="{{ route('admin.categories.create') }}" class="text-blue-600 hover:text-blue-700 font-bold underline transition-colors">أضف أول قسم للمتجر الآن</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($categories->hasPages())
    <div class="p-6 bg-gray-50/50 border-t border-gray-100">
        {{ $categories->links() }}
    </div>
    @endif
</div>
@endsection

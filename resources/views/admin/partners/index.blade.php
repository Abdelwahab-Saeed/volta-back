@extends('admin.layouts.app')

@section('title', 'الشركاء والعملاء')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4 text-right">
    <div>
        <h1 class="text-2xl font-black text-gray-900">الشركاء والعملاء</h1>
        <p class="text-gray-500 font-medium">الشعارات التي تظهر في قسم "شركاؤنا وعملاؤنا" بالصفحة الرئيسية.</p>
    </div>
    <a href="{{ route('admin.partners.create', ['type' => $type ?? 'partner']) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl shadow-md hover:shadow-lg transition-all font-bold flex items-center justify-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
        إضافة جديد
    </a>
</div>

<div class="flex gap-2 mb-4">
    @foreach ([null => 'الكل', 'partner' => 'الشركاء', 'client' => 'العملاء'] as $value => $label)
        <a href="{{ route('admin.partners.index', $value ? ['type' => $value] : []) }}"
           class="px-4 py-2 rounded-xl text-sm font-bold transition-colors {{ ($type ?? null) === ($value ?: null) ? 'bg-slate-900 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">{{ $label }}</a>
    @endforeach
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-right whitespace-nowrap">
            <thead class="bg-gray-50/50 text-gray-500 text-xs font-bold border-b border-gray-100">
                <tr>
                    <th class="px-6 py-4">الشعار</th>
                    <th class="px-6 py-4">الاسم</th>
                    <th class="px-6 py-4 text-center">النوع</th>
                    <th class="px-6 py-4 text-center">الترتيب</th>
                    <th class="px-6 py-4 text-center">الحالة</th>
                    <th class="px-6 py-4 text-left">العمليات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($partners as $partner)
                <tr class="hover:bg-gray-50/80 transition-colors">
                    <td class="px-6 py-4">
                        <div class="w-28 h-14 rounded-lg bg-gray-50 border border-gray-100 p-1.5 flex items-center justify-center">
                            <img src="{{ asset('storage/' . $partner->logo) }}" alt="" class="max-w-full max-h-full object-contain">
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <p class="font-black text-gray-900 text-sm">{{ $partner->name_ar ?: $partner->name_en }}</p>
                        @if($partner->website_url)
                            <a href="{{ $partner->website_url }}" target="_blank" rel="noopener" dir="ltr" class="text-xs text-blue-600 hover:underline">{{ \Illuminate\Support\Str::limit($partner->website_url, 40) }}</a>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold {{ $partner->type === 'client' ? 'bg-sky-100 text-sky-700' : 'bg-indigo-100 text-indigo-700' }}">
                            {{ $partner->type === 'client' ? 'عميل' : 'شريك' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center text-gray-500 text-sm">{{ $partner->sort_order }}</td>
                    <td class="px-6 py-4 text-center">@include('admin.partials.status-badge', ['active' => $partner->is_active])</td>
                    <td class="px-6 py-4">
                        @include('admin.partials.row-actions', [
                            'edit' => route('admin.partners.edit', $partner),
                            'destroy' => route('admin.partners.destroy', $partner),
                            'id' => 'delete-partner-' . $partner->id,
                            'confirm' => 'هل أنت متأكد من الحذف؟',
                        ])
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                        <p class="text-lg font-black mb-2">لا يوجد شركاء أو عملاء بعد</p>
                        <p class="text-sm mb-3">القسم لا يظهر في الموقع حتى تضيف شعاراً واحداً على الأقل.</p>
                        <a href="{{ route('admin.partners.create', ['type' => $type ?? 'partner']) }}" class="text-blue-600 hover:text-blue-700 font-bold underline">أضف أول شعار الآن</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($partners->hasPages())
    <div class="p-6 bg-gray-50/50 border-t border-gray-100">{{ $partners->links() }}</div>
    @endif
</div>
@endsection

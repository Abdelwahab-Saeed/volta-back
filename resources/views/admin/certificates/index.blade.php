@extends('admin.layouts.app')

@section('title', 'الشهادات')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between mb-8 gap-4 text-right">
    <div>
        <h1 class="text-2xl font-black text-gray-900">الشهادات والاعتمادات</h1>
        <p class="text-gray-500 font-medium">تظهر في قسم "شهاداتنا" بالصفحة الرئيسية، ويمكن للزائر فتح الصورة بالحجم الكامل.</p>
    </div>
    <a href="{{ route('admin.certificates.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl shadow-md hover:shadow-lg transition-all font-bold flex items-center justify-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
        إضافة شهادة
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-right whitespace-nowrap">
            <thead class="bg-gray-50/50 text-gray-500 text-xs font-bold border-b border-gray-100">
                <tr>
                    <th class="px-6 py-4">الصورة</th>
                    <th class="px-6 py-4">الشهادة</th>
                    <th class="px-6 py-4 text-center">السنة</th>
                    <th class="px-6 py-4 text-center">الترتيب</th>
                    <th class="px-6 py-4 text-center">الحالة</th>
                    <th class="px-6 py-4 text-left">العمليات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($certificates as $certificate)
                <tr class="hover:bg-gray-50/80 transition-colors">
                    <td class="px-6 py-4">
                        <img src="{{ asset('storage/' . $certificate->image) }}" alt="" class="w-16 h-20 rounded-lg object-cover border border-gray-100">
                    </td>
                    <td class="px-6 py-4">
                        <p class="font-black text-gray-900 text-sm">{{ $certificate->title_ar ?: $certificate->title_en }}</p>
                        <p class="text-xs text-gray-400">{{ $certificate->issuer_ar ?: $certificate->issuer_en }}</p>
                    </td>
                    <td class="px-6 py-4 text-center text-gray-500 text-sm">{{ $certificate->issued_year ?? '—' }}</td>
                    <td class="px-6 py-4 text-center text-gray-500 text-sm">{{ $certificate->sort_order }}</td>
                    <td class="px-6 py-4 text-center">@include('admin.partials.status-badge', ['active' => $certificate->is_active])</td>
                    <td class="px-6 py-4">
                        @include('admin.partials.row-actions', [
                            'edit' => route('admin.certificates.edit', $certificate),
                            'destroy' => route('admin.certificates.destroy', $certificate),
                            'id' => 'delete-certificate-' . $certificate->id,
                            'confirm' => 'هل أنت متأكد من حذف هذه الشهادة؟',
                        ])
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                        <p class="text-lg font-black mb-2">لا توجد شهادات بعد</p>
                        <p class="text-sm mb-3">القسم لا يظهر في الموقع حتى تضيف شهادة واحدة على الأقل.</p>
                        <a href="{{ route('admin.certificates.create') }}" class="text-blue-600 hover:text-blue-700 font-bold underline">أضف أول شهادة الآن</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($certificates->hasPages())
    <div class="p-6 bg-gray-50/50 border-t border-gray-100">{{ $certificates->links() }}</div>
    @endif
</div>
@endsection

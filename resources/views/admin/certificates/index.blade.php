@extends('admin.layouts.app')

@section('title', 'الشهادات')
@section('subtitle', 'تظهر في قسم "شهاداتنا" بالصفحة الرئيسية، ويمكن للزائر فتح الصورة بالحجم الكامل.')

@section('actions')
    <a href="{{ route('admin.certificates.create') }}" class="btn-primary">
        <x-admin.icon name="plus" class="w-4 h-4" />
        إضافة شهادة
    </a>
@endsection

@section('content')
@if($certificates->isEmpty())
    <div class="card">
        <x-admin.empty-state icon="badge" title="لا توجد شهادات بعد" text="القسم لا يظهر في الموقع حتى تضيف شهادة واحدة على الأقل." :action="route('admin.certificates.create')" action-label="أضف أول شهادة" />
    </div>
@else
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @foreach($certificates as $certificate)
            <article class="card overflow-hidden flex flex-col">
                <div class="relative aspect-[4/3] bg-gradient-to-b from-slate-50 to-slate-100 p-4 flex items-center justify-center">
                    <img src="{{ asset('storage/' . $certificate->image) }}" alt="" class="max-h-full max-w-full object-contain drop-shadow {{ $certificate->is_active ? '' : 'grayscale opacity-60' }}">
                    <span class="absolute top-3 right-3">@include('admin.partials.status-badge', ['active' => $certificate->is_active])</span>
                    @if($certificate->issued_year)
                        <span class="absolute bottom-3 right-3 rounded-full bg-navy-900 px-2.5 py-0.5 text-xs font-bold text-white">{{ $certificate->issued_year }}</span>
                    @endif
                </div>
                <div class="flex items-start gap-2 p-4 flex-1">
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-navy-900 line-clamp-2">{{ $certificate->title_ar ?: $certificate->title_en }}</p>
                        <p class="text-xs text-slate-400 mt-0.5 truncate">{{ $certificate->issuer_ar ?: $certificate->issuer_en }}</p>
                        <p class="text-[11px] text-slate-400 mt-1">الترتيب: {{ $certificate->sort_order }}</p>
                    </div>
                    @include('admin.partials.row-actions', [
                        'edit' => route('admin.certificates.edit', $certificate),
                        'destroy' => route('admin.certificates.destroy', $certificate),
                        'id' => 'delete-certificate-' . $certificate->id,
                        'confirm' => 'هل أنت متأكد من حذف هذه الشهادة؟',
                    ])
                </div>
            </article>
        @endforeach
    </div>
    @if($certificates->hasPages())
        <div class="card mt-6 px-5 py-4">{{ $certificates->links() }}</div>
    @endif
@endif
@endsection

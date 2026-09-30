@extends('admin.layouts.app')

@section('title', 'الشركاء والعملاء')
@section('subtitle', 'الشعارات التي تظهر في قسم "شركاؤنا وعملاؤنا" بالصفحة الرئيسية.')

@section('actions')
    <a href="{{ route('admin.partners.create', ['type' => $type ?? 'partner']) }}" class="btn-primary">
        <x-admin.icon name="plus" class="w-4 h-4" />
        إضافة شعار
    </a>
@endsection

@section('content')
<div class="inline-flex p-1 mb-4 rounded-xl bg-slate-200/60" role="tablist">
    @foreach ([null => 'الكل', 'partner' => 'الشركاء', 'client' => 'العملاء'] as $value => $label)
        @php($current = ($type ?? null) === ($value ?: null))
        <a href="{{ route('admin.partners.index', $value ? ['type' => $value] : []) }}" role="tab" aria-selected="{{ $current ? 'true' : 'false' }}"
           class="px-4 h-9 inline-flex items-center rounded-lg text-sm font-bold transition {{ $current ? 'bg-white text-navy-900 shadow-sm' : 'text-slate-500 hover:text-navy-900' }}">{{ $label }}</a>
    @endforeach
</div>

<div class="card overflow-hidden">
    @if($partners->isEmpty())
        <x-admin.empty-state icon="handshake" title="لا يوجد شركاء أو عملاء بعد" text="القسم لا يظهر في الموقع حتى تضيف شعاراً واحداً على الأقل." :action="route('admin.partners.create', ['type' => $type ?? 'partner'])" action-label="أضف أول شعار" />
    @else
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>الشعار</th>
                        <th>الاسم</th>
                        <th>النوع</th>
                        <th>الترتيب</th>
                        <th>الحالة</th>
                        <th class="text-left">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($partners as $partner)
                    <tr>
                        <td>
                            <div class="w-28 h-14 rounded-xl bg-white border border-slate-200 p-1.5 flex items-center justify-center">
                                <img src="{{ asset('storage/' . $partner->logo) }}" alt="" class="max-w-full max-h-full object-contain">
                            </div>
                        </td>
                        <td>
                            <p class="font-bold text-navy-900">{{ $partner->name_ar ?: $partner->name_en }}</p>
                            @if($partner->website_url)
                                <a href="{{ $partner->website_url }}" target="_blank" rel="noopener" dir="ltr" class="text-xs text-brand-700 hover:underline">{{ \Illuminate\Support\Str::limit($partner->website_url, 40) }}</a>
                            @endif
                        </td>
                        <td>
                            <span class="{{ $partner->type === 'client' ? 'badge-info' : 'badge-navy' }}">{{ $partner->type === 'client' ? 'عميل' : 'شريك' }}</span>
                        </td>
                        <td class="text-slate-500 tabular-nums">{{ $partner->sort_order }}</td>
                        <td>@include('admin.partials.status-badge', ['active' => $partner->is_active])</td>
                        <td>
                            @include('admin.partials.row-actions', [
                                'edit' => route('admin.partners.edit', $partner),
                                'destroy' => route('admin.partners.destroy', $partner),
                                'id' => 'delete-partner-' . $partner->id,
                                'confirm' => 'هل أنت متأكد من الحذف؟',
                            ])
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($partners->hasPages())
            <div class="card-footer">{{ $partners->links() }}</div>
        @endif
    @endif
</div>
@endsection

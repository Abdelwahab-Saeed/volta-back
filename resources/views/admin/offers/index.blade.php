@extends('admin.layouts.app')

@section('title', 'العروض')
@section('subtitle', 'باقات بسعر ثابت وعروض "اشترِ X واحصل على Y" يشتريها العميل مباشرة.')

@section('actions')
    <a href="{{ route('admin.offers.create') }}" class="btn-primary">
        <x-admin.icon name="plus" class="w-4 h-4" />
        إضافة عرض
    </a>
@endsection

@php
    // Shared by the filter and the status column, so both say the same thing
    $stateLabels = [
        'active' => ['label' => 'نشط', 'class' => 'badge-success'],
        'scheduled' => ['label' => 'لم يبدأ بعد', 'class' => 'badge-info'],
        'expired' => ['label' => 'منتهي', 'class' => 'badge-warning'],
        'inactive' => ['label' => 'متوقف', 'class' => 'badge-neutral'],
    ];
@endphp

@section('content')
<x-admin.filters :filters="$filters" :total="$offers->total()" placeholder="اسم العرض بالعربي أو الإنجليزي"
    :selects="[
        'type' => ['label' => 'النوع', 'options' => ['bundle' => 'باقة بسعر ثابت', 'buy_x_get_y' => 'اشترِ X احصل Y']],
        'state' => ['label' => 'الحالة', 'options' => array_map(fn ($state) => $state['label'], $stateLabels)],
    ]" />

<div class="card overflow-hidden">
    @if($offers->isEmpty() && $filters)
        <x-admin.no-results />
    @elseif($offers->isEmpty())
        <x-admin.empty-state icon="tag" title="لا توجد عروض حالياً" text="أنشئ باقة أو عرض «اشترِ واحصل» ليظهر في صفحة العروض." :action="route('admin.offers.create')" action-label="إضافة عرض" />
    @else
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>العرض</th>
                        <th>النوع</th>
                        <th>المنتجات</th>
                        <th>الحالة</th>
                        <th>ينتهي في</th>
                        <th class="text-left">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($offers as $offer)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3 min-w-[14rem]">
                                @if($offer->image)
                                    <img src="{{ asset('storage/' . $offer->image) }}" alt="" class="thumb">
                                @else
                                    <span class="w-11 h-11 rounded-xl bg-navy-900 flex items-center justify-center shrink-0"><img src="{{ asset('images/admin-logo-white.png') }}" alt="" class="w-8 opacity-90"></span>
                                @endif
                                <div class="min-w-0">
                                    <a href="{{ route('admin.offers.edit', $offer) }}" class="font-bold text-navy-900 hover:text-brand-700 block truncate">{{ $offer->name_ar }}</a>
                                    <p class="text-xs text-slate-400 truncate" dir="ltr">{{ $offer->name_en }}</p>
                                </div>
                            </div>
                        </td>
                        <td>
                            @php
                                $typeBadge = match($offer->type) {
                                    'bundle'      => ['label' => 'باقة بسعر ثابت', 'class' => 'badge-navy'],
                                    'buy_x_get_y' => ['label' => 'اشترِ X احصل Y', 'class' => 'badge-info'],
                                    default       => ['label' => $offer->type, 'class' => 'badge-neutral'],
                                };
                            @endphp
                            <span class="{{ $typeBadge['class'] }}">{{ $typeBadge['label'] }}</span>
                        </td>
                        <td><span class="font-bold text-slate-700 tabular-nums">{{ $offer->products_count }}</span> <span class="text-xs text-slate-400">منتج</span></td>
                        <td>
                            @php($state = $stateLabels[$offer->state()])
                            <span class="{{ $state['class'] }} badge-dot">{{ $state['label'] }}</span>
                        </td>
                        <td class="whitespace-nowrap">
                            @if($offer->expires_at)
                                <span class="text-sm font-semibold text-slate-600">{{ $offer->expires_at->timezone(\App\Http\Controllers\Admin\OfferController::ADMIN_TIMEZONE)->format('Y/m/d') }}</span>
                            @else
                                <span class="text-xs text-slate-400">بلا انتهاء</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('admin.offers.edit', $offer) }}" class="icon-btn-primary" data-tip="تعديل" aria-label="تعديل"><x-admin.icon name="edit" class="w-[18px] h-[18px]" /></a>
                                <form id="delete-offer-{{ $offer->id }}" action="{{ route('admin.offers.destroy', $offer) }}" method="POST" class="hidden">
                                    @csrf @method('DELETE')
                                </form>
                                <button type="button" onclick="confirmAction('delete-offer-{{ $offer->id }}', 'هل أنت متأكد من حذف هذا العرض؟', 'حذف العرض')" class="icon-btn-danger" data-tip="حذف" aria-label="حذف">
                                    <x-admin.icon name="trash" class="w-[18px] h-[18px]" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($offers->hasPages())
            <div class="card-footer">{{ $offers->links() }}</div>
        @endif
    @endif
</div>
@endsection

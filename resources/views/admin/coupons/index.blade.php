@extends('admin.layouts.app')

@section('title', 'الكوبونات')
@section('subtitle', 'أكواد خصم على السلة كلها، بمبلغ ثابت أو نسبة، مع حد أدنى للطلب إن أردت.')

@section('actions')
    <a href="{{ route('admin.coupons.create') }}" class="btn-primary">
        <x-admin.icon name="plus" class="w-4 h-4" />
        إضافة كوبون
    </a>
@endsection

@section('content')
<div class="card overflow-hidden">
    @if($coupons->isEmpty())
        <x-admin.empty-state icon="ticket" title="لا توجد كوبونات حالياً" text="أنشئ كود خصم وشاركه مع عملائك." :action="route('admin.coupons.create')" action-label="إضافة كوبون" />
    @else
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>الكود</th>
                        <th>الخصم</th>
                        <th>الحد الأدنى</th>
                        <th>الاستخدام</th>
                        <th>الصلاحية</th>
                        <th class="text-left">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($coupons as $coupon)
                    @php
                        $percent = $coupon->max_uses ? ($coupon->times_used / $coupon->max_uses) * 100 : 0;
                        $expired = $coupon->expires_at && $coupon->expires_at->isPast();
                    @endphp
                    <tr>
                        <td>
                            <span class="inline-flex items-center rounded-lg border border-dashed border-brand-300 bg-brand-50 px-3 py-1 font-extrabold tracking-wider text-brand-800 select-all" dir="ltr">{{ $coupon->code }}</span>
                        </td>
                        <td class="whitespace-nowrap">
                            <span class="font-extrabold text-navy-900 tabular-nums">
                                {{ $coupon->type === 'fixed' ? \App\Support\Money::format($coupon->value) : $coupon->value }}{{ $coupon->type === 'percent' ? '%' : '' }}
                            </span>
                            @if($coupon->type === 'fixed')<span class="text-xs font-bold text-slate-400">ج.م</span>@endif
                            <div class="text-xs text-slate-400">{{ $coupon->type === 'percent' ? 'نسبة مئوية' : 'مبلغ ثابت' }}</div>
                        </td>
                        <td class="text-slate-600 tabular-nums whitespace-nowrap">
                            {{ $coupon->min_order_amount ? \App\Support\Money::format($coupon->min_order_amount) . ' ج.م' : 'بدون حد' }}
                        </td>
                        <td>
                            <div class="w-28">
                                <div class="flex justify-between text-xs font-bold mb-1">
                                    <span class="text-slate-700 tabular-nums">{{ $coupon->times_used }}</span>
                                    <span class="text-slate-400 tabular-nums">من {{ $coupon->max_uses ?: '∞' }}</span>
                                </div>
                                <div class="h-1.5 rounded-full bg-slate-100 overflow-hidden">
                                    <div class="h-full rounded-full {{ $percent >= 100 ? 'bg-red-500' : 'bg-brand-600' }}" style="width: {{ min($percent, 100) }}%"></div>
                                </div>
                            </div>
                        </td>
                        <td class="whitespace-nowrap">
                            @if($coupon->expires_at)
                                @if($expired)
                                    <span class="badge-danger">انتهى {{ $coupon->expires_at->format('Y-m-d') }}</span>
                                @else
                                    <span class="text-sm font-semibold text-slate-600">حتى {{ $coupon->expires_at->format('Y-m-d') }}</span>
                                @endif
                            @else
                                <span class="badge-neutral">بلا انتهاء</span>
                            @endif
                        </td>
                        <td>
                            @include('admin.partials.row-actions', [
                                'edit' => route('admin.coupons.edit', $coupon),
                                'destroy' => route('admin.coupons.destroy', $coupon),
                                'id' => 'delete-coupon-' . $coupon->id,
                                'confirm' => 'هل أنت متأكد من حذف هذا الكوبون؟ لا يمكن التراجع عن هذا الإجراء.',
                            ])
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($coupons->hasPages())
            <div class="card-footer">{{ $coupons->links() }}</div>
        @endif
    @endif
</div>
@endsection

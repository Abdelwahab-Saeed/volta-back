@extends('admin.layouts.app')

@section('title', 'طلبات الموزعين')
@section('subtitle', 'طلبات "كن موزع فولتا" من الموقع. تغيير الحالة من القائمة يُحفظ فوراً.')

@section('actions')
    <a href="{{ route('admin.distributor-requests.create') }}" class="btn-primary">
        <x-admin.icon name="plus" class="w-4 h-4" />
        إضافة طلب
    </a>
@endsection

@php
    use App\Models\DistributorRequest;
    use App\Support\Governorates;

    // Tint of the inline status picker, so the list reads at a glance.
    $statusTone = [
        'new' => 'bg-brand-50 text-brand-800 border-brand-200',
        'contacted' => 'bg-amber-50 text-amber-800 border-amber-200',
        'approved' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
        'rejected' => 'bg-red-50 text-red-800 border-red-200',
    ];
    $yesNo = ['1' => 'نعم', '0' => 'لا'];
@endphp

@section('content')
<x-admin.filters :filters="$filters" :total="$requests->total()" dates
    placeholder="الاسم، الهاتف، المركز أو القرية"
    :selects="[
        'status' => ['label' => 'الحالة', 'options' => DistributorRequest::STATUSES],
        'governorate' => ['label' => 'المحافظة', 'options' => Governorates::ALL],
        'has_shop' => ['label' => 'محل تجاري', 'options' => $yesNo],
        'has_commercial_register' => ['label' => 'سجل تجاري', 'options' => $yesNo],
    ]" />

<div class="card overflow-hidden">
    @if($requests->isEmpty() && $filters)
        <x-admin.no-results />
    @elseif($requests->isEmpty())
        <x-admin.empty-state icon="inbox" title="لا توجد طلبات حالياً" text="ستظهر هنا طلبات التجار الذين يملؤون نموذج «كن موزع فولتا» في الموقع." :action="route('admin.distributor-requests.create')" action-label="أضف طلباً يدوياً" />
    @else
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>المتقدم</th>
                        <th>المنطقة</th>
                        <th>سجل تجاري</th>
                        <th>محل تجاري</th>
                        <th>الحالة</th>
                        <th>التاريخ</th>
                        <th class="text-left">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests as $item)
                    <tr>
                        <td>
                            <a href="{{ route('admin.distributor-requests.show', $item) }}" class="font-bold text-navy-900 hover:text-brand-700 whitespace-nowrap">{{ $item->name }}</a>
                            <div class="text-xs text-slate-400" dir="ltr">{{ $item->phone }}</div>
                        </td>
                        <td>
                            <div class="font-semibold text-slate-700 whitespace-nowrap">{{ $item->governorate_ar }}</div>
                            <div class="text-xs text-slate-400 max-w-[14rem] truncate">{{ $item->center }} · {{ $item->area }}</div>
                        </td>
                        <td><span class="{{ $item->has_commercial_register ? 'badge-success' : 'badge-neutral' }}">{{ $item->has_commercial_register ? 'نعم' : 'لا' }}</span></td>
                        <td><span class="{{ $item->has_shop ? 'badge-success' : 'badge-neutral' }}">{{ $item->has_shop ? 'نعم' : 'لا' }}</span></td>
                        <td>
                            <form action="{{ route('admin.distributor-requests.status', $item) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <label class="sr-only" for="status-{{ $item->id }}">حالة طلب {{ $item->name }}</label>
                                <select id="status-{{ $item->id }}" name="status" onchange="this.form.submit()"
                                    class="h-9 pr-3 pl-8 rounded-full border text-xs font-extrabold cursor-pointer appearance-none bg-no-repeat focus:outline-none focus:ring-4 focus:ring-brand-500/15 {{ $statusTone[$item->status] ?? 'bg-slate-50 text-slate-700 border-slate-200' }}"
                                    style="background-image: url(&quot;data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='currentColor' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e&quot;); background-position: left 0.5rem center; background-size: 1.1em;">
                                    @foreach(DistributorRequest::STATUSES as $value => $label)
                                        <option value="{{ $value }}" @selected($item->status === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </td>
                        <td class="whitespace-nowrap">
                            <div class="font-semibold text-slate-700">{{ $item->created_at->format('Y/m/d') }}</div>
                            <div class="text-xs text-slate-400">{{ $item->created_at->format('H:i') }}</div>
                        </td>
                        <td>
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('admin.distributor-requests.show', $item) }}" class="icon-btn" data-tip="عرض" aria-label="عرض طلب {{ $item->name }}">
                                    <x-admin.icon name="eye" class="w-[18px] h-[18px]" />
                                </a>
                                @include('admin.partials.row-actions', [
                                    'edit' => route('admin.distributor-requests.edit', $item),
                                    'destroy' => route('admin.distributor-requests.destroy', $item),
                                    'id' => 'delete-distributor-request-' . $item->id,
                                    'confirm' => 'هل أنت متأكد من حذف هذا الطلب؟',
                                ])
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($requests->hasPages())
            <div class="card-footer">{{ $requests->links() }}</div>
        @endif
    @endif
</div>
@endsection

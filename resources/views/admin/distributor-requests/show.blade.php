@extends('admin.layouts.app')

@php
    use App\Models\DistributorRequest;
    $item = $distributorRequest;
@endphp

@section('title', 'طلب موزع: ' . $item->name)
@section('back', route('admin.distributor-requests.index'))
@section('back_label', 'طلبات الموزعين')
@section('subtitle')
    أُرسل {{ $item->created_at?->format('Y/m/d · H:i') }}
    <span class="mx-1 text-slate-300">|</span>
    <span class="{{ DistributorRequest::STATUS_BADGES[$item->status] ?? 'badge-neutral' }}">{{ $item->status_label }}</span>
    <span class="mx-1 text-slate-300">|</span>
    من {{ DistributorRequest::SOURCES[$item->source] ?? $item->source }}
@endsection

@section('actions')
    <a href="{{ route('admin.distributor-requests.edit', $item) }}" class="btn-secondary">
        <x-admin.icon name="edit" class="w-4 h-4" />
        تعديل
    </a>
    <form id="delete-distributor-request" action="{{ route('admin.distributor-requests.destroy', $item) }}" method="POST">
        @csrf
        @method('DELETE')
        <button type="button" onclick="confirmAction('delete-distributor-request', 'هل أنت متأكد من حذف هذا الطلب؟')" class="btn-danger">
            <x-admin.icon name="trash" class="w-4 h-4" />
            حذف
        </button>
    </form>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
    <div class="lg:col-span-2 space-y-6">
        <section class="card overflow-hidden">
            <div class="card-header">
                <h2 class="card-title flex items-center gap-2"><x-admin.icon name="users" class="w-5 h-5 text-brand-600" /> بيانات المتقدم</h2>
            </div>
            <dl class="card-body grid sm:grid-cols-2 gap-x-6 gap-y-5 text-sm">
                <div>
                    <dt class="text-xs font-bold text-slate-400">الاسم</dt>
                    <dd class="mt-1 font-bold text-navy-900">{{ $item->name }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold text-slate-400">رقم الهاتف</dt>
                    <dd class="mt-1 flex flex-wrap items-center gap-2">
                        <span class="font-bold text-navy-900 tabular-nums" dir="ltr">{{ $item->phone }}</span>
                        <a href="tel:{{ $item->phone }}" class="btn-secondary btn-sm"><x-admin.icon name="phone" class="w-3.5 h-3.5" /> اتصال</a>
                        <a href="{{ $item->whatsapp_url }}" target="_blank" rel="noopener" class="btn-secondary btn-sm"><x-admin.icon name="external" class="w-3.5 h-3.5" /> واتساب</a>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-bold text-slate-400">المحافظة</dt>
                    <dd class="mt-1 font-bold text-navy-900">{{ $item->governorate_ar }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold text-slate-400">المركز</dt>
                    <dd class="mt-1 font-bold text-navy-900">{{ $item->center }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold text-slate-400">القرية أو الحي</dt>
                    <dd class="mt-1 font-bold text-navy-900">{{ $item->area }}</dd>
                </div>
                <div class="flex gap-6">
                    <div>
                        <dt class="text-xs font-bold text-slate-400">سجل تجاري</dt>
                        <dd class="mt-1"><span class="{{ $item->has_commercial_register ? 'badge-success' : 'badge-neutral' }}">{{ $item->has_commercial_register ? 'نعم' : 'لا' }}</span></dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold text-slate-400">محل تجاري</dt>
                        <dd class="mt-1"><span class="{{ $item->has_shop ? 'badge-success' : 'badge-neutral' }}">{{ $item->has_shop ? 'نعم' : 'لا' }}</span></dd>
                    </div>
                </div>
            </dl>
        </section>

        <section class="card">
            <div class="card-header">
                <h2 class="card-title">ملاحظات المتقدم</h2>
            </div>
            <div class="card-body text-sm leading-relaxed {{ $item->notes ? 'text-slate-700 whitespace-pre-line' : 'text-slate-400' }}">{{ $item->notes ?: 'لم يكتب المتقدم ملاحظات.' }}</div>
        </section>
    </div>

    <section class="card lg:sticky lg:top-24">
        <div class="card-header">
            <h2 class="card-title">المتابعة</h2>
            <span class="{{ DistributorRequest::STATUS_BADGES[$item->status] ?? 'badge-neutral' }}">{{ $item->status_label }}</span>
        </div>
        <form action="{{ route('admin.distributor-requests.status', $item) }}" method="POST" class="card-body space-y-4">
            @csrf
            @method('PATCH')
            <div>
                <label for="status" class="label">الحالة</label>
                <select id="status" name="status" class="input">
                    @foreach(DistributorRequest::STATUSES as $value => $label)
                        <option value="{{ $value }}" @selected($item->status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('status')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="admin_notes" class="label">ملاحظات داخلية</label>
                <textarea id="admin_notes" name="admin_notes" rows="5" maxlength="2000" class="input" placeholder="مثال: تم الاتصال، طلب عرض أسعار الجملة…">{{ old('admin_notes', $item->admin_notes) }}</textarea>
                <p class="hint">لا تظهر للمتقدم.</p>
                @error('admin_notes')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="btn-primary w-full">
                <x-admin.icon name="check" class="w-4 h-4" />
                حفظ المتابعة
            </button>
        </form>
    </section>
</div>
@endsection

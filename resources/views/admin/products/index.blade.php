@extends('admin.layouts.app')

@section('title', 'المنتجات')
@section('subtitle', 'تحكم في أسعار منتجاتك ومخزونها وصورها.')

@section('actions')
    <a href="{{ route('admin.products.create') }}" class="btn-primary">
        <x-admin.icon name="plus" class="w-4 h-4" />
        إضافة منتج
    </a>
@endsection

@section('content')
<div class="card overflow-hidden">
    @if($products->isEmpty())
        <x-admin.empty-state icon="cube" title="لا توجد منتجات حالياً" text="أضف أول منتج ليظهر في المتجر." :action="route('admin.products.create')" action-label="إضافة منتج" />
    @else
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>المنتج</th>
                        <th>القسم</th>
                        <th>السعر</th>
                        <th>التكلفة</th>
                        <th>المخزون</th>
                        <th>الحالة</th>
                        <th class="text-left">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                    <tr>
                        <td>
                            <a href="{{ route('admin.products.show', $product) }}" class="flex items-center gap-3 group min-w-[14rem]">
                                @if($product->image)
                                    <img class="thumb" src="{{ asset('storage/' . $product->image) }}" alt="">
                                @else
                                    <span class="thumb-empty"><x-admin.icon name="photo" class="w-5 h-5" /></span>
                                @endif
                                <span class="font-bold text-navy-900 group-hover:text-brand-700 line-clamp-2">{{ $product->name }}</span>
                            </a>
                        </td>
                        <td><span class="chip whitespace-nowrap">{{ $product->category->name ?? 'بدون قسم' }}</span></td>
                        <td>
                            <x-admin.money :value="$product->final_price" />
                            @if($product->discount_price > 0 || $product->discount > 0)
                                <div class="text-xs text-slate-400 line-through tabular-nums">{{ \App\Support\Money::format($product->price) }}</div>
                            @endif
                        </td>
                        <td class="text-slate-600 tabular-nums whitespace-nowrap">{{ \App\Support\Money::format($product->cost_price) }} <span class="text-xs text-slate-400">ج.م</span></td>
                        <td>
                            @if($product->stock <= 0)
                                <span class="badge-danger">نفد</span>
                            @elseif($product->stock <= 5)
                                <span class="badge-warning">{{ $product->stock }} فقط</span>
                            @else
                                <span class="font-bold text-slate-700 tabular-nums">{{ $product->stock }}</span> <span class="text-xs text-slate-400">قطعة</span>
                            @endif
                        </td>
                        <td>
                            <span class="{{ $product->status ? 'badge-success' : 'badge-neutral' }} badge-dot">{{ $product->status ? 'متاح' : 'متوقف' }}</span>
                        </td>
                        <td>
                            <div class="flex items-center justify-end gap-0.5">
                                <a href="{{ route('admin.products.show', $product) }}" class="icon-btn" data-tip="التفاصيل" aria-label="التفاصيل"><x-admin.icon name="eye" class="w-[18px] h-[18px]" /></a>
                                <a href="{{ route('admin.products.edit', $product) }}" class="icon-btn-primary" data-tip="تعديل" aria-label="تعديل"><x-admin.icon name="edit" class="w-[18px] h-[18px]" /></a>
                                <a href="{{ route('admin.products.features.index', $product->id) }}" class="icon-btn" data-tip="المميزات" aria-label="المميزات"><x-admin.icon name="list" class="w-[18px] h-[18px]" /></a>
                                <a href="{{ route('admin.products.images.index', $product->id) }}" class="icon-btn" data-tip="معرض الصور" aria-label="معرض الصور"><x-admin.icon name="photo" class="w-[18px] h-[18px]" /></a>
                                @if($product->preview_url)
                                    <a href="{{ $product->preview_url }}" target="_blank" rel="noopener" class="icon-btn" data-tip="الفيديو" aria-label="الفيديو"><x-admin.icon name="play" class="w-[18px] h-[18px]" /></a>
                                @endif
                                <form id="delete-product-{{ $product->id }}" action="{{ route('admin.products.destroy', $product) }}" method="POST" class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" onclick="confirmAction('delete-product-{{ $product->id }}', 'هل أنت متأكد من أرشفة هذا المنتج؟')" class="icon-btn-danger" data-tip="حذف" aria-label="حذف">
                                        <x-admin.icon name="trash" class="w-[18px] h-[18px]" />
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($products->hasPages())
            <div class="card-footer">{{ $products->links() }}</div>
        @endif
    @endif
</div>
@endsection

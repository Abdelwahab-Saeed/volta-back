@extends('admin.layouts.app')

@section('title', 'الأقسام')
@section('subtitle', 'نظّم منتجاتك في أقسام، وحدد ترتيب ظهورها في المتجر.')

@section('actions')
    <a href="{{ route('admin.categories.create') }}" class="btn-primary">
        <x-admin.icon name="plus" class="w-4 h-4" />
        إضافة قسم
    </a>
@endsection

@section('content')
<x-admin.filters :filters="$filters" :total="$categories->total()" placeholder="اسم القسم بالعربي أو الإنجليزي"
    :selects="['status' => ['label' => 'الحالة', 'options' => ['active' => 'نشط', 'inactive' => 'غير نشط']]]" />

<div class="card overflow-hidden">
    @if($categories->isEmpty() && $filters)
        <x-admin.no-results />
    @elseif($categories->isEmpty())
        <x-admin.empty-state icon="folder" title="لا توجد أقسام حالياً" text="أضف أول قسم لتنظيم منتجات المتجر." :action="route('admin.categories.create')" action-label="إضافة قسم" />
    @else
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>القسم</th>
                        <th>المنتجات</th>
                        <th>الترتيب</th>
                        <th>الحالة</th>
                        <th class="text-left">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categories as $category)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3 min-w-[14rem]">
                                @if($category->image)
                                    <img src="{{ asset('storage/' . $category->image) }}" alt="" class="thumb">
                                @else
                                    <span class="thumb-empty"><x-admin.icon name="photo" class="w-5 h-5" /></span>
                                @endif
                                <div class="min-w-0 max-w-xs">
                                    <a href="{{ route('admin.categories.edit', $category) }}" class="font-bold text-navy-900 hover:text-brand-700 truncate block">{{ $category->name }}</a>
                                    <p class="text-xs text-slate-400 truncate">{{ $category->description }}</p>
                                </div>
                            </div>
                        </td>
                        <td><span class="font-bold text-slate-700 tabular-nums">{{ $category->products_count ?? 0 }}</span> <span class="text-xs text-slate-400">منتج</span></td>
                        <td>
                            <form action="{{ route('admin.categories.update-order', $category) }}" method="POST" class="flex items-center gap-1.5">
                                @csrf
                                @method('PUT')
                                <input type="number" name="category_order" value="{{ $category->category_order }}" aria-label="ترتيب {{ $category->name }}" class="input h-9 w-20 text-center tabular-nums px-2">
                                <button type="submit" class="icon-btn-primary" data-tip="حفظ الترتيب" aria-label="حفظ الترتيب">
                                    <x-admin.icon name="check" class="w-4 h-4" />
                                </button>
                            </form>
                        </td>
                        <td><span class="{{ $category->status ? 'badge-success' : 'badge-neutral' }} badge-dot">{{ $category->status ? 'نشط' : 'غير نشط' }}</span></td>
                        <td>
                            @include('admin.partials.row-actions', [
                                'edit' => route('admin.categories.edit', $category),
                                'destroy' => route('admin.categories.destroy', $category),
                                'id' => 'delete-category-' . $category->id,
                                'confirm' => 'هل أنت متأكد من أرشفة هذا القسم؟ قد يؤثر ذلك على المنتجات التابعة له.',
                            ])
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($categories->hasPages())
            <div class="card-footer">{{ $categories->links() }}</div>
        @endif
    @endif
</div>
@endsection

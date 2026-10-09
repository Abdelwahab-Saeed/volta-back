@extends('admin.layouts.app')

@section('title', 'البانرات')
@section('subtitle', 'الصور المتحركة أعلى الصفحة الرئيسية للمتجر.')

@section('actions')
    <a href="{{ route('admin.banners.create') }}" class="btn-primary">
        <x-admin.icon name="plus" class="w-4 h-4" />
        إضافة بانر
    </a>
@endsection

@section('content')
@if($banners->isEmpty())
    <div class="card">
        <x-admin.empty-state icon="photo" title="لا توجد بانرات حالياً" text="أضف بانراً ليظهر أعلى الصفحة الرئيسية." :action="route('admin.banners.create')" action-label="إضافة بانر" />
    </div>
@else
    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
        @foreach($banners as $banner)
            <article class="card overflow-hidden flex flex-col">
                <div class="relative aspect-[1920/600] bg-slate-100">
                    @if($banner->image)
                        <img src="{{ asset('storage/' . $banner->image) }}" alt="" class="absolute inset-0 w-full h-full object-cover {{ $banner->status ? '' : 'grayscale opacity-60' }}">
                    @endif
                    <span class="absolute top-3 right-3 {{ $banner->status ? 'badge-success' : 'badge-neutral' }} badge-dot shadow-sm">{{ $banner->status ? 'نشط' : 'غير نشط' }}</span>
                </div>
                <div class="flex items-start gap-3 p-4">
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-navy-900 truncate">{{ $banner->title }}</p>
                        <p class="text-xs text-slate-400 truncate mt-0.5">{{ $banner->description ?: 'أضيف ' . $banner->created_at->format('Y-m-d') }}</p>
                        @unless($banner->image_mobile)
                            <p class="flex items-center gap-1 text-xs font-semibold text-amber-700 mt-1" title="الموبايل يعرض صورة الكمبيوتر بعد قص جانبيها">
                                {{-- <x-admin.icon name="phone" class="w-3.5 h-3.5 shrink-0" /> --}}
                                بدون صورة موبايل
                            </p>
                        @endunless
                        @if($banner->redirect_url)
                            <p class="flex items-center gap-1 text-xs text-brand-700 mt-1 min-w-0" title="رابط التوجيه">
                                <x-admin.icon name="link" class="w-3.5 h-3.5 shrink-0" />
                                <span class="truncate" dir="ltr">{{ $banner->redirect_url }}</span>
                            </p>
                        @endif
                    </div>
                    @include('admin.partials.row-actions', [
                        'edit' => route('admin.banners.edit', $banner),
                        'destroy' => route('admin.banners.destroy', $banner),
                        'id' => 'delete-banner-' . $banner->id,
                        'confirm' => 'هل أنت متأكد من حذف هذا البانر؟',
                    ])
                </div>
            </article>
        @endforeach
    </div>
    @if($banners->hasPages())
        <div class="card mt-6 px-5 py-4">{{ $banners->links() }}</div>
    @endif
@endif
@endsection

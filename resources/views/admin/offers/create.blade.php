@extends('admin.layouts.app')

@section('title', 'إضافة عرض جديد')
@section('subtitle', 'باقة بسعر ثابت أو "اشترِ X واحصل على Y". العميل يشتري العرض مباشرة من صفحته.')
@section('back', route('admin.offers.index'))
@section('back_label', 'العروض')

@section('content')
<form action="{{ route('admin.offers.store') }}" method="POST" enctype="multipart/form-data" class="max-w-5xl space-y-6">
    @csrf

    @include('admin.offers._form')

    <div class="sticky bottom-0 z-10 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-3 bg-white/90 backdrop-blur border-t border-slate-200 flex items-center gap-3">
        <button type="submit" class="btn-primary sm:min-w-[10rem]"><x-admin.icon name="check" class="w-4 h-4" /> حفظ العرض</button>
        <a href="{{ route('admin.offers.index') }}" class="btn-ghost">إلغاء</a>
    </div>
</form>
@endsection

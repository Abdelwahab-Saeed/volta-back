@extends('admin.layouts.app')

@section('title', 'تعديل العرض: ' . $offer->name_ar)

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-800">تعديل العرض</h3>
            <a href="{{ route('admin.offers.index') }}" class="text-sm text-gray-500 hover:text-gray-700 font-medium flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                رجوع
            </a>
        </div>

        @if($offer->orders()->exists())
        <div class="mx-6 mt-6 p-4 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-800">
            تم شراء هذا العرض من قبل. التعديل يسري على الطلبات الجديدة فقط؛ الطلبات السابقة محفوظ فيها العرض كما كان وقت الشراء.
        </div>
        @endif

        <form action="{{ route('admin.offers.update', $offer) }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-8">
            @csrf
            @method('PUT')

            @include('admin.offers._form')

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('admin.offers.index') }}" class="px-6 py-3 border border-gray-200 rounded-xl text-gray-700 font-bold hover:bg-gray-50 transition-all">إلغاء</a>
                <button type="submit" class="px-8 py-3 bg-blue-600 text-white rounded-xl font-bold hover:bg-blue-700 transition-all shadow-sm">حفظ التعديلات</button>
            </div>
        </form>
    </div>
</div>
@endsection

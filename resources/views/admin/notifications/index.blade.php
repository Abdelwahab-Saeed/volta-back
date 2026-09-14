@extends('admin.layouts.app')

@section('title', 'الإشعارات')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
        <h3 class="text-xl font-bold text-gray-800 flex items-center">
            <svg class="w-6 h-6 ml-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
            الإشعارات
            @if(auth()->user()->unreadNotifications->count() > 0)
                <span class="mr-3 bg-red-100 text-red-600 py-1 px-3 rounded-full text-sm font-bold">
                    {{ auth()->user()->unreadNotifications->count() }} جديد
                </span>
            @endif
        </h3>
        @if(auth()->user()->unreadNotifications->count() > 0)
            <form action="{{ route('admin.notifications.readAll') }}" method="POST">
                @csrf
                <button type="submit" class="text-sm bg-blue-50 text-blue-600 hover:bg-blue-100 font-bold py-2 px-4 rounded-xl transition-colors flex items-center">
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    تحديد الكل كمقروء
                </button>
            </form>
        @endif
    </div>

    @if($notifications->count() > 0)
        <div class="divide-y divide-gray-100">
            @foreach($notifications as $notification)
                <div class="p-6 transition-colors hover:bg-gray-50 flex items-start justify-between {{ empty($notification->read_at) ? 'bg-blue-50/30' : '' }}">
                    <div class="flex items-start">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold ml-4 {{ empty($notification->read_at) ? 'bg-blue-500' : 'bg-gray-300' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                        </div>
                        <div>
                            <p class="text-gray-800 font-semibold mb-1 {{ empty($notification->read_at) ? 'text-lg' : '' }}">
                                {{ $notification->data['message'] ?? 'إشعار جديد' }}
                            </p>
                            @if(isset($notification->data['total_amount']))
                                <p class="text-sm text-gray-500 mb-2">
                                    الإجمالي: <span class="font-bold text-gray-700">EGP {{ number_format($notification->data['total_amount'], 2) }}</span>
                                </p>
                            @endif
                            <p class="text-xs text-gray-400 flex items-center">
                                <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                {{ $notification->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-reverse space-x-3">
                        @if(isset($notification->data['order_id']))
                            <a href="{{ route('admin.orders.show', $notification->data['order_id']) }}" class="text-blue-500 hover:text-blue-700 text-sm font-bold bg-blue-50 px-3 py-1.5 rounded-lg transition-colors">
                                عرض الطلب
                            </a>
                        @endif
                        
                        @if(empty($notification->read_at))
                            <form action="{{ route('admin.notifications.read', $notification->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="text-gray-500 hover:text-green-600 text-sm font-bold bg-gray-100 hover:bg-green-50 px-3 py-1.5 rounded-lg transition-colors" title="تحديد كمقروء">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        
        <div class="p-6 border-t border-gray-100">
            {{ $notifications->links('pagination::tailwind') }}
        </div>
    @else
        <div class="p-12 text-center text-gray-500">
            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
            <p class="text-lg font-bold">لا توجد إشعارات حالياً</p>
            <p class="text-sm mt-2">ستظهر إشعارات الطلبات الجديدة هنا</p>
        </div>
    @endif
</div>
@endsection

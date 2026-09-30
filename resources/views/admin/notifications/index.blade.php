@extends('admin.layouts.app')

@section('title', 'الإشعارات')
@php($unread = auth()->user()->unreadNotifications->count())
@section('subtitle', $unread > 0 ? "لديك {$unread} إشعار غير مقروء." : 'كل الإشعارات مقروءة.')

@section('actions')
    @if($unread > 0)
        <form action="{{ route('admin.notifications.readAll') }}" method="POST">
            @csrf
            <button type="submit" class="btn-secondary">
                <x-admin.icon name="check" class="w-4 h-4" />
                تحديد الكل كمقروء
            </button>
        </form>
    @endif
@endsection

@section('content')
<div class="card overflow-hidden max-w-4xl">
    @if($notifications->count() > 0)
        <ul class="divide-y divide-slate-100">
            @foreach($notifications as $notification)
                @php($isUnread = empty($notification->read_at))
                <li class="relative flex flex-col sm:flex-row sm:items-center gap-4 px-5 py-4 {{ $isUnread ? 'bg-brand-50/40' : '' }}">
                    @if($isUnread)
                        <span class="absolute right-0 top-4 bottom-4 w-1 rounded-l-full bg-brand-500" aria-hidden="true"></span>
                    @endif
                    <div class="flex items-start gap-3 flex-1 min-w-0">
                        <span class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 {{ $isUnread ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-400' }}">
                            <x-admin.icon name="orders" class="w-5 h-5" />
                        </span>
                        <div class="min-w-0">
                            <p class="text-sm {{ $isUnread ? 'font-extrabold text-navy-900' : 'font-semibold text-slate-700' }}">
                                {{ $notification->data['message'] ?? 'إشعار جديد' }}
                                @if($isUnread)<span class="sr-only">(غير مقروء)</span>@endif
                            </p>
                            <p class="mt-1 text-xs text-slate-400 flex flex-wrap items-center gap-x-3 gap-y-1">
                                <span class="inline-flex items-center gap-1"><x-admin.icon name="clock" class="w-3.5 h-3.5" /> {{ $notification->created_at->diffForHumans() }}</span>
                                @if(isset($notification->data['total_amount']))
                                    <span>الإجمالي: <span class="font-bold text-slate-600 tabular-nums">{{ number_format($notification->data['total_amount'], 2) }} ج.م</span></span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 sm:shrink-0 pr-[3.25rem] sm:pr-0">
                        @if(isset($notification->data['order_id']))
                            <a href="{{ route('admin.orders.show', $notification->data['order_id']) }}" class="btn-secondary btn-sm">
                                <x-admin.icon name="eye" class="w-3.5 h-3.5" />
                                عرض الطلب
                            </a>
                        @endif

                        @if($isUnread)
                            <form action="{{ route('admin.notifications.read', $notification->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="icon-btn" data-tip="تحديد كمقروء" aria-label="تحديد كمقروء">
                                    <x-admin.icon name="check" class="w-[18px] h-[18px]" />
                                </button>
                            </form>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>

        @if($notifications->hasPages())
            <div class="card-footer">{{ $notifications->links('pagination::tailwind') }}</div>
        @endif
    @else
        <x-admin.empty-state icon="bell" title="لا توجد إشعارات حالياً" text="ستظهر إشعارات الطلبات الجديدة هنا." />
    @endif
</div>
@endsection

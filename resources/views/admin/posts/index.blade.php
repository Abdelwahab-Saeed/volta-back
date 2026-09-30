@extends('admin.layouts.app')

@section('title', 'المقالات')
@section('subtitle', 'مقالات المدونة التي تظهر في صفحة المدونة بالمتجر.')

@section('actions')
    <a href="{{ route('admin.posts.create') }}" class="btn-primary">
        <x-admin.icon name="plus" class="w-4 h-4" />
        مقال جديد
    </a>
@endsection

@section('content')
<div class="card overflow-hidden">
    @if($posts->isEmpty())
        <x-admin.empty-state icon="newspaper" title="لا توجد مقالات حالياً" text="اكتب أول مقال لمدونة المتجر." :action="route('admin.posts.create')" action-label="مقال جديد" />
    @else
        <ul class="divide-y divide-slate-100">
            @foreach($posts as $post)
                <li class="flex items-center gap-4 px-5 py-4 hover:bg-slate-50/70 transition-colors">
                    <div class="w-24 h-16 sm:w-28 sm:h-20 rounded-xl overflow-hidden bg-slate-100 border border-slate-200/70 shrink-0 flex items-center justify-center">
                        @if($post->image)
                            <img src="{{ asset('storage/' . $post->image) }}" alt="" class="w-full h-full object-cover">
                        @else
                            <x-admin.icon name="photo" class="w-7 h-7 text-slate-300" />
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <a href="{{ route('admin.posts.edit', $post) }}" class="font-bold text-navy-900 hover:text-brand-700 line-clamp-1">{{ $post->title }}</a>
                        <p class="text-sm text-slate-500 line-clamp-2 mt-0.5">{{ Str::limit($post->description, 160) }}</p>
                        <p class="text-xs text-slate-400 mt-1.5 flex items-center gap-1"><x-admin.icon name="calendar" class="w-3.5 h-3.5" /> {{ $post->created_at->format('Y-m-d') }}</p>
                    </div>
                    @include('admin.partials.row-actions', [
                        'edit' => route('admin.posts.edit', $post),
                        'destroy' => route('admin.posts.destroy', $post),
                        'id' => 'delete-post-' . $post->id,
                        'confirm' => 'هل أنت متأكد من حذف هذا المقال؟',
                    ])
                </li>
            @endforeach
        </ul>
        @if($posts->hasPages())
            <div class="card-footer">{{ $posts->links() }}</div>
        @endif
    @endif
</div>
@endsection

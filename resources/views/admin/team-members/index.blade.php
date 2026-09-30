@extends('admin.layouts.app')

@section('title', 'فريق العمل')
@section('subtitle', 'الأشخاص الذين يظهرون في قسم "فريق العمل" بالصفحة الرئيسية.')

@section('actions')
    <a href="{{ route('admin.team-members.create') }}" class="btn-primary">
        <x-admin.icon name="plus" class="w-4 h-4" />
        إضافة عضو
    </a>
@endsection

@section('content')
@if($members->isEmpty())
    <div class="card">
        <x-admin.empty-state icon="team" title="لم تتم إضافة أعضاء بعد" text="القسم لا يظهر في الموقع حتى تضيف عضواً واحداً على الأقل." :action="route('admin.team-members.create')" action-label="أضف أول عضو" />
    </div>
@else
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @foreach($members as $member)
            <article class="card p-5 flex flex-col items-center text-center {{ $member->is_active ? '' : 'opacity-70' }}">
                @if($member->photo)
                    <img src="{{ asset('storage/' . $member->photo) }}" alt="" class="w-20 h-20 rounded-full object-cover ring-4 ring-slate-100">
                @else
                    <span class="w-20 h-20 rounded-full bg-navy-900 text-white text-2xl font-extrabold flex items-center justify-center ring-4 ring-slate-100">{{ mb_substr($member->name_ar ?: $member->name_en, 0, 1) }}</span>
                @endif
                <p class="mt-3 font-extrabold text-navy-900">{{ $member->name_ar ?: $member->name_en }}</p>
                <p class="text-sm font-semibold text-brand-700">{{ $member->role_ar ?: $member->role_en }}</p>
                <div class="mt-2 flex items-center gap-2">
                    @include('admin.partials.status-badge', ['active' => $member->is_active])
                    <span class="chip">ترتيب {{ $member->sort_order }}</span>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 w-full flex justify-center">
                    @include('admin.partials.row-actions', [
                        'edit' => route('admin.team-members.edit', $member),
                        'destroy' => route('admin.team-members.destroy', $member),
                        'id' => 'delete-member-' . $member->id,
                        'confirm' => 'هل أنت متأكد من حذف هذا العضو؟',
                    ])
                </div>
            </article>
        @endforeach
    </div>
    @if($members->hasPages())
        <div class="card mt-6 px-5 py-4">{{ $members->links() }}</div>
    @endif
@endif
@endsection

@extends('layouts.app')
@section('title', 'Notifications - BonusHub')
@section('content')
<div class="page-container">
    <div class="page-header flex items-center justify-between">
        <div>
            <h1 class="page-title">Notifications</h1>
            <p class="page-subtitle">{{ $unread }} unread</p>
        </div>
        @if($unread > 0)
            <form method="POST" action="{{ route('notifications.readall') }}">@csrf
                <button type="submit" class="btn-outline px-4 py-2 text-sm">Mark all as read</button>
            </form>
        @endif
    </div>

    @if(session('success'))
        <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm">{{ session('success') }}</div>
    @endif

    <div class="card p-0 overflow-hidden">
        @forelse($items as $n)
            <div class="px-5 py-4 border-b border-gray-100 last:border-b-0 flex items-start justify-between gap-4 {{ $n->read_at ? '' : 'bg-indigo-50/50' }}">
                <div>
                    <p class="text-sm font-semibold text-surface-800">{{ $n->title }}</p>
                    @if($n->message)<p class="text-sm text-surface-600 mt-0.5">{{ $n->message }}</p>@endif
                    <p class="text-xs text-surface-400 mt-1">{{ optional($n->created_at)->diffForHumans() }}</p>
                </div>
                @unless($n->read_at)
                    <form method="POST" action="{{ route('notifications.read', $n->id) }}">@csrf
                        <button type="submit" class="text-xs text-indigo-600 hover:underline whitespace-nowrap">Mark read</button>
                    </form>
                @endunless
            </div>
        @empty
            <p class="p-8 text-center text-sm text-surface-500">No notifications yet.</p>
        @endforelse
    </div>
    <div class="mt-4">{{ $items->links() }}</div>
</div>
@endsection

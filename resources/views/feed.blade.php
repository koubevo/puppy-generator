@extends('layouts.app')

@section('title', 'Puppy Updates Feed')

@section('content')
    <div class="max-w-xl mx-auto px-4">
        {{-- Sticky Header --}}
        <header class="sticky top-0 z-10 flex items-center justify-between py-4 bg-bg-primary/80 backdrop-blur-md">
            <h1 class="text-2xl font-semibold">Puppy Feed</h1>

            <button id="notification-toggle" data-vapid-key="{{ $vapidPublicKey ?? '' }}"
                class="text-sm px-4 py-2 rounded-full bg-bg-secondary text-text-secondary hover:bg-bg-message transition-all hover:scale-105 active:scale-95">
                🔕 Enable Notifications
            </button>
        </header>

        {{-- Messages (newest first) --}}
        <div id="feed-container" class="space-y-5 pb-8">
            @forelse($logs as $log)
                @include('partials._feed-item', ['log' => $log])
            @empty
                <div class="text-center py-20">
                    <p class="text-5xl mb-4">🐶</p>
                    <p class="text-lg font-medium text-text-primary mb-1">No updates yet</p>
                    <p class="text-sm text-text-secondary">Your puppies are resting... check back soon!</p>
                </div>
            @endforelse
        </div>

        {{-- Load More --}}
        @if($hasMore)
            <div id="load-more-wrapper" class="flex justify-center py-6 pb-10">
                <button id="load-more-btn" data-next-before="{{ $logs->last()['id'] ?? '' }}" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full
                                                   bg-accent/10 text-accent text-sm font-semibold
                                                   border border-accent/20
                                                   hover:bg-accent/20 hover:border-accent/30
                                                   transition-all hover:scale-105 active:scale-95
                                                   cursor-pointer">
                    <span>Load more</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
            </div>
        @endif
    </div>
@endsection
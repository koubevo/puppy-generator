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

        {{-- Messages grouped by date --}}
        <div class="space-y-5 pb-8">
            @php $lastDateLabel = null; @endphp

            @forelse($logs->reverse() as $log)
                @php
                    $dateLabel = $log['isToday'] ? 'Today' : ($log['isYesterday'] ? 'Yesterday' : $log['sentAt']->format('M j'));
                @endphp

                {{-- Date separator --}}
                @if($dateLabel !== $lastDateLabel)
                    <div id="{{ $log['isToday'] ? 'today-section' : '' }}" class="flex items-center gap-3 pt-4 scroll-mt-16">
                        <span
                            class="text-xs font-medium text-text-secondary uppercase tracking-wide {{ $log['isToday'] ? 'text-accent' : '' }}">
                            {{ $dateLabel }}
                        </span>
                        <div class="flex-1 h-px bg-bg-message"></div>
                    </div>
                    @php $lastDateLabel = $dateLabel; @endphp
                @endif

                <article class="flex gap-3 group {{ $log['isToday'] ? 'animate-fade-in' : '' }}">
                    {{-- Bot Avatar --}}
                    <div
                        class="shrink-0 w-11 h-11 rounded-full {{ $log['bot']['color'] }} flex items-center justify-center text-lg shadow-sm group-hover:scale-105 transition-transform">
                        {{ $log['bot']['emoji'] }}
                    </div>

                    {{-- Message Content --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-baseline gap-2 mb-1">
                            <span class="font-semibold text-sm text-text-primary">{{ $log['bot']['name'] }}</span>
                            <span class="text-xs text-text-secondary">{{ $log['sentAt']->format('g:i A') }}</span>
                        </div>

                        <div
                            class="bg-white rounded-2xl rounded-tl-md px-4 py-3 shadow-sm border {{ $log['isToday'] ? 'border-accent/30' : 'border-bg-message/50' }}">
                            @if($log['imageUrl'])
                                <img src="{{ $log['imageUrl'] }}" alt="Update image"
                                    class="rounded-xl mb-3 max-w-full h-auto shadow-sm" loading="lazy">
                            @endif

                            <p class="text-sm leading-relaxed text-text-primary whitespace-pre-wrap">{{ $log['message'] }}</p>
                        </div>
                    </div>
                </article>
            @empty
                <div class="text-center py-20">
                    <p class="text-5xl mb-4">🐶</p>
                    <p class="text-lg font-medium text-text-primary mb-1">No updates yet</p>
                    <p class="text-sm text-text-secondary">Your puppies are resting... check back soon!</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection
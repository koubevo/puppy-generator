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
                <img src="{{ $log['imageUrl'] }}" alt="Update image" class="rounded-xl mb-3 max-w-full h-auto shadow-sm"
                    loading="lazy">
            @endif

            <p class="text-sm leading-relaxed text-text-primary whitespace-pre-wrap">{{ $log['message'] }}</p>
        </div>
    </div>
</article>
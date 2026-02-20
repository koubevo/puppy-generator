<div
    class="group relative aspect-square overflow-hidden rounded-2xl bg-white shadow-sm hover:shadow-md transition-shadow">
    <a href="{{ route('image.show', $log['id']) }}" data-gallery-image target="_blank" rel="noopener noreferrer"
        class="block w-full h-full cursor-pointer">
        <img src="{{ $log['imageUrl'] }}" alt="Puppy image"
            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy">

        <div
            class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col justify-end p-3">
            <span class="text-white text-xs {{ $log['message'] ? 'line-clamp-2 mb-1' : '' }}">
                {{ $log['message'] }}
            </span>
            <span class="text-white/80 text-[10px]">{{ $log['sentAt']->format('M j, Y') }}</span>
        </div>
    </a>
</div>
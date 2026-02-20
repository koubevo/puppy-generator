@extends('layouts.app')

@section('title', 'Puppies Gallery')

@section('content')
    <div class="max-w-4xl mx-auto px-4">
        {{-- Sticky Header --}}
        <header class="sticky top-0 z-10 flex items-center justify-between py-4 bg-bg-primary/80 backdrop-blur-md">
            <h1 class="text-2xl font-semibold">Puppies Gallery</h1>

            <a href="{{ route('feed') }}"
                class="text-sm px-4 py-2 rounded-full bg-bg-secondary text-text-secondary hover:bg-bg-message transition-all hover:scale-105 active:scale-95">
                🔙 Back to Feed
            </a>
        </header>

        {{-- Images Grid --}}
        <div id="gallery-container" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 pb-8">
            @forelse($logs as $log)
                @include('partials._gallery-item', ['log' => $log])
            @empty
                <div class="col-span-full text-center py-20">
                    <p class="text-5xl mb-4">🖼️</p>
                    <p class="text-lg font-medium text-text-primary mb-1">No images yet</p>
                    <p class="text-sm text-text-secondary">When puppies send pictures, they will appear here!</p>
                </div>
            @endforelse
        </div>

        {{-- Load More --}}
        @if($hasMore)
            <div id="load-more-gallery-wrapper" class="flex justify-center py-6 pb-10">
                <button id="load-more-gallery-btn" data-next-before="{{ $logs->last()['id'] ?? '' }}" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full
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

        {{-- Image Modal --}}
        <dialog id="image-modal" class="bg-transparent m-auto p-0 w-screen h-screen max-w-none max-h-none overflow-hidden backdrop:bg-black/90 backdrop:backdrop-blur-sm open:animate-fade-in outline-none">
            <div class="relative w-full h-full flex items-center justify-center p-4 sm:p-8">
                <img id="modal-image" src="" alt="Full size puppy image" class="pointer-events-auto max-w-full max-h-full object-contain rounded-lg shadow-[0_0_50px_rgba(0,0,0,0.5)]">
                <button type="button" id="close-modal-btn" class="absolute top-4 right-4 md:top-6 md:right-8 bg-black/40 hover:bg-black text-white p-2 rounded-full transition-all border border-white/10 hover:border-white/30 backdrop-blur-sm cursor-pointer z-50">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        </dialog>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const galleryContainer = document.getElementById('gallery-container');
                const loadMoreBtn = document.getElementById('load-more-gallery-btn');
                const loadMoreWrapper = document.getElementById('load-more-gallery-wrapper');

                if (loadMoreBtn) {
                    loadMoreBtn.addEventListener('click', async () => {
                        const nextBefore = loadMoreBtn.dataset.nextBefore;
                        if (!nextBefore) return;

                        loadMoreBtn.disabled = true;
                        loadMoreBtn.classList.add('opacity-50', 'cursor-not-allowed');
                        const originalText = loadMoreBtn.innerHTML;
                        loadMoreBtn.innerHTML = '<span>Loading...</span><svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';

                        try {
                            const response = await fetch(`/gallery/more?before=${nextBefore}`, {
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });

                            const data = await response.json();

                            if (data.html) {
                                galleryContainer.insertAdjacentHTML('beforeend', data.html);
                            }

                            if (data.hasMore && data.nextBefore) {
                                loadMoreBtn.dataset.nextBefore = data.nextBefore;
                            } else {
                                loadMoreWrapper.remove();
                            }
                        } catch (error) {
                            console.error('Failed to get more gallery items:', error);
                        } finally {
                            if (loadMoreWrapper && loadMoreWrapper.parentNode) {
                                loadMoreBtn.disabled = false;
                                loadMoreBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                                loadMoreBtn.innerHTML = originalText;
                            }
                        }
                    });
                }

                // Modal logic
                const modal = document.getElementById('image-modal');
                const modalImage = document.getElementById('modal-image');
                const closeModalBtn = document.getElementById('close-modal-btn');

                if (modal && modalImage && closeModalBtn) {
                    // Close on button click
                    closeModalBtn.addEventListener('click', () => modal.close());

                    // Close on backdrop click
                    modal.addEventListener('click', (e) => {
                        if (e.target === modal) {
                            modal.close();
                        }
                    });

                    // Event delegation for opening images
                    galleryContainer.addEventListener('click', (e) => {
                        const link = e.target.closest('a[data-gallery-image]');
                        if (link) {
                            e.preventDefault();
                            const fullImageUrl = link.getAttribute('href');
                            
                            // Prevent flash of previous image
                            modalImage.style.opacity = '0.5';
                            
                            // Load new image
                            modalImage.onload = () => {
                                modalImage.style.opacity = '1';
                            };
                            modalImage.src = fullImageUrl;
                            
                            modal.showModal();
                        }
                    });
                }
            });
        </script>
    @endpush
@endsection
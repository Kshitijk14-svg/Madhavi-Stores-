<section class="py-16 bg-white dark:bg-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-end md:justify-between mb-10">
            <div>
                <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white sm:text-4xl">
                    Follow Us on Instagram
                </h2>
                <p class="mt-3 max-w-2xl text-xl text-gray-500 dark:text-gray-400 sm:mt-4">
                    Stay updated with our latest collections and news.
                </p>
            </div>
            
            <div class="mt-6 md:mt-0">
                <a href="https://instagram.com/" target="_blank" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-full shadow-sm text-white bg-gradient-to-r from-purple-500 via-pink-500 to-orange-500 hover:opacity-90 transition duration-300">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ number_format($followersCount) }} Followers</span>
                </a>
            </div>
        </div>

        @if(empty($feed))
            <div class="text-center py-10">
                <p class="text-gray-500">Instagram feed is currently unavailable.</p>
            </div>
        @else
            <!-- Feed Grid -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                @foreach(array_slice($feed, 0, 6) as $post)
                    <a href="{{ $post['permalink'] ?? '#' }}" target="_blank" class="group relative block w-full aspect-w-1 aspect-h-1 overflow-hidden rounded-lg bg-gray-100">
                        @if(isset($post['media_type']) && $post['media_type'] === 'VIDEO')
                            <img src="{{ $post['thumbnail_url'] ?? '' }}" alt="{{ Str::limit($post['caption'] ?? 'Instagram Post', 50) }}" class="object-cover w-full h-full group-hover:scale-110 transition-transform duration-500">
                            <!-- Play Icon Overlay -->
                            <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-20 group-hover:bg-opacity-40 transition duration-300">
                                <svg class="w-10 h-10 text-white opacity-90" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        @else
                            <img src="{{ $post['media_url'] ?? '' }}" alt="{{ Str::limit($post['caption'] ?? 'Instagram Post', 50) }}" class="object-cover w-full h-full group-hover:scale-110 transition-transform duration-500">
                        @endif

                        <!-- Hover Overlay -->
                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300">
                            <span class="text-white text-sm font-semibold px-4 text-center line-clamp-3">
                                {{ $post['caption'] ?? '' }}
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</section>
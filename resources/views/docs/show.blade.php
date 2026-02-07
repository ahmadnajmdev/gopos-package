<x-gopos::docs.layout :title="$title" :isRtl="$isRtl" :currentLocale="$currentLocale">
    <!-- Mobile sidebar overlay -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden" onclick="toggleSidebar()"></div>

    <!-- Header -->
    <header class="fixed top-0 left-0 right-0 z-30 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 no-print">
        <div class="flex items-center justify-between px-4 py-3">
            <!-- Mobile menu button -->
            <button onclick="toggleSidebar()" class="lg:hidden p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                <svg class="w-6 h-6 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>

            <!-- Logo -->
            <a href="{{ route('docs.index') }}" class="flex items-center gap-2">
                <div class="w-8 h-8 bg-primary-600 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                </div>
                <span class="text-xl font-bold text-gray-900 dark:text-white">{{ __('docs.gopos_docs') }}</span>
            </a>

            <!-- Search -->
            <div class="hidden md:flex flex-1 max-w-md mx-8">
                <div class="relative w-full">
                    <input type="text" id="search-input" placeholder="{{ __('docs.search_placeholder') }}" onkeyup="searchDocs()" class="w-full px-4 py-2 {{ $isRtl ? 'pe-10 ps-4' : 'ps-10 pe-4' }} text-sm bg-gray-100 dark:bg-gray-700 border-0 rounded-lg focus:ring-2 focus:ring-primary-500 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400">
                    <svg class="absolute {{ $isRtl ? 'end-3' : 'start-3' }} top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>

            <!-- Right actions -->
            <div class="flex items-center gap-2">
                <!-- Language Switcher -->
                <div class="lang-dropdown">
                    <button class="flex items-center gap-2 px-3 py-2 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                        </svg>
                        <span class="hidden sm:inline">{{ $localeNames[$currentLocale] }}</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="lang-dropdown-menu border border-gray-200 dark:border-gray-700">
                        @foreach($localeNames as $locale => $name)
                            <a href="{{ route('docs.show', ['slug' => $currentSlug, 'lang' => $locale]) }}"
                               class="flex items-center gap-2 px-4 py-2 text-sm {{ $currentLocale === $locale ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} {{ $loop->first ? 'rounded-t-lg' : '' }} {{ $loop->last ? 'rounded-b-lg' : '' }}">
                                @if($currentLocale === $locale)
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                @else
                                    <span class="w-4"></span>
                                @endif
                                {{ $name }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- Dark mode toggle -->
                <button onclick="toggleDarkMode()" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" title="{{ __('docs.toggle_dark_mode') }}">
                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-300 dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                    </svg>
                    <svg class="w-5 h-5 text-gray-300 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </button>

                <!-- Print button -->
                <button onclick="window.print()" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" title="{{ __('docs.print') }}">
                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                </button>

                <!-- Back to app -->
                <a href="{{ url('/admin') }}" class="hidden sm:flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700 transition">
                    <svg class="w-4 h-4 {{ $isRtl ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    {{ __('docs.back_to_app') }}
                </a>
            </div>
        </div>
    </header>

    <div class="flex pt-16">
        <!-- Sidebar -->
        <aside id="sidebar" class="fixed lg:sticky top-16 {{ $isRtl ? 'right-0' : 'left-0' }} z-40 w-72 h-[calc(100vh-4rem)] bg-white dark:bg-gray-800 {{ $isRtl ? 'border-s' : 'border-e' }} border-gray-200 dark:border-gray-700 overflow-y-auto transform {{ $isRtl ? 'translate-x-full lg:translate-x-0' : '-translate-x-full lg:translate-x-0' }} transition-transform duration-300 no-print">
            <nav class="p-4 space-y-6">
                @foreach($navigation as $section)
                    <div>
                        <h3 class="flex items-center gap-2 px-3 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            @if($section['icon'] === 'book-open')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                            @else
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                                </svg>
                            @endif
                            {{ $section['title'] }}
                        </h3>
                        <ul class="mt-2 space-y-1">
                            @foreach($section['items'] as $slug => $itemTitle)
                                <li>
                                    <a href="{{ route('docs.show', $slug) }}"
                                       class="sidebar-link flex items-center gap-3 px-3 py-2 text-sm rounded-lg {{ $currentSlug === $slug ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300 font-medium' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $currentSlug === $slug ? 'bg-primary-500' : 'bg-gray-300 dark:bg-gray-600' }}"></span>
                                        {{ $itemTitle }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach

                <!-- Quick links -->
                <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                    <h3 class="px-3 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        {{ __('docs.quick_links') }}
                    </h3>
                    <ul class="mt-2 space-y-1">
                        <li>
                            <a href="{{ url('/admin') }}" class="sidebar-link flex items-center gap-3 px-3 py-2 text-sm text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                </svg>
                                {{ __('docs.dashboard') }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ url('/admin/pos') }}" class="sidebar-link flex items-center gap-3 px-3 py-2 text-sm text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                                {{ __('docs.point_of_sale') }}
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
        </aside>

        <!-- Main content -->
        <main class="flex-1 min-w-0">
            <div class="flex">
                <!-- Content -->
                <article id="doc-content" class="flex-1 max-w-4xl mx-auto px-6 py-8 print-full">
                    {!! $html !!}

                    <!-- Navigation footer -->
                    <div class="flex items-center justify-between mt-12 pt-6 border-t border-gray-200 dark:border-gray-700 no-print">
                        @php
                            $allSlugs = [];
                            foreach($navigation as $section) {
                                foreach($section['items'] as $slug => $title) {
                                    $allSlugs[] = $slug;
                                }
                            }
                            $currentIndex = array_search($currentSlug, $allSlugs);
                            $prevSlug = $currentIndex > 0 ? $allSlugs[$currentIndex - 1] : null;
                            $nextSlug = $currentIndex < count($allSlugs) - 1 ? $allSlugs[$currentIndex + 1] : null;
                        @endphp

                        @if($prevSlug)
                            <a href="{{ route('docs.show', $prevSlug) }}" class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">
                                <svg class="w-4 h-4 {{ $isRtl ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                                {{ __('docs.previous') }}
                            </a>
                        @else
                            <span></span>
                        @endif

                        @if($nextSlug)
                            <a href="{{ route('docs.show', $nextSlug) }}" class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">
                                {{ __('docs.next') }}
                                <svg class="w-4 h-4 {{ $isRtl ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        @else
                            <span></span>
                        @endif
                    </div>
                </article>

                <!-- Table of Contents -->
                @if(count($toc) > 0)
                    <aside class="hidden xl:block w-64 shrink-0 no-print">
                        <div class="sticky top-24 px-4 py-6">
                            <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">{{ __('docs.on_this_page') }}</h4>
                            <nav class="space-y-2">
                                @foreach($toc as $item)
                                    <a href="#{{ $item['title'] }}" class="block text-sm text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 truncate">
                                        {{ $item['title'] }}
                                    </a>
                                @endforeach
                            </nav>
                        </div>
                    </aside>
                @endif
            </div>
        </main>
    </div>

    <!-- Footer -->
    <footer class="border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 no-print">
        <div class="max-w-7xl mx-auto px-6 py-8">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
                    <div class="w-6 h-6 bg-primary-600 rounded flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                    <span class="text-sm">{{ __('docs.gopos_documentation') }}</span>
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    &copy; {{ date('Y') }} GoPOS. {{ __('docs.all_rights_reserved') }}
                </div>
            </div>
        </div>
    </footer>
</x-gopos::docs.layout>

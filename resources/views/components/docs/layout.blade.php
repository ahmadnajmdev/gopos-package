@props(['title' => 'Documentation', 'isRtl' => false, 'currentLocale' => 'en'])

<!DOCTYPE html>
<html lang="{{ $currentLocale }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} - GoPOS</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('favicon.ico') }}">

    <!-- Tailwind CSS via CDN for standalone docs -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        @font-face {
            font-family: 'Rabar';
            font-style: normal;
            src: url({{ asset('css/fonts/Rabar_021.ttf') }}) format('truetype');
        }

        body {
            font-family: 'Rabar', 'Inter', system-ui, sans-serif;
        }

        /* RTL specific styles */
        [dir="rtl"] .list-disc {
            list-style-position: inside;
        }

        [dir="rtl"] .list-decimal {
            list-style-position: inside;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .dark ::-webkit-scrollbar-track {
            background: #1f2937;
        }

        ::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        .dark ::-webkit-scrollbar-thumb {
            background: #4b5563;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #a1a1a1;
        }

        /* Smooth transitions */
        .sidebar-link {
            transition: all 0.2s ease;
        }

        /* Print styles */
        @media print {
            .no-print {
                display: none !important;
            }

            .print-full {
                width: 100% !important;
                margin: 0 !important;
                padding: 20px !important;
            }
        }

        /* Language dropdown */
        .lang-dropdown {
            position: relative;
        }

        .lang-dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            {{ $isRtl ? 'left' : 'right' }}: 0;
            min-width: 150px;
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1);
            z-index: 50;
            margin-top: 0.5rem;
        }

        .dark .lang-dropdown-menu {
            background: #1f2937;
        }

        .lang-dropdown:hover .lang-dropdown-menu,
        .lang-dropdown:focus-within .lang-dropdown-menu {
            display: block;
        }
    </style>
</head>

<body class="bg-gray-50 dark:bg-gray-900 min-h-screen">
    {{ $slot }}

    <script>
        // Dark mode toggle
        function toggleDarkMode() {
            document.documentElement.classList.toggle('dark');
            localStorage.setItem('darkMode', document.documentElement.classList.contains('dark'));
        }

        // Check for saved dark mode preference
        if (localStorage.getItem('darkMode') === 'true' ||
            (!localStorage.getItem('darkMode') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }

        // Mobile sidebar toggle
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            sidebar.classList.toggle('{{ $isRtl ? "translate-x-full" : "-translate-x-full" }}');
            overlay.classList.toggle('hidden');
        }

        // Smooth scroll to anchor
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Search functionality
        function searchDocs() {
            const query = document.getElementById('search-input').value.toLowerCase();
            const content = document.getElementById('doc-content');
            if (!query) {
                // Remove highlights
                content.innerHTML = content.innerHTML.replace(/<mark class="bg-yellow-200 dark:bg-yellow-800">(.*?)<\/mark>/gi, '$1');
                return;
            }

            // Simple highlight (for demo - production would use proper search)
            const regex = new RegExp(`(${query})`, 'gi');
            const walker = document.createTreeWalker(content, NodeFilter.SHOW_TEXT, null, false);
            let node;
            while (node = walker.nextNode()) {
                if (node.nodeValue.toLowerCase().includes(query)) {
                    const span = document.createElement('span');
                    span.innerHTML = node.nodeValue.replace(regex, '<mark class="bg-yellow-200 dark:bg-yellow-800">$1</mark>');
                    node.parentNode.replaceChild(span, node);
                }
            }
        }
    </script>
</body>

</html>

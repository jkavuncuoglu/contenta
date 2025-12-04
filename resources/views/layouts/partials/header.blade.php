{{-- Site Header - Customize as needed --}}
<header class="bg-white shadow-sm">
    <nav class="container mx-auto px-4 py-4">
        <div class="flex items-center justify-between">
            <a href="/" class="text-2xl font-bold text-gray-900">
                {{ config('app.name') }}
            </a>

            <div class="hidden md:flex items-center space-x-6">
                <a href="/" class="text-gray-700 hover:text-blue-600">Home</a>
                <a href="/about" class="text-gray-700 hover:text-blue-600">About</a>
                <a href="/contact" class="text-gray-700 hover:text-blue-600">Contact</a>
            </div>
        </div>
    </nav>
</header>

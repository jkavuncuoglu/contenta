{{-- Site Footer - Customize as needed --}}
<footer class="bg-gray-900 text-white mt-auto">
    <div class="container mx-auto px-4 py-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div>
                <h3 class="text-lg font-semibold mb-4">{{ config('app.name') }}</h3>
                <p class="text-gray-400">Building amazing websites with markdown and shortcodes.</p>
            </div>

            <div>
                <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                <ul class="space-y-2">
                    <li><a href="/" class="text-gray-400 hover:text-white">Home</a></li>
                    <li><a href="/about" class="text-gray-400 hover:text-white">About</a></li>
                    <li><a href="/contact" class="text-gray-400 hover:text-white">Contact</a></li>
                </ul>
            </div>

            <div>
                <h3 class="text-lg font-semibold mb-4">Legal</h3>
                <ul class="space-y-2">
                    <li><a href="/privacy" class="text-gray-400 hover:text-white">Privacy Policy</a></li>
                    <li><a href="/terms" class="text-gray-400 hover:text-white">Terms of Service</a></li>
                </ul>
            </div>
        </div>

        <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</footer>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $page->meta_title ?? $page->title ?? config('app.name') }}</title>

    @if($page->meta_description ?? null)
    <meta name="description" content="{{ $page->meta_description }}">
    @endif

    @if($page->meta_keywords ?? null)
    <meta name="keywords" content="{{ $page->meta_keywords }}">
    @endif

    <script src="https://cdn.tailwindcss.com"></script>

    @stack('head')
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-50">
        @include('layouts.partials.header')

        <main class="py-12">
            <div class="container mx-auto max-w-7xl px-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    {{-- Left Sidebar --}}
                    <aside class="md:col-span-1">
                        @include('layouts.partials.sidebar')
                    </aside>

                    {{-- Main Content --}}
                    <article class="md:col-span-3">
                        {!! $content !!}
                    </article>
                </div>
            </div>
        </main>

        @include('layouts.partials.footer')
    </div>

    @stack('scripts')
</body>
</html>

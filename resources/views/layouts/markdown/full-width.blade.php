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

    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>

    @stack('head')
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-white">
        {{-- Header/Navigation --}}
        @include('layouts.partials.header')

        {{-- Full Width Content --}}
        <main>
            {!! $content !!}
        </main>

        {{-- Footer --}}
        @include('layouts.partials.footer')
    </div>

    @stack('scripts')
</body>
</html>

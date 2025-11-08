<section class="hero-section relative py-20 px-4 text-center text-white overflow-hidden"
         data-block-id="{{ $blockId }}"
         data-block-type="{{ $blockType }}"
         @if(!empty($config['background_image']))
         style="background-image: url('{{ $config['background_image'] }}'); background-size: cover; background-position: center;"
         @endif>

    @if(!empty($config['background_image']))
    <div class="absolute inset-0 bg-black bg-opacity-40"></div>
    @endif

    <div class="container mx-auto relative z-10">
        <h1 class="text-4xl md:text-6xl font-bold mb-4">
            {{ $config['title'] ?? 'Welcome' }}
        </h1>

        @if(!empty($config['subtitle']))
        <p class="text-xl mb-8 max-w-2xl mx-auto">
            {{ $config['subtitle'] }}
        </p>
        @endif

        @if(!empty($config['button_text']))
        <a href="{{ $config['button_url'] ?? '#' }}"
           class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-lg transition-all duration-300 transform hover:scale-105 shadow-lg">
            {{ $config['button_text'] }}
        </a>
        @endif
    </div>
</section>
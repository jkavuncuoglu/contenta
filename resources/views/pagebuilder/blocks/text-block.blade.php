<div class="text-block py-8 px-4"
     data-block-id="{{ $blockId }}"
     data-block-type="{{ $blockType }}">

    <div class="container mx-auto">
        <div class="prose {{ $config['alignment'] === 'center' ? 'text-center mx-auto' : ($config['alignment'] === 'right' ? 'text-right ml-auto' : 'text-left') }}
                    text-{{ $config['font_size'] ?? 'base' }}
                    max-w-none
                    {{ !empty($config['max_width']) ? 'max-w-' . $config['max_width'] : 'max-w-4xl' }}">

            @if(!empty($config['title']))
            <h2 class="text-3xl font-bold mb-6 text-gray-900">
                {{ $config['title'] }}
            </h2>
            @endif

            <div class="text-gray-700 leading-relaxed">
                {!! $config['content'] ?? 'Sample text content. You can add any HTML content here.' !!}
            </div>
        </div>
    </div>
</div>
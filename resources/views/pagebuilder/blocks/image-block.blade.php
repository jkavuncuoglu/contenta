<div class="image-block py-8 px-4"
     data-block-id="{{ $blockId }}"
     data-block-type="{{ $blockType }}">

    <div class="container mx-auto">
        @if(!empty($config['src']))
        <figure class="{{ $config['alignment'] === 'center' ? 'mx-auto text-center' : ($config['alignment'] === 'right' ? 'ml-auto text-right' : 'text-left') }}
                         {{ !empty($config['max_width']) ? 'max-w-' . $config['max_width'] : 'max-w-4xl' }}">

            <img src="{{ $config['src'] }}"
                 alt="{{ $config['alt'] ?? '' }}"
                 class="max-w-full h-auto rounded-lg shadow-lg
                        {{ !empty($config['border_radius']) ? 'rounded-' . $config['border_radius'] : '' }}
                        {{ !empty($config['shadow']) ? 'shadow-' . $config['shadow'] : '' }}"
                 @if(!empty($config['width'])) style="width: {{ $config['width'] }};" @endif>

            @if(!empty($config['caption']))
            <figcaption class="mt-4 text-sm text-gray-600
                              {{ $config['alignment'] === 'center' ? 'text-center' : ($config['alignment'] === 'right' ? 'text-right' : 'text-left') }}">
                {{ $config['caption'] }}
            </figcaption>
            @endif
        </figure>
        @else
        <div class="image-block-placeholder py-12 px-8 border-2 border-dashed border-gray-300 rounded-lg text-center text-gray-500">
            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <p class="text-lg">No image selected</p>
            <p class="text-sm mt-2">Please select an image to display</p>
        </div>
        @endif
    </div>
</div>
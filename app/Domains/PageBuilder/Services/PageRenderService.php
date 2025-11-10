<?php

declare(strict_types=1);

namespace App\Domains\PageBuilder\Services;

use App\Domains\PageBuilder\Models\Block;
use App\Domains\PageBuilder\Models\Page;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class PageRenderService implements PageRenderServiceContract
{
    /**
     * Render a page to HTML
     */
    public function renderPage(Page $page): string
    {
        $data = $page->data ?? [];
        $layout = $page->layout;

        if (!$layout) {
            throw new \Exception('Page must have a layout to render');
        }

        $renderedSections = $this->renderSections($data['sections'] ?? []);

        /** @var array<string, mixed> $layoutStructure */
        $layoutStructure = $layout->structure;
        return $this->renderLayout($layoutStructure, $renderedSections, $page);
    }

    /**
     * Render individual sections
     *
     * @param array<int, array<string, mixed>> $sections
     * @return array<int, string>
     */
    protected function renderSections(array $sections): array
    {
        $renderedSections = [];

        foreach ($sections as $section) {
            $renderedSections[] = $this->renderSection($section);
        }

        return $renderedSections;
    }

    /**
     * Render a single section/block
     *
     * @param array<string, mixed> $section
     */
    protected function renderSection(array $section): string
    {
        $blockType = $section['type'] ?? null;
        if (!is_string($blockType) || empty($blockType)) {
            return '';
        }

        $config = $section['config'] ?? [];
        if (!is_array($config)) {
            $config = [];
        }

        $blockId = $section['id'] ?? Str::random(8);
        if (!is_string($blockId)) {
            $blockId = Str::random(8);
        }

        $block = Block::where('type', $blockType)->where('is_active', true)->first();

        if (!$block) {
            return $this->renderErrorBlock("Block type '{$blockType}' not found");
        }

        // Validate configuration against schema
        /** @var array<string, mixed> $config */
        $validationErrors = $block->validateConfig($config);
        if (!empty($validationErrors)) {
            return $this->renderErrorBlock("Invalid configuration: " . implode(', ', $validationErrors));
        }

        // Merge with default config
        $defaultConfig = $block->default_config;
        $finalConfig = array_merge($defaultConfig, $config);

        return $this->renderBlock($block, $finalConfig, $blockId);
    }

    /**
     * Render a block using its component
     *
     * @param array<string, mixed> $config
     */
    protected function renderBlock(Block $block, array $config, string $blockId): string
    {
        $componentPath = $block->component_path;

        // Try to find the Blade view
        $viewPath = "pagebuilder.blocks.{$componentPath}";

        if (View::exists($viewPath)) {
            return View::make($viewPath, [
                'config' => $config,
                'blockId' => $blockId,
                'blockType' => $block->type,
            ])->render();
        }

        // Fallback to default block rendering
        return $this->renderDefaultBlock($block, $config, $blockId);
    }

    /**
     * Render default block HTML
     *
     * @param array<string, mixed> $config
     */
    protected function renderDefaultBlock(Block $block, array $config, string $blockId): string
    {
        $html = "<div class=\"block block-{$block->type}\" data-block-id=\"{$blockId}\">";

        switch ($block->type) {
            case 'hero-block':
                $html .= $this->renderHeroBlock($config);
                break;
            case 'text-block':
                $html .= $this->renderTextBlock($config);
                break;
            case 'image-block':
                $html .= $this->renderImageBlock($config);
                break;
            default:
                $html .= "<p>Block type: {$block->type}</p>";
        }

        $html .= "</div>";

        return $html;
    }

    /**
     * Render hero block
     *
     * @param array<string, mixed> $config
     */
    protected function renderHeroBlock(array $config): string
    {
        $title = is_string($config['title'] ?? null) ? $config['title'] : 'Welcome';
        $subtitle = is_string($config['subtitle'] ?? null) ? $config['subtitle'] : '';
        $buttonText = is_string($config['button_text'] ?? null) ? $config['button_text'] : '';
        $buttonUrl = is_string($config['button_url'] ?? null) ? $config['button_url'] : '#';
        $backgroundImage = is_string($config['background_image'] ?? null) ? $config['background_image'] : '';

        $style = $backgroundImage ? "background-image: url('{$backgroundImage}'); background-size: cover; background-position: center;" : '';

        return "
            <section class=\"hero-section py-20 px-4 text-center text-white\" style=\"{$style}\">
                <div class=\"container mx-auto\">
                    <h1 class=\"text-4xl md:text-6xl font-bold mb-4\">{$title}</h1>
                    " . ($subtitle ? "<p class=\"text-xl mb-8\">{$subtitle}</p>" : '') . "
                    " . ($buttonText ? "<a href=\"{$buttonUrl}\" class=\"inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition-colors\">{$buttonText}</a>" : '') . "
                </div>
            </section>
        ";
    }

    /**
     * Render text block
     *
     * @param array<string, mixed> $config
     */
    protected function renderTextBlock(array $config): string
    {
        $content = is_string($config['content'] ?? null) ? $config['content'] : 'Sample text content';
        $alignment = is_string($config['alignment'] ?? null) ? $config['alignment'] : 'left';
        $fontSize = is_string($config['font_size'] ?? null) ? $config['font_size'] : 'base';

        $alignmentClass = "text-{$alignment}";
        $fontSizeClass = "text-{$fontSize}";

        return "
            <div class=\"text-block py-8 px-4\">
                <div class=\"container mx-auto\">
                    <div class=\"prose {$alignmentClass} {$fontSizeClass} max-w-none\">
                        {$content}
                    </div>
                </div>
            </div>
        ";
    }

    /**
     * Render image block
     *
     * @param array<string, mixed> $config
     */
    protected function renderImageBlock(array $config): string
    {
        $src = is_string($config['src'] ?? null) ? $config['src'] : '';
        $alt = is_string($config['alt'] ?? null) ? $config['alt'] : '';
        $caption = is_string($config['caption'] ?? null) ? $config['caption'] : '';
        $alignment = is_string($config['alignment'] ?? null) ? $config['alignment'] : 'center';

        if (!$src) {
            return '<div class="image-block-placeholder py-8">No image selected</div>';
        }

        $alignmentClass = $alignment === 'center' ? 'mx-auto' : ($alignment === 'right' ? 'ml-auto' : '');

        return "
            <div class=\"image-block py-8 px-4\">
                <div class=\"container mx-auto\">
                    <figure class=\"{$alignmentClass}\">
                        <img src=\"{$src}\" alt=\"{$alt}\" class=\"max-w-full h-auto rounded-lg shadow-lg\">
                        " . ($caption ? "<figcaption class=\"mt-2 text-sm text-gray-600 text-center\">{$caption}</figcaption>" : '') . "
                    </figure>
                </div>
            </div>
        ";
    }

    /**
     * Render layout with sections
     *
     * @param array<string, mixed> $layoutStructure
     * @param array<int, string> $renderedSections
     */
    protected function renderLayout(array $layoutStructure, array $renderedSections, Page $page): string
    {
        $areas = $layoutStructure['areas'] ?? ['main'];
        if (!is_array($areas)) {
            $areas = ['main'];
        }
        $settings = $layoutStructure['settings'] ?? [];
        // Type assertion since we know it's an array from the layoutStructure
        assert(is_array($settings));

        $html = "<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n";
        $html .= "<meta charset=\"UTF-8\">\n";
        $html .= "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n";
        $html .= "<title>" . ($page->meta_title ?: $page->title) . "</title>\n";

        if ($page->meta_description) {
            $html .= "<meta name=\"description\" content=\"{$page->meta_description}\">\n";
        }

        if ($page->meta_keywords) {
            $html .= "<meta name=\"keywords\" content=\"{$page->meta_keywords}\">\n";
        }

        // Add Tailwind CSS
        $html .= "<script src=\"https://cdn.tailwindcss.com\"></script>\n";
        $html .= "</head>\n<body>\n";

        // Render main content area
        if (in_array('main', $areas)) {
            $html .= "<main>\n";
            foreach ($renderedSections as $section) {
                $html .= $section . "\n";
            }
            $html .= "</main>\n";
        }

        $html .= "</body>\n</html>";

        return $html;
    }

    /**
     * Render error block
     */
    protected function renderErrorBlock(string $message): string
    {
        return "
            <div class=\"error-block bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded\">
                <strong>Error:</strong> {$message}
            </div>
        ";
    }
}
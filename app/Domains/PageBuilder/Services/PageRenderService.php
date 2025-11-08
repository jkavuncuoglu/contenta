<?php

declare(strict_types=1);

namespace App\Domains\PageBuilder\Services;

use App\Domains\PageBuilder\Models\Block;
use App\Domains\PageBuilder\Models\Page;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class PageRenderService
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

        return $this->renderLayout($layout->structure, $renderedSections, $page);
    }

    /**
     * Render individual sections
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
     */
    protected function renderSection(array $section): string
    {
        $blockType = $section['type'] ?? null;
        $config = $section['config'] ?? [];
        $blockId = $section['id'] ?? Str::random(8);

        if (!$blockType) {
            return '';
        }

        $block = Block::where('type', $blockType)->where('is_active', true)->first();

        if (!$block) {
            return $this->renderErrorBlock("Block type '{$blockType}' not found");
        }

        // Validate configuration against schema
        $validationErrors = $block->validateConfig($config);
        if (!empty($validationErrors)) {
            return $this->renderErrorBlock("Invalid configuration: " . implode(', ', $validationErrors));
        }

        // Merge with default config
        $finalConfig = array_merge($block->default_config, $config);

        return $this->renderBlock($block, $finalConfig, $blockId);
    }

    /**
     * Render a block using its component
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
     */
    protected function renderHeroBlock(array $config): string
    {
        $title = $config['title'] ?? 'Welcome';
        $subtitle = $config['subtitle'] ?? '';
        $buttonText = $config['button_text'] ?? '';
        $buttonUrl = $config['button_url'] ?? '#';
        $backgroundImage = $config['background_image'] ?? '';

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
     */
    protected function renderTextBlock(array $config): string
    {
        $content = $config['content'] ?? 'Sample text content';
        $alignment = $config['alignment'] ?? 'left';
        $fontSize = $config['font_size'] ?? 'base';

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
     */
    protected function renderImageBlock(array $config): string
    {
        $src = $config['src'] ?? '';
        $alt = $config['alt'] ?? '';
        $caption = $config['caption'] ?? '';
        $alignment = $config['alignment'] ?? 'center';

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
     */
    protected function renderLayout(array $layoutStructure, array $renderedSections, Page $page): string
    {
        $areas = $layoutStructure['areas'] ?? ['main'];
        $settings = $layoutStructure['settings'] ?? [];

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
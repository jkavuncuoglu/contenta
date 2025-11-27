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
            case 'hero':
                $html .= $this->renderHero($config);
                break;
            case 'features':
                $html .= $this->renderFeatures($config);
                break;
            case 'contact-form':
                $html .= $this->renderContactForm($config);
                break;
            case 'cta':
                $html .= $this->renderCTA($config);
                break;
            case 'faq':
                $html .= $this->renderFAQ($config);
                break;
            case 'stats':
                $html .= $this->renderStats($config);
                break;
            case 'legal-text':
                $html .= $this->renderLegalText($config);
                break;
            case 'team':
                $html .= $this->renderTeam($config);
                break;
            case 'pricing':
                $html .= $this->renderPricing($config);
                break;
            case 'text-block':
                $html .= $this->renderTextBlock($config);
                break;
            case 'image-block':
                $html .= $this->renderImageBlock($config);
                break;
            default:
                $html .= "<p class=\"p-4\">Block type: {$block->type} (Name: {$block->name})</p>";
        }

        $html .= "</div>";

        return $html;
    }

    /**
     * Render hero block
     *
     * @param array<string, mixed> $config
     */
    protected function renderHero(array $config): string
    {
        $title = is_string($config['title'] ?? null) ? $config['title'] : 'Welcome';
        $subtitle = is_string($config['subtitle'] ?? null) ? $config['subtitle'] : '';
        $description = is_string($config['description'] ?? null) ? $config['description'] : '';
        $primaryButtonText = is_string($config['primaryButtonText'] ?? null) ? $config['primaryButtonText'] : '';
        $primaryButtonUrl = is_string($config['primaryButtonUrl'] ?? null) ? $config['primaryButtonUrl'] : '#';
        $backgroundColor = is_string($config['backgroundColor'] ?? null) ? $config['backgroundColor'] : 'bg-gradient-to-b from-blue-50 to-white';

        return "
            <section class=\"hero-section py-20 px-4 text-center {$backgroundColor}\">
                <div class=\"container mx-auto max-w-4xl\">
                    <h1 class=\"text-4xl md:text-6xl font-bold mb-4 text-gray-900\">{$title}</h1>
                    " . ($subtitle ? "<p class=\"text-xl mb-4 text-gray-600\">{$subtitle}</p>" : '') . "
                    " . ($description ? "<p class=\"text-lg mb-8 text-gray-700\">{$description}</p>" : '') . "
                    " . ($primaryButtonText ? "<a href=\"{$primaryButtonUrl}\" class=\"inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition-colors\">{$primaryButtonText}</a>" : '') . "
                </div>
            </section>
        ";
    }

    /**
     * Render features block
     *
     * @param array<string, mixed> $config
     */
    protected function renderFeatures(array $config): string
    {
        $title = is_string($config['title'] ?? null) ? $config['title'] : 'Our Features';
        $subtitle = is_string($config['subtitle'] ?? null) ? $config['subtitle'] : '';
        $columns = is_array($config['columns'] ?? null) ? $config['columns'] : [];
        $numColumns = is_int($config['numColumns'] ?? null) ? $config['numColumns'] : 3;
        $backgroundColor = is_string($config['backgroundColor'] ?? null) ? $config['backgroundColor'] : 'bg-white';

        $gridClass = match($numColumns) {
            2 => 'md:grid-cols-2',
            4 => 'md:grid-cols-2 lg:grid-cols-4',
            default => 'md:grid-cols-3',
        };

        $html = "<section class=\"py-16 {$backgroundColor}\">
            <div class=\"container mx-auto px-4\">
                <div class=\"mx-auto mb-12 max-w-3xl text-center\">
                    <h2 class=\"mb-4 text-3xl font-bold text-gray-900 md:text-4xl\">{$title}</h2>
                    " . ($subtitle ? "<p class=\"text-lg text-gray-600\">{$subtitle}</p>" : '') . "
                </div>
                <div class=\"mx-auto grid max-w-6xl grid-cols-1 gap-8 {$gridClass}\">";

        foreach ($columns as $column) {
            $columnTitle = is_string($column['title'] ?? null) ? $column['title'] : 'Feature';
            $columnContent = is_string($column['content'] ?? null) ? $column['content'] : '';

            $html .= "
                <div class=\"rounded-xl bg-gray-50 p-8 shadow-sm\">
                    <h3 class=\"mb-2 text-xl font-semibold text-gray-900\">{$columnTitle}</h3>
                    <div class=\"text-gray-600\">{$columnContent}</div>
                </div>";
        }

        $html .= "
                </div>
            </div>
        </section>";

        return $html;
    }

    /**
     * Render contact form block
     *
     * @param array<string, mixed> $config
     */
    protected function renderContactForm(array $config): string
    {
        $title = is_string($config['title'] ?? null) ? $config['title'] : 'Get in Touch';
        $description = is_string($config['description'] ?? null) ? $config['description'] : '';
        $backgroundColor = is_string($config['backgroundColor'] ?? null) ? $config['backgroundColor'] : 'bg-gray-50';

        return "
            <section class=\"py-16 {$backgroundColor}\">
                <div class=\"container mx-auto px-4 max-w-2xl\">
                    <h2 class=\"mb-4 text-3xl font-bold text-gray-900 text-center\">{$title}</h2>
                    " . ($description ? "<p class=\"mb-8 text-gray-600 text-center\">{$description}</p>" : '') . "
                    <div class=\"space-y-4 bg-white p-8 rounded-lg shadow\">
                        <p class=\"text-gray-600\">Contact form (preview mode - form not functional)</p>
                    </div>
                </div>
            </section>
        ";
    }

    /**
     * Render CTA block
     *
     * @param array<string, mixed> $config
     */
    protected function renderCTA(array $config): string
    {
        $title = is_string($config['title'] ?? null) ? $config['title'] : 'Ready to Get Started?';
        $description = is_string($config['description'] ?? null) ? $config['description'] : '';
        $buttonText = is_string($config['buttonText'] ?? null) ? $config['buttonText'] : 'Get Started';
        $buttonUrl = is_string($config['buttonUrl'] ?? null) ? $config['buttonUrl'] : '#';
        $backgroundColor = is_string($config['backgroundColor'] ?? null) ? $config['backgroundColor'] : 'bg-blue-600';

        return "
            <section class=\"py-16 text-white {$backgroundColor}\">
                <div class=\"container mx-auto px-4 text-center max-w-4xl\">
                    <h2 class=\"mb-4 text-4xl font-bold\">{$title}</h2>
                    " . ($description ? "<p class=\"mb-8 text-xl opacity-90\">{$description}</p>" : '') . "
                    <a href=\"{$buttonUrl}\" class=\"inline-block bg-white text-blue-600 font-bold py-3 px-8 rounded-lg hover:bg-gray-100 transition-colors\">{$buttonText}</a>
                </div>
            </section>
        ";
    }

    /**
     * Render FAQ block
     *
     * @param array<string, mixed> $config
     */
    protected function renderFAQ(array $config): string
    {
        $title = is_string($config['title'] ?? null) ? $config['title'] : 'Frequently Asked Questions';
        $subtitle = is_string($config['subtitle'] ?? null) ? $config['subtitle'] : '';
        $items = is_array($config['items'] ?? null) ? $config['items'] : [];
        $backgroundColor = is_string($config['backgroundColor'] ?? null) ? $config['backgroundColor'] : 'bg-white';

        $html = "<section class=\"py-16 {$backgroundColor}\">
            <div class=\"container mx-auto px-4 max-w-4xl\">
                <div class=\"mb-12 text-center\">
                    <h2 class=\"mb-4 text-3xl font-bold text-gray-900\">{$title}</h2>
                    " . ($subtitle ? "<p class=\"text-lg text-gray-600\">{$subtitle}</p>" : '') . "
                </div>
                <div class=\"space-y-4\">";

        foreach ($items as $item) {
            $question = is_string($item['question'] ?? null) ? $item['question'] : '';
            $answer = is_string($item['answer'] ?? null) ? $item['answer'] : '';

            $html .= "
                <div class=\"bg-gray-50 p-6 rounded-lg\">
                    <h3 class=\"font-semibold text-lg text-gray-900 mb-2\">{$question}</h3>
                    <p class=\"text-gray-600\">{$answer}</p>
                </div>";
        }

        $html .= "
                </div>
            </div>
        </section>";

        return $html;
    }

    /**
     * Render stats block
     *
     * @param array<string, mixed> $config
     */
    protected function renderStats(array $config): string
    {
        $title = is_string($config['title'] ?? null) ? $config['title'] : 'Our Impact';
        $stats = is_array($config['stats'] ?? null) ? $config['stats'] : [];
        $backgroundColor = is_string($config['backgroundColor'] ?? null) ? $config['backgroundColor'] : 'bg-gray-50';

        $html = "<section class=\"py-16 {$backgroundColor}\">
            <div class=\"container mx-auto px-4\">
                " . ($title ? "<h2 class=\"mb-12 text-3xl font-bold text-gray-900 text-center\">{$title}</h2>" : '') . "
                <div class=\"grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto\">";

        foreach ($stats as $stat) {
            $value = is_string($stat['value'] ?? null) ? $stat['value'] : '0';
            $label = is_string($stat['label'] ?? null) ? $stat['label'] : 'Stat';

            $html .= "
                <div class=\"text-center\">
                    <div class=\"text-4xl font-bold text-blue-600 mb-2\">{$value}</div>
                    <div class=\"text-gray-600\">{$label}</div>
                </div>";
        }

        $html .= "
                </div>
            </div>
        </section>";

        return $html;
    }

    /**
     * Render legal text block
     *
     * @param array<string, mixed> $config
     */
    protected function renderLegalText(array $config): string
    {
        $title = is_string($config['title'] ?? null) ? $config['title'] : 'Legal Document';
        $lastUpdated = is_string($config['lastUpdated'] ?? null) ? $config['lastUpdated'] : '';
        $content = is_string($config['content'] ?? null) ? $config['content'] : '';

        return "
            <section class=\"py-16 bg-white\">
                <div class=\"container mx-auto px-4 max-w-4xl\">
                    <h1 class=\"mb-4 text-4xl font-bold text-gray-900\">{$title}</h1>
                    " . ($lastUpdated ? "<p class=\"mb-8 text-sm text-gray-600\">Last updated: {$lastUpdated}</p>" : '') . "
                    <div class=\"prose max-w-none\">{$content}</div>
                </div>
            </section>
        ";
    }

    /**
     * Render team block
     *
     * @param array<string, mixed> $config
     */
    protected function renderTeam(array $config): string
    {
        $title = is_string($config['title'] ?? null) ? $config['title'] : 'Meet Our Team';
        $subtitle = is_string($config['subtitle'] ?? null) ? $config['subtitle'] : '';
        $members = is_array($config['members'] ?? null) ? $config['members'] : [];
        $numColumns = is_int($config['columns'] ?? null) ? $config['columns'] : 3;
        $backgroundColor = is_string($config['backgroundColor'] ?? null) ? $config['backgroundColor'] : 'bg-white';

        $gridClass = match($numColumns) {
            2 => 'md:grid-cols-2',
            4 => 'md:grid-cols-2 lg:grid-cols-4',
            default => 'md:grid-cols-3',
        };

        $html = "<section class=\"py-16 {$backgroundColor}\">
            <div class=\"container mx-auto px-4\">
                <div class=\"mb-12 text-center\">
                    <h2 class=\"mb-4 text-3xl font-bold text-gray-900\">{$title}</h2>
                    " . ($subtitle ? "<p class=\"text-lg text-gray-600\">{$subtitle}</p>" : '') . "
                </div>
                <div class=\"grid grid-cols-1 gap-8 max-w-6xl mx-auto {$gridClass}\">";

        foreach ($members as $member) {
            $name = is_string($member['name'] ?? null) ? $member['name'] : 'Team Member';
            $role = is_string($member['role'] ?? null) ? $member['role'] : '';
            $bio = is_string($member['bio'] ?? null) ? $member['bio'] : '';

            $html .= "
                <div class=\"text-center\">
                    <div class=\"w-32 h-32 mx-auto mb-4 bg-gray-200 rounded-full\"></div>
                    <h3 class=\"text-xl font-semibold text-gray-900\">{$name}</h3>
                    " . ($role ? "<p class=\"text-blue-600 mb-2\">{$role}</p>" : '') . "
                    " . ($bio ? "<p class=\"text-gray-600 text-sm\">{$bio}</p>" : '') . "
                </div>";
        }

        $html .= "
                </div>
            </div>
        </section>";

        return $html;
    }

    /**
     * Render pricing block
     *
     * @param array<string, mixed> $config
     */
    protected function renderPricing(array $config): string
    {
        $title = is_string($config['title'] ?? null) ? $config['title'] : 'Pricing Plans';
        $subtitle = is_string($config['subtitle'] ?? null) ? $config['subtitle'] : '';
        $tiers = is_array($config['tiers'] ?? null) ? $config['tiers'] : [];
        $backgroundColor = is_string($config['backgroundColor'] ?? null) ? $config['backgroundColor'] : 'bg-gray-50';

        $html = "<section class=\"py-16 {$backgroundColor}\">
            <div class=\"container mx-auto px-4\">";

        if ($title || $subtitle) {
            $html .= "<div class=\"mb-12 text-center max-w-3xl mx-auto\">
                <h2 class=\"mb-4 text-3xl font-bold text-gray-900 md:text-4xl\">{$title}</h2>
                " . ($subtitle ? "<p class=\"text-lg text-gray-600\">{$subtitle}</p>" : '') . "
            </div>";
        }

        $html .= "<div class=\"grid grid-cols-1 gap-8 max-w-6xl mx-auto md:grid-cols-3\">";

        foreach ($tiers as $tier) {
            $name = is_string($tier['name'] ?? null) ? $tier['name'] : 'Plan';
            $price = is_string($tier['price'] ?? null) ? $tier['price'] : '$0';
            $period = is_string($tier['period'] ?? null) ? $tier['period'] : '';
            $description = is_string($tier['description'] ?? null) ? $tier['description'] : '';
            $features = is_array($tier['features'] ?? null) ? $tier['features'] : [];
            $highlighted = (bool)($tier['highlighted'] ?? false);
            $buttonText = is_string($tier['buttonText'] ?? null) ? $tier['buttonText'] : 'Get Started';
            $buttonUrl = is_string($tier['buttonUrl'] ?? null) ? $tier['buttonUrl'] : '#';

            $cardClass = $highlighted ? 'ring-2 ring-blue-600 scale-105' : '';
            $buttonClass = $highlighted
                ? 'bg-blue-600 text-white hover:bg-blue-700'
                : 'border-2 border-blue-600 text-blue-600 hover:bg-blue-50';

            $html .= "<div class=\"relative flex flex-col rounded-2xl bg-white p-8 shadow-lg transition-all hover:shadow-xl {$cardClass}\">";

            if ($highlighted) {
                $html .= "<div class=\"absolute -top-4 left-1/2 -translate-x-1/2 rounded-full bg-blue-600 px-4 py-1 text-sm font-semibold text-white\">Most Popular</div>";
            }

            $html .= "<div class=\"mb-6\">
                <h3 class=\"mb-2 text-2xl font-bold text-gray-900\">{$name}</h3>
                " . ($description ? "<p class=\"text-sm text-gray-600\">{$description}</p>" : '') . "
            </div>
            <div class=\"mb-6\">
                <div class=\"flex items-baseline\">
                    <span class=\"text-5xl font-bold text-gray-900\">{$price}</span>
                    " . ($period ? "<span class=\"ml-2 text-gray-600\">{$period}</span>" : '') . "
                </div>
            </div>
            <ul class=\"mb-8 flex-1 space-y-3\">";

            foreach ($features as $feature) {
                $featureText = is_string($feature) ? $feature : '';
                $html .= "<li class=\"flex items-start\">
                    <svg class=\"mr-3 h-5 w-5 flex-shrink-0 text-green-500\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                        <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M5 13l4 4L19 7\"></path>
                    </svg>
                    <span class=\"text-gray-700\">{$featureText}</span>
                </li>";
            }

            $html .= "</ul>
            <a href=\"{$buttonUrl}\" class=\"block rounded-lg px-6 py-3 text-center font-semibold transition-colors {$buttonClass}\">{$buttonText}</a>
            </div>";
        }

        $html .= "</div></div></section>";

        return $html;
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
<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Services\ShortcodeParser\Renderer;

use App\Domains\ContentManagement\Services\ShortcodeParser\AST\CommentNode;
use App\Domains\ContentManagement\Services\ShortcodeParser\AST\DocumentNode;
use App\Domains\ContentManagement\Services\ShortcodeParser\AST\MarkdownNode;
use App\Domains\ContentManagement\Services\ShortcodeParser\AST\Node;
use App\Domains\ContentManagement\Services\ShortcodeParser\AST\ShortcodeNode;
use App\Domains\ContentManagement\Services\ShortcodeParser\AST\TextNode;
use App\Domains\ContentManagement\Services\ShortcodeParser\Exceptions\RenderException;
use League\CommonMark\CommonMarkConverter;

class HtmlRenderer
{
    /** @var array<string, BlockRendererInterface> */
    private array $blockRenderers = [];

    private CommonMarkConverter $markdownConverter;

    public function __construct()
    {
        $this->markdownConverter = new CommonMarkConverter([
            'html_input' => 'allow',
            'allow_unsafe_links' => false,
        ]);

        $this->registerDefaultRenderers();
    }

    /**
     * Render AST to HTML
     */
    public function render(DocumentNode $document): string
    {
        $html = '';

        foreach ($document->getChildren() as $child) {
            $html .= $this->renderNode($child);
        }

        return $html;
    }

    /**
     * Render a single node
     */
    private function renderNode(Node $node): string
    {
        if ($node instanceof ShortcodeNode) {
            return $this->renderShortcode($node);
        }

        if ($node instanceof MarkdownNode) {
            return $this->renderMarkdown($node);
        }

        if ($node instanceof TextNode) {
            return $this->renderText($node);
        }

        if ($node instanceof CommentNode) {
            return $this->renderComment($node);
        }

        return '';
    }

    /**
     * Render shortcode node
     */
    private function renderShortcode(ShortcodeNode $node): string
    {
        $renderer = $this->getRenderer($node->tag);

        if ($renderer === null) {
            throw new RenderException(
                sprintf('No renderer found for shortcode tag: [#%s]', $node->tag),
                $node->line,
                $node->column
            );
        }

        // Validate attributes
        $renderer->validate($node->attributes);

        // Render children
        $innerHtml = '';
        foreach ($node->getChildren() as $child) {
            $innerHtml .= $this->renderNode($child);
        }

        return $renderer->render($node, $innerHtml);
    }

    /**
     * Render markdown content
     */
    private function renderMarkdown(MarkdownNode $node): string
    {
        return (string) $this->markdownConverter->convert($node->content);
    }

    /**
     * Render text content
     */
    private function renderText(TextNode $node): string
    {
        return nl2br(htmlspecialchars($node->content, ENT_QUOTES, 'UTF-8'));
    }

    /**
     * Render comment (or hide it)
     */
    private function renderComment(CommentNode $node): string
    {
        // Comments are not rendered in output
        return '';
    }

    /**
     * Register a block renderer
     */
    public function registerRenderer(BlockRendererInterface $renderer): void
    {
        $this->blockRenderers[$renderer->getTag()] = $renderer;
    }

    /**
     * Get renderer for a tag
     */
    private function getRenderer(string $tag): ?BlockRendererInterface
    {
        return $this->blockRenderers[$tag] ?? null;
    }

    /**
     * Register all default block renderers
     */
    private function registerDefaultRenderers(): void
    {
        $renderers = [
            new Blocks\HeroRenderer,
            new Blocks\FeaturesRenderer,
            new Blocks\FeatureRenderer,
            new Blocks\ColumnsRenderer,
            new Blocks\ColumnRenderer,
            new Blocks\ContainerRenderer,
            new Blocks\HeadingRenderer,
            new Blocks\TextRenderer,
            new Blocks\ButtonRenderer,
            new Blocks\ButtonGroupRenderer,
            new Blocks\ImageRenderer,
            new Blocks\GalleryRenderer,
            new Blocks\VideoRenderer,
            new Blocks\QuoteRenderer,
            new Blocks\ListRenderer,
            new Blocks\SeparatorRenderer,
            new Blocks\CtaRenderer,
            new Blocks\FaqRenderer,
            new Blocks\FaqItemRenderer,
            new Blocks\StatsRenderer,
            new Blocks\StatRenderer,
            new Blocks\TeamRenderer,
            new Blocks\MemberRenderer,
            new Blocks\PricingRenderer,
            new Blocks\PlanRenderer,
            new Blocks\ContactFormRenderer,
            new Blocks\NewsletterRenderer,
            new Blocks\MenuRenderer,
            new Blocks\MenuItemRenderer,
            new Blocks\BreadcrumbRenderer,
            new Blocks\BreadcrumbItemRenderer,
            new Blocks\HtmlRenderer,
            new Blocks\GridRenderer,
            new Blocks\GridItemRenderer,
            new Blocks\TestimonialsRenderer,
            new Blocks\TestimonialRenderer,
            new Blocks\LegalTextRenderer,
        ];

        foreach ($renderers as $renderer) {
            $this->registerRenderer($renderer);
        }
    }
}

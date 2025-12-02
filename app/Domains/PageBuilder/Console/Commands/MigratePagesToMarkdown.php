<?php

declare(strict_types=1);

namespace App\Domains\PageBuilder\Console\Commands;

use App\Domains\PageBuilder\Models\Page;
use App\Domains\PageBuilder\Models\Layout;
use App\Domains\PageBuilder\Services\MarkdownRenderServiceContract;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigratePagesToMarkdown extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pagebuilder:migrate-to-markdown
                            {--dry-run : Run without making changes}
                            {--page= : Migrate specific page by ID}
                            {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate legacy page builder pages to markdown format';

    /**
     * Execute the console command.
     */
    public function handle(MarkdownRenderServiceContract $markdownService): int
    {
        $dryRun = $this->option('dry-run');
        $pageId = $this->option('page');
        $force = $this->option('force');

        // Get pages to migrate
        $query = Page::where('content_type', Page::CONTENT_TYPE_LEGACY);

        if ($pageId) {
            $query->where('id', $pageId);
        }

        $pages = $query->get();

        if ($pages->isEmpty()) {
            $this->info('No legacy pages found to migrate.');
            return Command::SUCCESS;
        }

        $this->info("Found {$pages->count()} legacy page(s) to migrate.");

        if (!$force && !$dryRun) {
            if (!$this->confirm('Do you want to proceed with the migration?')) {
                $this->info('Migration cancelled.');
                return Command::SUCCESS;
            }
        }

        $successCount = 0;
        $errorCount = 0;

        foreach ($pages as $page) {
            try {
                $this->info("Migrating page: {$page->id} - {$page->title}");

                $markdown = $this->convertToMarkdown($page);

                if ($dryRun) {
                    $this->line("--- Markdown Preview for '{$page->title}' ---");
                    $this->line($markdown);
                    $this->line("--- End Preview ---\n");
                } else {
                    // Backup original data
                    $backup = [
                        'original_data' => $page->data,
                        'original_layout_id' => $page->layout_id,
                        'migrated_at' => now()->toDateTimeString(),
                    ];

                    // Convert layout_id to template name
                    $layoutTemplate = 'default';
                    if ($page->layout_id) {
                        $layout = Layout::find($page->layout_id);
                        if ($layout) {
                            $layoutTemplate = $this->mapLayoutToTemplate($layout->name);
                        }
                    }

                    DB::transaction(function () use ($page, $markdown, $layoutTemplate, $backup) {
                        $page->update([
                            'markdown_content' => $markdown,
                            'content_type' => Page::CONTENT_TYPE_MARKDOWN,
                            'layout_template' => $layoutTemplate,
                        ]);

                        // Store backup in page meta or separate table if needed
                        // For now, we rely on the revision system to keep history
                    });

                    $this->info("✓ Successfully migrated page {$page->id}");
                    $successCount++;
                }
            } catch (\Exception $e) {
                $this->error("✗ Failed to migrate page {$page->id}: {$e->getMessage()}");
                $errorCount++;
            }
        }

        if (!$dryRun) {
            $this->info("\nMigration complete:");
            $this->info("  Successful: {$successCount}");
            if ($errorCount > 0) {
                $this->error("  Failed: {$errorCount}");
            }
        }

        return Command::SUCCESS;
    }

    /**
     * Convert legacy page data to markdown with shortcodes
     */
    protected function convertToMarkdown(Page $page): string
    {
        $data = $page->data ?? [];
        $sections = $data['sections'] ?? [];

        if (empty($sections)) {
            return "# {$page->title}\n\nNo content to migrate.";
        }

        $markdown = '';

        foreach ($sections as $section) {
            $blockType = $section['type'] ?? null;
            $config = $section['config'] ?? [];

            if (!$blockType || !is_array($config)) {
                continue;
            }

            $markdown .= $this->convertBlockToShortcode($blockType, $config);
            $markdown .= "\n\n";
        }

        return trim($markdown);
    }

    /**
     * Convert a single block to shortcode syntax
     */
    protected function convertBlockToShortcode(string $blockType, array $config): string
    {
        return match ($blockType) {
            'hero' => $this->convertHero($config),
            'features' => $this->convertFeatures($config),
            'contact-form' => $this->convertContactForm($config),
            'cta' => $this->convertCTA($config),
            'faq' => $this->convertFAQ($config),
            'stats' => $this->convertStats($config),
            'legal-text' => $this->convertLegalText($config),
            'team' => $this->convertTeam($config),
            'pricing' => $this->convertPricing($config),
            'text-block' => $this->convertTextBlock($config),
            'image-block' => $this->convertImageBlock($config),
            'container-block' => $this->convertContainer($config),
            'heading-block' => $this->convertHeading($config),
            'quote-block' => $this->convertQuote($config),
            'list-block' => $this->convertList($config),
            default => "<!-- Unknown block type: {$blockType} -->\n"
        };
    }

    protected function convertHero(array $config): string
    {
        $attrs = $this->buildAttributes([
            'title' => $config['title'] ?? 'Welcome',
            'subtitle' => $config['subtitle'] ?? null,
            'background' => $config['backgroundColor'] ?? null,
            'align' => 'center',
        ]);

        $content = ($config['description'] ?? '') . "\n\n";

        if (!empty($config['primaryButtonText'])) {
            $content .= "[#button text=\"{$config['primaryButtonText']}\" url=\"" . ($config['primaryButtonUrl'] ?? '#') . "\" variant=\"primary\"][/#button]";
        }

        return "[#hero{$attrs}]{\n{$content}\n}[/#hero]";
    }

    protected function convertFeatures(array $config): string
    {
        // Determine if 'columns' is the array of features or just a number
        $columnsField = $config['columns'] ?? null;
        $isColumnsArray = is_array($columnsField);

        // Get the items - could be in 'features' or 'columns' field
        $items = $config['features'] ?? ($isColumnsArray ? $columnsField : []);

        // Get column count - prefer numColumns, or count items, or default to 3
        $columnCount = $config['numColumns'] ?? ($isColumnsArray ? count($items) : ($columnsField ?? 3));

        $attrs = $this->buildAttributes([
            'title' => $config['title'] ?? 'Our Features',
            'subtitle' => $config['subtitle'] ?? null,
            'columns' => $columnCount,
            'background' => $config['backgroundColor'] ?? null,
        ]);

        $content = '';

        // Ensure it's an array
        if (!is_array($items)) {
            $items = [];
        }

        foreach ($items as $item) {
            $colAttrs = $this->buildAttributes([
                'title' => $item['title'] ?? 'Feature',
            ]);

            $colContent = $item['content'] ?? $item['description'] ?? '';
            $content .= "[#feature-item{$colAttrs}]{\n{$colContent}\n}[/#feature-item]\n\n";
        }

        return "[#features{$attrs}]{\n{$content}}[/#features]";
    }

    protected function convertContactForm(array $config): string
    {
        $attrs = $this->buildAttributes([
            'title' => $config['title'] ?? 'Get in Touch',
            'description' => $config['description'] ?? null,
            'background' => $config['backgroundColor'] ?? null,
        ]);

        return "[#contact-form{$attrs}][/#contact-form]";
    }

    protected function convertCTA(array $config): string
    {
        $attrs = $this->buildAttributes([
            'title' => $config['title'] ?? 'Ready to Get Started?',
            'button-text' => $config['buttonText'] ?? 'Get Started',
            'button-url' => $config['buttonUrl'] ?? '#',
            'background' => $config['backgroundColor'] ?? null,
        ]);

        $content = $config['description'] ?? '';

        return "[#cta{$attrs}]{\n{$content}\n}[/#cta]";
    }

    protected function convertFAQ(array $config): string
    {
        $attrs = $this->buildAttributes([
            'title' => $config['title'] ?? 'Frequently Asked Questions',
            'subtitle' => $config['subtitle'] ?? null,
            'background' => $config['backgroundColor'] ?? null,
        ]);

        $content = '';
        $items = $config['items'] ?? [];

        foreach ($items as $item) {
            $question = $item['question'] ?? '';
            $answer = $item['answer'] ?? '';
            $content .= "[#faq-item question=\"" . $this->escapeAttribute($question) . "\"]{\n{$answer}\n}[/#faq-item]\n\n";
        }

        return "[#faq{$attrs}]{\n{$content}}[/#faq]";
    }

    protected function convertStats(array $config): string
    {
        $attrs = $this->buildAttributes([
            'title' => $config['title'] ?? null,
            'background' => $config['backgroundColor'] ?? null,
        ]);

        $content = '';
        $stats = $config['stats'] ?? [];

        foreach ($stats as $stat) {
            $statAttrs = $this->buildAttributes([
                'value' => $stat['value'] ?? '0',
                'label' => $stat['label'] ?? 'Stat',
            ]);
            $content .= "[#stat{$statAttrs}][/#stat]\n";
        }

        return "[#stats{$attrs}]{\n{$content}}[/#stats]";
    }

    protected function convertLegalText(array $config): string
    {
        $attrs = $this->buildAttributes([
            'title' => $config['title'] ?? 'Legal Document',
            'last-updated' => $config['lastUpdated'] ?? null,
        ]);

        $content = $config['content'] ?? '';

        return "[#legal-text{$attrs}]{\n{$content}\n}[/#legal-text]";
    }

    protected function convertTeam(array $config): string
    {
        $attrs = $this->buildAttributes([
            'title' => $config['title'] ?? 'Meet Our Team',
            'subtitle' => $config['subtitle'] ?? null,
            'columns' => $config['columns'] ?? 3,
            'background' => $config['backgroundColor'] ?? null,
        ]);

        $content = '';
        $members = $config['members'] ?? [];

        foreach ($members as $member) {
            $memberAttrs = $this->buildAttributes([
                'name' => $member['name'] ?? 'Team Member',
                'role' => $member['role'] ?? null,
                'image' => $member['image'] ?? null,
            ]);

            $bio = $member['bio'] ?? '';
            $content .= "[#team-member{$memberAttrs}]{\n{$bio}\n}[/#team-member]\n\n";
        }

        return "[#team{$attrs}]{\n{$content}}[/#team]";
    }

    protected function convertPricing(array $config): string
    {
        $attrs = $this->buildAttributes([
            'title' => $config['title'] ?? 'Pricing Plans',
            'subtitle' => $config['subtitle'] ?? null,
            'background' => $config['backgroundColor'] ?? null,
        ]);

        $content = '';
        $tiers = $config['tiers'] ?? [];

        foreach ($tiers as $tier) {
            $tierAttrs = $this->buildAttributes([
                'name' => $tier['name'] ?? 'Plan',
                'price' => $tier['price'] ?? '$0',
                'period' => $tier['period'] ?? null,
                'description' => $tier['description'] ?? null,
                'button-text' => $tier['buttonText'] ?? 'Get Started',
                'button-url' => $tier['buttonUrl'] ?? '#',
                'highlighted' => isset($tier['highlighted']) && $tier['highlighted'] ? 'true' : null,
            ]);

            $features = $tier['features'] ?? [];
            $featureList = '';
            foreach ($features as $feature) {
                $featureList .= "- {$feature}\n";
            }

            $content .= "[#pricing-tier{$tierAttrs}]{\n{$featureList}}[/#pricing-tier]\n\n";
        }

        return "[#pricing{$attrs}]{\n{$content}}[/#pricing]";
    }

    protected function convertTextBlock(array $config): string
    {
        $attrs = $this->buildAttributes([
            'align' => $config['alignment'] ?? null,
            'size' => $config['font_size'] ?? null,
        ]);

        $content = $config['content'] ?? 'Sample text content';

        return "[#text{$attrs}]{\n{$content}\n}[/#text]";
    }

    protected function convertImageBlock(array $config): string
    {
        $attrs = $this->buildAttributes([
            'src' => $config['src'] ?? '',
            'alt' => $config['alt'] ?? '',
            'caption' => $config['caption'] ?? null,
            'align' => $config['alignment'] ?? null,
        ]);

        return "[#image{$attrs}][/#image]";
    }

    protected function convertContainer(array $config): string
    {
        $attrs = $this->buildAttributes([
            'width' => $config['width'] ?? 'container',
            'padding' => $config['padding'] ?? 'normal',
            'background' => $config['background'] ?? null,
        ]);

        $content = $config['content'] ?? '';

        return "[#text{$attrs}]{\n{$content}\n}[/#text]";
    }

    protected function convertHeading(array $config): string
    {
        $level = $config['level'] ?? 'h2';
        $text = $config['text'] ?? 'Heading';
        $size = $config['size'] ?? '2xl';
        $alignment = $config['alignment'] ?? 'left';

        // Convert heading to markdown
        $headingLevel = match($level) {
            'h1' => '#',
            'h2' => '##',
            'h3' => '###',
            'h4' => '####',
            'h5' => '#####',
            'h6' => '######',
            default => '##'
        };

        return "{$headingLevel} {$text}";
    }

    protected function convertQuote(array $config): string
    {
        $quote = $config['quote'] ?? 'Quote text';
        $author = $config['author'] ?? null;
        $cite = $config['cite'] ?? null;

        $markdown = "> {$quote}";

        if ($author) {
            $markdown .= "\n>\n> — {$author}";
            if ($cite) {
                $markdown .= ", {$cite}";
            }
        }

        return $markdown;
    }

    protected function convertList(array $config): string
    {
        $title = $config['title'] ?? null;
        $items = $config['items'] ?? '';
        $style = $config['style'] ?? 'bulleted';

        $markdown = '';

        if ($title) {
            $markdown .= "### {$title}\n\n";
        }

        // Handle items - could be comma-separated string or array
        if (is_string($items)) {
            $itemsArray = array_map('trim', explode(',', $items));
        } else {
            $itemsArray = $items;
        }

        $prefix = $style === 'numbered' ? '1.' : '-';

        foreach ($itemsArray as $item) {
            if (!empty($item)) {
                $markdown .= "{$prefix} {$item}\n";
            }
        }

        return rtrim($markdown);
    }

    /**
     * Build attribute string from array
     */
    protected function buildAttributes(array $attrs): string
    {
        $parts = [];

        foreach ($attrs as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $escapedValue = $this->escapeAttribute((string) $value);
            $parts[] = "{$key}=\"{$escapedValue}\"";
        }

        return empty($parts) ? '' : ' ' . implode(' ', $parts);
    }

    /**
     * Escape attribute value for use in shortcode
     */
    protected function escapeAttribute(string $value): string
    {
        return str_replace('"', '\"', $value);
    }

    /**
     * Map legacy layout names to template names
     */
    protected function mapLayoutToTemplate(string $layoutName): string
    {
        $normalized = strtolower(str_replace([' ', '_'], '-', $layoutName));

        return match (true) {
            str_contains($normalized, 'full-width') => 'full-width',
            str_contains($normalized, 'sidebar-left') || str_contains($normalized, 'left-sidebar') => 'sidebar-left',
            str_contains($normalized, 'sidebar-right') || str_contains($normalized, 'right-sidebar') => 'sidebar-right',
            default => 'default',
        };
    }
}

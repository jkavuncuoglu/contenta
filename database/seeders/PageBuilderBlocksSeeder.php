<?php

namespace Database\Seeders;

use App\Domains\PageBuilder\Models\Block;
use Illuminate\Database\Seeder;

class PageBuilderBlocksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $blocks = [
            [
                'name' => 'Hero Section',
                'type' => 'hero',
                'category' => Block::CATEGORY_CONTENT,
                'component_path' => 'pagebuilder/blocks/HeroBlock.vue',
                'description' => 'Large hero section with title, description and call-to-action buttons',
                'config_schema' => [
                    'title' => ['type' => 'string', 'default' => 'Welcome to Our Site', 'required' => true],
                    'subtitle' => ['type' => 'string', 'default' => ''],
                    'description' => ['type' => 'string', 'default' => 'Discover amazing content', 'required' => true],
                    'primaryButtonText' => ['type' => 'string', 'default' => 'Get Started'],
                    'primaryButtonUrl' => ['type' => 'string', 'default' => '#'],
                    'secondaryButtonText' => ['type' => 'string', 'default' => 'Learn More'],
                    'secondaryButtonUrl' => ['type' => 'string', 'default' => '#'],
                    'backgroundImage' => ['type' => 'string', 'default' => ''],
                    'backgroundColor' => ['type' => 'string', 'default' => 'bg-gradient-to-b from-blue-50 to-white dark:from-gray-900 dark:to-gray-800'],
                    'textAlign' => ['type' => 'string', 'default' => 'center'],
                ],
            ],
            [
                'name' => 'Features Grid',
                'type' => 'features',
                'category' => Block::CATEGORY_CONTENT,
                'component_path' => 'pagebuilder/blocks/FeaturesBlock.vue',
                'description' => 'Grid layout showcasing features with editable columns',
                'config_schema' => [
                    'title' => ['type' => 'string', 'default' => 'Our Features', 'required' => true],
                    'subtitle' => ['type' => 'string', 'default' => 'Everything you need to succeed'],
                    'columns' => ['type' => 'array', 'default' => []],
                    'numColumns' => ['type' => 'number', 'default' => 3],
                    'backgroundColor' => ['type' => 'string', 'default' => 'bg-white dark:bg-gray-900'],
                ],
            ],
            [
                'name' => 'Contact Form',
                'type' => 'contact-form',
                'category' => Block::CATEGORY_FORMS,
                'component_path' => 'pagebuilder/blocks/ContactFormBlock.vue',
                'description' => 'Contact form with name, email, phone, subject and message fields',
                'config_schema' => [
                    'title' => ['type' => 'string', 'default' => 'Get in Touch', 'required' => true],
                    'description' => ['type' => 'string', 'default' => 'Fill out the form below'],
                    'submitUrl' => ['type' => 'string', 'default' => '/contact', 'required' => true],
                    'successMessage' => ['type' => 'string', 'default' => 'Thank you for your message!'],
                    'showPhone' => ['type' => 'boolean', 'default' => true],
                    'showSubject' => ['type' => 'boolean', 'default' => true],
                    'backgroundColor' => ['type' => 'string', 'default' => 'bg-gray-50 dark:bg-gray-800'],
                ],
            ],
            [
                'name' => 'Call to Action',
                'type' => 'cta',
                'category' => Block::CATEGORY_CONTENT,
                'component_path' => 'pagebuilder/blocks/CTABlock.vue',
                'description' => 'Call-to-action section with button',
                'config_schema' => [
                    'title' => ['type' => 'string', 'default' => 'Ready to Get Started?', 'required' => true],
                    'description' => ['type' => 'string', 'default' => 'Join thousands today'],
                    'buttonText' => ['type' => 'string', 'default' => 'Get Started Now'],
                    'buttonUrl' => ['type' => 'string', 'default' => '#'],
                    'backgroundColor' => ['type' => 'string', 'default' => 'bg-blue-600'],
                    'pattern' => ['type' => 'string', 'default' => 'gradient'],
                ],
            ],
            [
                'name' => 'FAQ Section',
                'type' => 'faq',
                'category' => Block::CATEGORY_CONTENT,
                'component_path' => 'pagebuilder/blocks/FAQBlock.vue',
                'description' => 'Accordion-style FAQ section',
                'config_schema' => [
                    'title' => ['type' => 'string', 'default' => 'Frequently Asked Questions', 'required' => true],
                    'subtitle' => ['type' => 'string', 'default' => ''],
                    'items' => ['type' => 'array', 'default' => []],
                    'backgroundColor' => ['type' => 'string', 'default' => 'bg-white dark:bg-gray-900'],
                ],
            ],
            [
                'name' => 'Statistics',
                'type' => 'stats',
                'category' => Block::CATEGORY_CONTENT,
                'component_path' => 'pagebuilder/blocks/StatsBlock.vue',
                'description' => 'Display key statistics and numbers',
                'config_schema' => [
                    'title' => ['type' => 'string', 'default' => 'Our Impact'],
                    'stats' => ['type' => 'array', 'default' => []],
                    'backgroundColor' => ['type' => 'string', 'default' => 'bg-gray-50 dark:bg-gray-800'],
                ],
            ],
            [
                'name' => 'Legal Text',
                'type' => 'legal-text',
                'category' => Block::CATEGORY_CONTENT,
                'component_path' => 'pagebuilder/blocks/LegalTextBlock.vue',
                'description' => 'Formatted text block for legal pages',
                'config_schema' => [
                    'title' => ['type' => 'string', 'default' => 'Legal Document', 'required' => true],
                    'lastUpdated' => ['type' => 'string', 'default' => ''],
                    'content' => ['type' => 'string', 'default' => ''],
                    'sections' => ['type' => 'array', 'default' => []],
                ],
            ],
            [
                'name' => 'Team Members',
                'type' => 'team',
                'category' => Block::CATEGORY_CONTENT,
                'component_path' => 'pagebuilder/blocks/TeamBlock.vue',
                'description' => 'Display team members with photos and bios',
                'config_schema' => [
                    'title' => ['type' => 'string', 'default' => 'Meet Our Team', 'required' => true],
                    'subtitle' => ['type' => 'string', 'default' => ''],
                    'members' => ['type' => 'array', 'default' => []],
                    'columns' => ['type' => 'number', 'default' => 3],
                    'backgroundColor' => ['type' => 'string', 'default' => 'bg-white dark:bg-gray-900'],
                ],
            ],
            [
                'name' => 'Pricing Plans',
                'type' => 'pricing',
                'category' => Block::CATEGORY_CONTENT,
                'component_path' => 'pagebuilder/blocks/PricingBlock.vue',
                'description' => 'Display pricing tiers with features and call-to-action buttons',
                'config_schema' => [
                    'title' => ['type' => 'string', 'default' => 'Pricing Plans', 'required' => true],
                    'subtitle' => ['type' => 'string', 'default' => 'Choose the perfect plan for your needs'],
                    'tiers' => ['type' => 'array', 'default' => []],
                    'backgroundColor' => ['type' => 'string', 'default' => 'bg-gray-50 dark:bg-gray-900'],
                ],
            ],
        ];

        foreach ($blocks as $blockData) {
            Block::updateOrCreate(
                ['type' => $blockData['type']],
                $blockData
            );
        }

        $this->command->info('PageBuilder blocks seeded successfully!');
    }
}

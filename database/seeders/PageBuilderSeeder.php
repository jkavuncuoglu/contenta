<?php

namespace Database\Seeders;

use App\Domains\PageBuilder\Models\Block;
use App\Domains\PageBuilder\Models\Layout;
use Illuminate\Database\Seeder;

class PageBuilderSeeder extends Seeder
{
    public function run(): void
    {
        // Create or update default layouts
        Layout::updateOrCreate(
            ['slug' => 'default'],
            [
                'name' => 'Default Layout',
                'structure' => [
                    'areas' => ['main'],
                    'settings' => [
                        'container_width' => 'full',
                        'spacing' => 'normal'
                    ]
                ],
                'description' => 'A simple single-area layout for basic pages',
                'is_active' => true,
            ]
        );

        Layout::updateOrCreate(
            ['slug' => 'landing'],
            [
                'name' => 'Landing Page Layout',
                'structure' => [
                    'areas' => ['hero', 'main', 'footer'],
                    'settings' => [
                        'container_width' => 'full',
                        'spacing' => 'tight'
                    ]
                ],
                'description' => 'Layout optimized for landing pages with hero section',
                'is_active' => true,
            ]
        );

        // Layout Blocks
        Block::updateOrCreate(
            ['type' => 'hero-block'],
            [
                'name' => 'Hero Block',
                'category' => Block::CATEGORY_LAYOUT,
                'config_schema' => [
                    'title' => [
                        'type' => 'string',
                        'required' => true,
                        'default' => 'Welcome to Our Site',
                        'label' => 'Title',
                        'description' => 'Main hero title'
                    ],
                    'subtitle' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => '',
                        'label' => 'Subtitle',
                        'description' => 'Optional subtitle text'
                    ],
                    'button_text' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => '',
                        'label' => 'Button Text',
                        'description' => 'Call-to-action button text'
                    ],
                    'button_url' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => '#',
                        'label' => 'Button URL',
                        'description' => 'Call-to-action button link'
                    ],
                    'background_image' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => '',
                        'label' => 'Background Image',
                        'description' => 'Hero background image URL'
                    ]
                ],
                'component_path' => 'hero-block',
                'description' => 'A large hero section perfect for landing pages',
                'is_active' => true,
            ]
        );

        Block::updateOrCreate(
            ['type' => 'two-column-block'],
            [
                'name' => 'Two Column Block',
                'category' => Block::CATEGORY_LAYOUT,
                'config_schema' => [
                    'left_content' => [
                        'type' => 'string',
                        'required' => true,
                        'default' => 'Left column content',
                        'label' => 'Left Column',
                        'description' => 'Content for left column'
                    ],
                    'right_content' => [
                        'type' => 'string',
                        'required' => true,
                        'default' => 'Right column content',
                        'label' => 'Right Column',
                        'description' => 'Content for right column'
                    ],
                    'column_ratio' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => '50-50',
                        'label' => 'Column Ratio',
                        'description' => 'Width ratio of columns',
                        'options' => ['50-50', '60-40', '40-60', '70-30', '30-70']
                    ],
                    'gap' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => 'normal',
                        'label' => 'Gap Size',
                        'description' => 'Space between columns',
                        'options' => ['tight', 'normal', 'loose']
                    ]
                ],
                'component_path' => 'two-column-block',
                'description' => 'Two column layout for side-by-side content',
                'is_active' => true,
            ]
        );

        Block::updateOrCreate(
            ['type' => 'container-block'],
            [
                'name' => 'Container Block',
                'category' => Block::CATEGORY_LAYOUT,
                'config_schema' => [
                    'content' => [
                        'type' => 'string',
                        'required' => true,
                        'default' => 'Container content',
                        'label' => 'Content',
                        'description' => 'Content within the container'
                    ],
                    'width' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => 'container',
                        'label' => 'Width',
                        'description' => 'Container width',
                        'options' => ['narrow', 'container', 'wide', 'full']
                    ],
                    'background' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => 'transparent',
                        'label' => 'Background',
                        'description' => 'Background color',
                        'options' => ['transparent', 'white', 'gray', 'primary']
                    ],
                    'padding' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => 'normal',
                        'label' => 'Padding',
                        'description' => 'Internal padding',
                        'options' => ['none', 'tight', 'normal', 'loose']
                    ]
                ],
                'component_path' => 'container-block',
                'description' => 'Container block with customizable width and styling',
                'is_active' => true,
            ]
        );

        // Content Blocks
        Block::updateOrCreate(
            ['type' => 'text-block'],
            [
                'name' => 'Text Block',
                'category' => Block::CATEGORY_CONTENT,
                'config_schema' => [
                    'title' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => '',
                        'label' => 'Title',
                        'description' => 'Optional section title'
                    ],
                    'content' => [
                        'type' => 'string',
                        'required' => true,
                        'default' => 'Your content goes here...',
                        'label' => 'Content',
                        'description' => 'Rich text content (HTML supported)'
                    ],
                    'alignment' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => 'left',
                        'label' => 'Text Alignment',
                        'description' => 'Text alignment (left, center, right)',
                        'options' => ['left', 'center', 'right']
                    ],
                    'font_size' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => 'base',
                        'label' => 'Font Size',
                        'description' => 'Text size',
                        'options' => ['sm', 'base', 'lg', 'xl', '2xl']
                    ],
                    'max_width' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => '4xl',
                        'label' => 'Max Width',
                        'description' => 'Maximum content width',
                        'options' => ['sm', 'md', 'lg', 'xl', '2xl', '4xl', '6xl']
                    ]
                ],
                'component_path' => 'text-block',
                'description' => 'Rich text content block with formatting options',
                'is_active' => true,
            ]
        );

        Block::updateOrCreate(
            ['type' => 'heading-block'],
            [
                'name' => 'Heading Block',
                'category' => Block::CATEGORY_CONTENT,
                'config_schema' => [
                    'text' => [
                        'type' => 'string',
                        'required' => true,
                        'default' => 'Heading Text',
                        'label' => 'Heading Text',
                        'description' => 'The heading text'
                    ],
                    'level' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => 'h2',
                        'label' => 'Heading Level',
                        'description' => 'Semantic heading level',
                        'options' => ['h1', 'h2', 'h3', 'h4', 'h5', 'h6']
                    ],
                    'size' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => '2xl',
                        'label' => 'Size',
                        'description' => 'Visual size',
                        'options' => ['lg', 'xl', '2xl', '3xl', '4xl', '5xl']
                    ],
                    'alignment' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => 'left',
                        'label' => 'Alignment',
                        'description' => 'Text alignment',
                        'options' => ['left', 'center', 'right']
                    ],
                    'color' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => 'default',
                        'label' => 'Color',
                        'description' => 'Text color',
                        'options' => ['default', 'primary', 'secondary', 'muted']
                    ]
                ],
                'component_path' => 'heading-block',
                'description' => 'Customizable heading/title block',
                'is_active' => true,
            ]
        );

        Block::updateOrCreate(
            ['type' => 'quote-block'],
            [
                'name' => 'Quote Block',
                'category' => Block::CATEGORY_CONTENT,
                'config_schema' => [
                    'quote' => [
                        'type' => 'string',
                        'required' => true,
                        'default' => 'Your quote goes here...',
                        'label' => 'Quote Text',
                        'description' => 'The quotation text'
                    ],
                    'author' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => '',
                        'label' => 'Author',
                        'description' => 'Quote attribution'
                    ],
                    'cite' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => '',
                        'label' => 'Citation',
                        'description' => 'Source or citation'
                    ],
                    'style' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => 'bordered',
                        'label' => 'Style',
                        'description' => 'Quote styling',
                        'options' => ['simple', 'bordered', 'highlighted', 'large']
                    ]
                ],
                'component_path' => 'quote-block',
                'description' => 'Blockquote for testimonials and quotes',
                'is_active' => true,
            ]
        );

        Block::updateOrCreate(
            ['type' => 'list-block'],
            [
                'name' => 'List Block',
                'category' => Block::CATEGORY_CONTENT,
                'config_schema' => [
                    'title' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => '',
                        'label' => 'Title',
                        'description' => 'Optional list title'
                    ],
                    'items' => [
                        'type' => 'array',
                        'required' => true,
                        'default' => 'Item 1, Item 2, Item 3',
                        'label' => 'List Items',
                        'description' => 'Comma-separated list items'
                    ],
                    'style' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => 'bulleted',
                        'label' => 'List Style',
                        'description' => 'Type of list',
                        'options' => ['bulleted', 'numbered', 'checkmarks', 'none']
                    ],
                    'icon' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => 'default',
                        'label' => 'Icon Style',
                        'description' => 'Icon for list items',
                        'options' => ['default', 'check', 'arrow', 'star', 'none']
                    ]
                ],
                'component_path' => 'list-block',
                'description' => 'Bullet points or numbered list',
                'is_active' => true,
            ]
        );

        // Media Blocks
        Block::updateOrCreate(
            ['type' => 'image-block'],
            [
                'name' => 'Image Block',
                'category' => Block::CATEGORY_MEDIA,
                'config_schema' => [
                    'src' => [
                        'type' => 'string',
                        'required' => true,
                        'default' => '',
                        'label' => 'Image URL',
                        'description' => 'Image source URL'
                    ],
                    'alt' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => '',
                        'label' => 'Alt Text',
                        'description' => 'Alternative text for accessibility'
                    ],
                    'caption' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => '',
                        'label' => 'Caption',
                        'description' => 'Image caption text'
                    ],
                    'alignment' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => 'center',
                        'label' => 'Alignment',
                        'description' => 'Image alignment',
                        'options' => ['left', 'center', 'right']
                    ],
                    'max_width' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => '4xl',
                        'label' => 'Max Width',
                        'description' => 'Maximum image width',
                        'options' => ['sm', 'md', 'lg', 'xl', '2xl', '4xl', '6xl', 'full']
                    ],
                    'border_radius' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => 'lg',
                        'label' => 'Border Radius',
                        'description' => 'Image border radius',
                        'options' => ['none', 'sm', 'md', 'lg', 'xl', 'full']
                    ],
                    'shadow' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => 'lg',
                        'label' => 'Shadow',
                        'description' => 'Image shadow effect',
                        'options' => ['none', 'sm', 'md', 'lg', 'xl', '2xl']
                    ]
                ],
                'component_path' => 'image-block',
                'description' => 'Display images with various styling options',
                'is_active' => true,
            ]
        );

        Block::updateOrCreate(
            ['type' => 'video-block'],
            [
                'name' => 'Video Block',
                'category' => Block::CATEGORY_MEDIA,
                'config_schema' => [
                    'video_url' => [
                        'type' => 'string',
                        'required' => true,
                        'default' => '',
                        'label' => 'Video URL',
                        'description' => 'YouTube, Vimeo, or direct video URL'
                    ],
                    'title' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => '',
                        'label' => 'Title',
                        'description' => 'Optional video title'
                    ],
                    'description' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => '',
                        'label' => 'Description',
                        'description' => 'Video description or caption'
                    ],
                    'aspect_ratio' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => '16/9',
                        'label' => 'Aspect Ratio',
                        'description' => 'Video aspect ratio',
                        'options' => ['16/9', '4/3', '1/1', '21/9']
                    ],
                    'autoplay' => [
                        'type' => 'boolean',
                        'required' => false,
                        'default' => false,
                        'label' => 'Autoplay',
                        'description' => 'Auto-play video on load'
                    ]
                ],
                'component_path' => 'video-block',
                'description' => 'Embed videos from YouTube, Vimeo, or direct links',
                'is_active' => true,
            ]
        );

        Block::updateOrCreate(
            ['type' => 'gallery-block'],
            [
                'name' => 'Image Gallery Block',
                'category' => Block::CATEGORY_MEDIA,
                'config_schema' => [
                    'title' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => '',
                        'label' => 'Gallery Title',
                        'description' => 'Optional gallery title'
                    ],
                    'images' => [
                        'type' => 'array',
                        'required' => true,
                        'default' => '',
                        'label' => 'Image URLs',
                        'description' => 'Comma-separated image URLs'
                    ],
                    'columns' => [
                        'type' => 'number',
                        'required' => false,
                        'default' => 3,
                        'label' => 'Columns',
                        'description' => 'Number of columns'
                    ],
                    'gap' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => 'normal',
                        'label' => 'Gap Size',
                        'description' => 'Space between images',
                        'options' => ['tight', 'normal', 'loose']
                    ],
                    'lightbox' => [
                        'type' => 'boolean',
                        'required' => false,
                        'default' => true,
                        'label' => 'Enable Lightbox',
                        'description' => 'Click to enlarge images'
                    ]
                ],
                'component_path' => 'gallery-block',
                'description' => 'Image gallery with grid layout',
                'is_active' => true,
            ]
        );

        // Form Blocks
        Block::updateOrCreate(
            ['type' => 'contact-form-block'],
            [
                'name' => 'Contact Form Block',
                'category' => Block::CATEGORY_FORMS,
                'config_schema' => [
                    'title' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => 'Contact Us',
                        'label' => 'Form Title',
                        'description' => 'Form heading'
                    ],
                    'description' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => '',
                        'label' => 'Description',
                        'description' => 'Form description text'
                    ],
                    'recipient_email' => [
                        'type' => 'string',
                        'required' => true,
                        'default' => '',
                        'label' => 'Recipient Email',
                        'description' => 'Where to send form submissions'
                    ],
                    'show_phone' => [
                        'type' => 'boolean',
                        'required' => false,
                        'default' => false,
                        'label' => 'Include Phone Field',
                        'description' => 'Show phone number field'
                    ],
                    'show_subject' => [
                        'type' => 'boolean',
                        'required' => false,
                        'default' => true,
                        'label' => 'Include Subject Field',
                        'description' => 'Show subject field'
                    ],
                    'submit_text' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => 'Send Message',
                        'label' => 'Submit Button Text',
                        'description' => 'Text for submit button'
                    ]
                ],
                'component_path' => 'contact-form-block',
                'description' => 'Customizable contact form',
                'is_active' => true,
            ]
        );

        Block::updateOrCreate(
            ['type' => 'newsletter-block'],
            [
                'name' => 'Newsletter Signup Block',
                'category' => Block::CATEGORY_FORMS,
                'config_schema' => [
                    'title' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => 'Subscribe to Our Newsletter',
                        'label' => 'Title',
                        'description' => 'Form heading'
                    ],
                    'description' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => 'Get the latest updates delivered to your inbox',
                        'label' => 'Description',
                        'description' => 'Subscription pitch'
                    ],
                    'button_text' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => 'Subscribe',
                        'label' => 'Button Text',
                        'description' => 'Submit button text'
                    ],
                    'show_name' => [
                        'type' => 'boolean',
                        'required' => false,
                        'default' => false,
                        'label' => 'Ask for Name',
                        'description' => 'Include name field'
                    ],
                    'style' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => 'inline',
                        'label' => 'Form Style',
                        'description' => 'Layout style',
                        'options' => ['inline', 'stacked']
                    ]
                ],
                'component_path' => 'newsletter-block',
                'description' => 'Email newsletter subscription form',
                'is_active' => true,
            ]
        );

        // Navigation Blocks
        Block::updateOrCreate(
            ['type' => 'button-block'],
            [
                'name' => 'Button Block',
                'category' => Block::CATEGORY_NAVIGATION,
                'config_schema' => [
                    'text' => [
                        'type' => 'string',
                        'required' => true,
                        'default' => 'Click Here',
                        'label' => 'Button Text',
                        'description' => 'Text displayed on button'
                    ],
                    'url' => [
                        'type' => 'string',
                        'required' => true,
                        'default' => '#',
                        'label' => 'Link URL',
                        'description' => 'Button destination'
                    ],
                    'style' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => 'primary',
                        'label' => 'Button Style',
                        'description' => 'Visual style',
                        'options' => ['primary', 'secondary', 'outline', 'ghost']
                    ],
                    'size' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => 'medium',
                        'label' => 'Button Size',
                        'description' => 'Button size',
                        'options' => ['small', 'medium', 'large']
                    ],
                    'alignment' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => 'left',
                        'label' => 'Alignment',
                        'description' => 'Button alignment',
                        'options' => ['left', 'center', 'right']
                    ],
                    'new_tab' => [
                        'type' => 'boolean',
                        'required' => false,
                        'default' => false,
                        'label' => 'Open in New Tab',
                        'description' => 'Open link in new tab'
                    ]
                ],
                'component_path' => 'button-block',
                'description' => 'Call-to-action button with link',
                'is_active' => true,
            ]
        );

        Block::updateOrCreate(
            ['type' => 'cta-block'],
            [
                'name' => 'CTA Block',
                'category' => Block::CATEGORY_NAVIGATION,
                'config_schema' => [
                    'title' => [
                        'type' => 'string',
                        'required' => true,
                        'default' => 'Ready to Get Started?',
                        'label' => 'Title',
                        'description' => 'CTA heading'
                    ],
                    'description' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => '',
                        'label' => 'Description',
                        'description' => 'Supporting text'
                    ],
                    'button_text' => [
                        'type' => 'string',
                        'required' => true,
                        'default' => 'Get Started',
                        'label' => 'Button Text',
                        'description' => 'Primary button text'
                    ],
                    'button_url' => [
                        'type' => 'string',
                        'required' => true,
                        'default' => '#',
                        'label' => 'Button URL',
                        'description' => 'Primary button link'
                    ],
                    'secondary_button_text' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => '',
                        'label' => 'Secondary Button Text',
                        'description' => 'Optional second button'
                    ],
                    'secondary_button_url' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => '',
                        'label' => 'Secondary Button URL',
                        'description' => 'Second button link'
                    ],
                    'background' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => 'primary',
                        'label' => 'Background',
                        'description' => 'Background color/style',
                        'options' => ['primary', 'secondary', 'gradient', 'white', 'gray']
                    ]
                ],
                'component_path' => 'cta-block',
                'description' => 'Call-to-action section with buttons',
                'is_active' => true,
            ]
        );

        Block::updateOrCreate(
            ['type' => 'breadcrumb-block'],
            [
                'name' => 'Breadcrumb Block',
                'category' => Block::CATEGORY_NAVIGATION,
                'config_schema' => [
                    'separator' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => 'slash',
                        'label' => 'Separator Style',
                        'description' => 'Breadcrumb separator',
                        'options' => ['slash', 'arrow', 'chevron', 'dot']
                    ],
                    'show_home' => [
                        'type' => 'boolean',
                        'required' => false,
                        'default' => true,
                        'label' => 'Show Home',
                        'description' => 'Include home link'
                    ],
                    'style' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => 'default',
                        'label' => 'Style',
                        'description' => 'Visual style',
                        'options' => ['default', 'bold', 'minimal']
                    ]
                ],
                'component_path' => 'breadcrumb-block',
                'description' => 'Navigation breadcrumbs',
                'is_active' => true,
            ]
        );
    }
}

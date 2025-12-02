# Migration Example: Legacy Page → Markdown

This document shows an example of what a migrated page looks like after running the migration command.

## Original Legacy Page Data (JSON)

```json
{
  "sections": [
    {
      "id": "hero-1",
      "type": "hero",
      "config": {
        "title": "Welcome to Contenta",
        "subtitle": "Build amazing content-driven websites",
        "description": "Contenta is a powerful CMS platform built with Laravel and Vue.js",
        "primaryButtonText": "Get Started",
        "primaryButtonUrl": "/signup",
        "backgroundColor": "bg-gradient-to-r from-blue-500 to-purple-600"
      }
    },
    {
      "id": "features-1",
      "type": "features",
      "config": {
        "title": "Why Choose Contenta",
        "subtitle": "Everything you need to build great content",
        "numColumns": 3,
        "backgroundColor": "bg-gray-50",
        "columns": [
          {
            "title": "Easy to Use",
            "content": "Intuitive interface makes content creation a breeze"
          },
          {
            "title": "Powerful",
            "content": "Built on Laravel 12 with modern architecture"
          },
          {
            "title": "Flexible",
            "content": "Customize everything to match your needs"
          }
        ]
      }
    },
    {
      "id": "pricing-1",
      "type": "pricing",
      "config": {
        "title": "Simple Pricing",
        "subtitle": "Choose the plan that fits your needs",
        "backgroundColor": "bg-white",
        "tiers": [
          {
            "name": "Starter",
            "price": "$9",
            "period": "/month",
            "description": "Perfect for small projects",
            "features": [
              "5 pages",
              "Basic support",
              "Community access"
            ],
            "buttonText": "Get Started",
            "buttonUrl": "/signup?plan=starter",
            "highlighted": false
          },
          {
            "name": "Professional",
            "price": "$29",
            "period": "/month",
            "description": "For growing businesses",
            "features": [
              "Unlimited pages",
              "Priority support",
              "Advanced features",
              "Custom domain"
            ],
            "buttonText": "Start Free Trial",
            "buttonUrl": "/signup?plan=pro",
            "highlighted": true
          }
        ]
      }
    },
    {
      "id": "cta-1",
      "type": "cta",
      "config": {
        "title": "Ready to Get Started?",
        "description": "Join thousands of users already building with Contenta",
        "buttonText": "Create Account",
        "buttonUrl": "/signup",
        "backgroundColor": "bg-blue-600"
      }
    }
  ]
}
```

## Migrated Markdown Content

```markdown
[#hero title="Welcome to Contenta" subtitle="Build amazing content-driven websites" background="bg-gradient-to-r from-blue-500 to-purple-600" align="center"]{
Contenta is a powerful CMS platform built with Laravel and Vue.js

[#button text="Get Started" url="/signup" variant="primary"][/#button]
}[/#hero]

[#features title="Why Choose Contenta" subtitle="Everything you need to build great content" columns="3" background="bg-gray-50"]{
[#feature-item title="Easy to Use"]{
Intuitive interface makes content creation a breeze
}[/#feature-item]

[#feature-item title="Powerful"]{
Built on Laravel 12 with modern architecture
}[/#feature-item]

[#feature-item title="Flexible"]{
Customize everything to match your needs
}[/#feature-item]

}[/#features]

[#pricing title="Simple Pricing" subtitle="Choose the plan that fits your needs" background="bg-white"]{
[#pricing-tier name="Starter" price="$9" period="/month" description="Perfect for small projects" button-text="Get Started" button-url="/signup?plan=starter"]{
- 5 pages
- Basic support
- Community access
}[/#pricing-tier]

[#pricing-tier name="Professional" price="$29" period="/month" description="For growing businesses" button-text="Start Free Trial" button-url="/signup?plan=pro" highlighted="true"]{
- Unlimited pages
- Priority support
- Advanced features
- Custom domain
}[/#pricing-tier]

}[/#pricing]

[#cta title="Ready to Get Started?" button-text="Create Account" button-url="/signup" background="bg-blue-600"]{
Join thousands of users already building with Contenta
}[/#cta]
```

## Migration Command Usage

### Dry Run (Preview Only)
```bash
./vendor/bin/sail artisan page:migrate-md --dry-run
```

### Migrate Specific Page
```bash
./vendor/bin/sail artisan page:migrate-md --page=123
```

### Migrate All Legacy Pages
```bash
./vendor/bin/sail artisan page:migrate-md
```

### Force Migration (Skip Confirmation)
```bash
./vendor/bin/sail artisan page:migrate-md --force
```

## Features

- **Automatic Layout Mapping**: Converts layout_id to layout_template (default, full-width, sidebar-left, sidebar-right)
- **Nested Content Support**: Properly converts columns, items, tiers, and other nested structures
- **Attribute Escaping**: Safely escapes quotes in attribute values
- **Backup via Revisions**: Relies on the existing revision system to preserve original data
- **Dry Run Mode**: Preview migration without making changes
- **Transaction Safety**: Uses database transactions to ensure data integrity
- **Error Handling**: Continues processing even if individual pages fail

## Supported Block Types

1. Hero
2. Features
3. Contact Form
4. CTA (Call to Action)
5. FAQ
6. Stats
7. Legal Text
8. Team
9. Pricing
10. Text Block
11. Image Block

All block types from the legacy page builder are fully supported!

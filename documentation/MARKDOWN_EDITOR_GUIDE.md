# Markdown Editor User Guide

**Version:** 1.0
**Last Updated:** November 28, 2025

---

## Table of Contents

1. [Introduction](#introduction)
2. [Getting Started](#getting-started)
3. [Basic Markdown](#basic-markdown)
4. [Using Shortcodes](#using-shortcodes)
5. [Common Page Layouts](#common-page-layouts)
6. [Tips & Best Practices](#tips--best-practices)
7. [Troubleshooting](#troubleshooting)

---

## Introduction

### What is the Markdown Editor?

The Markdown Editor is a powerful yet simple content creation tool that lets you write pages and posts using plain text with special formatting. It combines:

- **Markdown** - Simple text formatting (headings, bold, links, etc.)
- **Shortcodes** - Pre-built components for complex layouts (buttons, galleries, hero sections, etc.)

### Why Use Markdown?

**Benefits:**
- ✅ Write faster without clicking toolbar buttons
- ✅ Content is portable and future-proof
- ✅ Focus on content, not formatting
- ✅ Version control friendly
- ✅ Clean, semantic output

**Example:**
Instead of using a visual editor to make text bold, you simply write:
```markdown
This is **bold text** and this is *italic text*.
```

---

## Getting Started

### Accessing the Editor

1. Navigate to **Admin** → **Content** → **Posts** or **Pages**
2. Click **"Create New Post"** or **"Create New Page"**
3. You'll see the Markdown Editor with:
   - Main editing area on the left
   - Live preview on the right (if enabled)
   - Shortcode library sidebar (click to browse available components)

### Editor Interface

**Main Components:**

| Section | Description |
|---------|-------------|
| **Toolbar** | Quick formatting buttons (optional) |
| **Editor Pane** | Where you write your markdown content |
| **Preview Pane** | Live preview of how your content will look |
| **Shortcode Library** | Browse and insert pre-built components |

### Your First Content

Let's create a simple page:

```markdown
# Welcome to My Page

This is a paragraph with **bold** and *italic* text.

## Section 1

Here's a list:
- First item
- Second item
- Third item

[Read More](/about)
```

**Result:** A clean page with a heading, paragraph, subheading, list, and link.

---

## Basic Markdown

### Text Formatting

```markdown
**bold text**
*italic text*
***bold and italic***
~~strikethrough~~
`inline code`
```

**Renders as:**
- **bold text**
- *italic text*
- ***bold and italic***
- ~~strikethrough~~
- `inline code`

### Headings

```markdown
# Heading 1 (Page Title)
## Heading 2 (Main Sections)
### Heading 3 (Subsections)
#### Heading 4 (Minor Sections)
##### Heading 5
###### Heading 6
```

**Tip:** Use only one H1 per page (for the page title).

### Links

```markdown
[Link text](https://example.com)
[Internal page](/about)
[Link with title](https://example.com "Hover text")
```

### Images

```markdown
![Image description](/images/photo.jpg)
![Logo](/images/logo.png "Company Logo")
```

**Note:** The text in brackets is important for accessibility (screen readers).

### Lists

**Unordered Lists:**
```markdown
- First item
- Second item
  - Nested item
  - Another nested item
- Third item
```

**Ordered Lists:**
```markdown
1. First step
2. Second step
3. Third step
```

**Task Lists:**
```markdown
- [x] Completed task
- [ ] Incomplete task
- [ ] Another task
```

### Quotes

```markdown
> This is a quote.
> It can span multiple lines.
>
> — Author Name
```

### Horizontal Lines

```markdown
---
```

Creates a horizontal divider.

### Tables

```markdown
| Header 1 | Header 2 | Header 3 |
|----------|----------|----------|
| Cell 1   | Cell 2   | Cell 3   |
| Cell 4   | Cell 5   | Cell 6   |
```

### Code Blocks

For code snippets:

````markdown
```javascript
function hello() {
  console.log("Hello, World!");
}
```
````

---

## Using Shortcodes

### What are Shortcodes?

Shortcodes are pre-built components that you can insert into your content. They handle complex layouts and functionality without requiring coding knowledge.

**Format:**
```markdown
[#component-name attribute="value"]
{content goes here}
[/#component-name]
```

### Browsing Available Shortcodes

1. Click the **"Shortcodes"** button in the editor sidebar
2. Browse categories: Layout, Content, Media, Forms, etc.
3. Click any shortcode to see details and examples
4. Click **"Insert"** to add it to your content

### Most Commonly Used Shortcodes

#### 1. Hero Section (Page Header)

Perfect for landing pages and page headers.

```markdown
[#hero
  title="Welcome to Our Platform"
  subtitle="Build amazing content"
  primaryButtonText="Get Started"
  primaryButtonUrl="/signup"
]
[/#hero]
```

**Use When:** Starting a landing page or highlighting key information.

---

#### 2. Call-to-Action (CTA)

Prominent boxes encouraging user action.

```markdown
[#cta
  title="Ready to Get Started?"
  description="Join thousands of users already using our platform"
  buttonText="Sign Up Free"
  buttonUrl="/signup"
]
[/#cta]
```

**Use When:** Encouraging newsletter signups, downloads, or purchases.

---

#### 3. Button

Simple clickable button.

```markdown
[#button url="/contact" variant="primary" size="lg"]
{Contact Us}
[/#button]
```

**Variants:**
- `primary` - Main action button (blue)
- `secondary` - Less prominent (gray)
- `outline` - Outlined button
- `ghost` - Minimal styling

**Sizes:** `sm`, `md`, `lg`

---

#### 4. Two-Column Layout

Split content into side-by-side columns.

```markdown
[#columns ratio="1:1"]
  [#column]
  {
  ## Left Column
  Content for the left side
  }
  [/#column]

  [#column]
  {
  ## Right Column
  Content for the right side
  }
  [/#column]
[/#columns]
```

**Ratios:**
- `1:1` - Equal columns (50/50)
- `1:2` - Narrow left, wide right (33/66)
- `2:1` - Wide left, narrow right (66/33)

**Use When:** Comparing features, image + text layouts, testimonials.

---

#### 5. Image with Caption

Enhanced image with styling.

```markdown
[#image
  src="/images/product.jpg"
  alt="Product photo"
  caption="Our flagship product"
  width="full"
  rounded="lg"
]
[/#image]
```

**Widths:** `sm`, `md`, `lg`, `xl`, `full`
**Rounded:** `none`, `sm`, `md`, `lg`, `xl`, `full` (circular)

---

#### 6. Gallery

Grid of images.

```markdown
[#gallery columns="3" gap="4" rounded="lg"]
  [#image src="/img1.jpg" alt="Photo 1"][/#image]
  [#image src="/img2.jpg" alt="Photo 2"][/#image]
  [#image src="/img3.jpg" alt="Photo 3"][/#image]
  [#image src="/img4.jpg" alt="Photo 4"][/#image]
  [#image src="/img5.jpg" alt="Photo 5"][/#image]
  [#image src="/img6.jpg" alt="Photo 6"][/#image]
[/#gallery]
```

**Use When:** Showcasing portfolio work, product photos, team photos.

---

#### 7. Features Grid

Highlight product/service features.

```markdown
[#features title="Why Choose Us" columns="3"]
  [#feature icon="⚡" title="Fast Performance"]
  {Lightning-fast load times and optimization}
  [/#feature]

  [#feature icon="🔒" title="Secure by Default"]
  {Enterprise-grade security built-in}
  [/#feature]

  [#feature icon="✨" title="Easy to Use"]
  {Intuitive interface for everyone}
  [/#feature]
[/#features]
```

**Use When:** Landing pages, about pages, service pages.

---

#### 8. Quote Block

Styled blockquote with attribution.

```markdown
[#quote author="Steve Jobs" role="Apple Co-founder"]
{
Design is not just what it looks like and feels like.
Design is how it works.
}
[/#quote]
```

**Use When:** Customer testimonials, expert opinions, inspirational quotes.

---

#### 9. FAQ / Accordion

Collapsible Q&A sections.

```markdown
[#faq title="Frequently Asked Questions"]
  [#faq-item question="How do I get started?"]
  {
  Simply create an account and follow our quick start guide.
  }
  [/#faq-item]

  [#faq-item question="Is there a free trial?"]
  {
  Yes! We offer a 14-day free trial with no credit card required.
  }
  [/#faq-item]

  [#faq-item question="Can I cancel anytime?"]
  {
  Absolutely. Cancel anytime from your account settings.
  }
  [/#faq-item]
[/#faq]
```

**Use When:** Help pages, product pages, support documentation.

---

#### 10. Stats Display

Show impressive numbers.

```markdown
[#stats columns="4"]
  [#stat value="10k+" label="Active Users"][/#stat]
  [#stat value="99%" label="Uptime"][/#stat]
  [#stat value="24/7" label="Support"][/#stat]
  [#stat value="50+" label="Countries"][/#stat]
[/#stats]
```

**Use When:** About pages, landing pages, showcasing growth.

---

### Nesting Shortcodes

You can nest shortcodes inside each other for complex layouts:

```markdown
[#container width="xl" padding="12" backgroundColor="bg-gray-50"]
  [#columns ratio="1:1"]
    [#column]
      [#heading level="2"]{Our Services}[/#heading]
      [#text]
      {
      We offer comprehensive solutions for your business needs.
      }
      [/#text]
    [/#column]

    [#column]
      [#image src="/services.jpg" alt="Our services"][/#image]
    [/#column]
  [/#columns]
[/#container]
```

**Result:** A full-width container with a two-column layout containing a heading, text, and image.

---

## Common Page Layouts

### Landing Page Example

```markdown
# Welcome to Contenta

[#hero
  title="The Modern CMS for Content Creators"
  subtitle="Build beautiful pages without coding"
  primaryButtonText="Get Started Free"
  primaryButtonUrl="/signup"
  secondaryButtonText="Watch Demo"
  secondaryButtonUrl="/demo"
]
[/#hero]

[#features title="Why Choose Contenta" columns="3"]
  [#feature icon="⚡" title="Lightning Fast"]
  {Optimized for speed and performance}
  [/#feature]

  [#feature icon="🎨" title="Beautiful Design"]
  {Modern, responsive layouts out of the box}
  [/#feature]

  [#feature icon="🔧" title="Easy to Use"]
  {No coding required - just write and publish}
  [/#feature]
[/#features]

[#cta
  title="Ready to Start Creating?"
  description="Join thousands of content creators today"
  buttonText="Create Your Account"
  buttonUrl="/signup"
]
[/#cta]
```

---

### About Page Example

```markdown
# About Our Company

[#columns ratio="1:1"]
  [#column]
  {
  ## Our Story

  Founded in 2020, we set out to make content management simple and powerful.
  Our team of designers and developers work together to create the best CMS experience.

  **Our Mission:** Empower creators to build amazing content without technical barriers.
  }
  [/#column]

  [#column]
  {
  [#image src="/about.jpg" alt="Our team" rounded="lg"][/#image]
  }
  [/#column]
[/#columns]

## Our Team

[#team title="Meet the Team" columns="3"]
  [#member
    name="Sarah Johnson"
    role="CEO & Founder"
    image="/team/sarah.jpg"
    linkedin="https://linkedin.com/in/sarahjohnson"
  ]
  [/#member]

  [#member
    name="Mike Chen"
    role="CTO"
    image="/team/mike.jpg"
    linkedin="https://linkedin.com/in/mikechen"
  ]
  [/#member]

  [#member
    name="Emma Wilson"
    role="Lead Designer"
    image="/team/emma.jpg"
    linkedin="https://linkedin.com/in/emmawilson"
  ]
  [/#member]
[/#team]
```

---

### Blog Post Example

```markdown
# How to Build Better Websites

![Header image](/blog/header.jpg)

Building websites doesn't have to be complicated. Here are 5 tips to help you create better web experiences.

## 1. Start with Content

Before designing, focus on your **content strategy**. What are you trying to communicate?

[#quote author="Content strategist"]
{Content is king, but context is queen.}
[/#quote]

## 2. Keep It Simple

Less is more when it comes to web design. Focus on:
- Clear navigation
- Readable typography
- Ample white space
- Fast load times

## 3. Make It Mobile-Friendly

Over 60% of web traffic is mobile. Use responsive design principles.

[#image src="/mobile-design.jpg" alt="Mobile design" width="lg" rounded="lg"][/#image]

## 4. Optimize for Speed

[#stats columns="3"]
  [#stat value="53%" label="Users leave if site takes >3s to load"][/#stat]
  [#stat value="2x" label="Mobile users expect faster load times"][/#stat]
  [#stat value="100ms" label="Delay = 1% revenue loss"][/#stat]
[/#stats]

## 5. Test with Real Users

Get feedback early and often. Use tools like:
- User testing
- Analytics
- Heatmaps
- A/B testing

---

**Ready to build your website?**

[#button url="/signup" variant="primary" size="lg"]
{Get Started Today}
[/#button]
```

---

### Contact Page Example

```markdown
# Get in Touch

We'd love to hear from you! Reach out with any questions or feedback.

[#columns ratio="1:1" gap="12"]
  [#column]
  {
  ## Contact Information

  **Email:** contact@example.com
  **Phone:** (555) 123-4567
  **Address:** 123 Main St, City, State 12345

  **Business Hours:**
  Monday - Friday: 9am - 5pm
  Saturday: 10am - 2pm
  Sunday: Closed
  }
  [/#column]

  [#column]
  {
  [#contact-form
    title="Send Us a Message"
    submitText="Send Message"
    successMessage="Thank you! We'll respond within 24 hours."
  ]
  [/#contact-form]
  }
  [/#column]
[/#columns]

[#separator /]

[#faq title="Common Questions"]
  [#faq-item question="What's your response time?"]
  {We typically respond to inquiries within 24 business hours.}
  [/#faq-item]

  [#faq-item question="Do you offer phone support?"]
  {Yes! Call us during business hours for immediate assistance.}
  [/#faq-item]
[/#faq]
```

---

## Tips & Best Practices

### Writing Good Content

1. **Use descriptive headings** - Help readers scan your content
2. **Break up long paragraphs** - Keep paragraphs 2-4 sentences
3. **Use lists** - Easier to read than long sentences
4. **Add images** - Visual content increases engagement
5. **Include CTAs** - Guide readers to the next action

### Markdown Best Practices

1. **One H1 per page** - Use `#` only for the page title
2. **Use proper heading hierarchy** - Don't skip levels (H2 → H4)
3. **Add alt text to images** - Important for accessibility
4. **Use descriptive link text** - Avoid "click here"
5. **Preview before publishing** - Always check how it looks

### Shortcode Best Practices

1. **Don't overuse shortcodes** - Use only when needed
2. **Keep nesting simple** - Max 2-3 levels deep
3. **Use semantic shortcuts** - Choose the right component for your content
4. **Test responsive behavior** - Check mobile view
5. **Validate required attributes** - Make sure you include all required fields

### Performance Tips

1. **Optimize images** - Compress before uploading
2. **Use appropriate sizes** - Don't use `width="full"` for small images
3. **Lazy load images** - Enable in settings
4. **Minimize nesting** - Deep nesting can slow rendering
5. **Cache content** - Enable page caching in settings

### SEO Tips

1. **Use descriptive headings** - Include keywords naturally
2. **Write good meta descriptions** - Set in page settings
3. **Add alt text** - Helps search engines understand images
4. **Create internal links** - Link to related content
5. **Use readable URLs** - Set clean slugs in page settings

---

## Troubleshooting

### Common Issues

#### Shortcode Not Rendering

**Problem:** Shortcode appears as plain text instead of rendering.

**Solutions:**
1. Check for typos in the tag name
2. Verify closing tag matches opening tag
3. Ensure required attributes are present
4. Check for unescaped special characters

**Example Error:**
```markdown
[#buton url="/test"]{Click Me}[/#buton]
```
**Fix:** Change `buton` to `button`

---

#### Images Not Showing

**Problem:** Image doesn't display.

**Solutions:**
1. Verify image URL is correct
2. Check image file exists in media library
3. Ensure image path starts with `/`
4. Check file permissions

---

#### Broken Layout

**Problem:** Columns or layout looks wrong.

**Solutions:**
1. Check for unclosed shortcodes
2. Verify proper nesting structure
3. Test on different screen sizes
4. Clear browser cache

---

#### Formatting Not Working

**Problem:** Bold, italic, or other markdown not rendering.

**Solutions:**
1. No spaces between markers and text: `**text**` not `** text **`
2. Check for smart quotes - use straight quotes `"` not `""`
3. Escape special characters with `\`

---

### Getting Help

**Resources:**
- **Shortcode Library** - Built-in examples and documentation
- **Preview Pane** - See real-time rendering
- **Technical Spec** - See `SHORTCUT_SYNTAX_SPEC.md` for full reference
- **Support** - Contact support@example.com

**Quick Tips:**
- Use the preview pane to catch errors early
- Start simple and add complexity gradually
- Copy working examples from the shortcode library
- Save drafts frequently

---

## Keyboard Shortcuts

Speed up your editing with these shortcuts:

| Shortcut | Action |
|----------|--------|
| `Ctrl/Cmd + B` | Bold selected text |
| `Ctrl/Cmd + I` | Italic selected text |
| `Ctrl/Cmd + K` | Insert link |
| `Ctrl/Cmd + S` | Save draft |
| `Ctrl/Cmd + Z` | Undo |
| `Ctrl/Cmd + Shift + Z` | Redo |
| `Tab` | Indent |
| `Shift + Tab` | Outdent |

---

## Appendix: Quick Reference

### Markdown Cheat Sheet

```markdown
# Heading 1
## Heading 2
### Heading 3

**bold**
*italic*
***bold italic***
~~strikethrough~~
`code`

[Link text](URL)
![Image alt](URL)

- List item
- List item

1. Numbered item
2. Numbered item

> Quote

---

| Table | Header |
|-------|--------|
| Cell  | Cell   |
```

### Common Shortcodes

```markdown
[#hero title="Title" subtitle="Subtitle"][/#hero]

[#button url="/page" variant="primary"]{Text}[/#button]

[#columns ratio="1:1"]
  [#column]{Content}[/#column]
  [#column]{Content}[/#column]
[/#columns]

[#image src="/path.jpg" alt="Description"][/#image]

[#cta title="Title" buttonText="Text" buttonUrl="/page"][/#cta]

[#features title="Title" columns="3"]
  [#feature title="Feature"]{Description}[/#feature]
[/#features]

[#faq title="Questions"]
  [#faq-item question="Q?"]{Answer}[/#faq-item]
[/#faq]
```

---

## Version History

- **v1.0** (2025-11-28) - Initial user guide

---

**Need Help?** Contact support at support@example.com or consult the [Technical Documentation](../SHORTCUT_SYNTAX_SPEC.md).

---

[← Back to User Manual](USER_MANUAL.md) • [Main README](../README.md)

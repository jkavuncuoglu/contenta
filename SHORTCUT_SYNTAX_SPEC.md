# Shortcut Syntax Specification

## Version 1.0

This document defines the markdown shortcut syntax for all block types in the Contenta CMS.

## Syntax Rules

### Basic Structure
```
[#tag attribute="value" attribute2='value']
{inner content}
[/#tag]
```

### Rules
1. **Opening tag**: `[#tagname ...]`
2. **Closing tag**: `[/#tagname]`
3. **Attributes**: `key="value"` or `key='value'`
4. **Content**: Wrapped in `{...}` or between tags
5. **Nesting**: Shortcuts can be nested unlimited levels
6. **Self-closing**: Some tags can be self-closing: `[#separator /]`
7. **Whitespace**: Flexible whitespace handling
8. **Escaping**: Use `\` to escape special characters

---

## Block Type Reference

### 1. Hero Block
**Purpose:** Large hero section with title, subtitle, description, and CTA buttons

**Syntax:**
```markdown
[#hero
  title="Welcome to Our Platform"
  subtitle="Build amazing content"
  description="Get started today with our powerful CMS"
  primaryButtonText="Get Started"
  primaryButtonUrl="/signup"
  secondaryButtonText="Learn More"
  secondaryButtonUrl="/about"
  backgroundColor="bg-gradient-to-b from-blue-50 to-white"
  textAlign="center"
]
[/#hero]
```

**Attributes:**
- `title` (required): Main heading
- `subtitle` (optional): Subheading
- `description` (optional): Body text
- `primaryButtonText` (optional): Primary CTA text
- `primaryButtonUrl` (optional): Primary CTA link
- `secondaryButtonText` (optional): Secondary CTA text
- `secondaryButtonUrl` (optional): Secondary CTA link
- `backgroundColor` (optional): Tailwind background class
- `textAlign` (optional): `left`, `center`, `right` (default: `center`)

---

### 2. Features Block
**Purpose:** Multi-column feature grid

**Syntax:**
```markdown
[#features
  title="Our Amazing Features"
  subtitle="Everything you need"
  columns="3"
]
{
  ## Fast Performance
  Lightning-fast load times and optimization

  ---

  ## Secure by Default
  Enterprise-grade security built-in

  ---

  ## Easy to Use
  Intuitive interface for everyone
}
[/#features]
```

**Alternative JSON-style:**
```markdown
[#features title="Our Features" columns="3"]
[#feature icon="⚡" title="Fast Performance"]
{Lightning-fast load times}
[/#feature]

[#feature icon="🔒" title="Secure by Default"]
{Enterprise-grade security}
[/#feature]

[#feature icon="✨" title="Easy to Use"]
{Intuitive interface}
[/#feature]
[/#features]
```

**Attributes:**
- `title` (optional): Section title
- `subtitle` (optional): Section subtitle
- `columns` (optional): `2`, `3`, `4` (default: `3`)

---

### 3. Two-Column Block / Flex Layout
**Purpose:** Side-by-side columns with configurable ratio

**Syntax:**
```markdown
[#columns ratio="1:1" gap="8" reverse="false"]
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

**Attributes:**
- `ratio` (optional): `1:1`, `1:2`, `2:1`, `1:3`, `3:1` (default: `1:1`)
- `gap` (optional): Tailwind spacing value (default: `8`)
- `reverse` (optional): `true` or `false` - reverse on mobile
- `align` (optional): `start`, `center`, `end`, `stretch`

---

### 4. Container Block
**Purpose:** Wrapper container with styling

**Syntax:**
```markdown
[#container width="lg" padding="8" backgroundColor="bg-white" className="shadow-lg rounded-lg"]
{
Your content here with custom container styling
}
[/#container]
```

**Attributes:**
- `width` (optional): `sm`, `md`, `lg`, `xl`, `2xl`, `full` (default: `lg`)
- `padding` (optional): Tailwind spacing value (default: `8`)
- `backgroundColor` (optional): Tailwind background class
- `className` (optional): Additional CSS classes

---

### 5. Heading Block
**Purpose:** Heading element (h1-h6)

**Syntax:**
```markdown
[#heading level="2" align="center" className="text-blue-600"]
{Your Heading Text}
[/#heading]
```

**Attributes:**
- `level` (required): `1`, `2`, `3`, `4`, `5`, `6`
- `align` (optional): `left`, `center`, `right` (default: `left`)
- `className` (optional): Additional CSS classes

**Shorthand:** Standard markdown headings work too:
```markdown
# H1 Heading
## H2 Heading
### H3 Heading
```

---

### 6. Text Block
**Purpose:** Rich text content

**Syntax:**
```markdown
[#text align="left" fontSize="base" className="text-gray-700"]
{
Your **rich text** content here with *markdown* support.

Multiple paragraphs work fine.
}
[/#text]
```

**Attributes:**
- `align` (optional): `left`, `center`, `right`, `justify` (default: `left`)
- `fontSize` (optional): `sm`, `base`, `lg`, `xl` (default: `base`)
- `className` (optional): Additional CSS classes

**Note:** Regular markdown text doesn't need the wrapper - use `[#text]` only for special styling.

---

### 7. Button Block
**Purpose:** Call-to-action button

**Syntax:**
```markdown
[#button url="/signup" variant="primary" size="lg" target="_blank"]
{Get Started Now}
[/#button]
```

**Attributes:**
- `url` (required): Link destination
- `variant` (optional): `primary`, `secondary`, `outline`, `ghost` (default: `primary`)
- `size` (optional): `sm`, `md`, `lg` (default: `md`)
- `target` (optional): `_blank`, `_self` (default: `_self`)
- `className` (optional): Additional CSS classes

---

### 8. Button Group
**Purpose:** Group multiple buttons together

**Syntax:**
```markdown
[#button-group align="center" gap="4"]
[#button url="/signup" variant="primary"]{Sign Up}[/#button]
[#button url="/login" variant="secondary"]{Log In}[/#button]
[/#button-group]
```

**Attributes:**
- `align` (optional): `left`, `center`, `right` (default: `left`)
- `gap` (optional): Tailwind spacing value (default: `4`)

---

### 9. Image Block
**Purpose:** Responsive image with caption

**Syntax:**
```markdown
[#image
  src="/images/hero.jpg"
  alt="Hero image description"
  caption="Photo by John Doe"
  width="full"
  rounded="lg"
  shadow="true"
]
[/#image]
```

**Attributes:**
- `src` (required): Image URL
- `alt` (required): Alt text for accessibility
- `caption` (optional): Image caption
- `width` (optional): `sm`, `md`, `lg`, `xl`, `full` (default: `full`)
- `rounded` (optional): `sm`, `md`, `lg`, `xl`, `full`, `none` (default: `md`)
- `shadow` (optional): `true` or `false` (default: `false`)
- `className` (optional): Additional CSS classes

**Shorthand:** Standard markdown images work too:
```markdown
![Alt text](/path/to/image.jpg)
```

---

### 10. Gallery Block
**Purpose:** Image grid gallery

**Syntax:**
```markdown
[#gallery columns="3" gap="4" rounded="lg"]
[#image src="/img1.jpg" alt="Image 1"][/#image]
[#image src="/img2.jpg" alt="Image 2"][/#image]
[#image src="/img3.jpg" alt="Image 3"][/#image]
[#image src="/img4.jpg" alt="Image 4"][/#image]
[/#gallery]
```

**Attributes:**
- `columns` (optional): `2`, `3`, `4`, `5`, `6` (default: `3`)
- `gap` (optional): Tailwind spacing value (default: `4`)
- `rounded` (optional): Border radius (default: `md`)
- `aspectRatio` (optional): `square`, `video`, `portrait` (default: `square`)

---

### 11. Video Block
**Purpose:** Embedded video player

**Syntax:**
```markdown
[#video
  src="https://youtube.com/watch?v=..."
  provider="youtube"
  title="Video title"
  aspectRatio="16:9"
]
[/#video]
```

**Alternative (direct embed):**
```markdown
[#video url="https://example.com/video.mp4" controls="true" autoplay="false"]
[/#video]
```

**Attributes:**
- `src` or `url` (required): Video URL
- `provider` (optional): `youtube`, `vimeo`, `direct` (auto-detected)
- `title` (optional): Video title
- `aspectRatio` (optional): `16:9`, `4:3`, `1:1` (default: `16:9`)
- `controls` (optional): `true` or `false` (default: `true`)
- `autoplay` (optional): `true` or `false` (default: `false`)

---

### 12. Quote Block
**Purpose:** Blockquote with attribution

**Syntax:**
```markdown
[#quote author="Albert Einstein" role="Physicist" cite="https://source.com"]
{
The important thing is not to stop questioning. Curiosity has its own reason for existing.
}
[/#quote]
```

**Attributes:**
- `author` (optional): Quote author
- `role` (optional): Author's role/title
- `cite` (optional): Source URL
- `className` (optional): Additional CSS classes

**Shorthand:** Standard markdown blockquotes work too:
```markdown
> This is a quote
> — Author Name
```

---

### 13. List Block
**Purpose:** Ordered or unordered lists

**Syntax:**
```markdown
[#list type="unordered" icon="✓" className="text-green-600"]
{
- First item
- Second item
- Third item
}
[/#list]
```

**Attributes:**
- `type` (optional): `ordered`, `unordered`, `checklist` (default: `unordered`)
- `icon` (optional): Custom bullet icon/emoji
- `className` (optional): Additional CSS classes

**Shorthand:** Standard markdown lists work:
```markdown
- Unordered item
- Another item

1. Ordered item
2. Another item
```

---

### 14. Separator / Divider
**Purpose:** Horizontal divider

**Syntax:**
```markdown
[#separator style="solid" thickness="2" color="gray-300" spacing="8" /]
```

**Attributes:**
- `style` (optional): `solid`, `dashed`, `dotted` (default: `solid`)
- `thickness` (optional): Line thickness in pixels (default: `1`)
- `color` (optional): Tailwind border color (default: `gray-200`)
- `spacing` (optional): Vertical spacing (default: `8`)

**Shorthand:** Standard markdown:
```markdown
---
```

---

### 15. Call-to-Action (CTA) Block
**Purpose:** Prominent call-to-action section

**Syntax:**
```markdown
[#cta
  title="Ready to Get Started?"
  description="Join thousands of users already using our platform"
  buttonText="Sign Up Free"
  buttonUrl="/signup"
  backgroundColor="bg-blue-600"
  textColor="text-white"
]
[/#cta]
```

**Attributes:**
- `title` (required): CTA heading
- `description` (optional): Supporting text
- `buttonText` (required): Button label
- `buttonUrl` (required): Button link
- `backgroundColor` (optional): Background color
- `textColor` (optional): Text color

---

### 16. FAQ / Accordion Block
**Purpose:** Collapsible FAQ sections

**Syntax:**
```markdown
[#faq title="Frequently Asked Questions"]
[#faq-item question="What is Contenta?"]
{
Contenta is a modern content management system built with Laravel and Vue.
}
[/#faq-item]

[#faq-item question="How much does it cost?"]
{
Contenta is open-source and free to use.
}
[/#faq-item]
[/#faq]
```

**Attributes (faq):**
- `title` (optional): Section title
- `openFirst` (optional): `true` or `false` - open first item by default

**Attributes (faq-item):**
- `question` (required): Question text

---

### 17. Stats Block
**Purpose:** Statistics display

**Syntax:**
```markdown
[#stats columns="4"]
[#stat value="10k+" label="Active Users"][/#stat]
[#stat value="99%" label="Uptime"][/#stat]
[#stat value="24/7" label="Support"][/#stat]
[#stat value="50+" label="Countries"][/#stat]
[/#stats]
```

**Attributes (stats):**
- `columns` (optional): `2`, `3`, `4` (default: `3`)
- `backgroundColor` (optional): Background color

**Attributes (stat):**
- `value` (required): Statistic value
- `label` (required): Statistic label
- `description` (optional): Additional description

---

### 18. Team Block
**Purpose:** Team member grid

**Syntax:**
```markdown
[#team title="Meet Our Team" columns="3"]
[#member
  name="John Doe"
  role="CEO & Founder"
  image="/team/john.jpg"
  bio="Passionate about building great products"
  linkedin="https://linkedin.com/in/johndoe"
  twitter="@johndoe"
]
[/#member]

[#member
  name="Jane Smith"
  role="CTO"
  image="/team/jane.jpg"
]
[/#member]
[/#team]
```

**Attributes (team):**
- `title` (optional): Section title
- `columns` (optional): `2`, `3`, `4` (default: `3`)

**Attributes (member):**
- `name` (required): Member name
- `role` (required): Member role
- `image` (optional): Photo URL
- `bio` (optional): Short bio
- `linkedin` (optional): LinkedIn URL
- `twitter` (optional): Twitter handle
- `email` (optional): Email address

---

### 19. Pricing Block
**Purpose:** Pricing table

**Syntax:**
```markdown
[#pricing title="Choose Your Plan" subtitle="Flexible pricing for teams of all sizes"]
[#plan
  name="Starter"
  price="$9"
  period="month"
  description="Perfect for individuals"
  highlighted="false"
]
{
- 10 GB Storage
- 5 Projects
- Email Support
- Basic Analytics
}
[/#plan]

[#plan
  name="Professional"
  price="$29"
  period="month"
  description="Best for small teams"
  highlighted="true"
]
{
- 100 GB Storage
- Unlimited Projects
- Priority Support
- Advanced Analytics
- Custom Integrations
}
[/#plan]
[/#pricing]
```

**Attributes (pricing):**
- `title` (optional): Section title
- `subtitle` (optional): Section subtitle

**Attributes (plan):**
- `name` (required): Plan name
- `price` (required): Price (e.g., "$29", "Free")
- `period` (optional): Billing period (e.g., "month", "year")
- `description` (optional): Plan description
- `highlighted` (optional): `true` or `false` - highlight this plan
- `buttonText` (optional): CTA button text (default: "Get Started")
- `buttonUrl` (optional): CTA button link

---

### 20. Contact Form Block
**Purpose:** Contact form

**Syntax:**
```markdown
[#contact-form
  title="Get in Touch"
  submitText="Send Message"
  successMessage="Thank you! We'll be in touch soon."
  recipientEmail="contact@example.com"
]
[/#contact-form]
```

**Attributes:**
- `title` (optional): Form title
- `submitText` (optional): Submit button text (default: "Submit")
- `successMessage` (optional): Success message
- `recipientEmail` (optional): Email recipient
- `fields` (optional): Comma-separated list: `name,email,phone,message` (default: all)

---

### 21. Newsletter Signup Block
**Purpose:** Newsletter subscription form

**Syntax:**
```markdown
[#newsletter
  title="Subscribe to Our Newsletter"
  description="Get the latest updates delivered to your inbox"
  placeholder="Enter your email"
  buttonText="Subscribe"
  provider="mailchimp"
  listId="abc123"
]
[/#newsletter]
```

**Attributes:**
- `title` (optional): Section title
- `description` (optional): Supporting text
- `placeholder` (optional): Email input placeholder
- `buttonText` (optional): Submit button text (default: "Subscribe")
- `provider` (optional): Email provider (mailchimp, sendgrid, etc.)
- `listId` (optional): Provider-specific list ID

---

### 22. Navigation Menu Block
**Purpose:** Navigation menu

**Syntax:**
```markdown
[#menu id="primary" orientation="horizontal"]
[#menu-item url="/" label="Home"][/#menu-item]
[#menu-item url="/about" label="About"][/#menu-item]
[#menu-item url="/contact" label="Contact"][/#menu-item]
[/#menu]
```

**Attributes (menu):**
- `id` (optional): Menu identifier
- `orientation` (optional): `horizontal`, `vertical` (default: `horizontal`)
- `className` (optional): Additional CSS classes

**Attributes (menu-item):**
- `url` (required): Link URL
- `label` (required): Link text
- `icon` (optional): Icon/emoji
- `target` (optional): `_blank`, `_self`

---

### 23. Breadcrumb Block
**Purpose:** Breadcrumb navigation

**Syntax:**
```markdown
[#breadcrumb separator=">"]
[#breadcrumb-item url="/" label="Home"][/#breadcrumb-item]
[#breadcrumb-item url="/blog" label="Blog"][/#breadcrumb-item]
[#breadcrumb-item label="Current Page"][/#breadcrumb-item]
[/#breadcrumb]
```

**Attributes (breadcrumb):**
- `separator` (optional): Separator character (default: `/`)

**Attributes (breadcrumb-item):**
- `url` (optional): Link URL (omit for current page)
- `label` (required): Text label

---

### 24. HTML Block
**Purpose:** Raw HTML content (use sparingly)

**Syntax:**
```markdown
[#html]
{
<div class="custom-component">
  <p>Raw HTML content</p>
</div>
}
[/#html]
```

**Security Note:** HTML will be sanitized unless explicitly trusted. Use only for trusted content.

---

### 25. Grid Layout
**Purpose:** Custom grid layouts (3×2, 4×2, etc.)

**Syntax:**
```markdown
[#grid columns="3" gap="6" responsive="true"]
[#grid-item]{Content 1}[/#grid-item]
[#grid-item]{Content 2}[/#grid-item]
[#grid-item]{Content 3}[/#grid-item]
[#grid-item]{Content 4}[/#grid-item]
[#grid-item]{Content 5}[/#grid-item]
[#grid-item]{Content 6}[/#grid-item]
[/#grid]
```

**Attributes:**
- `columns` (optional): Number of columns (default: `3`)
- `gap` (optional): Tailwind spacing (default: `6`)
- `responsive` (optional): `true` or `false` - stack on mobile

---

### 26. Testimonials Block
**Purpose:** Customer testimonials

**Syntax:**
```markdown
[#testimonials title="What Our Customers Say" columns="2"]
[#testimonial
  author="Sarah Johnson"
  role="CEO, TechCo"
  image="/testimonials/sarah.jpg"
  rating="5"
]
{
This product has transformed how we work. Highly recommended!
}
[/#testimonial]

[#testimonial
  author="Mike Chen"
  role="Developer"
  rating="5"
]
{
Best CMS I've ever used. Clean, fast, and powerful.
}
[/#testimonial]
[/#testimonials]
```

**Attributes (testimonials):**
- `title` (optional): Section title
- `columns` (optional): `1`, `2`, `3` (default: `2`)

**Attributes (testimonial):**
- `author` (required): Testimonial author
- `role` (optional): Author's role/company
- `image` (optional): Author photo
- `rating` (optional): Star rating (1-5)

---

### 27. Legal Text Block
**Purpose:** Legal documents with metadata

**Syntax:**
```markdown
[#legal-text
  documentType="Terms of Service"
  effectiveDate="2025-01-01"
  lastUpdated="2025-11-27"
  jurisdiction="United States"
]
{
## 1. Terms and Conditions

Your legal content here in markdown format...

## 2. Privacy Policy

More content...
}
[/#legal-text]
```

**Attributes:**
- `documentType` (required): Document type
- `effectiveDate` (optional): Effective date (YYYY-MM-DD)
- `lastUpdated` (optional): Last update date
- `jurisdiction` (optional): Legal jurisdiction
- `version` (optional): Document version

---

## Layout Templates

Pages can reference layout templates using front matter:

```markdown
---
layout: default
title: My Page Title
meta_description: Page description for SEO
---

[#hero title="Welcome"]
[/#hero]

Your content here...
```

**Available Layouts:**
- `default` - Standard single-column layout
- `full-width` - Full-width layout with no container
- `sidebar-left` - Two-column with left sidebar
- `sidebar-right` - Two-column with right sidebar

---

## Nesting Examples

Shortcuts can be nested arbitrarily deep:

```markdown
[#container width="xl" padding="12"]
  [#columns ratio="1:2"]
    [#column]
      [#heading level="2"]{Features}[/#heading]
      [#list type="checklist"]
      {
      - Feature 1
      - Feature 2
      }
      [/#list]
    [/#column]

    [#column]
      [#image src="/demo.jpg" alt="Demo"][/#image]
      [#text]
      {
      This is a nested example showing how shortcuts work together.
      }
      [/#text]
    [/#column]
  [/#columns]
[/#container]
```

---

## Escaping

To display shortcut syntax literally:

```markdown
\[#button url="/test"]{Don't render me}\[/#button]
```

Or use a code block:

````markdown
```
[#button url="/test"]{This won't be parsed}[/#button]
```
````

---

## Comments

Comments are ignored during parsing:

```markdown
[#!-- This is a comment --]

[#hero title="Welcome"]
[/#hero]

[#!-- TODO: Add more content here --]
```

---

## Error Handling

Invalid syntax will display a descriptive error message:

```markdown
[#button url="/test"]{Unclosed button
```

**Error:** "Unclosed shortcut tag: [#button] at line 1"

---

## Best Practices

1. **Use semantic shortcuts** - Choose the right block for your content
2. **Leverage markdown** - Don't wrap simple text in `[#text]` unless needed
3. **Keep it readable** - Use proper indentation for nested blocks
4. **Validate attributes** - Required attributes must be present
5. **Escape when needed** - Use `\` for literal bracket characters
6. **Comment complex sections** - Help future editors understand structure
7. **Test rendering** - Preview changes before publishing

---

## Migration Notes

When migrating from the old page builder:

- **hero-block** → `[#hero]`
- **two-column-block** → `[#columns]`
- **container-block** → `[#container]`
- **heading-block** → `[#heading]` or standard markdown `## Heading`
- **text-block** → `[#text]` or just write markdown
- **button-block** → `[#button]`
- **image-block** → `[#image]` or `![alt](url)`
- **gallery-block** → `[#gallery]`
- **video-block** → `[#video]`
- **quote-block** → `[#quote]` or `> Quote`
- **navigation-menu-block** → `[#menu]`
- **breadcrumb-block** → `[#breadcrumb]`
- **contact-form-block** → `[#contact-form]`
- **newsletter-block** → `[#newsletter]`
- **list-block** → `[#list]` or standard markdown lists

Additional blocks (rendered dynamically):
- **features** → `[#features]`
- **cta** → `[#cta]`
- **faq** → `[#faq]`
- **stats** → `[#stats]`
- **legal-text** → `[#legal-text]`
- **team** → `[#team]`
- **pricing** → `[#pricing]`

---

## Version History

- **v1.0** (2025-11-27) - Initial specification with 27 block types

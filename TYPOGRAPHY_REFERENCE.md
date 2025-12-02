# Typography Reference

This document shows all available typography styles in the Contenta project, implemented using Tailwind CSS v4.

**IMPORTANT:** All typography styles are scoped to the `.markdown-content` class. This means they only apply to frontend markdown content (pages and posts), not to the admin interface or site-wide elements.

## Headings

All headings have proper spacing, font weights, and dark mode support:

```html
<h1>Heading 1 - 36px/800 weight</h1>
<h2>Heading 2 - 30px/700 weight</h2>
<h3>Heading 3 - 24px/600 weight</h3>
<h4>Heading 4 - 20px/600 weight</h4>
<h5>Heading 5 - 18px/600 weight</h5>
<h6>Heading 6 - 16px/600 weight</h6>
```

## Paragraphs and Text

```html
<p>Regular paragraph text with 16px font size and 28px line height for optimal readability.</p>

<strong>Bold text</strong> or <b>bold text</b>

<em>Italic text</em> or <i>italic text</i>

<small>Small text for less important content</small>
```

## Links

Links have blue color with hover effects and underline decoration:

```html
<a href="#">This is a link</a>
```

- Light mode: Blue (hsl(217.2 91.2% 59.8%))
- Dark mode: Lighter blue (hsl(217.2 91.2% 69.8%))
- Hover effects included

## Code

### Inline Code

```html
<code>inline code</code>
```

Renders with monospace font, muted background, and proper padding.

### Code Blocks

```html
<pre><code>
function example() {
  return "code block";
}
</code></pre>
```

Full-width code blocks with syntax highlighting support and horizontal scrolling.

## Blockquotes

```html
<blockquote>
  This is a blockquote with left border and italic styling.
</blockquote>
```

## Lists

### Unordered Lists

```html
<ul>
  <li>First item</li>
  <li>Second item</li>
  <li>Third item
    <ul>
      <li>Nested item (circle)</li>
      <li>Another nested item
        <ul>
          <li>Deep nested (square)</li>
        </ul>
      </li>
    </ul>
  </li>
</ul>
```

### Ordered Lists

```html
<ol>
  <li>First item</li>
  <li>Second item</li>
  <li>Third item</li>
</ol>
```

### Definition Lists

```html
<dl>
  <dt>Term</dt>
  <dd>Definition of the term</dd>
  <dt>Another Term</dt>
  <dd>Another definition</dd>
</dl>
```

## Tables

```html
<table>
  <thead>
    <tr>
      <th>Header 1</th>
      <th>Header 2</th>
      <th>Header 3</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>Cell 1</td>
      <td>Cell 2</td>
      <td>Cell 3</td>
    </tr>
    <tr>
      <td>Cell 4</td>
      <td>Cell 5</td>
      <td>Cell 6</td>
    </tr>
  </tbody>
</table>
```

## Images

```html
<img src="/path/to/image.jpg" alt="Description">
```

Images are responsive (max-width: 100%) with automatic height, display block, and rounded corners.

## Special Elements

### Keyboard Shortcuts

```html
Press <kbd>Ctrl</kbd> + <kbd>S</kbd> to save
```

### Highlighted Text

```html
<mark>This text is highlighted</mark>
```

### Deleted and Inserted Text

```html
<del>This text was deleted</del>
<ins>This text was inserted</ins>
```

### Subscript and Superscript

```html
H<sub>2</sub>O
E = mc<sup>2</sup>
```

### Abbreviations

```html
<abbr title="HyperText Markup Language">HTML</abbr>
```

## Horizontal Rule

```html
<hr>
```

## Figures with Captions

```html
<figure>
  <img src="/path/to/image.jpg" alt="Description">
  <figcaption>This is a caption for the image</figcaption>
</figure>
```

## Address

```html
<address>
  123 Main Street<br>
  City, State 12345<br>
  <a href="mailto:email@example.com">email@example.com</a>
</address>
```

## Prose Utility Classes

Use these classes to constrain content width for better readability:

```html
<!-- Standard prose (65 characters wide) -->
<div class="prose">
  <!-- Your content here -->
</div>

<!-- Small prose (50 characters wide, smaller font) -->
<div class="prose-sm">
  <!-- Your content here -->
</div>

<!-- Large prose (75 characters wide, larger font) -->
<div class="prose-lg">
  <!-- Your content here -->
</div>

<!-- Extra large prose (85 characters wide, larger font) -->
<div class="prose-xl">
  <!-- Your content here -->
</div>

<!-- Full width prose (no max-width constraint) -->
<div class="prose-full">
  <!-- Your content here -->
</div>
```

## Dark Mode Support

All typography elements automatically adapt to dark mode when the `.dark` class is present on a parent element:

- Background colors use `var(--muted)` and `var(--background)`
- Text colors use `var(--foreground)` and `var(--muted-foreground)`
- Borders use `var(--border)`
- Links have lighter blue in dark mode
- Highlighted text uses adjusted colors

## Usage in Markdown Content

**All typography styles require the `.markdown-content` class** to be applied. This ensures the styles only affect markdown content and don't interfere with the admin interface.

```vue
<template>
  <!-- Apply markdown-content class to enable typography styles -->
  <div class="markdown-content" v-html="markdownContent"></div>
</template>
```

You can combine with prose utility classes for width constraints:

```vue
<template>
  <!-- Typography styles + width constraint -->
  <div class="markdown-content prose">
    <div v-html="markdownContent"></div>
  </div>
</template>
```

**Where it's automatically applied:**
- `Page.vue`: Frontend page rendering (line 90)
- `EditorJSWrapper.vue`: Visual markdown editor (line 2)

**Where to add it:**
- Any custom components that render markdown content
- Post detail views
- Custom markdown content areas

## Customization

All typography styles are defined in `resources/css/app.css` using CSS custom properties (CSS variables) from the Tailwind theme. To customize:

1. Modify the values in `app.css` under the `@layer base` section
2. Adjust the CSS custom properties in `:root` or `.dark` for color changes
3. Use Tailwind's `@theme` directive to add new custom properties

## Implementation Details

- Uses Tailwind CSS v4's `@layer base` for global typography
- All styles use CSS custom properties for theming
- No `@apply` directives (raw CSS for v4 compatibility)
- Fully responsive and accessible
- Optimized for readability with proper line heights and spacing

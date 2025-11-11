# Contenta - Complete User Manual & Documentation

[← Back: Avatar Upload](AVATAR_UPLOAD.md) • [Read next: Releases & Forking →](RELEASES.md)

> **Version:** 1.0  
> **Last Updated:** November 11, 2025  
> **Framework:** Laravel 12 + Vue 3 + Inertia.js

---

## Table of Contents

1. [Introduction](#1-introduction)
2. [System Requirements](#2-system-requirements)
3. [Installation & Setup](#3-installation--setup)
4. [Architecture Overview](#4-architecture-overview)
5. [User Guide](#5-user-guide)
6. [Admin Guide](#6-admin-guide)
7. [Content Management](#7-content-management)
8. [Page Builder](#8-page-builder)
9. [Navigation Management](#9-navigation-management)
10. [Media Management](#10-media-management)
11. [User Management & Security](#11-user-management--security)
12. [Settings & Configuration](#12-settings--configuration)
13. [Plugin System](#13-plugin-system)
14. [API & Development](#14-api--development)
15. [Deployment Guide](#15-deployment-guide)
16. [Troubleshooting](#16-troubleshooting)
17. [Advanced Topics](#17-advanced-topics)

---

## 1. Introduction

### 1.1 What is Contenta?

Contenta is a modern, modular content management system built with Laravel 12 and Vue 3. It's designed for content-centric platforms including:

- Content Management Systems (CMS)
- Knowledge bases
- Publishing platforms
- Blogs and magazines
- Corporate websites

### 1.2 Key Features

**Content Management**
- ✅ Advanced post management with markdown/HTML support
- ✅ Page builder with drag-and-drop blocks
- ✅ Hierarchical categories and tags
- ✅ Comments system with moderation
- ✅ Media library with collections
- ✅ SEO optimization tools

**Security & Authentication**
- ✅ Two-factor authentication (TOTP)
- ✅ WebAuthn support (security keys, biometrics)
- ✅ API token management
- ✅ Role-based access control
- ✅ Activity logging

**Modern Development**
- ✅ Domain-driven architecture
- ✅ TypeScript + Vue 3 composition API
- ✅ Inertia.js for seamless SPA experience
- ✅ Tailwind CSS v4
- ✅ Comprehensive testing suite

### 1.3 Technology Stack

| Layer | Technology |
|-------|-----------|
| **Backend** | Laravel 12, PHP 8.4 |
| **Frontend** | Vue 3, TypeScript, Inertia.js |
| **Styling** | Tailwind CSS v4 |
| **Database** | SQLite (default), MySQL/PostgreSQL |
| **Queue** | Laravel Horizon, Database driver |
| **Media** | Spatie Media Library |
| **Permissions** | Spatie Laravel Permission |
| **Testing** | Pest (backend), Playwright (E2E) |

---

## 2. System Requirements

### 2.1 Minimum Requirements

- **PHP:** 8.4 or higher
- **Node.js:** 20.x or higher
- **Composer:** 2.x
- **Database:** SQLite 3.x, MySQL 8.0+, or PostgreSQL 13+
- **Memory:** 512MB minimum (2GB recommended)
- **Disk Space:** 500MB minimum

### 2.2 Development Tools

- Git
- npm or pnpm
- A modern code editor (VS Code, PHPStorm)

### 2.3 Production Requirements

- Web server: Apache 2.4+ or Nginx 1.18+
- SSL certificate (required for WebAuthn)
- Redis (recommended for caching and queues)
- Process supervisor (Supervisor, systemd)

---

## 3. Installation & Setup

### 3.1 Quick Start (Development)

#### Step 1: Clone and Install Dependencies

```bash
# Clone the repository
git clone <repository-url> contenta
cd contenta

# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install
```

#### Step 2: Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

#### Step 3: Configure Database

**For SQLite (default):**
```bash
# Database file is created automatically
touch database/database.sqlite
```

**For MySQL:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=contenta
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

#### Step 4: Run Migrations

```bash
php artisan migrate
```

#### Step 5: Seed Data (Optional)

```bash
php artisan db:seed
```

#### Step 6: Start Development Server

```bash
# Start all services (PHP server, queue worker, logs, Vite)
composer dev
```

This command starts:
- Laravel development server on `http://localhost:8000`
- Queue worker for background jobs
- Log viewer (Pail)
- Vite dev server for hot module replacement

**Alternative:** Start services individually:

```bash
# Terminal 1: Laravel server
php artisan serve

# Terminal 2: Vite dev server
npm run dev

# Terminal 3: Queue worker
php artisan queue:work

# Terminal 4: Logs
php artisan pail
```

### 3.2 Docker Setup

Contenta includes Docker support via Laravel Sail:

```bash
# Start Docker containers
./vendor/bin/sail up -d

# Run migrations
./vendor/bin/sail artisan migrate

# Access the application
# Open http://localhost in your browser
```

Services included:
- **MySQL 8.0** - Primary database
- **Redis** - Caching and queue driver
- **MinIO** - S3-compatible object storage
- **Mailpit** - Email testing

### 3.3 Initial Account Setup

After installation, you can register your first user:

1. Visit `http://localhost:8000/register`
2. Fill in the registration form
3. Verify your email (check Mailpit at `http://localhost:8025`)
4. Access the admin dashboard at `/admin/dashboard`

### 3.4 File Permissions

Ensure the following directories are writable:

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

---

## 4. Architecture Overview

### 4.1 Domain-Driven Design

Contenta uses a domain-driven architecture with bounded contexts:

```
app/Domains/
├── ContentManagement/    # Posts, pages, categories, tags, comments
├── Media/               # File uploads and media management
├── Navigation/          # Menu system
├── PageBuilder/         # Visual page builder
├── Plugins/            # Plugin management
├── Security/           # Authentication, authorization
└── Settings/           # Application configuration
```

### 4.2 Directory Structure

```
contenta/
├── app/
│   ├── Domains/              # Domain logic
│   ├── Http/                 # Shared HTTP layer
│   ├── Models/               # Shared models (User)
│   └── Providers/            # Service providers
├── bootstrap/                # Framework bootstrap
├── config/                   # Configuration files
├── database/
│   ├── migrations/           # Database migrations
│   ├── factories/            # Model factories
│   └── seeders/             # Database seeders
├── public/                   # Public assets
├── resources/
│   ├── css/                  # Stylesheets
│   ├── js/                   # Vue application
│   │   ├── components/       # Vue components
│   │   ├── layouts/          # Page layouts
│   │   ├── pages/            # Inertia pages
│   │   ├── stores/           # State management
│   │   └── types/            # TypeScript definitions
│   └── views/                # Blade templates
├── routes/                   # Route definitions
├── storage/                  # Application storage
├── tests/                    # Test suites
└── vendor/                   # Composer dependencies
```

### 4.3 Request Lifecycle

1. **Request** → Web server → `public/index.php`
2. **Routing** → Route matched in `routes/*.php`
3. **Middleware** → Authentication, CSRF, etc.
4. **Controller** → Business logic in domain controllers
5. **Service Layer** → Optional service abstraction
6. **Model** → Database interaction
7. **Response** → Inertia response with Vue page + props
8. **Frontend** → Vue renders the page component

### 4.4 Frontend Architecture

**Inertia.js Pattern:**
- Server renders Vue components
- No separate API layer needed
- Automatic form validation
- Client-side navigation with server-side routing

**State Management:**
- Pinia stores for global state
- Composables for reusable logic
- Props for page-specific data

---

## 5. User Guide

### 5.1 Registration & Login

#### Creating an Account

1. Navigate to `/register`
2. Provide:
   - Username (unique)
   - Email address
   - Password (min 8 characters)
3. Click "Register"
4. Check your email for verification link
5. Click the verification link

#### Logging In

1. Navigate to `/login`
2. Enter email and password
3. Click "Log In"
4. If 2FA is enabled, enter your 6-digit code

#### Password Recovery

1. Click "Forgot Password?" on login page
2. Enter your email address
3. Check email for reset link
4. Create a new password

### 5.2 User Dashboard

After logging in, you'll see your personalized dashboard with:

- Recent activity
- Quick links to common actions
- System notifications
- Content statistics

### 5.3 Profile Management

Access your profile settings at `/user/settings/profile`:

**Profile Information**
- Update name and email
- Upload profile picture
- Set bio and contact details

**Security Settings**
- Change password
- Enable two-factor authentication
- Manage API tokens
- View login history

### 5.4 Two-Factor Authentication

#### Enabling 2FA

1. Go to **Settings** → **Security** → **Two-Factor Authentication**
2. Click "Enable 2FA"
3. Scan QR code with authenticator app (e.g., Google Authenticator, Authy)
4. Enter verification code
5. **Save recovery codes** in a secure location

#### Using Recovery Codes

If you lose access to your authenticator:
1. Click "Use Recovery Code" on login page
2. Enter one recovery code
3. That code is now used and cannot be reused

#### Regenerating Recovery Codes

1. Go to 2FA settings
2. Click "Regenerate Recovery Codes"
3. Confirm via email
4. Save new codes securely

### 5.5 WebAuthn (Security Keys)

#### Registering a Security Key

1. Go to **Settings** → **Security** → **WebAuthn**
2. Click "Add Security Key"
3. Insert your physical key or use biometric
4. Name your device (e.g., "YubiKey 5")

Supported authenticators:
- Hardware keys (YubiKey, Titan, etc.)
- Platform authenticators (Touch ID, Face ID, Windows Hello)

---

## 6. Admin Guide

### 6.1 Admin Dashboard

Access: `/admin/dashboard`

The admin dashboard provides:
- Content overview statistics
- Recent posts and pages
- User activity
- System health indicators

### 6.2 Navigation Structure

**Admin Menu:**
```
Dashboard
├── Content
│   ├── Posts
│   ├── Categories
│   ├── Tags
│   └── Comments
├── Page Builder
│   ├── Pages
│   ├── Layouts
│   └── Blocks
├── Navigation
│   └── Menus
├── Media
├── Users & Roles
├── Settings
│   ├── Site Settings
│   ├── Security
│   └── Themes
└── Plugins
```

### 6.3 User Roles & Permissions

**Default Roles:**
- **Super Admin** - Full system access
- **Admin** - Content and user management
- **Editor** - Create and edit content
- **Author** - Create own content
- **Contributor** - Submit content for review

**Managing Roles:**
1. Go to **Admin** → **Settings** → **Users**
2. Select a user
3. Assign roles and permissions

---

## 7. Content Management

### 7.1 Posts

#### Creating a Post

1. Navigate to **Admin** → **Content** → **Posts**
2. Click "Create New Post"
3. Fill in the form:
   - **Title:** Post headline
   - **Slug:** URL-friendly identifier (auto-generated)
   - **Content:** Markdown or HTML editor
   - **Excerpt:** Brief summary
   - **Featured Image:** Main post image
   - **Categories:** Assign categories
   - **Tags:** Add tags
   - **Status:** Draft, Scheduled, Published, Archived
   - **Published At:** Schedule publication

#### Post Fields

**Basic Information:**
- Title
- Slug (URL path)
- Content (Markdown/HTML)
- Excerpt
- Author

**Taxonomy:**
- Categories (hierarchical)
- Tags (flat)

**Media:**
- Featured image
- Gallery images
- Attachments

**SEO & Metadata:**
- Meta title
- Meta description
- Meta keywords
- Open Graph data (Facebook/LinkedIn)
- Twitter Card data
- Structured data (JSON-LD)

**Publishing:**
- Status: Draft, Scheduled, Published, Archived
- Published date/time
- Visibility settings

**Advanced:**
- Custom fields (JSON)
- Template override
- Table of contents (auto-generated)
- Reading time (calculated)
- View count
- Comment settings

#### Post Status Workflow

```
Draft → Review → Scheduled → Published → Archived
   ↓                              ↓
Trash ←────────────────────────── Trash
```

#### Calendar View

Access the post calendar at **Posts** → **Calendar**:
- Visual timeline of scheduled posts
- Drag-and-drop rescheduling
- Filter by status and category

### 7.2 Categories

#### Creating Categories

1. Go to **Content** → **Categories**
2. Click "Create Category"
3. Configure:
   - **Name:** Display name
   - **Slug:** URL segment
   - **Parent:** For hierarchical structure
   - **Description:** Optional description
   - **Featured:** Highlight on frontend
   - **Order:** Display order
   - **Meta:** SEO fields

#### Hierarchical Structure

Categories support unlimited nesting:

```
Technology
├── Web Development
│   ├── Frontend
│   └── Backend
└── Mobile Development
    ├── iOS
    └── Android
```

**Category Properties:**
- `full_path` - Complete hierarchical path
- `depth` - Nesting level
- `getAllChildren()` - Recursive child retrieval

### 7.3 Tags

Tags are flat, non-hierarchical labels:

1. Go to **Content** → **Tags**
2. Create tags as needed
3. Assign to posts

**Tag Features:**
- Auto-complete when typing
- Post count tracking
- Slug generation
- Meta description for tag pages

### 7.4 Comments

#### Comment Moderation

Access: **Content** → **Comments**

**Actions:**
- Approve pending comments
- Mark as spam
- Delete comments
- Reply to comments
- Bulk actions

**Comment Status:**
- **Pending:** Awaiting moderation
- **Approved:** Visible on site
- **Spam:** Flagged as spam
- **Trash:** Soft deleted

#### Comment Settings

Configure in **Settings** → **Site**:
- Enable/disable comments globally
- Require approval for first comment
- Close comments after X days
- Enable nested replies
- Maximum nesting depth

---

## 8. Page Builder

### 8.1 Overview

The Page Builder provides a visual, drag-and-drop interface for creating pages without coding.

### 8.2 Creating Pages

1. Navigate to **Page Builder** → **Pages**
2. Click "Create Page"
3. Choose a layout
4. Add blocks by dragging from sidebar
5. Configure each block
6. Preview and publish

### 8.3 Layouts

**Layout Structure:**
```
Layout
├── Header region
├── Main content region
├── Sidebar region (optional)
└── Footer region
```

**Creating Custom Layouts:**
1. Go to **Page Builder** → **Layouts**
2. Define regions and constraints
3. Set default block configurations

### 8.4 Blocks

**Built-in Block Types:**

| Block | Description |
|-------|-------------|
| **Text** | Rich text editor |
| **Heading** | H1-H6 headings |
| **Image** | Single image with caption |
| **Gallery** | Image grid/slider |
| **Video** | Embedded video |
| **Button** | Call-to-action button |
| **Spacer** | Vertical spacing |
| **Divider** | Horizontal line |
| **Columns** | Multi-column layout |
| **HTML** | Custom HTML code |
| **Form** | Contact/custom forms |

**Block Configuration:**
Each block has:
- Content settings
- Style options (colors, spacing, etc.)
- Visibility rules
- Animation settings

### 8.5 Page Settings

**Page Metadata:**
- Title and slug
- Meta description
- Schema.org structured data
- Status (draft/published/archived)

**Page Revisions:**
- Auto-save drafts
- Manual revision creation
- Restore previous versions
- Revision comparison

**Publishing:**
- Publish immediately
- Schedule for future
- Unpublish anytime
- Duplicate page

### 8.6 Creating Custom Blocks

Developers can create custom blocks:

1. Create block component in `app/Domains/PageBuilder/Resources/Blocks/`
2. Define configuration schema
3. Register in `BlockController`
4. Frontend component in `resources/js/components/blocks/`

---

## 9. Navigation Management

### 9.1 Menu System

Create and manage navigation menus for your site.

### 9.2 Creating Menus

1. Go to **Navigation** → **Menus**
2. Click "Create Menu"
3. Configure:
   - **Name:** Display name
   - **Slug:** Unique identifier
   - **Location:** Where menu appears (header, footer, etc.)
   - **Settings:** Additional options

### 9.3 Menu Items

**Item Types:**
- Custom links
- Posts
- Pages
- Categories
- Tags
- External URLs

**Adding Items:**
1. Select menu to edit
2. Choose item type
3. Configure:
   - Label
   - URL/target
   - Open in new tab
   - CSS classes
   - Icon (optional)
4. Drag to reorder
5. Nest for dropdowns

**Menu Structure Example:**
```
Home
About
Services ▼
├── Web Design
├── Development
└── Consulting
Blog
Contact
```

### 9.4 Menu Actions

**Bulk Operations:**
- Add multiple items from posts/pages
- Reorder via drag-and-drop
- Bulk delete

**Advanced:**
- Duplicate menu
- Export/import menu structure (JSON)
- Conditional visibility rules

---

## 10. Media Management

### 10.1 Media Library

Access: **Admin** → **Media**

The media library manages all uploaded files.

### 10.2 Uploading Files

**Methods:**
1. **Drag & Drop:** Drag files into the media library
2. **File Browser:** Click "Upload" and select files
3. **Direct Upload:** Via post/page editor

**Supported Formats:**
- Images: JPG, PNG, GIF, WebP, SVG
- Documents: PDF, DOC, DOCX, XLS, XLSX
- Videos: MP4, WebM, OGG
- Audio: MP3, WAV, OGG

**File Constraints:**
- Maximum size: 10MB (configurable)
- Automatic resizing for large images
- WebP conversion (optional)

### 10.3 Media Collections

Media is organized into collections:

**Post Collections:**
- `featured_images` - Featured/hero images
- `gallery` - Image galleries
- `attachments` - Downloadable files

**User Collections:**
- `avatar` - Profile pictures
- `cover` - Cover images

**General Collections:**
- `general` - Uncategorized uploads

### 10.4 Image Processing

**Automatic Processing:**
- Thumbnail generation (150x150)
- Medium size (300x300)
- Large size (1024x1024)
- WebP conversion for web

**Manual Editing:**
- Crop and resize
- Rotate and flip
- Apply filters

### 10.5 Media Details

Click on any media item to view:
- File name and type
- Dimensions (images)
- File size
- Upload date
- Used in (posts/pages)
- Public URL
- Alt text (images)
- Title and caption

---

## 11. User Management & Security

### 11.1 User Management

Access: **Admin** → **Settings** → **Users**

### 11.2 User Listing

**View Options:**
- List view with filters
- Search by name, email, username
- Filter by role
- Sort by registration date, last login

**User Information:**
- Avatar
- Name and username
- Email (with verification status)
- Roles
- Registration date
- Last login

### 11.3 User Actions

**Individual Actions:**
- Edit user profile
- Reset password
- Change roles
- Enable/disable account
- Delete user

**Bulk Actions:**
- Assign roles to multiple users
- Delete multiple users
- Export user list

### 11.4 Security Features

#### API Tokens

Users can create personal access tokens for API access:

1. Go to **Settings** → **API Tokens**
2. Click "Create Token"
3. Name the token
4. Select abilities:
   - Read
   - Write
   - Delete
   - Full access
5. Copy token (shown only once)

**Token Management:**
- View all tokens
- Last used timestamp
- Revoke tokens
- Maximum 10 tokens per user

#### Login Attempts

Track and monitor failed login attempts:
- IP address tracking
- Automatic throttling
- Account lockout after X attempts
- Email notifications

#### Activity Logging

All important actions are logged:
- User logins/logouts
- Content creation/editing
- Permission changes
- Settings modifications

Access logs at **Settings** → **Security** → **Activity Log**

### 11.5 Roles & Permissions

#### Managing Roles

1. Go to **Settings** → **Roles**
2. Create or edit role
3. Assign permissions

**Permission Categories:**
- Content (create, edit, delete posts/pages)
- Users (view, create, edit, delete)
- Media (upload, delete)
- Settings (view, edit)
- Plugins (install, activate)

#### Creating Custom Roles

```php
// Example: Create "Moderator" role
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

$role = Role::create(['name' => 'moderator']);

$permissions = [
    'view posts',
    'edit posts',
    'moderate comments',
];

$role->givePermissionTo($permissions);
```

---

## 12. Settings & Configuration

### 12.1 Site Settings

Access: **Admin** → **Settings** → **Site**

**General Settings:**
- Site name
- Tagline
- Logo
- Favicon
- Contact email
- Timezone
- Date format
- Language

**Content Settings:**
- Posts per page
- Excerpt length
- Default post status
- Comment settings
- Search indexing

**Media Settings:**
- Max upload size
- Image quality
- Thumbnail sizes
- Storage driver (local/S3)

### 12.2 Security Settings

**Authentication:**
- Registration enabled/disabled
- Email verification required
- Password requirements
- Session timeout
- Remember me duration

**Two-Factor Authentication:**
- Enforce 2FA for admins
- Recovery code count
- QR code provider

**API Security:**
- Rate limiting
- Allowed origins (CORS)
- Token expiration

### 12.3 Theme Settings

**Appearance:**
- Active theme
- Color scheme
- Typography
- Custom CSS

**Layout:**
- Sidebar position
- Footer widgets
- Header style

### 12.4 Email Settings

**SMTP Configuration:**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yoursite.com
MAIL_FROM_NAME="Your Site Name"
```

**Email Templates:**
- Welcome email
- Password reset
- Email verification
- 2FA enabled/disabled

### 12.5 Cache Settings

**Cache Drivers:**
- File (default)
- Database
- Redis (recommended)
- Memcached

**Cache Management:**
```bash
# Clear all caches
php artisan cache:clear

# Clear specific caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## 13. Plugin System

### 13.1 Overview

Contenta includes a plugin system for extending functionality.

### 13.2 Installing Plugins

**Method 1: Upload**
1. Go to **Admin** → **Plugins**
2. Click "Upload Plugin"
3. Select plugin ZIP file
4. Click "Install"

**Method 2: Discovery**
1. Click "Discover Plugins"
2. System scans plugin directory
3. Newly found plugins appear

### 13.3 Plugin Management

**Actions:**
- **Enable:** Activate plugin
- **Disable:** Deactivate plugin
- **Uninstall:** Remove plugin completely
- **Scan:** Check plugin health

**Plugin Information:**
- Name and description
- Version
- Author
- Requirements
- Status (enabled/disabled)

### 13.4 Plugin Structure

```
plugins/
└── your-plugin/
    ├── plugin.json          # Plugin metadata
    ├── PluginServiceProvider.php
    ├── routes/
    │   └── web.php
    ├── resources/
    │   └── views/
    └── src/
        ├── Controllers/
        ├── Models/
        └── Services/
```

**plugin.json Example:**
```json
{
  "name": "Your Plugin",
  "slug": "your-plugin",
  "description": "Plugin description",
  "version": "1.0.0",
  "author": "Your Name",
  "requires": {
    "php": ">=8.4",
    "laravel": ">=12.0"
  },
  "type": "feature"
}
```

### 13.5 Plugin Types

- **feature:** Add new functionality
- **theme:** Visual themes
- **integration:** Third-party integrations
- **utility:** Helper tools

---

## 14. API & Development

### 14.1 API Authentication

Contenta uses Laravel Sanctum for API authentication.

**Creating a Token:**
```bash
# Via UI: Settings → API Tokens
# Token format: 1|AbCdEf123456...
```

**Using the Token:**
```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
     https://yoursite.com/api/posts
```

### 14.2 API Endpoints

**Posts API:**
```
GET    /api/posts              # List posts
GET    /api/posts/{id}         # Show post
POST   /api/posts              # Create post
PUT    /api/posts/{id}         # Update post
DELETE /api/posts/{id}         # Delete post
```

**Categories API:**
```
GET    /api/categories         # List categories
GET    /api/categories/{id}    # Show category
POST   /api/categories         # Create category
PUT    /api/categories/{id}    # Update category
DELETE /api/categories/{id}    # Delete category
```

**Tags API:**
```
GET    /api/tags               # List tags
GET    /api/tags/{id}          # Show tag
POST   /api/tags               # Create tag
PUT    /api/tags/{id}          # Update tag
DELETE /api/tags/{id}          # Delete tag
```

### 14.3 API Response Format

**Success Response:**
```json
{
  "data": {
    "id": 1,
    "title": "Post Title",
    "slug": "post-title",
    "status": "published"
  }
}
```

**Error Response:**
```json
{
  "message": "Validation error",
  "errors": {
    "title": ["The title field is required."]
  }
}
```

### 14.4 Development Commands

**Artisan Commands:**
```bash
# Generate controller
php artisan make:controller PostController

# Generate model with migration
php artisan make:model Post -m

# Generate service class
php artisan make:service PostService

# Clear all caches
php artisan optimize:clear

# Run tests
php artisan test
```

**Composer Scripts:**
```bash
# Run development environment
composer dev

# Run with SSR
composer dev:ssr

# Run tests
composer test

# Code analysis
composer analyse
```

**NPM Scripts:**
```bash
# Development server
npm run dev

# Build for production
npm run build

# Build with SSR
npm run build:ssr

# Lint code
npm run lint

# Format code
npm run format

# E2E tests
npm run test:e2e
```

### 14.5 Database Queries

**Eloquent Examples:**

```php
// Get published posts
$posts = Post::where('status', 'published')
    ->with(['author', 'categories', 'tags'])
    ->latest('published_at')
    ->paginate(15);

// Get category tree
$categories = Category::whereNull('parent_id')
    ->with('children')
    ->orderBy('order')
    ->get();

// Create post
$post = Post::create([
    'title' => 'My Post',
    'content_markdown' => '# Hello World',
    'status' => 'draft',
    'author_id' => auth()->id(),
]);

// Attach categories
$post->categories()->attach([1, 2, 3]);
```

### 14.6 Testing

**Running Tests:**
```bash
# All tests
php artisan test

# Specific test file
php artisan test tests/Feature/PostTest.php

# With coverage
php artisan test --coverage

# Parallel execution
php artisan test --parallel
```

**Test Example:**
```php
use function Pest\Laravel\{actingAs, get, post};

test('user can create post', function () {
    $user = User::factory()->create();
    
    actingAs($user)
        ->post('/admin/posts', [
            'title' => 'Test Post',
            'content_markdown' => 'Content',
            'status' => 'draft',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');
    
    expect(Post::where('title', 'Test Post')->exists())
        ->toBeTrue();
});
```

**E2E Tests:**
```bash
# Install Playwright
npx playwright install --with-deps

# Run tests
npm run test:e2e

# Run with UI
npx playwright test --ui
```

---

## 15. Deployment Guide

### 15.1 Pre-Deployment Checklist

- [ ] Set `APP_ENV=production` in `.env`
- [ ] Set `APP_DEBUG=false`
- [ ] Configure production database
- [ ] Set up email provider
- [ ] Configure file storage (S3, etc.)
- [ ] Set secure `APP_KEY`
- [ ] Configure Redis for caching/queues
- [ ] Set up SSL certificate
- [ ] Configure domain and DNS

### 15.2 Build Process

```bash
# Install dependencies
composer install --optimize-autoloader --no-dev

# Clear and cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Build frontend assets
npm run build

# Build with SSR (optional)
npm run build:ssr

# Run migrations
php artisan migrate --force

# Create storage link
php artisan storage:link
```

### 15.3 Web Server Configuration

#### Nginx Configuration

```nginx
server {
    listen 80;
    server_name yoursite.com;
    root /var/www/contenta/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

#### Apache Configuration

```apache
<VirtualHost *:80>
    ServerName yoursite.com
    DocumentRoot /var/www/contenta/public

    <Directory /var/www/contenta/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### 15.4 Queue Worker

**Using Supervisor:**

Create `/etc/supervisor/conf.d/contenta-worker.conf`:

```ini
[program:contenta-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/contenta/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/contenta/storage/logs/worker.log
stopwaitsecs=3600
```

Start worker:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start contenta-worker:*
```

### 15.5 Scheduled Tasks

Add to crontab:
```bash
* * * * * cd /var/www/contenta && php artisan schedule:run >> /dev/null 2>&1
```

### 15.6 Environment Variables

**Production .env:**
```env
APP_NAME="Your Site"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yoursite.com

DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_DATABASE=your-db-name
DB_USERNAME=your-db-user
DB_PASSWORD=your-secure-password

REDIS_HOST=your-redis-host
REDIS_PASSWORD=your-redis-password

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls

FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your-access-key
AWS_SECRET_ACCESS_KEY=your-secret-key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket-name
```

### 15.7 SSL Certificate

**Using Let's Encrypt:**
```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx

# Obtain certificate
sudo certbot --nginx -d yoursite.com -d www.yoursite.com

# Auto-renewal is configured automatically
```

### 15.8 Performance Optimization

**Enable OPcache:**
```ini
; php.ini
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
```

**Redis Configuration:**
- Use Redis for cache, sessions, and queues
- Configure Redis persistence
- Set maxmemory policy

**CDN Integration:**
- Serve static assets from CDN
- Configure asset URL in .env
- Enable browser caching

---

## 16. Troubleshooting

### 16.1 Common Issues

#### "500 Internal Server Error"

**Solutions:**
```bash
# Check error logs
tail -f storage/logs/laravel.log

# Ensure proper permissions
chmod -R 775 storage bootstrap/cache

# Clear caches
php artisan optimize:clear

# Check .env file exists
cp .env.example .env
php artisan key:generate
```

#### "419 Page Expired" (CSRF)

**Causes:**
- Session expired
- Cookie issues
- Time zone mismatch

**Solutions:**
```bash
# Clear sessions
php artisan session:flush

# Verify session driver
# Check SESSION_DRIVER in .env

# Ensure APP_URL matches your domain
```

#### Database Connection Error

**Solutions:**
```bash
# Test connection
php artisan db:show

# Verify credentials in .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Restart database service
sudo systemctl restart mysql
```

#### Assets Not Loading

**Solutions:**
```bash
# Rebuild assets
npm run build

# Clear browser cache
# Check public/build directory exists

# Verify APP_URL in .env
APP_URL=http://localhost:8000
```

#### Queue Jobs Not Processing

**Solutions:**
```bash
# Check queue worker is running
php artisan queue:work

# Clear failed jobs
php artisan queue:flush

# Restart queue worker
php artisan queue:restart
```

### 16.2 Debug Mode

**Enable debug mode temporarily:**
```env
APP_DEBUG=true
APP_ENV=local
```

**Laravel Telescope (Development):**
```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

### 16.3 Performance Issues

**Identify slow queries:**
```bash
# Enable query log
DB_LOG_QUERIES=true

# Check logs
tail -f storage/logs/laravel.log | grep "Query"
```

**Clear all caches:**
```bash
php artisan optimize:clear
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
composer dump-autoload
```

### 16.4 Getting Help

**Resources:**
- Check documentation
- Search GitHub issues
- Laravel documentation: https://laravel.com/docs
- Vue 3 documentation: https://vuejs.org
- Inertia.js documentation: https://inertiajs.com

---

## 17. Advanced Topics

### 17.1 Custom Domain Objects

Create custom domain aggregates:

```php
namespace App\Domains\YourDomain\Aggregates;

class YourAggregate
{
    public function __construct(
        public int $id,
        public string $name,
        // ... properties
    ) {}
    
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
```

### 17.2 Event Sourcing

Implement domain events:

```php
namespace App\Domains\YourDomain\Events;

class SomethingHappened
{
    public function __construct(
        public int $entityId,
        public array $data
    ) {}
}
```

### 17.3 Custom Service Providers

Create domain-specific providers:

```php
namespace App\Domains\YourDomain;

use Illuminate\Support\ServiceProvider;

class YourDomainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            YourServiceContract::class,
            YourService::class
        );
    }
    
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');
    }
}
```

Register in `config/app.php`:
```php
'providers' => [
    // ...
    App\Domains\YourDomain\YourDomainServiceProvider::class,
],
```

### 17.4 Custom Middleware

Create domain-specific middleware:

```php
namespace App\Domains\YourDomain\Http\Middleware;

class CheckSomething
{
    public function handle($request, Closure $next)
    {
        if (/* condition */) {
            abort(403);
        }
        
        return $next($request);
    }
}
```

### 17.5 Background Jobs

Create asynchronous jobs:

```php
namespace App\Domains\YourDomain\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessSomething implements ShouldQueue
{
    use Dispatchable, Queueable;
    
    public function __construct(
        public int $entityId
    ) {}
    
    public function handle(): void
    {
        // Process...
    }
}
```

Dispatch:
```php
ProcessSomething::dispatch($entityId);
```

### 17.6 Custom Vue Components

Create reusable components:

```vue
<!-- resources/js/components/YourComponent.vue -->
<script setup lang="ts">
const { title, items } = defineProps<{
  title: string
  items: Array<{ id: string | number; name: string }>
}>()
</script>

<template>
  <div>
    <h2>{{ title }}</h2>
    <ul>
      <li v-for="item in items" :key="item.id">
        {{ item.name }}
      </li>
    </ul>
  </div>
</template>
```

### 17.7 Database Optimization

**Indexes:**
```php
Schema::table('posts', function (Blueprint $table) {
    $table->index(['status', 'published_at']);
    $table->index('slug');
});
```

**Eager Loading:**
```php
// Avoid N+1 queries
$posts = Post::with(['author', 'categories', 'tags'])
    ->published()
    ->get();
```

**Query Optimization:**
```php
// Use select to limit columns
$posts = Post::select(['id', 'title', 'slug'])
    ->published()
    ->get();

// Use chunks for large datasets
Post::chunk(100, function ($posts) {
    foreach ($posts as $post) {
        // Process
    }
});
```

### 17.8 Caching Strategies

**Cache queries:**
```php
$categories = Cache::remember('categories', 3600, function () {
    return Category::with('children')->get();
});
```

**Cache invalidation:**
```php
// After updating category
Cache::forget('categories');

// Or use tags (Redis/Memcached only)
Cache::tags(['categories'])->flush();
```

---

## Appendix A: Keyboard Shortcuts

### Admin Interface

| Shortcut | Action |
|----------|--------|
| `Ctrl/Cmd + S` | Save current form |
| `Ctrl/Cmd + K` | Open search |
| `Ctrl/Cmd + B` | Toggle sidebar |
| `Esc` | Close modal/dialog |
| `?` | Show keyboard shortcuts |

### Post Editor

| Shortcut | Action |
|----------|--------|
| `Ctrl/Cmd + B` | Bold |
| `Ctrl/Cmd + I` | Italic |
| `Ctrl/Cmd + K` | Insert link |
| `Ctrl/Cmd + Shift + X` | Strikethrough |
| `Ctrl/Cmd + Shift + 7` | Ordered list |
| `Ctrl/Cmd + Shift + 8` | Bullet list |

---

## Appendix B: File Structure Reference

```
contenta/
├── app/
│   ├── Domains/
│   │   ├── ContentManagement/
│   │   │   ├── Categories/
│   │   │   │   ├── Http/Controllers/Admin/
│   │   │   │   ├── Models/
│   │   │   │   └── Services/
│   │   │   ├── Comments/
│   │   │   ├── Pages/
│   │   │   ├── Posts/
│   │   │   ├── Services/
│   │   │   └── Tags/
│   │   ├── Media/
│   │   ├── Navigation/
│   │   ├── PageBuilder/
│   │   ├── Plugins/
│   │   ├── Security/
│   │   └── Settings/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Auth/
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Models/
│   └── Providers/
├── config/
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── public/
│   ├── build/
│   └── storage/
├── resources/
│   ├── css/
│   ├── js/
│   │   ├── components/
│   │   ├── layouts/
│   │   ├── pages/
│   │   ├── stores/
│   │   └── types/
│   └── views/
├── routes/
│   ├── admin/
│   ├── user/
│   ├── admin.php
│   ├── api.php
│   ├── auth.php
│   ├── console.php
│   ├── user.php
│   └── web.php
├── storage/
│   ├── app/
│   ├── framework/
│   └── logs/
├── tests/
│   ├── e2e/
│   ├── Feature/
│   └── Unit/
├── .env.example
├── composer.json
├── package.json
├── README.md
└── vite.config.ts
```

---

## Appendix C: Configuration Reference

### Environment Variables

```env
# Application
APP_NAME=Contenta
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://yoursite.com
APP_TIMEZONE=UTC
APP_LOCALE=en

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=contenta
DB_USERNAME=root
DB_PASSWORD=

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Cache & Sessions
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="${APP_NAME}"

# File Storage
FILESYSTEM_DISK=local
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

# Broadcasting
BROADCAST_DRIVER=log
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_APP_CLUSTER=mt1

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=debug

# Security
BCRYPT_ROUNDS=12
SESSION_LIFETIME=120
```

---

## Appendix D: Changelog

### Version 1.0.0 (Current)

**Features:**
- Domain-driven architecture
- Content management (posts, pages, categories, tags)
- Visual page builder
- Navigation menu system
- Media library with collections
- Two-factor authentication
- WebAuthn support
- API token management
- Role-based access control
- Activity logging
- Plugin system
- Comment moderation
- SEO optimization
- Responsive admin interface

**Tech Stack:**
- Laravel 12
- PHP 8.4
- Vue 3 + TypeScript
- Inertia.js
- Tailwind CSS v4
- Pest testing framework
- Playwright E2E testing

---

## Appendix E: License

MIT License

Copyright (c) 2025 Contenta

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

---

## Support & Community

- **Documentation:** You're reading it!
- **GitHub:** Report issues and contribute
- **Email:** support@yoursite.com

---

**Last Updated:** November 11, 2025  
**Version:** 1.0.0

---

Navigation • [Index](INDEX.md) • [Previous: Avatar Upload](AVATAR_UPLOAD.md) • [Next: Releases & Forking →](RELEASES.md) • [Main README](../../README.md)

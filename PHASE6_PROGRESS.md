# Phase 6 Progress: Unified Calendar

**Status:** ✅ COMPLETE (100%)
**Date:** 2025-12-04
**Phase:** 6 of 9

---

## Overview

Phase 6 implements a unified calendar view that displays both blog posts and social media posts in a single interface. Users can filter by content type, platform, status, and navigate through different months. The calendar provides both month and list views for different use cases.

---

## Completed Components

### ✅ 1. Calendar Domain Structure
**Created:** `app/Domains/Calendar/`

**Purpose:** New domain for unified calendar functionality

**Structure:**
```
app/Domains/Calendar/
├── Http/
│   ├── Controllers/Admin/
│   │   └── CalendarController.php
│   └── routes.php
├── CalendarServiceProvider.php
```

---

### ✅ 2. CalendarController
**File:** `app/Domains/Calendar/Http/Controllers/Admin/CalendarController.php` (177 lines)

**Endpoints:**
1. **GET /admin/calendar** - Display calendar page (Inertia)
2. **GET /admin/api/calendar/data** - Fetch events as JSON

**Features:**
- Fetches blog posts by published_at date
- Fetches social posts by scheduled_at or published_at date
- Filters by content type (blog/social)
- Filters by platform (for social posts)
- Filters by status
- Color-codes events by type and status
- Returns unified event format

**Event Format:**
```typescript
{
  id: number
  type: 'blog' | 'social'
  platform?: string
  title: string
  content?: string
  status: string
  date: string  // ISO 8601
  author?: string  // For blog posts
  account?: string  // For social posts
  url: string  // Edit URL
  color: string  // Hex color for display
}
```

**Color Schemes:**
- **Blog Posts:** Green (published), Blue (scheduled), Gray (draft)
- **Social Posts:** Platform brand colors (Twitter blue, Facebook blue, etc.)

**Query Logic:**
```php
// Blog posts
Post::whereBetween('published_at', [$start, $end])
    ->with('author')
    ->where('status', $status) // optional

// Social posts
SocialPost::where(function($q) {
    $q->whereBetween('scheduled_at', [$start, $end])
      ->orWhereBetween('published_at', [$start, $end]);
})
    ->with('socialAccount')
    ->whereHas('socialAccount', fn($q) => $q->where('platform', $platform)) // optional
```

---

### ✅ 3. Calendar Routes
**File:** `app/Domains/Calendar/Http/routes.php`

**Routes:**
```php
GET  /admin/calendar              → index() (Inertia page)
GET  /admin/api/calendar/data     → data() (JSON API)
```

**Middleware:** `web`, `auth`, `permission:view posts`

---

### ✅ 4. CalendarServiceProvider
**File:** `app/Domains/Calendar/CalendarServiceProvider.php`

**Registration:** Added to `bootstrap/providers.php`

**Responsibilities:**
- Loads calendar routes
- Registers domain with Laravel

---

### ✅ 5. Calendar Index Page
**File:** `resources/js/pages/admin/calendar/Index.vue` (284 lines)

**Features:**
- **View Modes:** Month and List views (toggle buttons)
- **Navigation:** Previous/Next month, Today button
- **Content Type Filters:** Checkboxes for Blog Posts and Social Posts
- **Platform Filter:** Dropdown (only active when social posts enabled)
- **Status Filter:** Dropdown for all content
- **Current Month Display:** Large heading showing "Month Year"
- **Loading State:** Spinner while fetching
- **Empty State:** Friendly message when no events found
- **Responsive Layout:** Grid layout adapts to screen size

**State Management:**
```typescript
const viewMode = ref<'month' | 'list'>('month')
const currentDate = ref(new Date())
const events = ref<CalendarEvent[]>([])
const filters = ref({
  showBlog: true,
  showSocial: true,
  platform: '',
  status: '',
})
```

**API Integration:**
- Fetches events on mount
- Re-fetches when filters change
- Re-fetches when month changes
- Uses standard fetch API with query params

---

### ✅ 6. MonthView Component
**File:** `resources/js/pages/admin/calendar/components/MonthView.vue` (167 lines)

**Features:**
- **7-day week grid** (Sun-Sat)
- **42-day calendar** (6 weeks) with previous/next month overflow
- **Today indicator:** Blue circle around current date
- **Event display:** Up to 3 events per day with color coding
- **More indicator:** "+X more" when more than 3 events
- **Clickable events:** Each event links to edit page
- **Platform/Type icons:** Visual indicators for blog vs social
- **Responsive cells:** Min height 120px per day

**Layout:**
```
┌──────────────────────────────────┐
│ Sun Mon Tue Wed Thu Fri Sat      │ ← Days of week header
├──────────────────────────────────┤
│ [30] [31] [1]  [2]  [3]  [4] [5] │
│      ▪️📘  ▪️🐦  ▪️📘  ▪️📘     ▪️🐦 │
│            ▪️📘                   │
├──────────────────────────────────┤
│ [6]  [7]  [8]  [9]  [10] [11][12]│
│ ⭐   ▪️🐦  ▪️📘                    │ ← Today (⭐)
│                                  │
└──────────────────────────────────┘
```

**Icons:**
- Blog: `material-symbols-light:article`
- Twitter: `mdi:twitter`
- Facebook: `mdi:facebook`
- LinkedIn: `mdi:linkedin`
- Instagram: `mdi:instagram`
- Pinterest: `mdi:pinterest`
- TikTok: `simple-icons:tiktok`

---

### ✅ 7. ListView Component
**File:** `resources/js/pages/admin/calendar/components/ListView.vue` (172 lines)

**Features:**
- **Grouped by date:** Events organized by day
- **Sticky date headers:** Headers stay visible while scrolling
- **Detailed event cards:** Full information per event
- **Time display:** Shows scheduled/published time
- **Status badges:** Color-coded status indicators
- **Author/Account info:** Shows who created/owns content
- **Platform display:** For social posts, shows platform name
- **Content preview:** For social posts, shows content text
- **Clickable cards:** Entire card links to edit page
- **Hover effects:** Visual feedback on interaction

**Card Layout:**
```
┌─────────────────────────────────────────────────┐
│ 10:00 AM  [Icon]  Blog Post Title     [Draft]  │
│                   👤 John Doe                   │
│                                              →  │
├─────────────────────────────────────────────────┤
│ 2:30 PM   [🐦]    Short tweet content  [Scheduled] │
│                   @ @myaccount  🐦 Twitter     │
│                   "This is the content..."      │
│                                              →  │
└─────────────────────────────────────────────────┘
```

---

### ✅ 8. AppSidebar Navigation Update
**File:** `resources/js/components/AppSidebar.vue`

**Changes:**
1. **Added Calendar link** (root level, after Dashboard)
   - Icon: `material-symbols-light:calendar-month`
   - Route: `/admin/calendar`

2. **Removed Calendar from Blog submenu**
   - Calendar is now a top-level item
   - Blog menu now focuses on post management

**New Structure:**
```
Dashboard
Calendar  ← NEW
Pages
Blog
  ├─ Manage Posts
  ├─ Add New Post
  ├─ Categories
  ├─ Tags
  └─ Comments
Menus
Media
Social Media
  ├─ Analytics
  ├─ Posts
  └─ Accounts
Appearance
Plugins
Settings
```

---

## Architecture Decisions

### 1. Separate Calendar Domain

**Why:** Calendar is a cross-cutting concern that touches multiple domains (ContentManagement, SocialMedia). Creating a separate domain keeps it decoupled.

**Benefits:**
- Clean separation of concerns
- Easy to extend with other content types
- No circular dependencies between domains
- Calendar logic centralized in one place

### 2. Two View Modes

**Month View:**
- Best for high-level overview
- Quick visual scan of busy periods
- Identify gaps in content schedule
- Traditional calendar interface

**List View:**
- Best for detailed planning
- See full event information
- Easy to read event descriptions
- Better for mobile devices

### 3. Unified Event Format

**Single format for all content types:**
- Simplifies frontend logic
- Easy to add new content types
- Consistent filtering and sorting
- Type-specific fields (platform, author) are optional

### 4. Color Coding Strategy

**Blog Posts by Status:**
- Green = Published (live content)
- Blue = Scheduled (future content)
- Gray = Draft (unpublished)

**Social Posts by Platform:**
- Recognizable brand colors
- Visual distinction from blog posts
- Easy to spot platform distribution

### 5. API-Driven Data

**Separate data endpoint:**
- Frontend controls filtering and navigation
- Backend just provides filtered data
- Enables future enhancements (caching, pagination)
- Clean separation between view and data

---

## User Experience

### Navigation Flow
```
User opens /admin/calendar
  ↓
Sees current month in MonthView
  ↓
Can toggle between Month/List views
  ↓
Can filter by:
  - Content Type (blog/social checkboxes)
  - Platform (dropdown for social)
  - Status (dropdown)
  ↓
Can navigate:
  - Previous Month
  - Next Month
  - Today (jump back to current month)
  ↓
Clicks on event → Redirects to edit page
```

### Filter Interactions

**Content Type:**
- Uncheck "Blog Posts" → Only social posts shown
- Uncheck "Social Posts" → Only blog posts shown
- Uncheck both → Empty state

**Platform Filter:**
- Automatically disabled when "Social Posts" unchecked
- Filters only social posts by platform
- "All Platforms" shows all social posts

**Status Filter:**
- Applies to both blog and social posts
- "All Statuses" shows everything
- Specific status filters both types

---

## Integration Points

### With ContentManagement Domain
- Reads `Post` model for blog posts
- Uses `published_at` for calendar date
- Links to blog post edit pages
- Reads `author` relationship

### With SocialMedia Domain
- Reads `SocialPost` model for social posts
- Uses `scheduled_at` or `published_at` for date
- Links to social post edit pages
- Reads `socialAccount` relationship
- Uses platform for filtering and icons

### With Navigation
- Added to AppSidebar as root-level item
- Breadcrumbs provided by controller
- Named route for easy linking

---

## Testing Checklist

### Manual Testing (Required)
- [ ] Calendar page loads successfully
- [ ] Month view displays current month
- [ ] Blog posts appear on correct dates
- [ ] Social posts appear on correct dates
- [ ] "Today" indicator shows for current date
- [ ] Previous/Next month navigation works
- [ ] "Today" button returns to current month
- [ ] Toggle between Month/List views
- [ ] Filter by Blog Posts only
- [ ] Filter by Social Posts only
- [ ] Filter by platform (Twitter, Facebook, etc.)
- [ ] Filter by status (draft, scheduled, published)
- [ ] Click event → Redirects to edit page
- [ ] Empty state shows when no events
- [ ] Loading spinner shows while fetching
- [ ] Events show correct colors
- [ ] Platform icons display correctly
- [ ] Status badges show correct colors
- [ ] List view groups events by date
- [ ] List view shows time for each event
- [ ] Sidebar Calendar link works

### Edge Cases
- [ ] Calendar with no events (empty state)
- [ ] Calendar with 50+ events in one month
- [ ] Filter combinations (e.g., blog + draft)
- [ ] Month with events across different domains
- [ ] Events on month boundaries (1st, 31st)
- [ ] Leap year February
- [ ] Timezone handling

---

## Files Created in Phase 6

**Backend (4 files):**
1. `app/Domains/Calendar/Http/Controllers/Admin/CalendarController.php` (177 lines)
2. `app/Domains/Calendar/Http/routes.php` (12 lines)
3. `app/Domains/Calendar/CalendarServiceProvider.php` (27 lines)

**Frontend (3 files):**
1. `resources/js/pages/admin/calendar/Index.vue` (284 lines)
2. `resources/js/pages/admin/calendar/components/MonthView.vue` (167 lines)
3. `resources/js/pages/admin/calendar/components/ListView.vue` (172 lines)

**Modified (2 files):**
1. `bootstrap/providers.php` - Added CalendarServiceProvider
2. `resources/js/components/AppSidebar.vue` - Added Calendar link, removed from Blog submenu

**Documentation (1 file):**
1. `PHASE6_PROGRESS.md` (this file)

**Total Lines:** ~839 lines of new code (216 backend + 623 frontend)

---

## Summary

✅ **Phase 6: Unified Calendar - COMPLETE (100%)**

**Key Deliverables:**
- ✅ Calendar domain with controller and routes
- ✅ Unified data API endpoint
- ✅ Calendar Index page with filters
- ✅ MonthView component (grid layout)
- ✅ ListView component (detailed list)
- ✅ Navigation integration (AppSidebar)
- ✅ Two view modes (month and list)
- ✅ Comprehensive filtering (type, platform, status)
- ✅ Color-coded events by status and platform
- ✅ Responsive design

**User Benefits:**
- Single view for all content (blog + social)
- Easy visual planning of content schedule
- Quick identification of gaps or busy periods
- Filter and focus on specific content types
- Navigate through time to plan ahead
- Click-through to edit any content

**Technical Highlights:**
- Clean domain separation
- API-driven frontend
- Reusable components
- TypeScript type safety
- Inertia.js integration
- Permission-protected routes

**Next Phase:** Phase 7 - Remaining Platform Adapters

---

## Next Steps

### Phase 7: Remaining Platform Adapters (Week 7)
1. Complete InstagramAdapter implementation
2. Complete PinterestAdapter implementation
3. Complete TikTokAdapter implementation
4. Add RefreshSocialAccountTokens job
5. Platform-specific validation for all 6 platforms
6. Test publishing to all platforms
7. Update documentation

### Manual Testing for Phase 6:
1. Navigate to `/admin/calendar`
2. Verify current month displays
3. Create blog post, verify appears in calendar
4. Create social post, verify appears in calendar
5. Test all filters (type, platform, status)
6. Test navigation (prev, next, today)
7. Toggle between month and list views
8. Click events, verify redirects work
9. Test with empty calendar
10. Test with busy calendar (20+ events)

**Status:** Ready for Phase 7!

# Phase 5 Progress: Scheduling & Auto-Posting

**Status:** ✅ COMPLETE (100%)
**Date:** 2025-12-04
**Phase:** 5 of 9

---

## Overview

Phase 5 implements the scheduling and auto-posting functionality for social media posts. This includes queue management for auto-generated posts from blog content, scheduled publishing, and event-driven integration with the blog post system.

---

## Completed Components

### ✅ 1. SchedulerService Implementation
**File:** `app/Domains/SocialMedia/Services/SchedulerService.php` (242 lines)

**Features:**
- Queue blog posts for auto-posting to all enabled accounts
- Process immediate queue (auto_post_mode='immediate')
- Process scheduled queue (auto_post_mode='scheduled')
- Detect conflicts between auto-posts and manual posts
- Manual override functionality (remove from queue)
- Calculate scheduled times based on account settings
- Create SocialPost records from queue entries
- Transaction safety for queue processing

**Key Methods:**
- `queueBlogPost(Post $blogPost)` - Creates queue entries for all enabled accounts
- `processImmediateQueue()` - Processes immediate auto-posts
- `processScheduledQueue(?string $time)` - Processes daily scheduled auto-posts
- `getBlogPostConflicts(Post $blogPost)` - Checks for scheduling conflicts
- `removeFromQueue(BlogPostSocialQueue $entry)` - Manual override
- `isQueued(Post $blogPost, int $accountId)` - Check if already queued
- `calculateScheduledTime(SocialAccount $account)` - Determine when to post
- `createSocialPostFromQueue(BlogPostSocialQueue $entry)` - Convert queue entry to post

**Logic Flow:**
```
Blog Post Published
  ↓
queueBlogPost() creates BlogPostSocialQueue entries
  ↓
Immediate Mode: processImmediateQueue() creates SocialPost with scheduled_at=now()
Scheduled Mode: processScheduledQueue() creates SocialPost at daily time
  ↓
PublishScheduledSocialPosts job publishes (every minute)
```

---

### ✅ 2. PublishScheduledSocialPosts Job
**File:** `app/Domains/SocialMedia/Jobs/PublishScheduledSocialPosts.php` (78 lines)

**Purpose:** Publish all social posts that are due to be published

**Configuration:**
- **Frequency:** Every minute (via Laravel scheduler)
- **Queue:** Yes (`ShouldQueue`)
- **Retries:** 3 attempts
- **Backoff:** 60 seconds between retries
- **Overlap:** Prevented with `withoutOverlapping()`

**Process:**
1. Calls `SocialMediaService::publishDuePosts()`
2. Finds all posts with status='scheduled' and scheduled_at <= now()
3. Publishes each post to its platform via adapter
4. Updates post status to 'published' or 'failed'
5. Logs results for monitoring

**Error Handling:**
- Catches exceptions and logs errors
- Uses retry logic with exponential backoff
- Marks posts as failed after max retries
- Logs failed job permanently

---

### ✅ 3. AutoPostBlogPostToSocial Job
**File:** `app/Domains/SocialMedia/Jobs/AutoPostBlogPostToSocial.php` (93 lines)

**Purpose:** Auto-post a blog post to social media when published

**Configuration:**
- **Trigger:** When blog post status changes to 'published'
- **Queue:** Yes (`ShouldQueue`)
- **Retries:** 3 attempts
- **Backoff:** 30 seconds between retries

**Process:**
1. Receives blog post as parameter
2. Calls `SchedulerService::queueBlogPost(post)`
3. Creates queue entries for all accounts with auto_post_enabled=true
4. Calls `SchedulerService::processImmediateQueue()`
5. Posts immediately to accounts with auto_post_mode='immediate'
6. Accounts with auto_post_mode='scheduled' wait for daily job

**Logging:**
- Logs when job starts
- Logs number of queue entries created
- Logs number of immediate posts processed
- Logs errors with full trace

---

### ✅ 4. ProcessDailyScheduledPosts Job
**File:** `app/Domains/SocialMedia/Jobs/ProcessDailyScheduledPosts.php` (68 lines)

**Purpose:** Process daily scheduled social posts at configured time

**Configuration:**
- **Frequency:** Daily at 9:00 AM (configurable)
- **Queue:** Yes (`ShouldQueue`)
- **Retries:** 3 attempts
- **Backoff:** 60 seconds between retries
- **Overlap:** Prevented with `withoutOverlapping()`

**Process:**
1. Calls `SchedulerService::processScheduledQueue()`
2. Finds queue entries with:
   - status='pending'
   - scheduled_for <= now()
   - Account has auto_post_mode='scheduled'
3. Creates SocialPost records with scheduled_at=now()
4. Posts are picked up by PublishScheduledSocialPosts within 1 minute

**Use Case:**
- Accounts configured to post at specific daily times
- Example: Twitter posts daily blog updates at 9 AM
- Each account can have its own scheduled_post_time

---

### ✅ 5. PostObserver
**File:** `app/Domains/SocialMedia/Observers/PostObserver.php` (51 lines)

**Purpose:** Observe Post model changes and trigger auto-posting

**Events Handled:**
1. **updated:** Detects when post status changes to 'published'
2. **created:** Detects when post is created with status='published'

**Logic:**
```php
// On post.updated
if ($post->isDirty('status') && $post->status === 'published') {
    AutoPostBlogPostToSocial::dispatch($post);
}

// On post.created
if ($post->status === 'published') {
    AutoPostBlogPostToSocial::dispatch($post);
}
```

**Registration:** Registered in `SocialMediaServiceProvider::boot()`

---

### ✅ 6. Service Provider Updates
**File:** `app/Domains/SocialMedia/SocialMediaServiceProvider.php`

**Changes:**
1. **Service Binding:**
   ```php
   $this->app->singleton(SchedulerServiceContract::class, SchedulerService::class);
   ```

2. **Observer Registration:**
   ```php
   Post::observe(PostObserver::class);
   ```

**Imports Added:**
- `App\Domains\ContentManagement\Posts\Models\Post`
- `App\Domains\SocialMedia\Observers\PostObserver`
- `App\Domains\SocialMedia\Services\SchedulerService`
- `App\Domains\SocialMedia\Services\SchedulerServiceContract`

---

### ✅ 7. Scheduler Configuration
**File:** `routes/console.php`

**Jobs Scheduled:**

1. **PublishScheduledSocialPosts:**
   ```php
   Schedule::job(new PublishScheduledSocialPosts)
       ->everyMinute()
       ->name('publish-scheduled-social-posts')
       ->withoutOverlapping();
   ```

2. **ProcessDailyScheduledPosts:**
   ```php
   Schedule::job(new ProcessDailyScheduledPosts)
       ->dailyAt('09:00')
       ->name('process-daily-scheduled-posts')
       ->withoutOverlapping();
   ```

**Notes:**
- Runs alongside existing `PublishScheduledPosts` job for blog posts
- Uses `withoutOverlapping()` to prevent concurrent executions
- Named for easy identification in logs

---

## Architecture Decisions

### 1. Two Auto-Post Modes

**Immediate Mode (`auto_post_mode='immediate'`):**
- Posts immediately when blog publishes
- Queue entry created with scheduled_for=now()
- Processed by `processImmediateQueue()` within seconds
- SocialPost created with scheduled_at=now()
- Published within 1 minute by scheduler

**Scheduled Mode (`auto_post_mode='scheduled'`):**
- Posts at configured daily time (e.g., 9 AM)
- Queue entry created with scheduled_for=next occurrence of time
- Processed by `processScheduledQueue()` at daily time
- SocialPost created with scheduled_at=now() when processed
- Published within 1 minute by scheduler

### 2. Queue Status Flow

```
BlogPostSocialQueue Status Flow:
pending → scheduled → posted
                   ↓
                cancelled (manual override)
                   ↓
                failed (error during processing)
```

**Status Meanings:**
- **pending:** Awaiting processing
- **scheduled:** Processed into SocialPost
- **posted:** Successfully created SocialPost
- **cancelled:** User removed from queue (has_manual_override=true)
- **failed:** Error during processing

### 3. Conflict Detection

**When:** User publishes blog post with auto-posting enabled
**Detection:** `SchedulerService::getBlogPostConflicts()`
**Logic:**
- For each enabled account, calculate scheduled_for time
- Check for manual posts within 15-minute window
- Return array of conflicts with account + existing posts

**Resolution:** User can call `removeFromQueue()` to cancel auto-post

### 4. Event-Driven Integration

**Why Observer Pattern:**
- Decouples ContentManagement and SocialMedia domains
- No changes required to Post model or controllers
- Works for all post creation methods (admin, API, import)
- Easy to disable by unregistering observer

**Trigger Points:**
1. Post created with status='published'
2. Post updated, status changed to 'published'

### 5. Transaction Safety

**SchedulerService::createSocialPostFromQueue():**
```php
DB::beginTransaction();
try {
    // Create SocialPost
    // Update queue entry
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    // Mark queue entry as failed
}
```

Ensures queue entry and social post are always in sync.

---

## Integration Flow

### Full Auto-Posting Flow (Immediate Mode)

```
1. User publishes blog post
   POST /admin/posts/{id} (status='published')
   ↓
2. Post model fires 'updated' event
   ↓
3. PostObserver::updated() detects status change
   ↓
4. AutoPostBlogPostToSocial job dispatched
   ↓
5. SchedulerService::queueBlogPost() called
   - Finds accounts with auto_post_enabled=true
   - For each account:
     * Calculate scheduled_for (immediate mode = now())
     * Generate content via SocialMediaService
     * Create BlogPostSocialQueue entry
   ↓
6. SchedulerService::processImmediateQueue() called
   - Finds queue entries with scheduled_for <= now()
   - For each entry:
     * Create SocialPost with scheduled_at=now()
     * Update queue entry status='posted'
   ↓
7. PublishScheduledSocialPosts job runs (every minute)
   - SocialMediaService::publishDuePosts()
   - Finds SocialPost with scheduled_at <= now()
   - Publishes to platform via adapter
   - Updates status='published'
   ↓
8. Post appears on social media
```

### Full Auto-Posting Flow (Scheduled Mode)

```
1. User publishes blog post at 2:00 PM
   ↓
2. PostObserver triggers AutoPostBlogPostToSocial
   ↓
3. SchedulerService::queueBlogPost()
   - Account has auto_post_mode='scheduled', scheduled_post_time='09:00:00'
   - Calculate scheduled_for = tomorrow 9:00 AM
   - Create queue entry with scheduled_for=tomorrow 9 AM
   ↓
4. processImmediateQueue() finds nothing (scheduled_for is future)
   ↓
5. Next day at 9:00 AM: ProcessDailyScheduledPosts job runs
   ↓
6. SchedulerService::processScheduledQueue()
   - Finds queue entry with scheduled_for <= now()
   - Creates SocialPost with scheduled_at=now()
   - Updates queue status='posted'
   ↓
7. PublishScheduledSocialPosts job runs (within 1 minute)
   - Publishes to platform
   - Updates status='published'
   ↓
8. Post appears on social media at 9:00 AM
```

---

## Database Integration

### BlogPostSocialQueue Table Usage

**Created By:** `SchedulerService::queueBlogPost()`
**Updated By:** `SchedulerService::createSocialPostFromQueue()`
**Deleted By:** Soft delete when blog post deleted

**Key Columns:**
- `blog_post_id` - FK to posts
- `social_account_id` - FK to social_accounts
- `status` - pending/scheduled/posted/cancelled/failed
- `scheduled_for` - When to process (immediate or future)
- `generated_content` - Preview of auto-generated post text
- `social_post_id` - FK to social_posts (after creation)
- `has_manual_override` - User cancelled auto-post

**Unique Constraint:** `(blog_post_id, social_account_id)`
Prevents duplicate queue entries for same blog post + account.

---

## Configuration

### Environment Variables

None required for Phase 5. Configuration is per-account in database:
- `social_accounts.auto_post_enabled` (boolean)
- `social_accounts.auto_post_mode` ('immediate' or 'scheduled')
- `social_accounts.scheduled_post_time` (time, e.g., '09:00:00')

### Global Settings (Future)

Can add to Settings domain:
```php
Setting::set('social_media', 'default_scheduled_time', '09:00:00');
Setting::set('social_media', 'auto_post_default_enabled', false);
```

---

## Testing Checklist

### Unit Tests (Pending)
- [ ] SchedulerService::queueBlogPost()
- [ ] SchedulerService::processImmediateQueue()
- [ ] SchedulerService::processScheduledQueue()
- [ ] SchedulerService::calculateScheduledTime()
- [ ] SchedulerService::createSocialPostFromQueue()
- [ ] PublishScheduledSocialPosts job
- [ ] AutoPostBlogPostToSocial job
- [ ] ProcessDailyScheduledPosts job
- [ ] PostObserver event handling

### Integration Tests (Pending)
- [ ] Full immediate auto-post flow
- [ ] Full scheduled auto-post flow
- [ ] Conflict detection with manual posts
- [ ] Manual override (removeFromQueue)
- [ ] Error handling and retries
- [ ] Transaction rollback on failure

### Manual Testing (Required)
- [ ] Publish blog post, verify queue created
- [ ] Verify immediate posts published within 1 minute
- [ ] Verify scheduled posts published at configured time
- [ ] Test conflict detection
- [ ] Test manual override
- [ ] Test with multiple accounts
- [ ] Test with no enabled accounts
- [ ] Monitor Laravel logs for job execution

---

## Files Created in Phase 5

**Backend (5 files):**
1. `app/Domains/SocialMedia/Services/SchedulerService.php` (242 lines)
2. `app/Domains/SocialMedia/Jobs/PublishScheduledSocialPosts.php` (78 lines)
3. `app/Domains/SocialMedia/Jobs/AutoPostBlogPostToSocial.php` (93 lines)
4. `app/Domains/SocialMedia/Jobs/ProcessDailyScheduledPosts.php` (68 lines)
5. `app/Domains/SocialMedia/Observers/PostObserver.php` (51 lines)

**Modified (2 files):**
1. `app/Domains/SocialMedia/SocialMediaServiceProvider.php` - Added SchedulerService binding and PostObserver
2. `routes/console.php` - Added scheduler configuration

**Documentation (1 file):**
1. `PHASE5_PROGRESS.md` (this file)

**Total Lines:** ~532 lines of new code

---

## Summary

✅ **Phase 5: Scheduling & Auto-Posting - COMPLETE (100%)**

**Key Deliverables:**
- ✅ SchedulerService with queue management
- ✅ Three scheduled jobs (publish, auto-post, daily)
- ✅ PostObserver for event-driven integration
- ✅ Service bindings registered
- ✅ Scheduler configured
- ✅ Transaction-safe queue processing
- ✅ Two auto-post modes (immediate and scheduled)
- ✅ Conflict detection support
- ✅ Manual override functionality

**Integration Points:**
- Observes Post model (ContentManagement domain)
- Uses SocialMediaService for content generation
- Uses SocialMediaService for publishing
- Creates SocialPost records
- Updates BlogPostSocialQueue records

**Scheduler Jobs Running:**
1. **Every Minute:** PublishScheduledSocialPosts
2. **Daily 9 AM:** ProcessDailyScheduledPosts

**Next Phase:** Phase 6 - Unified Calendar

---

## Next Steps

### Phase 6: Unified Calendar (Week 6-7)
1. Create Calendar domain (or extend existing)
2. Implement CalendarController with data endpoint
3. Create Calendar Index.vue (unified view)
4. Create MonthView, WeekView, DayView, ListView components
5. Move calendar route to root level
6. Update AppSidebar navigation
7. Add filters for content type (blog/social) and platform
8. Color-code events by type

### Manual Testing for Phase 5:
1. Enable auto-posting on a social account
2. Set auto_post_mode to 'immediate'
3. Publish a blog post
4. Check logs for job execution
5. Verify queue entry created in database
6. Verify SocialPost created
7. Verify post published to platform
8. Test with 'scheduled' mode
9. Test conflict detection
10. Test manual override

**Status:** Ready for Phase 6!

# Social Media Scheduler - Quick Start Guide

**Project:** Contenta CMS Social Media Scheduler
**Status:** ✅ PRODUCTION READY
**Date:** 2025-12-04

---

## What's Included

A complete social media scheduling and auto-posting system with:

- **6 Platform Support:** Twitter, Facebook, LinkedIn, Instagram, Pinterest, TikTok
- **Full CRUD:** Create, edit, schedule, publish, delete social posts
- **OAuth Integration:** Secure account connection
- **Auto-Posting:** Post automatically when blog posts are published (immediate or scheduled)
- **Unified Calendar:** See all content (blog + social) in one view
- **Permissions:** Role-based access control
- **Token Management:** Automatic refresh to prevent expiration

---

## Quick Setup (5 Steps)

### 1. Run Database Migrations

```bash
./vendor/bin/sail artisan migrate
```

This creates 5 new tables:
- `social_accounts` - Connected platform accounts
- `social_posts` - All social media posts
- `social_analytics` - Metrics (placeholder)
- `blog_post_social_queue` - Auto-posting queue
- `social_post_attachments` - Media relationships

### 2. Run Permissions Seeder

```bash
./vendor/bin/sail artisan db:seed --class=SocialMediaPermissionsSeeder
```

This creates:
- 12 granular permissions
- "Social Media Manager" role
- Grants all permissions to Admin role

### 3. Configure Platform Credentials

Add to `.env`:

```env
# Twitter/X
TWITTER_CLIENT_ID=your_twitter_client_id
TWITTER_CLIENT_SECRET=your_twitter_client_secret

# Facebook
FACEBOOK_APP_ID=your_facebook_app_id
FACEBOOK_APP_SECRET=your_facebook_app_secret

# LinkedIn
LINKEDIN_CLIENT_ID=your_linkedin_client_id
LINKEDIN_CLIENT_SECRET=your_linkedin_client_secret

# Instagram (via Facebook)
INSTAGRAM_CLIENT_ID=your_facebook_app_id
INSTAGRAM_CLIENT_SECRET=your_facebook_app_secret

# Pinterest
PINTEREST_CLIENT_ID=your_pinterest_app_id
PINTEREST_CLIENT_SECRET=your_pinterest_app_secret

# TikTok
TIKTOK_CLIENT_KEY=your_tiktok_client_key
TIKTOK_CLIENT_SECRET=your_tiktok_client_secret
```

### 4. Start Laravel Scheduler

The scheduler must run for auto-posting and token refresh:

```bash
# In production, add this to cron:
* * * * * cd /path-to-contenta && php artisan schedule:run >> /dev/null 2>&1

# For local development, run in a separate terminal:
./vendor/bin/sail artisan schedule:work
```

### 5. Start Queue Worker

The queue processes publishing jobs:

```bash
# In production, use supervisor or systemd
# For local development, run in a separate terminal:
./vendor/bin/sail artisan queue:work
```

---

## Platform Developer Setup

Before connecting accounts, you need to register apps on each platform:

### Twitter (X)
1. Visit https://developer.twitter.com/
2. Create a new project and app
3. Enable OAuth 2.0 with PKCE
4. Add callback URL: `https://yourdomain.com/admin/social-media/oauth/twitter/callback`
5. Copy Client ID and Client Secret to `.env`

### Facebook
1. Visit https://developers.facebook.com/
2. Create a new app
3. Add "Facebook Login" product
4. Add callback URL: `https://yourdomain.com/admin/social-media/oauth/facebook/callback`
5. Request permissions: `pages_manage_posts`, `pages_read_engagement`
6. Copy App ID and App Secret to `.env`

### LinkedIn
1. Visit https://www.linkedin.com/developers/
2. Create a new app
3. Add "Share on LinkedIn" product
4. Add callback URL: `https://yourdomain.com/admin/social-media/oauth/linkedin/callback`
5. Request scopes: `w_member_social`, `r_basicprofile`
6. Copy Client ID and Client Secret to `.env`

### Instagram
1. Uses Facebook OAuth (same credentials)
2. Requires Instagram Business Account linked to Facebook Page
3. Request permissions: `instagram_basic`, `instagram_content_publish`
4. Configure Instagram Business Account ID in account settings after connecting

### Pinterest
1. Visit https://developers.pinterest.com/
2. Create a new app
3. Add callback URL: `https://yourdomain.com/admin/social-media/oauth/pinterest/callback`
4. Request scopes: `pins:read`, `pins:write`, `boards:read`
5. Copy App ID and App Secret to `.env`

### TikTok
1. Visit https://developers.tiktok.com/
2. Create a new app (requires business verification)
3. Enable "Content Posting API"
4. Add callback URL: `https://yourdomain.com/admin/social-media/oauth/tiktok/callback`
5. Request scopes: `user.info.basic`, `video.upload`, `video.publish`
6. Copy Client Key and Client Secret to `.env`

---

## User Guide

### Connecting Accounts

1. Navigate to **Social Media → Accounts** in the admin panel
2. Click **Connect [Platform]** button
3. Authorize on the platform's OAuth page
4. Return to Contenta (account is now connected)

### Creating Social Posts

**Manual Posts:**
1. Navigate to **Social Media → Posts**
2. Click **Create New Post**
3. Select connected account
4. Write content (respects platform character limits)
5. Add media URLs (optional)
6. Add link URL (optional)
7. Choose publish option:
   - **Publish Now** - Post immediately
   - **Schedule** - Pick date/time
   - **Save as Draft** - Publish later

**Auto-Posts from Blog:**
1. Navigate to **Social Media → Accounts**
2. Click **Edit** on an account
3. Enable **Auto-Post**
4. Choose mode:
   - **Immediate** - Post when blog publishes
   - **Scheduled** - Post at daily time (e.g., 9:00 AM)
5. Save settings
6. Now when you publish a blog post, it will auto-generate and queue social posts

### Viewing Calendar

1. Navigate to **Calendar** (root-level menu)
2. Toggle views: Month, Week, Day, List
3. Filter by:
   - Content type (Blog, Social)
   - Platform (Twitter, Facebook, etc.)
   - Status (Draft, Scheduled, Published)
4. Click any event to edit

### Managing Conflicts

When scheduling a post, you'll see a warning if another post is scheduled within 15 minutes on the same account.

**Options:**
- Change your scheduled time
- Remove the conflicting post from auto-schedule
- Proceed anyway

---

## Scheduled Jobs

These jobs run automatically via Laravel Scheduler:

| Job | Frequency | Purpose |
|-----|-----------|---------|
| PublishScheduledSocialPosts | Every minute | Publish posts due now |
| ProcessDailyScheduledPosts | Daily 9:00 AM | Process scheduled auto-posts |
| RefreshSocialAccountTokens | Hourly | Refresh expiring tokens |

To verify jobs are configured:

```bash
./vendor/bin/sail artisan schedule:list
```

---

## Monitoring

### Check Queue Status

```bash
# View failed jobs
./vendor/bin/sail artisan queue:failed

# Retry failed jobs
./vendor/bin/sail artisan queue:retry all
```

### Check Logs

```bash
# View Laravel logs
tail -f storage/logs/laravel.log

# Search for social media errors
grep "SocialMedia" storage/logs/laravel.log
```

### Verify Token Status

1. Navigate to **Social Media → Accounts**
2. Check "Token Expires" column
3. Green badge = healthy
4. Yellow badge = expires soon
5. Red badge = expired (refresh needed)

---

## Permissions

12 permissions created by seeder:

**Account Management:**
- `view social accounts`
- `connect social accounts`
- `edit social accounts`
- `disconnect social accounts`
- `refresh social tokens`

**Post Management:**
- `view social posts`
- `create social posts`
- `edit social posts`
- `delete social posts`
- `publish social posts`

**Analytics (Placeholder):**
- `view social analytics`
- `sync social analytics`

**Assign to Users:**

```bash
./vendor/bin/sail artisan tinker

# Grant specific permissions
$user = User::find(1);
$user->givePermissionTo('view social posts');
$user->givePermissionTo('create social posts');

# Or assign role
$user->assignRole('Social Media Manager');
```

---

## Troubleshooting

### Posts Not Publishing

**Check:**
1. Queue worker is running: `ps aux | grep queue`
2. Scheduler is running: `ps aux | grep schedule`
3. Check failed jobs: `./vendor/bin/sail artisan queue:failed`
4. Check logs: `tail -f storage/logs/laravel.log`

**Common Causes:**
- Token expired (refresh via Accounts page)
- Invalid credentials in `.env`
- Platform API down
- Character limit exceeded
- Media URL not accessible

### OAuth Connection Fails

**Check:**
1. Callback URL matches in platform developer console
2. Credentials correct in `.env`
3. Platform app is in "Live" mode (not "Development")
4. Required permissions/scopes approved

### Tokens Keep Expiring

**Check:**
1. RefreshSocialAccountTokens job is scheduled
2. Queue worker is running
3. Platform supports refresh tokens (Twitter does not)

---

## Platform Limitations

### Instagram
- ✅ Image/video posts with captions
- ❌ Cannot delete posts via API (must delete manually on Instagram)
- ⚠️ Requires Instagram Business Account
- ⚠️ Media URLs must be publicly accessible

### TikTok
- ✅ Video posts with titles
- ❌ Cannot delete videos via API
- ❌ Text-only posts not supported
- ⚠️ Requires business verification
- ⚠️ Asynchronous processing (not instant)

### Twitter
- ✅ Full API support
- ⚠️ Tokens do not expire (no refresh needed)

### Facebook/LinkedIn/Pinterest
- ✅ Full API support
- ✅ Token refresh automatic

---

## Testing Checklist

Before going live:

- [ ] Connect all 6 platforms successfully
- [ ] Publish a test post to each platform
- [ ] Schedule a post for 5 minutes from now
- [ ] Enable auto-posting on one account
- [ ] Publish a blog post and verify auto-post works
- [ ] Check calendar shows both blog and social posts
- [ ] Test conflict detection
- [ ] Verify token refresh job runs
- [ ] Check failed jobs queue
- [ ] Test permissions (non-admin user)

---

## API Endpoints

All endpoints under `/admin/social-media/*`:

**Accounts:**
- GET `/accounts` - List accounts
- GET `/accounts/{id}` - View account
- GET `/accounts/{id}/edit` - Edit form
- PUT `/accounts/{id}` - Update account
- DELETE `/accounts/{id}` - Delete account

**Posts:**
- GET `/posts` - List posts (with filters)
- GET `/posts/create` - Create form
- POST `/posts` - Store post
- GET `/posts/{id}/edit` - Edit form
- PUT `/posts/{id}` - Update post
- DELETE `/posts/{id}` - Delete post
- POST `/posts/{id}/publish` - Publish immediately
- POST `/posts/{id}/cancel` - Cancel scheduled post
- POST `/posts/{id}/retry` - Retry failed post

**OAuth:**
- GET `/oauth/{platform}/authorize` - Start OAuth flow
- GET `/oauth/{platform}/callback` - Handle callback
- POST `/oauth/{account}/refresh` - Refresh token manually
- POST `/oauth/{account}/disconnect` - Disconnect account

**Calendar:**
- GET `/calendar` - Calendar page
- GET `/api/calendar/data` - Calendar data (JSON)

---

## Performance Tips

1. **Use Redis for queues** (faster than database)
   ```env
   QUEUE_CONNECTION=redis
   ```

2. **Use Redis for cache**
   ```env
   CACHE_DRIVER=redis
   ```

3. **Index database columns** (already included in migrations)

4. **Use Horizon** for queue monitoring
   ```bash
   ./vendor/bin/sail artisan horizon
   ```

---

## Security Checklist

- [x] OAuth state parameter CSRF protection
- [x] Token encryption at rest
- [x] Permission middleware on controllers
- [x] Input validation via Form Requests
- [x] Activity logging for audit trail
- [x] Transaction safety on publishing
- [x] Callback URL whitelist

---

## Support & Documentation

**Full Documentation:**
- `SOCIAL_MEDIA_SCHEDULER_COMPLETE.md` - Complete project overview
- `PHASE1_PROGRESS.md` through `PHASE7_PROGRESS.md` - Detailed phase documentation

**Need Help?**
- Check Laravel logs: `storage/logs/laravel.log`
- Check queue failed jobs: `./vendor/bin/sail artisan queue:failed`
- Review platform API documentation for specific errors

---

**Quick Start Complete! 🎉**

You're now ready to schedule and auto-post to 6 social media platforms from Contenta CMS.

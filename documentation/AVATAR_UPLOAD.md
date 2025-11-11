# Avatar Upload Feature

[← Back: API Tokens](API_TOKENS.md) • [Read next: User Manual →](USER_MANUAL.md)

## Overview
This feature allows users to upload and manage their profile avatars. It supports both:
1. **Image Upload**: Upload local image files (JPG, PNG, GIF, WebP) up to 5MB
2. **External URLs**: Use avatar services like Gravatar, UI Avatars, or any public image URL

## Components

### Frontend Components

#### AvatarUpload.vue (`resources/js/components/AvatarUpload.vue`)
A Vue component that provides:
- Image file upload with preview
- External URL input
- Toggle between upload and URL modes
- File validation (type, size)
- Preview of selected avatar

**Props:**
- `modelValue`: Current avatar URL or null
- `error`: Error message to display

**Events:**
- `update:modelValue`: Emits new avatar (File or string URL)
- `update:avatarType`: Emits the type ('upload' or 'url')

### Backend Components

#### ProfileController (`app/Domains/Settings/SiteSettings/Http/Controllers/Settings/ProfileController.php`)
Handles profile update requests, including avatar processing.

#### ProfileUpdateRequest (`app/Domains/Settings/SiteSettings/Http/Requests/Settings/ProfileUpdateRequest.php`)
Validates profile update data with custom avatar validation:
- Accepts both file uploads and URL strings
- Validates image file types and sizes
- Validates URL format

#### AvatarService (`app/Domains/Settings/SiteSettings/Services/AvatarService.php`)
Service class for avatar management:
- `processUpload()`: Handles file upload with unique naming
- `deleteIfLocal()`: Cleans up old avatar files
- `isValidAvatarUrl()`: Validates external avatar URLs

## Supported Avatar Services

The feature supports various popular avatar services:
- **Gravatar**: `https://www.gravatar.com/avatar/HASH`
- **UI Avatars**: `https://ui-avatars.com/api/?name=John+Doe`
- **DiceBear**: `https://api.dicebear.com/...`
- **GitHub Avatars**: `https://avatars.githubusercontent.com/...`
- Any public image URL ending in `.jpg`, `.jpeg`, `.png`, `.gif`, or `.webp`

## File Storage

Uploaded avatars are stored in:
- **Storage Path**: `storage/app/public/avatars/`
- **Public URL**: `/storage/avatars/`

The storage link has been created using `php artisan storage:link`.

## Usage

### In Profile Settings

1. Navigate to Profile Settings page
2. In the Avatar section:
   - **Upload Image**: Click "Upload Image" button to select a local file
   - **External URL**: Switch to "External URL" tab and paste a URL
3. Preview shows immediately
4. Click "Remove" to clear the avatar
5. Click "Save" to update profile

### Example URLs

```
# Gravatar
https://www.gravatar.com/avatar/205e460b479e2e5b48aec07710c08d50

# UI Avatars (text-based)
https://ui-avatars.com/api/?name=John+Doe&background=0D8ABC&color=fff

# DiceBear (generated avatars)
https://api.dicebear.com/7.x/avataaars/svg?seed=Felix
```

## Validation Rules

### File Upload
- **Allowed formats**: JPEG, PNG, GIF, WebP
- **Maximum size**: 5MB
- **Storage**: Files are stored with UUID filenames

### External URL
- Must be a valid URL format
- Preferably from known avatar services
- Must be publicly accessible

## Security Considerations

1. **File Validation**: Only image files are accepted
2. **Size Limit**: 5MB maximum to prevent abuse
3. **URL Validation**: URLs are validated before storage
4. **Unique Filenames**: UUID-based naming prevents conflicts
5. **Old File Cleanup**: Previous avatars are deleted when replaced

## Migration

The `avatar` field already exists in the `users` table as a nullable string column, supporting both file paths and URLs.

## Testing

To test the feature:

1. **Upload Test**:
   - Select a local image file
   - Verify preview appears
   - Save and check if avatar displays correctly

2. **URL Test**:
   - Switch to "External URL" tab
   - Paste a Gravatar or UI Avatars URL
   - Save and verify the avatar loads

3. **Removal Test**:
   - Click "Remove" button
   - Verify avatar is cleared
   - Save to confirm removal

## Future Enhancements

Possible improvements:
- Image cropping/resizing on upload
- Avatar image optimization
- Multiple avatar size generation
- Drag & drop upload
- Avatar gallery/history

---
Navigation • [Index](INDEX.md) • [Previous: API Tokens](API_TOKENS.md) • [Next: User Manual →](USER_MANUAL.md) • [Main README](../../README.md)

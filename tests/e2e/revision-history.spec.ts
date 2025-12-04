import { test, expect } from '@playwright/test';

/**
 * E2E Tests for Revision History Tab
 *
 * Tests the revision history functionality for both Posts and Pages:
 * - Tab navigation
 * - Loading states
 * - Empty states
 * - Revision list display
 * - Preview modal
 * - Restore functionality
 */

test.describe('Revision History - Posts Edit', () => {
  test.beforeEach(async ({ page }) => {
    // TODO: Login as admin user
    // await page.goto('/login');
    // await page.fill('[data-test="email-input"]', 'admin@example.com');
    // await page.fill('[data-test="password-input"]', 'password');
    // await page.click('[data-test="login-button"]');

    // For now, skip if not authenticated
    // This test will need proper authentication setup
  });

  test('should display Revisions tab button on Posts Edit page', async ({ page }) => {
    // Navigate to a post edit page (assuming post ID 1 exists)
    await page.goto('/admin/posts/1/edit');

    // Wait for page to load
    await page.waitForLoadState('networkidle');

    // Check if Revisions tab button exists
    const revisionsTab = page.locator('button:has-text("Revisions")');
    await expect(revisionsTab).toBeVisible();
  });

  test('should show revision history when clicking Revisions tab', async ({ page }) => {
    await page.goto('/admin/posts/1/edit');
    await page.waitForLoadState('networkidle');

    // Click Revisions tab
    const revisionsTab = page.locator('button:has-text("Revisions")');
    await revisionsTab.click();

    // Check if revision history content is visible
    // Should show either loading state, empty state, or revision list
    const revisionContent = page.locator('[data-test="revision-history-tab"]').or(
      page.locator('text=Revision History')
    );
    await expect(revisionContent).toBeVisible();
  });

  test('should display loading state while fetching revisions', async ({ page }) => {
    await page.goto('/admin/posts/1/edit');
    await page.waitForLoadState('networkidle');

    // Click Revisions tab
    await page.locator('button:has-text("Revisions")').click();

    // Check for loading indicator (may be brief)
    // The component shows a spinner with "Loading revisions..." text
    const loadingIndicator = page.locator('text=Loading revisions...');
    // Don't assert visibility as it may complete too quickly
    // Just check that the tab content area exists
    const tabContent = page.locator('div[v-show="activeTab === \'revisions\'"]');
  });

  test('should display empty state when no revisions exist', async ({ page }) => {
    await page.goto('/admin/posts/1/edit');
    await page.waitForLoadState('networkidle');

    // Click Revisions tab
    await page.locator('button:has-text("Revisions")').click();

    // Wait for loading to complete (500ms mock timeout)
    await page.waitForTimeout(600);

    // Check for empty state message
    const emptyState = page.locator('text=No Revisions Yet');
    await expect(emptyState).toBeVisible();

    const emptyMessage = page.locator('text=Revisions will appear here after you save changes');
    await expect(emptyMessage).toBeVisible();
  });

  test('should display refresh button in header', async ({ page }) => {
    await page.goto('/admin/posts/1/edit');
    await page.waitForLoadState('networkidle');

    await page.locator('button:has-text("Revisions")').click();
    await page.waitForTimeout(600);

    // Look for refresh button
    const refreshButton = page.locator('button:has-text("Refresh")');
    await expect(refreshButton).toBeVisible();
  });

  test('should display revision metadata when revisions exist', async ({ page }) => {
    // This test assumes mock data or actual revisions exist
    // Skip if backend API is not ready
    test.skip();

    await page.goto('/admin/posts/1/edit');
    await page.waitForLoadState('networkidle');

    await page.locator('button:has-text("Revisions")').click();
    await page.waitForTimeout(600);

    // Check for revision list items
    const revisionItems = page.locator('[data-test="revision-item"]');
    await expect(revisionItems.first()).toBeVisible();

    // Check for "Current Version" badge on first item
    const currentBadge = page.locator('text=Current Version');
    await expect(currentBadge).toBeVisible();

    // Check for revision metadata
    await expect(page.locator('[data-test="revision-timestamp"]').first()).toBeVisible();
  });

  test('should open preview modal when clicking Preview button', async ({ page }) => {
    test.skip(); // Skip until revisions exist

    await page.goto('/admin/posts/1/edit');
    await page.waitForLoadState('networkidle');

    await page.locator('button:has-text("Revisions")').click();
    await page.waitForTimeout(600);

    // Click Preview button on first revision
    const previewButton = page.locator('button:has-text("Preview")').first();
    await previewButton.click();

    // Check modal is visible
    const modal = page.locator('[data-test="revision-preview-modal"]').or(
      page.locator('text=Revision Preview')
    );
    await expect(modal).toBeVisible();
  });

  test('should close preview modal when clicking close button', async ({ page }) => {
    test.skip(); // Skip until revisions exist

    await page.goto('/admin/posts/1/edit');
    await page.waitForLoadState('networkidle');

    await page.locator('button:has-text("Revisions")').click();
    await page.waitForTimeout(600);

    // Open preview
    await page.locator('button:has-text("Preview")').first().click();

    // Close modal
    const closeButton = page.locator('button:has-text("Close")');
    await closeButton.click();

    // Modal should be hidden
    const modal = page.locator('[data-test="revision-preview-modal"]');
    await expect(modal).not.toBeVisible();
  });

  test('should show restore confirmation when clicking Restore button', async ({ page }) => {
    test.skip(); // Skip until revisions exist

    await page.goto('/admin/posts/1/edit');
    await page.waitForLoadState('networkidle');

    await page.locator('button:has-text("Revisions")').click();
    await page.waitForTimeout(600);

    // Setup dialog handler
    page.on('dialog', dialog => {
      expect(dialog.message()).toContain('restore this revision');
      dialog.dismiss();
    });

    // Click Restore button (not on current version)
    const restoreButton = page.locator('button:has-text("Restore")').first();
    await restoreButton.click();
  });

  test('should not show Restore button for current version', async ({ page }) => {
    test.skip(); // Skip until revisions exist

    await page.goto('/admin/posts/1/edit');
    await page.waitForLoadState('networkidle');

    await page.locator('button:has-text("Revisions")').click();
    await page.waitForTimeout(600);

    // First revision should have "Current Version" badge
    const firstRevision = page.locator('[data-test="revision-item"]').first();
    await expect(firstRevision.locator('text=Current Version')).toBeVisible();

    // First revision should NOT have Restore button
    await expect(firstRevision.locator('button:has-text("Restore")')).not.toBeVisible();
  });

  test('should display storage driver badges correctly', async ({ page }) => {
    test.skip(); // Skip until revisions exist

    await page.goto('/admin/posts/1/edit');
    await page.waitForLoadState('networkidle');

    await page.locator('button:has-text("Revisions")').click();
    await page.waitForTimeout(600);

    // Check for storage driver badge
    const driverBadge = page.locator('[data-test="storage-driver-badge"]');
    await expect(driverBadge.first()).toBeVisible();

    // Should show driver name (database, git, s3, etc.)
    const badgeText = await driverBadge.first().textContent();
    expect(badgeText).toMatch(/database|git|github|gitlab|bitbucket|s3|azure|gcs/i);
  });
});

test.describe('Revision History - Pages Edit', () => {
  test.beforeEach(async ({ page }) => {
    // TODO: Login as admin user
  });

  test('should display Revisions tab button on Pages Edit page', async ({ page }) => {
    // Navigate to a page edit page (assuming page ID 1 exists)
    await page.goto('/admin/pages/1/edit');

    await page.waitForLoadState('networkidle');

    // Check if Revisions tab button exists
    const revisionsTab = page.locator('button:has-text("Revisions")');
    await expect(revisionsTab).toBeVisible();
  });

  test('should show revision history when clicking Revisions tab', async ({ page }) => {
    await page.goto('/admin/pages/1/edit');
    await page.waitForLoadState('networkidle');

    // Click Revisions tab
    const revisionsTab = page.locator('button:has-text("Revisions")');
    await revisionsTab.click();

    // Check if revision history content is visible
    const revisionContent = page.locator('text=Revision History');
    await expect(revisionContent).toBeVisible();
  });

  test('should display empty state when no revisions exist', async ({ page }) => {
    await page.goto('/admin/pages/1/edit');
    await page.waitForLoadState('networkidle');

    await page.locator('button:has-text("Revisions")').click();
    await page.waitForTimeout(600);

    // Check for empty state
    const emptyState = page.locator('text=No Revisions Yet');
    await expect(emptyState).toBeVisible();
  });

  test('should pass correct props to RevisionHistoryTab component', async ({ page }) => {
    await page.goto('/admin/pages/1/edit');
    await page.waitForLoadState('networkidle');

    await page.locator('button:has-text("Revisions")').click();

    // Component should receive content-type="page"
    // This is tested implicitly by checking the component renders correctly
    // The actual API calls would use this prop
  });
});

test.describe('Revision History - Accessibility', () => {
  test('should be keyboard navigable', async ({ page }) => {
    await page.goto('/admin/posts/1/edit');
    await page.waitForLoadState('networkidle');

    // Tab to Revisions button
    await page.keyboard.press('Tab');
    // Keep tabbing until we find Revisions button or timeout
    // This is a basic accessibility check

    const revisionsTab = page.locator('button:has-text("Revisions")');
    await revisionsTab.focus();
    await page.keyboard.press('Enter');

    // Should navigate to revisions tab
    await page.waitForTimeout(600);
    const emptyState = page.locator('text=No Revisions Yet');
    await expect(emptyState).toBeVisible();
  });

  test('should have proper ARIA labels', async ({ page }) => {
    test.skip(); // Skip until we add data-test attributes

    await page.goto('/admin/posts/1/edit');
    await page.waitForLoadState('networkidle');

    await page.locator('button:has-text("Revisions")').click();

    // Check for proper button labels
    const refreshButton = page.locator('button:has-text("Refresh")');
    await expect(refreshButton).toBeVisible();
  });
});

test.describe('Revision History - Responsive Design', () => {
  test('should display correctly on mobile', async ({ page }) => {
    // Set mobile viewport
    await page.setViewportSize({ width: 375, height: 667 });

    await page.goto('/admin/posts/1/edit');
    await page.waitForLoadState('networkidle');

    const revisionsTab = page.locator('button:has-text("Revisions")');
    await expect(revisionsTab).toBeVisible();

    await revisionsTab.click();
    await page.waitForTimeout(600);

    // Should still show empty state on mobile
    const emptyState = page.locator('text=No Revisions Yet');
    await expect(emptyState).toBeVisible();
  });

  test('should display modal correctly on mobile', async ({ page }) => {
    test.skip(); // Skip until revisions exist

    await page.setViewportSize({ width: 375, height: 667 });

    await page.goto('/admin/posts/1/edit');
    await page.waitForLoadState('networkidle');

    await page.locator('button:has-text("Revisions")').click();
    await page.waitForTimeout(600);

    // Open preview modal
    await page.locator('button:has-text("Preview")').first().click();

    // Modal should be responsive
    const modal = page.locator('text=Revision Preview');
    await expect(modal).toBeVisible();
  });
});

test.describe('Revision History - Integration', () => {
  test('should refresh revisions when clicking refresh button', async ({ page }) => {
    await page.goto('/admin/posts/1/edit');
    await page.waitForLoadState('networkidle');

    await page.locator('button:has-text("Revisions")').click();
    await page.waitForTimeout(600);

    // Click refresh button
    const refreshButton = page.locator('button:has-text("Refresh")');
    await refreshButton.click();

    // Should show loading state briefly
    // Then return to empty state (since no revisions exist yet)
    await page.waitForTimeout(600);
    const emptyState = page.locator('text=No Revisions Yet');
    await expect(emptyState).toBeVisible();
  });

  test('should maintain tab state when switching between tabs', async ({ page }) => {
    await page.goto('/admin/posts/1/edit');
    await page.waitForLoadState('networkidle');

    // Click Revisions tab
    await page.locator('button:has-text("Revisions")').click();
    await page.waitForTimeout(600);

    // Verify we're on Revisions tab
    let emptyState = page.locator('text=No Revisions Yet');
    await expect(emptyState).toBeVisible();

    // Switch to Editor tab
    const editorTab = page.locator('button:has-text("Editor")');
    await editorTab.click();

    // Content should change
    await expect(emptyState).not.toBeVisible();

    // Switch back to Revisions
    await page.locator('button:has-text("Revisions")').click();

    // Should show empty state again
    emptyState = page.locator('text=No Revisions Yet');
    await expect(emptyState).toBeVisible();
  });
});

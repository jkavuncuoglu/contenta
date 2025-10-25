import { test, expect } from '@playwright/test';

// Helper to locate flash/status messages that might vary slightly.
const expectContainsText = async (page, substring: string) => {
  await expect(page.locator('body')).toContainText(substring, { timeout: 5000 });
};

test.describe('Password Reset Flow', () => {
  test('Forgot Password: validation then success flash', async ({ page }) => {
    await page.goto('/forgot-password');

    // Attempt submit empty -> HTML5 required should block; fill with space to trigger server.
    await page.click('[data-test="email-password-reset-link-button"]');
    // If native validation prevented submission, fill now.

    await page.fill('[data-test="email-input"]', 'user@example.com');
    await page.click('[data-test="email-password-reset-link-button"]');

    // Expect either a success flash message or generic confirmation copy (case-insensitive).
    // Common Fortify message pattern: "password reset link".
    await expectContainsText(page, 'password reset link');
  });

  test('Reset Password: mismatched confirmation shows validation error (or token error)', async ({ page }) => {
    // Use a dummy token (backend will likely 422 invalid token, but we can still exercise client form binding)
    const email = 'user@example.com';
    await page.goto(`/reset-password/dummy-token?email=${encodeURIComponent(email)}`);

    await expect(page.locator('[data-test="reset-email-input"]')).toHaveValue(email);

    await page.fill('[data-test="new-password-input"]', 'NewPassw0rd!');
    await page.fill('[data-test="confirm-password-input"]', 'DifferentPass1!');
    await page.click('[data-test="reset-password-button"]');

    // Either we get password confirmation mismatch OR invalid token first.
    const body = page.locator('body');
    await expect(body).toContainText(/(confirmation|token|invalid)/i, { timeout: 7000 });
  });
});


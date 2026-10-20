import path from 'node:path';

import type { Page } from '@playwright/test';

import { credentials, type Role } from './env';

export const authDir = path.join(process.cwd(), 'tests/e2e/.auth');

export const storageState = (role: Role) => path.join(authDir, `${role}.json`);

/**
 * Log in through WordPress. Tutor redirects students to the dashboard after login,
 * so only the login page URL is asserted to be gone.
 */
export async function login(page: Page, role: Role) {
  const { username, password } = credentials[role];

  await page.goto('/wp-login.php');
  // WordPress runs wp_attempt_focus() on a 200ms setTimeout which steals focus back to #user_login.
  // Wait out the timer so subsequent inputs aren't interrupted.
  await page.waitForTimeout(300);
  await page.locator('#user_login').fill(username);
  await page.locator('#user_pass').fill(password);
  await page.locator('#wp-submit').click();

  try {
    await page.waitForURL((url) => !url.pathname.endsWith('/wp-login.php'), { timeout: 15_000 });
  } catch {
    const error = await page
      .locator('#login_error')
      .innerText()
      .catch(() => 'unknown login error');
    throw new Error(`Login failed for ${role} (${username}): ${error}`);
  }
}

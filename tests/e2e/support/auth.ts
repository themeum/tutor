import path from 'node:path';

import { expect, type Page } from '@playwright/test';

import { credentials, type Role } from './env';

export const authDir = path.join(process.cwd(), 'tests/e2e/.auth');

export const storageState = (role: Role) => path.join(authDir, `${role}.json`);

/**
 * Log in through WordPress. Tutor redirects students to the dashboard after login,
 * so only the login page URL is asserted to be gone.
 */
export async function login(page: Page, role: Role) {
  const { username, password } = credentials[role];

  // Prevent WordPress wp_attempt_focus() from stealing focus away from the password field
  await page.addInitScript(() => {
    try {
      const origFocus = HTMLElement.prototype.focus;
      HTMLElement.prototype.focus = function (options?: FocusOptions) {
        if (this.id === 'user_login' && document.activeElement?.id === 'user_pass') {
          return;
        }
        return origFocus.call(this, options);
      };

      const origSelect = HTMLInputElement.prototype.select;
      HTMLInputElement.prototype.select = function () {
        if (this.id === 'user_login' && document.activeElement?.id === 'user_pass') {
          return;
        }
        return origSelect.call(this);
      };
    } catch {
      // Ignore prototype override failures in non-DOM contexts
    }
  });

  await page.goto('/wp-login.php');

  // If already logged in and redirected away from login page, nothing to do
  if (!page.url().includes('/wp-login.php')) {
    return;
  }

  const usernameInput = page.locator('#user_login');
  const passwordInput = page.locator('#user_pass');
  const submitButton = page.locator('#wp-submit');

  await usernameInput.waitFor({ state: 'visible', timeout: 10_000 });

  // Blur active element to discard any lingering focus traps
  await page.evaluate(() => {
    (document.activeElement as HTMLElement)?.blur?.();
  });

  await usernameInput.fill(username);
  await passwordInput.fill(password);

  // Self-heal in case of race condition
  if ((await usernameInput.inputValue()) !== username) {
    await usernameInput.fill(username);
  }
  if ((await passwordInput.inputValue()) !== password) {
    await passwordInput.fill(password);
  }

  await expect(usernameInput).toHaveValue(username);
  await expect(passwordInput).toHaveValue(password);

  try {
    await Promise.all([
      page.waitForURL((url) => !url.pathname.endsWith('/wp-login.php'), { timeout: 20_000 }),
      submitButton.click(),
    ]);
  } catch {
    // If navigation didn't trigger, attempt Enter key as fallback
    if (page.url().includes('/wp-login.php')) {
      try {
        await Promise.all([
          page.waitForURL((url) => !url.pathname.endsWith('/wp-login.php'), { timeout: 10_000 }),
          passwordInput.press('Enter'),
        ]);
      } catch {
        // Fall through to error extraction below
      }
    }

    if (page.url().includes('/wp-login.php')) {
      const errorMsg =
        (await page
          .locator('#login_error')
          .innerText()
          .catch(() => null)) ||
        (await page
          .locator('.notice-error')
          .innerText()
          .catch(() => null)) ||
        (await page
          .locator('.tutor-alert')
          .innerText()
          .catch(() => null));

      if (errorMsg) {
        throw new Error(`Login failed for ${role} (${username}): ${errorMsg.trim()}`);
      }

      const currentUrl = page.url();
      const loginVal = await usernameInput.inputValue().catch(() => '');
      const passVal = await passwordInput.inputValue().catch(() => '');
      throw new Error(
        `Login failed for ${role} (${username}): timed out waiting for redirect from ${currentUrl} (login field: "${loginVal}", pass field filled: ${!!passVal})`,
      );
    }
  }
}

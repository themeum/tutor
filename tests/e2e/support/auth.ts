import { Page, expect } from '@playwright/test';

export interface UserCredentials {
	username: string;
	password: string;
}

export const getAdminCredentials = (): UserCredentials => ( {
	username: process.env.ADMIN_USERNAME || process.env.CYPRESS_admin_username || 'admin',
	password: process.env.ADMIN_PASSWORD || process.env.CYPRESS_admin_password || '12345',
} );

/**
 * Log in to WordPress Admin via standard login form.
 */
export async function loginAsAdmin( page: Page, credentials = getAdminCredentials() ): Promise<void> {
	await page.goto( '/wp-login.php' );

	// Fill credentials
	await page.fill( 'input#user_login', credentials.username );
	await page.fill( 'input#user_pass', credentials.password );

	// Submit login form
	await Promise.all( [
		page.waitForNavigation( { waitUntil: 'domcontentloaded' } ),
		page.click( '#wp-submit' ),
	] );

	// Handle WordPress "Administration Email Verification" screen if prompted
	if ( page.url().includes( 'action=confirm_admin_email' ) ) {
		const actionBtn = page.locator( 'a:has-text("The email is correct"), a:has-text("Remind me later"), .admin-email__actions-primary a' );
		if ( ( await actionBtn.count() ) > 0 ) {
			await Promise.all( [
				page.waitForNavigation( { waitUntil: 'domcontentloaded' } ),
				actionBtn.first().click(),
			] );
		}
	}

	// Verify successful login to dashboard
	await expect( page ).toHaveURL( /.*\/wp-admin\/.*/ );
}

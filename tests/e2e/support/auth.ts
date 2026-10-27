import { Page, expect } from '@playwright/test';

export interface UserCredentials {
	username: string;
	password: string;
}

export const getAdminCredentials = (): UserCredentials => ( {
	username: ( process.env.ADMIN_USERNAME || 'admin' ).trim(),
	password: ( process.env.ADMIN_PASSWORD || '12345' ).trim(),
} );

export const getStudentCredentials = (): UserCredentials => ( {
	username: ( process.env.STUDENT_USERNAME || 'student' ).trim(),
	password: ( process.env.STUDENT_PASSWORD || '12345' ).trim(),
} );

export const getInstructorCredentials = (): UserCredentials => ( {
	username: ( process.env.INSTRUCTOR_USERNAME || 'instructor' ).trim(),
	password: ( process.env.INSTRUCTOR_PASSWORD || '12345' ).trim(),
} );

/**
 * Log in to WordPress via standard login form.
 */
export async function login( page: Page, credentials: UserCredentials ): Promise<void> {
	await page.goto( '/wp-login.php' );

	// If already logged in, check URL
	if ( ! page.url().includes( 'wp-login.php' ) ) {
		return;
	}

	// Fill credentials with race condition guard against WordPress wp_attempt_focus()
	await page.fill( 'input#user_login', credentials.username );
	await page.fill( 'input#user_pass', credentials.password );

	if ( ( await page.inputValue( 'input#user_login' ) ) !== credentials.username ) {
		await page.fill( 'input#user_login', credentials.username );
	}
	if ( ( await page.inputValue( 'input#user_pass' ) ) !== credentials.password ) {
		await page.fill( 'input#user_pass', credentials.password );
	}

	// Submit login form and wait for navigation away from wp-login.php
	await Promise.all( [
		page.waitForURL( ( url ) => ! url.pathname.endsWith( '/wp-login.php' ) && ! url.pathname.endsWith( 'wp-login.php' ) || url.search.includes( 'action=confirm_admin_email' ), { timeout: 15000 } ),
		page.click( '#wp-submit' ),
	] );

	// Handle WordPress "Administration Email Verification" screen if prompted
	if ( page.url().includes( 'action=confirm_admin_email' ) ) {
		const actionBtn = page.locator(
			'a:has-text("The email is correct"), a:has-text("Remind me later"), .admin-email__actions-primary a'
		);
		if ( ( await actionBtn.count() ) > 0 ) {
			await Promise.all( [
				page.waitForNavigation( { waitUntil: 'domcontentloaded' } ).catch( () => {} ),
				actionBtn.first().click(),
			] );
		}
	}
}

/**
 * Log in to WordPress Admin.
 */
export async function loginAsAdmin( page: Page, credentials = getAdminCredentials() ): Promise<void> {
	await login( page, credentials );
	await expect( page ).toHaveURL( /.*\/wp-admin\/.*/ );
}

/**
 * Log in as Student.
 */
export async function loginAsStudent( page: Page, credentials = getStudentCredentials() ): Promise<void> {
	await login( page, credentials );
}

/**
 * Log in as Instructor.
 */
export async function loginAsInstructor( page: Page, credentials = getInstructorCredentials() ): Promise<void> {
	await login( page, credentials );
}

/**
 * Log out from current session.
 */
export async function logout( page: Page ): Promise<void> {
	await page.goto( '/wp-login.php?action=logout' );
	const confirmLogout = page.locator( 'a:has-text("log out"), a:has-text("Log Out")' );
	if ( ( await confirmLogout.count() ) > 0 ) {
		await confirmLogout.first().click();
	}
}

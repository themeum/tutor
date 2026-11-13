import { test, expect } from '@playwright/test';
import { loginAsAdmin } from '../support/auth';

test.describe( 'Tutor LMS E2E Smoke Tests', () => {
	test( 'should load the home page under dedicated test environment', async ( { page } ) => {
		const response = await page.goto( '/' );
		expect( response?.status() ).toBeLessThan( 400 );
	} );

	test( 'should display the WordPress login page', async ( { page } ) => {
		await page.goto( '/wp-login.php' );
		await expect( page.locator( '#loginform' ) ).toBeVisible();
		await expect( page.locator( 'input#user_login' ) ).toBeVisible();
	} );

	test( 'should log in as admin and access Tutor LMS dashboard', async ( { page } ) => {
		await loginAsAdmin( page );

		// Navigate to Tutor LMS courses dashboard
		await page.goto( '/wp-admin/admin.php?page=tutor' );
		await expect( page ).toHaveURL( /.*page=tutor.*/ );

		// Check Tutor admin courses table is present
		const coursesTable = page.locator( 'table' );
		await expect( coursesTable.first() ).toBeVisible();
	} );
} );

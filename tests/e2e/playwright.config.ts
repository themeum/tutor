import { defineConfig, devices } from '@playwright/test';
import * as dotenv from 'dotenv';
import * as path from 'path';

// Load .env from plugin root
dotenv.config( { path: path.resolve( __dirname, '../../.env' ) } );

const baseURL = process.env.WP_BASE_URL || process.env.CYPRESS_base_url || 'https://tutor-dev.local';

export default defineConfig( {
	testDir: './specs',
	timeout: 30 * 1000,
	expect: {
		timeout: 5000,
	},
	fullyParallel: false,
	forbidOnly: !! process.env.CI,
	retries: process.env.CI ? 2 : 0,
	workers: 1,
	reporter: [ [ 'list' ], [ 'html', { open: 'never', outputFolder: 'tests/e2e/report' } ] ],

	use: {
		baseURL,
		ignoreHTTPSErrors: true, // Allow local self-signed SSL certificates (*.local)
		trace: 'on-first-retry',
		screenshot: 'only-on-failure',
		video: 'retain-on-failure',
		// Custom header ensuring WordPress serves requests from the dedicated test database (local_test)
		extraHTTPHeaders: {
			'X-WP-Test-Env': 'playwright',
		},
	},

	projects: [
		{
			name: 'chromium',
			use: { ...devices['Desktop Chrome'] },
		},
	],
} );

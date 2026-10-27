import { execFileSync } from 'child_process';
import * as path from 'path';

export interface PageUrls {
	site_url: string;
	cart: string;
	checkout: string;
	dashboard: string;
	courses: string;
	student_register: string;
	instructor_register: string;
	login: string;
	admin_orders: string;
	admin_settings: string;
	admin_monetization: string;
	admin_payment_settings: string;
}

let cachedUrls: PageUrls | null = null;

/**
 * Fetch dynamic page URLs by executing `tests/support/page-urls.php`.
 * This reads actual page permalinks directly from the active test database.
 */
export function getPageUrls( forceRefresh = false ): PageUrls {
	if ( cachedUrls && ! forceRefresh ) {
		return cachedUrls;
	}

	const scriptPath = path.resolve( __dirname, '../../support/page-urls.php' );
	const output = execFileSync( 'php', [ scriptPath ], {
		encoding: 'utf-8',
		env: { ...process.env, WP_TEST_ENV: '1' },
	} );

	const jsonStart = output.indexOf( '{' );
	const jsonEnd = output.lastIndexOf( '}' );

	if ( jsonStart === -1 || jsonEnd === -1 ) {
		throw new Error( `Failed to parse dynamic URLs from page-urls.php:\n${ output }` );
	}

	const jsonContent = output.substring( jsonStart, jsonEnd + 1 );
	cachedUrls = JSON.parse( jsonContent ) as PageUrls;
	return cachedUrls;
}

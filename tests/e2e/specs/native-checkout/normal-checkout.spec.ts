import { test, expect } from '@playwright/test';
import { loginAsAdmin, loginAsStudent } from '../../support/auth';
import { getPageUrls } from '../../support/urls';
import {
	addCurrentCourseToCart,
	extractAmount,
	fillBillingForm,
	selectPaymentMethod,
	acceptTermsIfPresent,
	submitPayNow,
} from '../../support/checkout-helpers';

test.describe.serial( 'Tutor LMS Native Checkout E2E - Normal Checkout', () => {
	const urls = getPageUrls();
	let placedOrderId: string;
	let expectedTotal = 0;

	const testCourses = [
		{ slug: '/courses/mastering-react-19/', expectedPrice: 40.0 },
		{ slug: '/courses/advanced-typescript-guide/', expectedPrice: 45.0 },
		{ slug: '/courses/wordpress-playwright-e2e-course/', expectedPrice: 75.0 },
	];

	test( 'should verify dynamic page URLs are fetched via PHP from test environment', () => {
		expect( urls.cart ).toContain( '/cart' );
		expect( urls.checkout ).toContain( '/checkout' );
		expect( urls.admin_orders ).toContain( 'page=tutor_orders' );
	} );

	test( 'should log in as student and add 3 paid courses to cart', async ( { page } ) => {
		await loginAsStudent( page );

		for ( const course of testCourses ) {
			await page.goto( course.slug );
			await expect( page.locator( 'h1, .tutor-course-header-h1' ).first() ).toBeVisible();
			await addCurrentCourseToCart( page );
		}

		// Navigate to dynamic cart URL
		await page.goto( urls.cart );
		await expect( page ).toHaveURL( new RegExp( urls.cart.replace( /[.*+?^${}()|[\]\\]/g, '\\$&' ) ) );

		// Verify 3 courses are listed in cart
		const cartItems = page.locator( '.tutor-cart-course-item' );
		await expect( cartItems ).toHaveCount( 3 );

		// Verify Cart Grand Total matches sum of course prices ($40 + $45 + $75 = $160)
		const grandTotalText = await page
			.locator( '.tutor-cart-summery-item:has-text("Grand total")' )
			.locator( 'div' )
			.nth( 1 )
			.innerText();

		const cartTotal = extractAmount( grandTotalText );
		expectedTotal = testCourses.reduce( ( sum, c ) => sum + c.expectedPrice, 0 );
		expect( cartTotal ).toBeCloseTo( expectedTotal, 2 );
	} );

	test( 'should proceed to dynamic checkout, select manual payment, and place order successfully', async ( { page } ) => {
		await loginAsStudent( page );
		await page.goto( urls.cart );

		// Click Proceed to checkout
		const checkoutBtn = page.locator(
			'a[data-cy="tutor-native-checkout-button"], a.tutor-btn:has-text("Proceed to checkout")'
		);
		await expect( checkoutBtn ).toBeVisible();
		await checkoutBtn.click();

		// Ensure we are on the dynamic checkout page
		await expect( page ).toHaveURL( new RegExp( urls.checkout.replace( /[.*+?^${}()|[\]\\]/g, '\\$&' ) ) );

		// Verify checkout grand total equals cart total
		const checkoutTotalElem = page.locator(
			'.tutor-checkout-summary-item:has-text("Grand Total"), .tutor-checkout-grand-total'
		);
		await expect( checkoutTotalElem.first() ).toBeVisible();
		const checkoutTotalText = await checkoutTotalElem.first().innerText();
		expect( extractAmount( checkoutTotalText ) ).toBeCloseTo( expectedTotal, 2 );

		// Fill billing information
		await fillBillingForm( page );

		// Select Manual Payment (Bank Transfer)
		await selectPaymentMethod( page, 'bank-transfer' );

		// Verify manual payment instructions are revealed
		const instructions = page.locator( '.tutor-payment-instructions' );
		await expect( instructions ).toBeVisible();
		await expect( instructions ).toContainText( 'Bank Account' );

		// Agree to terms and conditions if present
		await acceptTermsIfPresent( page );

		// Click Pay Now
		await submitPayNow( page );

		// Expect redirect to success screen: ?tutor_order_placement=success&order_id={id}
		await page.waitForURL( /.*tutor_order_placement=success.*/, { timeout: 15000 } );

		const currentUrl = page.url();
		const match = currentUrl.match( /order_id=(\d+)/ );
		expect( match ).not.toBeNull();
		placedOrderId = match ? match[1] : '';
		expect( Number( placedOrderId ) ).toBeGreaterThan( 0 );

		// Check order placement success template
		const successHeading = page.locator( 'h2, h3, .tutor-fs-4, .tutor-order-placement-success' );
		await expect( successHeading.first() ).toBeVisible();
	} );

	test( 'should verify the placed manual order in Admin Orders dashboard', async ( { page } ) => {
		expect( placedOrderId ).toBeDefined();
		await loginAsAdmin( page );

		await page.goto( urls.admin_orders );
		await expect( page.locator( 'table' ).first() ).toBeVisible();

		// Search for the specific order ID
		const searchInput = page.locator( 'input[name="search"], input[type="search"]' );
		if ( ( await searchInput.count() ) > 0 ) {
			await searchInput.fill( placedOrderId );
			await page.keyboard.press( 'Enter' );
			await page.waitForLoadState( 'domcontentloaded' );
		}

		// Verify order row contains order ID, payment method, and total
		const orderRow = page.locator( `tr:has-text("#${ placedOrderId }"), tr:has-text("${ placedOrderId }")` );
		await expect( orderRow.first() ).toBeVisible();
		await expect( orderRow.first() ).toContainText( /bank[- ]transfer/i );
		await expect( orderRow.first() ).toContainText( '$160.00' );
	} );
} );

import { Page, expect } from '@playwright/test';

export interface BillingData {
	firstName: string;
	lastName: string;
	email: string;
	phone: string;
	address: string;
	city: string;
	zipCode: string;
}

export const defaultBillingData: BillingData = {
	firstName: 'Jane',
	lastName: 'Doe',
	email: 'student@example.org',
	phone: '+1 555-019-2834',
	address: '123 Learning Way',
	city: 'Austin',
	zipCode: '78701',
};

/**
 * Extracts a numeric float from price string like "$40.00" or "$1,250.50".
 */
export function extractAmount( text: string ): number {
	const match = text.replace( /,/g, '' ).match( /[\d]+(\.\d+)?/ );
	if ( ! match ) {
		throw new Error( `Could not parse numeric amount from string: "${ text }"` );
	}
	return parseFloat( match[0] );
}

/**
 * Adds the course on the current single course page to cart.
 */
export async function addCurrentCourseToCart( page: Page ): Promise<void> {
	const addToCartBtn = page.locator(
		'button[data-cy="tutor-native-add-to-cart"], button.tutor-native-add-to-cart'
	);
	await expect( addToCartBtn ).toBeVisible();
	await addToCartBtn.click();

	// Wait for "View Cart" button or badge to appear confirming course was added
	const viewCartBtn = page.locator(
		'a[data-cy="tutor-native-view-cart"], a.tutor-btn:has-text("View Cart")'
	);
	await expect( viewCartBtn.first() ).toBeVisible();
}

/**
 * Fills billing address form on the checkout page.
 */
export async function fillBillingForm( page: Page, data = defaultBillingData ): Promise<void> {
	const fields = [
		{ name: 'billing_first_name', value: data.firstName },
		{ name: 'billing_last_name', value: data.lastName },
		{ name: 'billing_email', value: data.email },
		{ name: 'billing_phone', value: data.phone },
		{ name: 'billing_address', value: data.address },
		{ name: 'billing_city', value: data.city },
		{ name: 'billing_zip_code', value: data.zipCode },
	];

	for ( const field of fields ) {
		const input = page.locator( `input[name="${ field.name }"]` );
		if ( ( await input.count() ) > 0 ) {
			await input.fill( field.value );
		}
	}

	// Select country (required by HTML validation)
	const countrySelect = page.locator( 'select[name="billing_country"]' );
	if ( ( await countrySelect.count() ) > 0 ) {
		await countrySelect.selectOption( { label: 'United States' } );
		await page.waitForTimeout( 500 );
	}

	const stateSelect = page.locator( 'select[name="billing_state"]' );
	if ( ( await stateSelect.count() ) > 0 ) {
		const optionCount = await stateSelect.locator( 'option' ).count();
		if ( optionCount > 1 ) {
			await stateSelect.selectOption( { index: 1 } );
		}
	}
}

/**
 * Selects a payment method radio on the checkout page.
 */
export async function selectPaymentMethod( page: Page, methodName: string ): Promise<void> {
	const paymentLabel = page.locator(
		`.tutor-checkout-payment-item[data-payment-method="${ methodName }"], label:has(input[value="${ methodName }"])`
	);
	if ( ( await paymentLabel.count() ) > 0 ) {
		await paymentLabel.first().click();
	}

	const radio = page.locator( `input[type="radio"][name="payment_method"][value="${ methodName }"]` );
	if ( ( await radio.count() ) > 0 ) {
		await radio.check( { force: true } );
	}
}

/**
 * Agrees to checkout terms and conditions if the checkbox is rendered.
 */
export async function acceptTermsIfPresent( page: Page ): Promise<void> {
	const termsCheckbox = page.locator(
		'input#tutor_checkout_agree_to_terms, input[name="agree_to_terms"]'
	);
	if ( ( await termsCheckbox.count() ) > 0 && ( await termsCheckbox.isVisible() ) ) {
		await termsCheckbox.check( { force: true } );
	}
}

/**
 * Submits the checkout form via Pay Now button.
 */
export async function submitPayNow( page: Page ): Promise<void> {
	const payNowBtn = page.locator( 'button#tutor-checkout-pay-now-button' );
	await expect( payNowBtn ).toBeVisible();
	await payNowBtn.click();
}

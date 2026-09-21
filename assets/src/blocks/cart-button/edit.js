import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
	PanelBody,
	RadioControl,
	TextControl,
	RangeControl,
	BaseControl,
	ColorPalette,
	__experimentalToggleGroupControl as ExperimentalToggleGroupControl,
	__experimentalToggleGroupControlOption as ExperimentalToggleGroupControlOption,
	ToggleGroupControl as WpToggleGroupControl,
	ToggleGroupControlOption as WpToggleGroupControlOption,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import CartIcon from '../../../icons/cart.svg';
import BagIcon from '../../../icons/bag.svg';
import BasketIcon from '../../../icons/basket.svg';

const ToggleGroupControl = WpToggleGroupControl || ExperimentalToggleGroupControl;
const ToggleGroupControlOption = WpToggleGroupControlOption || ExperimentalToggleGroupControlOption;

const ICON_COMPONENTS = {
	cart: CartIcon,
	bag: BagIcon,
	basket: BasketIcon,
};

export default function Edit( { attributes, setAttributes } ) {
	const {
		showCount = 'if_has_items',
		customClass = 'tutor-cart-button',
		cartIcon = 'cart',
		iconSize = 20,
		iconColor,
		badgeBgColor,
		badgeTextColor,
	} = attributes;

	// Use a sample count for editor preview
	const cartCount = 3;
	const SelectedIcon = ICON_COMPONENTS[ cartIcon ] || ICON_COMPONENTS.cart;

	return (
		<>
			<InspectorControls group="settings">
				<PanelBody title={ __( 'Settings', 'tutor' ) }>
					{ ToggleGroupControl && (
						<ToggleGroupControl
							__nextHasNoMarginBottom
							__next40pxDefaultSize
							label={ __( 'Cart Icon', 'tutor' ) }
							value={ cartIcon }
							isBlock
							onChange={ ( value ) => setAttributes( { cartIcon: value } ) }
						>
							<ToggleGroupControlOption
								value="cart"
								label={ (
									<span style={ { display: 'inline-flex', alignItems: 'center', justifyContent: 'center' } }>
										<CartIcon width={ 20 } height={ 20 } />
									</span>
								) }
								aria-label={ __( 'Cart', 'tutor' ) }
							/>
							<ToggleGroupControlOption
								value="bag"
								label={ (
									<span style={ { display: 'inline-flex', alignItems: 'center', justifyContent: 'center' } }>
										<BagIcon width={ 20 } height={ 20 } />
									</span>
								) }
								aria-label={ __( 'Bag', 'tutor' ) }
							/>
							<ToggleGroupControlOption
								value="basket"
								label={ (
									<span style={ { display: 'inline-flex', alignItems: 'center', justifyContent: 'center' } }>
										<BasketIcon width={ 20 } height={ 20 } />
									</span>
								) }
								aria-label={ __( 'Basket', 'tutor' ) }
							/>
						</ToggleGroupControl>
					) }

					<RangeControl
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						label={ __( 'Icon Size', 'tutor' ) }
						value={ iconSize }
						onChange={ ( value ) => setAttributes( { iconSize: value } ) }
						min={ 16 }
						max={ 48 }
						step={ 2 }
					/>

					<RadioControl
						label={ __( 'Cart Item Count', 'tutor' ) }
						selected={ showCount }
						options={ [
							{ label: __( 'Always (even if empty)', 'tutor' ), value: 'always' },
							{ label: __( 'Only if has items', 'tutor' ), value: 'if_has_items' },
							{ label: __( 'Never', 'tutor' ), value: 'never' },
						] }
						onChange={ ( value ) => setAttributes( { showCount: value } ) }
						help={ __( 'The editor does not display the real count value, but a placeholder to indicate how it will look on the front-end.', 'tutor' ) }
					/>
					<TextControl
						label={ __( 'Custom CSS Class', 'tutor' ) }
						value={ customClass }
						onChange={ ( value ) => setAttributes( { customClass: value } ) }
						placeholder="tutor-cart-button"
					/>
				</PanelBody>
			</InspectorControls>

			<InspectorControls group="styles">
				<PanelBody title={ __( 'Colors', 'tutor' ) }>
					<BaseControl label={ __( 'Icon Color', 'tutor' ) }>
						<ColorPalette
							colors={ [] }
							value={ iconColor }
							onChange={ ( value ) => setAttributes( { iconColor: value } ) }
							disableCustomColors={ false }
							clearable={ true }
						/>
					</BaseControl>
					<BaseControl label={ __( 'Badge Background Color', 'tutor' ) }>
						<ColorPalette
							colors={ [] }
							value={ badgeBgColor }
							onChange={ ( value ) => setAttributes( { badgeBgColor: value } ) }
							disableCustomColors={ false }
							clearable={ true }
						/>
					</BaseControl>
					<BaseControl label={ __( 'Badge Text Color', 'tutor' ) }>
						<ColorPalette
							colors={ [] }
							value={ badgeTextColor }
							onChange={ ( value ) => setAttributes( { badgeTextColor: value } ) }
							disableCustomColors={ false }
							clearable={ true }
						/>
					</BaseControl>
				</PanelBody>
			</InspectorControls>

			<div { ...useBlockProps() }>
				<div className="tutor-cart-button">
					<span
						className="tutor-btn-cart"
						style={ {
							...( iconColor && { '--tutor-cart-icon-color': iconColor } ),
							...( iconSize && { '--tutor-cart-icon-size': `${ iconSize }px` } ),
						} }
					>
						<SelectedIcon viewBox="0 0 20 20" />
						{ ( showCount === 'always' || showCount === 'if_has_items' ) && (
							<span
								className="tutor-cart-count"
								style={ {
									...( badgeBgColor && { '--tutor-cart-badge-bg': badgeBgColor } ),
									...( badgeTextColor && { '--tutor-cart-badge-color': badgeTextColor } ),
								} }
							>
								{ cartCount }
							</span>
						) }
					</span>
				</div>
			</div>
		</>
	);
}

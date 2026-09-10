import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, RadioControl, TextControl, BaseControl } from '@wordpress/components';
import { ColorPalette } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export default function Edit( { attributes, setAttributes } ) {
	const { showCount, customClass, iconColor, badgeBgColor, badgeTextColor } = attributes;

	// Use a sample count for editor preview
	const cartCount = 3;

	return (
		<>
			<InspectorControls group="settings">
				<PanelBody title={ __( 'Settings', 'tutor' ) }>
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
						placeholder="cart-contents"
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
				</PanelBody>
			</InspectorControls>

			<div { ...useBlockProps() }>
				<div className="tutor-cart-button">
					<span
						className="tutor-btn-cart"
						style={ {
							...(iconColor && { '--tutor-cart-icon-color': iconColor }),
						} }
					>
							<svg
								width="20"
								height="20"
								viewBox="0 0 20 20"
								fill="none"
								xmlns="http://www.w3.org/2000/svg"
							>
								<path
									d="M6.75055 17.964C7.1915 17.964 7.54895 17.6065 7.54895 17.1656C7.54895 16.7246 7.1915 16.3672 6.75055 16.3672C6.30961 16.3672 5.95215 16.7246 5.95215 17.1656C5.95215 17.6065 6.30961 17.964 6.75055 17.964Z"
									stroke="currentColor"
									strokeWidth="1.3"
									strokeLinecap="round"
									strokeLinejoin="round"
								/>
								<path
									d="M15.5328 17.964C15.9737 17.964 16.3312 17.6065 16.3312 17.1656C16.3312 16.7246 15.9737 16.3672 15.5328 16.3672C15.0918 16.3672 14.7344 16.7246 14.7344 17.1656C14.7344 17.6065 15.0918 17.964 15.5328 17.964Z"
									stroke="currentColor"
									strokeWidth="1.3"
									strokeLinecap="round"
									strokeLinejoin="round"
								/>
								<path
									d="M2 2.03516H3.59681L5.72056 11.9513C5.79847 12.3145 6.00053 12.6391 6.29198 12.8694C6.58343 13.0996 6.94603 13.2211 7.31736 13.2128H15.1257C15.4892 13.2122 15.8415 13.0877 16.1246 12.8598C16.4076 12.6319 16.6045 12.3142 16.6826 11.9593L18 6.02717H4.4511"
									stroke="currentColor"
									strokeWidth="1.3"
									strokeLinecap="round"
									strokeLinejoin="round"
								/>
							</svg>
							{ ( showCount === 'always' || showCount === 'if_has_items' ) && (
								<span
									className="tutor-cart-count"
									style={ {
										...(badgeBgColor && { '--tutor-cart-badge-bg': badgeBgColor }),
										...(badgeTextColor && { '--tutor-cart-badge-color': badgeTextColor }),
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

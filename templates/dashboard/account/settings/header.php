<?php
/**
 * Tutor dashboard profile header
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;
use Tutor\Helpers\UrlHelper;
use Tutor\Components\Badge;
use Tutor\Components\Button;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;

$back_url = UrlHelper::back();

?>
<div class="tutor-profile-header">
	<div 
		x-data="{ 
			windowWidth: window.innerWidth,
			isDirty: {}
		}"
		class="tutor-dashboard-container tutor-flex tutor-items-center tutor-justify-between">
		<div class="tutor-profile-header-left tutor-flex tutor-items-center"
			@resize.window="windowWidth = window.innerWidth"
			@tutor-form-state-change.document="if ($event.detail.id === `tutor-${activeTab}-form`) isDirty[$event.detail.id] = $event.detail.isDirty"
		>
			<?php
				Button::make()
					->label( __( 'Back', 'tutor' ) )
					->variant( Variant::GHOST )
					->size( Size::X_SMALL )
					->icon( Icon::LEFT, 'left', 20, 20 )
					->tag( 'a' )
					->icon_only()
					->attr( 'href', esc_url( $back_url ) )
					->render();
			?>
			<h4 
				class="tutor-profile-header-title tutor-text-h4 tutor-font-semibold tutor-ml-4"
				x-text="windowWidth <= 768 ? (activeTab === 'none' ? '<?php esc_html_e( 'Settings', 'tutor' ); ?>' : tabs.find(tab => tab.id == activeTab).label) : '<?php esc_html_e( 'Settings', 'tutor' ); ?>'"
			></h4>

			<?php
				Badge::make()
					->variant( Variant::SECONDARY )
					->circle()
					->label( __( 'Unsaved changes', 'tutor' ) )
					->attr( 'x-show', 'activeTab !== "none" && isDirty[`tutor-${activeTab}-form`]' )
					->attr( 'x-cloak', '' )
					->attr( 'class', 'tutor-ml-5 tutor-md-hidden' )
					->render();
			?>
		</div>
		<div class="tutor-profile-header-right tutor-flex tutor-items-center">
			<div x-show="activeTab !== 'none' && isDirty[`tutor-${activeTab}-form`]" x-cloak>
				<?php
					Button::make()
						->label( __( 'Discard', 'tutor' ) )
						->variant( Variant::SECONDARY )
						->size( Size::X_SMALL )
						->attr( 'type', 'button' )
						->attr( '@click', 'TutorCore.form.reset(`tutor-${activeTab}-form`)' )
						->render();

					Button::make()
						->label( __( 'Save', 'tutor' ) )
						->variant( Variant::PRIMARY )
						->size( Size::X_SMALL )
						->attr( 'type', 'submit' )
						->attr( 'class', 'tutor-ml-4' )
						->attr( 'x-bind:form', 'activeTab === "none" ? "" : `tutor-${activeTab}-form`' )
						->attr( ':class', '{ \'tutor-btn-loading\': saveBillingInfoMutation?.isPending || updateProfileMutation?.isPending || saveSocialProfileMutation?.isPending }' )
						->render();
				?>
			</div>
			<div 
				class="tutor-profile-header-close tutor-md-hidden"
				@click="activeTab = 'none'"
				x-show="activeTab === 'none' || !isDirty[`tutor-${activeTab}-form`]"
			>
				<?php
					Button::make()
						->label( __( 'Close', 'tutor' ) )
						->variant( Variant::GHOST )
						->tag( 'a' )
						->icon( Icon::CROSS, 'left', 20, 20 )
						->icon_only()
						->size( Size::X_SMALL )
						->attr( 'type', 'button' )
						->attr( 'href', esc_url( $back_url ) )
						->render();
				?>
			</div>
			<div 
				class="tutor-profile-header-close tutor-hidden tutor-md-flex"
				@click="activeTab = 'none'"
				x-show="activeTab === 'none' || !isDirty[`tutor-${activeTab}-form`]"
			>
				<?php
					Button::make()
						->label( __( 'Close', 'tutor' ) )
						->variant( Variant::GHOST )
						->icon( Icon::CROSS, 'left', 20, 20 )
						->icon_only()
						->size( Size::X_SMALL )
						->attr( 'type', 'button' )
						->attr( '@click', 'activeTab = "none"' )
						->render();
				?>
			</div>
		</div>
	</div>
</div>
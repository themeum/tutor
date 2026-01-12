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
use Tutor\Components\Badge;
use Tutor\Components\Button;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;

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
			<button @click="window.history.back()" class="tutor-btn tutor-btn-ghost tutor-btn-x-small tutor-btn-icon">
				<?php tutor_utils()->render_svg_icon( Icon::LEFT ); ?>
			</button>
			<h4 
				class="tutor-profile-header-title tutor-text-h4 tutor-font-semibold tutor-ml-4"
				x-text="windowWidth <= 576 ? (activeTab === 'none' ? '<?php esc_html_e( 'Settings', 'tutor' ); ?>' : tabs.find(tab =>tab.id == activeTab).label) : '<?php esc_html_e( 'Settings', 'tutor' ); ?>'"
			></h4>

			<?php
				Badge::make()
					->variant( Variant::SECONDARY )
					->circle()
					->label( __( 'Unsaved changes', 'tutor' ) )
					->attr( 'x-show', 'activeTab !== "none" && isDirty[`tutor-${activeTab}-form`]' )
					->attr( 'x-cloak', '' )
					->attr( 'class', 'tutor-ml-5 tutor-sm-hidden' )
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
						->render();
				?>
			</div>
			<div 
				class="tutor-profile-header-close"
				@click="activeTab = 'none'"
				x-show="activeTab === 'none' || !isDirty[`tutor-${activeTab}-form`]"
			>
				<?php
					Button::make()
						->label( __( 'Close', 'tutor' ) )
						->variant( Variant::GHOST )
						->icon( Icon::CROSS )
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
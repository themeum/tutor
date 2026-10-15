<?php
/**
 * Frontend Dashboard Tour Content Template
 *
 * Loaded by Modal::template() as the modal body.
 * Alpine state is inherited from the parent x-data="tutorTour(...)" wrapper.
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 *
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\Button;
use Tutor\Components\Constants\Variant;

?>

<div class="tutor-tour-modal tutor-modal-content">
	<div class="tutor-tour-images tutor-mb-6" :data-direction="slideDirection">
		<template x-for="(slide, index) in slides" :key="index">
			<div
				class="tutor-tour-slide-image"
				:class="'tutor-slide-' + index"
				x-show="currentSlide === index"
				x-transition:enter="tutor-transition-slide-enter"
				x-transition:enter-start="tutor-transition-slide-enter-start"
				x-transition:enter-end="tutor-transition-slide-enter-end"
				x-transition:leave="tutor-transition-slide-leave"
				x-transition:leave-start="tutor-transition-slide-leave-start"
				x-transition:leave-end="tutor-transition-slide-leave-end"
			>
				<picture>
					<source media="(min-width: 768px)" :srcset="slide.imageLarge">
					<img :src="slide.imageSmall" :alt="slide.title" class="tutor-img-responsive">
				</picture>
			</div>
		</template>
	</div>

	<div class="tutor-tour-body">
		<!-- Dots -->
		<div class="tutor-tour-dots tutor-flex tutor-justify-center tutor-items-center tutor-gap-2 tutor-mb-7">
			<template x-for="(slide, index) in slides" :key="index">
				<span class="tutor-tour-dot" :class="{ 'is-active': currentSlide === index }"></span>
			</template>
		</div>

		<!-- Title -->
		<h3 class="tutor-h4 tutor-mb-6" x-text="slides[currentSlide].title"></h3>

		<!-- Actions -->
		<div class="tutor-tour-actions tutor-flex tutor-gap-4 tutor-mb-4">
			<template x-if="currentSlide === 0 && slides.length > 1">
				<?php
				Button::make()
					->label( __( 'Take the Tour', 'tutor' ) )
					->block()
					->attr( '@click', 'next' )
					->render();
				?>
			</template>
			<template x-if="currentSlide > 0 && currentSlide < slides.length - 1">
				<div class="tutor-flex tutor-gap-4 tutor-w-full">
					<?php
					Button::make()
						->label( __( 'Back', 'tutor' ) )
						->block()
						->variant( Variant::SECONDARY )
						->attr( '@click', 'back' )
						->render();

					Button::make()
						->label( __( 'Next', 'tutor' ) )
						->block()
						->attr( '@click', 'next' )
						->render();
					?>
				</div>
			</template>
			<template x-if="currentSlide === slides.length - 1">
				<?php
				Button::make()
					->label( __( 'Get Started', 'tutor' ) )
					->block()
					->attr( '@click', 'skip' )
					->render();
				?>
			</template>
		</div>
	</div>
</div>

<div
	class="tutor-tour-skip-wrapper tutor-text-center tutor-mt-4"
	:style="currentSlide < slides.length - 1 ? '' : 'opacity: 0; visibility: hidden; pointer-events: none;'"
>
	<?php
	Button::make()
		->label( __( 'Skip', 'tutor' ) )
		->variant( Variant::GHOST )
		->attr( 'class', 'tutor-tour-skip-btn' )
		->attr( '@click', 'skip' )
		->render();
	?>
</div>


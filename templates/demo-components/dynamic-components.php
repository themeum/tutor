<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Dynamic Components</title>
</head>
<body>
<div id="wpwrap">
	<?php

	use Tutor\Components\Accordion;
	use Tutor\Components\Avatar;
	use Tutor\Components\Badge;
	use Tutor\Components\Button;
	use Tutor\Components\Constants\Positions;
	use Tutor\Components\Constants\Size;
	use Tutor\Components\Constants\Variant;
	use Tutor\Components\InputField;
	use Tutor\Components\Modal;
	use Tutor\Components\Nav;
	use Tutor\Components\Pagination;
	use Tutor\Components\Popover;
	use Tutor\Components\Progress;
	use Tutor\Components\Tabs;
	use Tutor\Components\Table;
	use TUTOR\Icon;

	?>

	<!-- button component  -->
	<div class="btn-wrapper tutor-mb-12">
		<h2>Button</h2>
		<pre><code>
		&lt;?php
			Button::make()->label( 'I am a button' )->size( Size::LARGE )->variant( Variant::PRIMARY )->render();
			Button::make()->label( 'I am a button' )->size( Size::MEDIUM )->variant( Variant::PRIMARY_SOFT )->render();
			Button::make()->label( 'I am a button' )->size( Size::SMALL )->variant( Variant::SECONDARY )->render();
			Button::make()->label( 'I am a button' )->size( Size::X_SMALL)->variant( Variant::DESTRUCTIVE )->render();
			Button::make()->label( 'I am a button' )->variant(Variant::DESTRUCTIVE_SOFT )->attr( 'class', 'tutor-btn-loading' )->render(); // phpcs:ignore 
			Button::make()->label( 'I am a button' )->variant( Variant::OUTLINE )->render(); // phpcs:ignore 
			Button::make()->label( 'I am a button' )->variant( Variant::PENDING )->render(); // phpcs:ignore 
			Button::make()->size( Size::LARGE )->icon(
				'SVG' )->variant( Variant::COMPLETED )->render(); // phpcs:ignore

			Button::make()->attr( 'class', 'tutor-btn-block' )->label( 'I am a block button' )->variant( Variant::PRIMARY_SOFT )->render();
		?&gt;
		</code></pre>


		<div class="tutor-dynamic-btn-wrapper tutor-flex tutor-gap-3 tutor-items-center tutor-flex-wrap">
			<?php
			Button::make()->label( 'I am a button' )->size( Size::LARGE )->variant( Variant::PRIMARY )->render();
			Button::make()->label( 'I am a button' )->size( Size::MEDIUM )->variant( Variant::PRIMARY_SOFT )->render();
			Button::make()->label( 'I am a button' )->size( Size::SMALL )->variant( Variant::SECONDARY )->render();
			Button::make()->label( 'I am a button' )->size( Size::X_SMALL )->variant( Variant::DESTRUCTIVE )->render();
			Button::make()->label( 'I am a button' )->variant(Variant::DESTRUCTIVE_SOFT )->attr( 'class', 'tutor-btn-loading' )->render(); // phpcs:ignore 
			Button::make()->label( 'I am a button' )->variant( Variant::OUTLINE )->render(); // phpcs:ignore 
			Button::make()->label( 'I am a button' )->variant( Variant::PENDING )->render(); // phpcs:ignore 
			Button::make()->size( Size::LARGE )->icon(
				'<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M11.617 5.28089C12.9779 6.05395 14.0472 6.66146 14.809 7.21795C15.576 7.77827 16.1434 8.36389 16.3465 9.13605C16.4955 9.70222 16.4955 10.2979 16.3465 10.8641C16.1434 11.6362 15.576 12.2218 14.809 12.7821C14.0472 13.3386 12.9779 13.9461 11.6171 14.7192C10.3026 15.466 9.19413 16.0957 8.35263 16.4537C7.50438 16.8145 6.73103 16.9974 5.97943 16.7844C5.42706 16.6278 4.92447 16.3307 4.51959 15.9222C3.97012 15.3679 3.74955 14.6016 3.6452 13.6796C3.54161 12.7641 3.54162 11.5659 3.54163 10.0418V9.9583C3.54162 8.43422 3.54161 7.23596 3.6452 6.32059C3.74955 5.39847 3.97012 4.63223 4.51959 4.07784C4.92447 3.66936 5.42706 3.37227 5.97943 3.21574C6.73103 3.00276 7.50438 3.18563 8.35263 3.54643C9.19413 3.90435 10.3026 4.53409 11.617 5.28089Z" fill="currentColor"></path>
								</svg>' )->variant( Variant::COMPLETED )->render(); // phpcs:ignore

			Button::make()->attr( 'class', 'tutor-btn-block' )->label( 'I am a block button' )->variant( Variant::PRIMARY_SOFT )->render();
			?>
		</div>
	</div>
	<!-- avatar component  -->
	<div class="avatar-wrapper tutor-mb-12">
		<h2>Avatar</h2>
		<pre><code>
		&lt;?php
		Avatar::make()->src( 'https://avatar.iran.liara.run/public/14' )->size( Size::SIZE_20 )->bordered()->render();
		Avatar::make()->src( 'https://avatar.iran.liara.run/public/14' )->size( Size::SIZE_40 )->render();
		Avatar::make()->src( 'https://avatar.iran.liara.run/public/14' )->size( Size::SIZE_56 )->render();
		Avatar::make()->src( 'https://avatar.iran.liara.run/public/14' )->size( Size::SIZE_104 )->render();

		Avatar::make()->initials( 'SK' )->shape( 'square' )->size( Size::SIZE_20 )->render();
		Avatar::make()->initials( 'SK' )->shape( 'square' )->size( Size::SIZE_40 )->render();
		Avatar::make()->initials( 'SK' )->shape( 'square' )->size( Size::SIZE_56 )->render();
		?&gt;
		</code></pre>
		<div class="tutor-dynamic-btn-wrapper tutor-flex tutor-gap-3 tutor-items-center tutor-flex-wrap">
		<?php
		Avatar::make()->src( 'https://avatar.iran.liara.run/public/14' )->size( Size::SIZE_20 )->bordered()->render();
		Avatar::make()->src( 'https://avatar.iran.liara.run/public/14' )->size( Size::SIZE_40 )->render();
		Avatar::make()->src( 'https://avatar.iran.liara.run/public/14' )->size( Size::SIZE_56 )->render();
		Avatar::make()->src( 'https://avatar.iran.liara.run/public/14' )->size( Size::SIZE_104 )->render();

		Avatar::make()->initials( 'SK' )->shape( 'square' )->size( Size::SIZE_20 )->render();
		Avatar::make()->initials( 'SK' )->shape( 'square' )->size( Size::SIZE_40 )->render();
		Avatar::make()->initials( 'SK' )->shape( 'square' )->size( Size::SIZE_56 )->render();
		?>
		</div>
	</div>

	<div class="badge-wrapper tutor-mb-12">
		<h2>Badge</h2>
		<pre><code>
		&lt;?php
			Badge::make()->label( 'Primary' )->variant( Variant::PRIMARY )->icon( 'svg' )->render();
			Badge::make()->label( 'Points: 20' )->variant( Variant::SECONDARY )->render();
			Badge::make()->label( 'Completed' )->variant( Variant::COMPLETED )->circle()->render();
			Badge::make()->label( 'Cancelled' )->variant( Variant::CANCELLED )->circle()->render();
		?&gt;
		</code></pre>
		<div class="tutor-flex tutor-gap-3 tutor-items-center tutor-flex-wrap">
		<?php
			Badge::make()->label( 'Primary' )->variant( Variant::PRIMARY )->icon( '<svg width="16" height="16" viewBox="0 0 20 20" fill="none" role="presentation" aria-hidden="true"><path fill-rule="evenodd" clip-rule="evenodd" d="M2.91699 10.0004C2.91699 6.09462 6.09458 2.91699 10.0003 2.91699C13.9061 2.91699 17.0837 6.09462 17.0837 10.0004C17.0837 13.9061 13.9061 17.0837 10.0003 17.0837C6.09458 17.0837 2.91699 13.9061 2.91699 10.0004ZM4.20448 10.0006C4.20448 13.1962 6.80428 15.796 9.99993 15.796C13.1955 15.796 15.7954 13.1962 15.7954 10.0006C15.7954 6.80501 13.1956 4.20513 9.99993 4.20513C6.80428 4.20513 4.20448 6.80501 4.20448 10.0006ZM10.0001 5.92213C9.52679 5.92213 9.14171 6.30747 9.14171 6.78111C9.14171 7.25432 9.52679 7.63931 10.0001 7.63931C10.4735 7.63931 10.8585 7.25432 10.8585 6.78111C10.8585 6.30747 10.4735 5.92213 10.0001 5.92213ZM9.35615 9.571C9.35615 9.21537 9.64446 8.92706 10.0001 8.92706C10.3557 8.92706 10.644 9.21537 10.644 9.571V13.4346C10.644 13.7903 10.3557 14.0786 10.0001 14.0786C9.64446 14.0786 9.35615 13.7903 9.35615 13.4346V9.571Z" fill="currentColor"></path></svg>' )->render();
			Badge::make()->label( 'Points: 20' )->variant( Variant::SECONDARY )->render();
			Badge::make()->label( 'Completed' )->variant( Variant::COMPLETED )->circle()->render();
			Badge::make()->label( 'Cancelled' )->variant( Variant::CANCELLED )->circle()->render();
		?>
		</div>
	</div>

	<div class="progress-wrapper tutor-mb-12">
		<h2>Progress</h2>
		<pre><code>
		&lt;?php
		Progress::make()->type( 'bar' )->value( 75 )->render();
		Progress::make()->type( 'bar' )->value( 75 )->animated()->render();
		Progress::make()->type( 'bar' )->value( 50 )
		->attrs(
			array(
				'id'             => 'my-progress',
				'data-course-id' => '123',
			)
		)->render();

		Progress::make()->type( 'circle' )->value( 75 )->render();
		?&gt;
		</code></pre>
		<div class="tutor-flex tutor-gap-3 tutor-items-center tutor-flex-wrap">
		<?php
		Progress::make()->type( 'bar' )->value( 75 )->render();

		// Animated progress bar.
		Progress::make()->type( 'bar' )->value( 75 )->animated()->render();

		// With custom attributes.
		Progress::make()->type( 'bar' )->value( 50 )
		->attrs(
			array(
				'id'             => 'my-progress',
				'data-course-id' => '123',
			)
		)->render();

		Progress::make()->type( 'circle' )->value( 75 )->render();
		?>
		</div>
	</div>

	<div class="tabs-wrapper tutor-mb-12">
		<h2>Tabs</h2>
		<pre><code>
		&lt;?php
		$tabs_data = array(
			array(
				'id'      => 'lesson',
				'label'   => 'Lessons',
				'icon'    => 'book',
				'content' => '<p>This is lesson content</p>',
			),
			array(
				'id'      => 'assignments',
				'label'   => 'Assignments',
				'icon'    => 'file',
				'content' => '<p>This is assignments content</p>',
			),
			array(
				'id'      => 'quizzes',
				'label'   => 'Quizzes',
				'icon'    => 'check',
				'content' => '<p>This is quizzes content</p>',
			),
		);

		Tabs::make()
		->tabs( $tabs_data )
		->default_tab( 'quizzes' )
		->orientation( Tabs::TYPE_HORIZONTAL )
		->url_params( array( 'enabled' => true ) )
		->render();
		?&gt;
		</code></pre>
		<div class="tutor-flex tutor-gap-3 tutor-items-center tutor-flex-wrap">
		<?php
		$tabs_data = array(
			array(
				'id'      => 'lesson',
				'label'   => 'Lessons',
				'icon'    => 'book',
				'content' => '<p>This is lesson content</p>',
			),
			array(
				'id'      => 'assignments',
				'label'   => 'Assignments',
				'icon'    => 'file',
				'content' => '<p>This is assignments content</p>',
			),
			array(
				'id'      => 'quizzes',
				'label'   => 'Quizzes',
				'icon'    => 'check',
				'content' => '<p>This is quizzes content</p>',
			),
		);

		Tabs::make()
		->tabs( $tabs_data )
		->default_tab( 'quizzes' )
		->orientation( Tabs::TYPE_HORIZONTAL )
		->url_params( array( 'enabled' => true ) )
		->render();

		?>
		</div>
	</div>

	<div class="modal-wrapper tutor-mb-12">
		<h2>Modal</h2>
		<pre><code>
		&lt;?php
		Modal::make()
		->id( 'full-modal' )
		->title( 'Confirm Submission' )
		->subtitle( 'Are you sure you want to submit?' )
		->body( 'This action cannot be undone.' )
		->footer_buttons( Button::make()->label( 'Close' )->variant( 'secondary' )->size( 'sm' )->attr( '@click', 'TutorCore.modal.closeModal("full-modal")' )->render() )
		->footer_alignment( 'right' )
		->render();

		Modal::make()
		->id( 'another-modal' )
		->title( 'Components' )
		->template( tutor()->path . 'templates/demo-components/avatar.php' )
			->footer_buttons(
				Button::make()->label( 'Close' )->variant( 'secondary' )->size( 'sm' )->attr( '@click', 'TutorCore.modal.closeModal("another-modal")' )->render()
			)
		->footer_alignment( 'center' )
		->render();

		$content = 'Hey I am headless 🤯! Footless 👣!';
		$content .= Button::make()->label( 'Close me' )->attr( 'class', 'tutor-btn-block' )->attr( '@click', 'TutorCore.modal.closeModal("headless-modal")' )->get();

		Modal::make()
		->id( 'headless-modal' )
		->closeable( false )
		->body( $content )
		->render();
		?&gt;
		</code></pre>
		<div class="tutor-flex tutor-gap-6">
			<?php
				Button::make()->label( 'Open Modal' )->attr( 'onclick', 'TutorCore.modal.showModal("full-modal")' )->render();
				Button::make()->label( 'Another Modal' )->variant( 'destructive' )->attr( 'onclick', 'TutorCore.modal.showModal("another-modal")' )->render();
				Button::make()->label( 'Headless Modal' )->variant( 'primary-soft' )->attr( 'onclick', 'TutorCore.modal.showModal("headless-modal")' )->render();
			?>
					
		</div>
		<?php
		Modal::make()
		->id( 'full-modal' )
		->title( 'Confirm Submission' )
		->subtitle( 'Are you sure you want to submit?' )
		->body( 'This action cannot be undone.' )
		->footer_buttons( Button::make()->label( 'Close' )->variant( 'secondary' )->size( 'sm' )->attr( '@click', 'TutorCore.modal.closeModal("full-modal")' )->get() )
		->footer_alignment( 'right' )
		->render();

		Modal::make()
		->id( 'another-modal' )
		->title( 'Components' )
		->template( tutor()->path . 'templates/demo-components/components/avatar.php' )
			->footer_buttons(
				Button::make()->label( 'Close' )->variant( 'secondary' )->size( 'sm' )->attr( '@click', 'TutorCore.modal.closeModal("another-modal")' )->get()
			)
		->footer_alignment( 'center' )
		->render();

		$content  = 'Hey I am headless 🤯! Footless 👣!';
		$content .= Button::make()->label( 'Close me' )->attr( 'class', 'tutor-btn-block' )->attr( '@click', 'TutorCore.modal.closeModal("headless-modal")' )->get();

		Modal::make()
		->id( 'headless-modal' )
		->closeable( false )
		->body( $content )
		->render();
		?>
		</div>
	<div class="accordion-wrapper tutor-mb-12">
		<h2>Accordion</h2>
		<pre><code>
		&lt;?php
		Accordion::make()
			->add_item( 'About Course', '<h2>Description...</h2>' )
			->render();
			echo "<br>";

		Accordion::make()
			->add_item( 'About Course', '<p>Description...</p>' )
			->add_item( 'Requirements', '<p>Prerequisites...</p>' )
			->add_item( 'Instructor', '<p>Meet your instructor...</p>' )
			->default_open( array( 0 ) )
			->render();
		echo "<br>";

		// With custom icon and template.
		Accordion::make()
			->add_item( 'Details', '', 'path/to/template.php', 'custom-icon' )
			->allow_multiple( false )
			->render();
		?&gt;
		</code></pre>
		<?php
		Accordion::make()
			->add_item( 'About Course', '<h2>Description...</h2>' )
			->render();
			echo '<br>';

		Accordion::make()
			->add_item( 'About Course', '<p>Description...</p>' )
			->add_item( 'Requirements', '<p>Prerequisites...</p>' )
			->add_item( 'Instructor', '<p>Meet your instructor...</p>' )
			->default_open( array( 0 ) )
			->render();
		echo '<br>';

		// With custom icon and template.
		Accordion::make()
			->add_item( 'Details', '', 'path/to/template.php', 'custom-icon' )
			->allow_multiple( false )
			->render();
		?>
	</div>
	<!-- table component -->
	<div class="tutor-bg-white tutor-py-6 tutor-px-6 tutor-mb-12">
		<h2>Table</h2>
		<br>
		<pre><code>&lt;php
			$heading = array(
				array(
					'content' => __( 'Quiz Info', 'tutor' ),
				),
				array(
					'content' => __( 'Marks', 'tutor' ),
				),
			);

			$content = array(
				array(
					'columns' => array(
						array(
							'content' => '&lt;div class="tutor-flex tutor-gap-3 tutor-items-center">
								' . tutor_utils()->get_svg_icon( Icon::QUESTION_CIRCLE ) . __( 'Questions', 'tutor' ) . '
							&lt;/div&gt;',
						),
						array( 'content' => 20 ),
					),
				),
			);

			echo Table::make()
				->headings( $heading )
				->contents( $content )
				->attributes( 'tutor-table-wrapper tutor-table-column-borders tutor-mb-6' )
				->render();
		</code></pre>
		<?php
		$heading = array(
			array(
				'content' => __( 'Quiz Info', 'tutor' ),
			),
			array(
				'content' => __( 'Marks', 'tutor' ),
			),
		);

		$content = array(
			array(
				'columns' => array(
					array(
						'content' => '<div class="tutor-flex tutor-gap-3 tutor-items-center">
							' . tutor_utils()->get_svg_icon( Icon::QUESTION_CIRCLE ) . __( 'Questions', 'tutor' ) . '
						</div>',
					),
					array( 'content' => 20 ),
				),
			),
		);

		echo Table::make()
			->headings( $heading )
			->contents( $content )
			->attributes( 'tutor-table-wrapper tutor-table-column-borders tutor-mb-6' )
			->render();
		?>
		<pre><code>&lt;php
		$content = array(
				array(
					'columns' => array(
						array(
							'content' => '&lt;div class="tutor-flex tutor-gap-3 tutor-items-center">
							' . tutor_utils()->get_svg_icon( Icon::TICK_MARK ) . __( 'Total Marks', 'tutor' ) . '
						&lt;/div&gt;',
						),
						array( 'content' => 10 ),
					),
				),
				array(
					'columns' => array(
						array(
							'content' => '&lt;div class="tutor-flex tutor-gap-3 tutor-items-center">
							' . tutor_utils()->get_svg_icon( Icon::PASSED ) . __( 'Passing Marks', 'tutor' ) . '
						&lt;/div&gt;',
						),
						array( 'content' => 6 ),
					),
				),
			);

			echo Table::make()
			->contents( $content )
			->attributes( 'tutor-table-wrapper tutor-table-column-borders tutor-mb-6' )
			->render();php&gt;
		</code></pre>

		<?php
			$content = array(
				array(
					'columns' => array(
						array(
							'content' => '<div class="tutor-flex tutor-gap-3 tutor-items-center">
							' . tutor_utils()->get_svg_icon( Icon::TICK_MARK ) . __( 'Total Marks', 'tutor' ) . '
						</div>',
						),
						array( 'content' => 10 ),
					),
				),
				array(
					'columns' => array(
						array(
							'content' => '<div class="tutor-flex tutor-gap-3 tutor-items-center">
							' . tutor_utils()->get_svg_icon( Icon::PASSED ) . __( 'Passing Marks', 'tutor' ) . '
						</div>',
						),
						array( 'content' => 6 ),
					),
				),
			);

			echo Table::make()
			->contents( $content )
			->attributes( 'tutor-table-wrapper tutor-table-column-borders tutor-mb-6' )
			->render();
			?>

	</div>
	<!-- table component -->

	<!-- popover component -->
	<div class="popover-wrapper tutor-mb-12">
		<h2>Popover</h2>
		<h3>Basic Popover</h3>
		<br>
		<pre><code>echo Popover::make()
				->title( 'Basic' )
				->body( '&lt;p&gt;This is a popover component&lt;/p&gt;' )
				->closeable( true )
				->trigger(
					Button::make()
					->label( 'Show Popover' )
					->attr( 'x-ref', 'trigger' )
					->attr( '@click', 'toggle()' )
					->size( 'medium' )
					->variant( 'primary' )
					->get()
				)
				->render();
			</code></pre>
		<br>
		<?php
			echo Popover::make()
				->title( 'Basic' )
				->body( '<p>This is a popover component</p>' )
				->closeable( true )
				->trigger(
					Button::make()
					->label( 'Show Popover' )
					->attr( 'x-ref', 'trigger' )
					->attr( '@click', 'toggle()' )
					->size( 'medium' )
					->variant( 'primary' )
					->get()
				)
				->render();

			?>
		<br>
		<h3> Popover with placement variations</h3>
		<br>
		<pre><code>$button = Button::make()->attr( 'x-ref', 'trigger' )->attr( '@click', 'toggle()' )->size( 'small' )->variant( 'secondary' );

		$top_button    = $button->label( 'Top' )->get();
		$right_button  = $button->label( 'Right' )->get();
		$left_button   = $button->label( 'Left' )->get();
		$bottom_button = $button->label( 'Bottom' )->get();

		echo Popover::make()
			->body( '&lt;p&gt;Right component&lt;/p&gt;' )
			->trigger( $right_button )
			->placement( 'right' )
			->render();
		echo Popover::make()
			->body( '&lt;p&gt;Left component&lt;/p&gt;' )
			->trigger( $left_button )
			->placement( 'left' )
			->render();
		echo Popover::make()
			->body( '&lt;p&gt;Top component&lt;/p&gt;' )
			->trigger( $top_button )
			->placement( 'top' )
			->render();
		echo Popover::make()
			->body( '&lt;p&gt;Bottom component&lt;/p&gt;' )
			->trigger( $bottom_button )
			->placement( 'bottom' )
			->render();
		</code></pre>
		<br>
		<div class="tutor-flex tutor-align-center tutor-gap-7">
			<?php
			$button = Button::make()->attr( 'x-ref', 'trigger' )->attr( '@click', 'toggle()' )->size( 'small' )->variant( 'secondary' );

			$top_button    = $button->label( 'Top' )->get();
			$right_button  = $button->label( 'Right' )->get();
			$left_button   = $button->label( 'Left' )->get();
			$bottom_button = $button->label( 'Bottom' )->get();

			echo Popover::make()
				->body( '<p>Right component</p>' )
				->trigger( $right_button )
				->placement( Positions::RIGHT )
				->render();
			echo Popover::make()
				->body( '<p>Top component</p>' )
				->trigger( $top_button )
				->placement( Positions::TOP )
				->render();
			echo Popover::make()
				->body( '<p>Bottom component</p>' )
				->trigger( $bottom_button )
				->placement( Positions::BOTTOM )
				->render();
			echo Popover::make()
				->body( '<p>Left component</p>' )
				->trigger( $left_button )
				->placement( Positions::LEFT )
				->render();
			?>
		</div>
		<br>
		<h3>Popover with footer</h3>
		<br>
		<pre><code>$footer_buttons = array(
			Button::make()->label( 'Cancel' )->size( 'medium' )->variant( 'secondary' )->get(),
			Button::make()->label( 'Delete' )->size( 'medium' )->variant( 'destructive' )->attr( '@click', 'hide()' )->get(),
		);

		echo Popover::make()
		->title( 'Confirm Action' )
		->body( '&lt;p&gt;Are you sure you want to delete this item? This action cannot be undone.&lt;/p&gt;' )
		->footer( $footer_buttons )
		->dismissible( false )
		->trigger(
			Button::make()
			->label( 'Popover Footer' )
			->attr( 'x-ref', 'trigger' )
			->attr( '@click', 'toggle()' )
			->size( 'medium' )
			->variant( 'destructive' )
			->render()
		)
		->render();
		</code></pre>
		<br>
		<?php
			$footer_buttons = array(
				Button::make()->label( 'Cancel' )->size( 'medium' )->variant( 'secondary' )->get(),
				Button::make()->label( 'Delete' )->size( 'medium' )->variant( 'destructive' )->attr( '@click', 'hide()' )->get(),
			);

			echo Popover::make()
			->title( 'Confirm Action' )
			->body( '<p>Are you sure you want to delete this item? This action cannot be undone.</p>' )
			->footer( $footer_buttons )
			->dismissible( false )
			->trigger(
				Button::make()
				->label( 'Popover Footer' )
				->attr( 'x-ref', 'trigger' )
				->attr( '@click', 'toggle()' )
				->size( 'medium' )
				->variant( 'destructive' )
				->get()
			)
			->render();

			?>
		<br>
		<h3>Popover with menu</h3>
		<br>
		<pre><code>$kebab_button = Button::make()->size( 'medium' )->icon( tutor_utils()->get_svg_icon( Icon::THREE_DOTS_VERTICAL, 24, 24 ) )->attr( 'x-ref', 'trigger' )->attr( '@click', 'toggle()' )->variant( 'secondary' )->get();
			echo Popover::make()
				->trigger( $kebab_button )
				->menu_item(
					array(
						'tag'     => 'a',
						'content' => 'Edit',
						'icon'    => tutor_utils()->get_svg_icon( Icon::EDIT_2 ),
						'attr'    => array( 'href' => '#' ),
					)
				)
				->menu_item(
					array(
						'tag'     => 'a',
						'content' => 'Delete',
						'icon'    => tutor_utils()->get_svg_icon( Icon::DELETE_2 ),
						'attr'    => array( 'href' => '#' ),
					)
				)
				->render();
		</code></pre>
		<br>
		<?php
			$kebab_button = Button::make()->size( 'medium' )->icon( tutor_utils()->get_svg_icon( Icon::THREE_DOTS_VERTICAL, 24, 24 ) )->attr( 'x-ref', 'trigger' )->attr( '@click', 'toggle()' )->variant( 'secondary' )->get();
			echo Popover::make()
				->trigger( $kebab_button )
				->menu_item(
					array(
						'tag'     => 'a',
						'content' => 'Edit',
						'icon'    => tutor_utils()->get_svg_icon( Icon::EDIT_2 ),
						'attr'    => array( 'href' => '#' ),
					)
				)
				->menu_item(
					array(
						'tag'     => 'a',
						'content' => 'Delete',
						'icon'    => tutor_utils()->get_svg_icon( Icon::DELETE_2 ),
						'attr'    => array( 'href' => '#' ),
					)
				)
				->render();
			?>
	</div>
	<!-- popover component -->

	<!-- pagination component -->
	<div class="pagination-wrapper tutor-mb-12">
		<h2>Pagination</h2>
		<br>
		<pre><code>echo Pagination::make()
				->current( 2 )
				->total( 200 )
				->limit( tutor_utils()->get_option( 'pagination_per_page' ) )
				->prev( tutor_utils()->get_svg_icon( Icon::CHEVRON_LEFT_2 ) )
				->next( tutor_utils()->get_svg_icon( Icon::CHEVRON_RIGHT_2 ) )
				->render();</code></pre>
		<br>
		<?php

		echo Pagination::make()
			->current( 2 )
			->total( 200 )
			->limit( tutor_utils()->get_option( 'pagination_per_page' ) )
			->prev( tutor_utils()->get_svg_icon( Icon::CHEVRON_LEFT_2 ) )
			->next( tutor_utils()->get_svg_icon( Icon::CHEVRON_RIGHT_2 ) )
			->render();
		?>
	</div>
	<!-- pagination component -->

	<!-- nav component -->
	<div class="nav-wrapper tutor-mb-12">
		<h2>Nav Component</h2>
		<pre><code>$dropdown = array(
				'type'    => 'dropdown',
				'icon'    => Icon::ENROLLED,
				'active'  => true,
				'options' => array(
					array(
						'label'  => 'Active',
						'icon'   => Icon::PLAY_LINE,
						'url'    => '#',
						'active' => false,
					),
					array(
						'label'  => 'Enrolled',
						'icon'   => Icon::ENROLLED,
						'url'    => '#',
						'active' => true,
					),
				),
			);

			echo Nav::make()
				->items( array( $dropdown ) )
				->size( Size::SM )
				->variant( Variant::SECONDARY )
				->render();</code></pre>
		<br>
		<?php
			$wishlist = array(
				'type'   => 'link',
				'label'  => __( 'Wishlist', 'tutor' ),
				'icon'   => Icon::WISHLIST,
				'url'    => '#',
				'active' => false,
			);

			$dropdown = array(
				'type'    => 'dropdown',
				'icon'    => Icon::ENROLLED,
				'active'  => true,
				'options' => array(
					array(
						'label'  => 'Active',
						'icon'   => Icon::PLAY_LINE,
						'url'    => '#',
						'active' => false,
					),
					array(
						'label'  => 'Enrolled',
						'icon'   => Icon::ENROLLED,
						'url'    => '#',
						'active' => true,
					),
				),
			);

			echo Nav::make()
				->items( array( $wishlist, $dropdown ) )
				->size( Size::LG )
				->render();

			echo Nav::make()
				->items( array( $dropdown ) )
				->size( Size::SM )
				->variant( Variant::SECONDARY )
				->render();
			?>
	</div>
	<!-- nav component -->

	<div class="input-field-wrapper tutor-mb-12">
		<h2>Input Fields</h2>
		<pre><code>
		&lt;?php
		?&gt;</code></pre>
			<?php

			// Text input with left icon
			// InputField::make()
			// ->type('email')
			// ->name('email')
			// ->label('Email')
			// ->placeholder('Enter your email')
			// ->left_icon('<svg>...</svg>')
			// ->clearable()
			// ->help_text('We will never share your email.')
			// ->render();

			// // Disabled input
			// InputField::make()
			// ->type('text')
			// ->name('username')
			// ->label('Username')
			// ->placeholder('Enter username')
			// ->disabled()
			// ->help_text('This field is disabled.')
			// ->render();

			// // Input with error
			// InputField::make()
			// ->type('text')
			// ->name('username')
			// ->label('Username')
			// ->placeholder('Enter username')
			// ->required()
			// ->error('This field is required.')
			// ->render();

			// // Textarea
			// InputField::make()
			// ->type('textarea')
			// ->name('bio')
			// ->label('Bio')
			// ->placeholder('Tell us about yourself')
			// ->required()
			// ->clearable()
			// ->help_text('Maximum 500 characters.')
			// ->render();

			// // Small checkbox
			// InputField::make()
			// ->type('checkbox')
			// ->name('agree')
			// ->label('I agree to terms')
			// ->required()
			// ->help_text('You must agree to continue.')
			// ->render();

			// // Medium checkbox (checked)
			// InputField::make()
			// ->type('checkbox')
			// ->name('subscribe')
			// ->label('Subscribe to newsletter')
			// ->size('md')
			// ->checked()
			// ->help_text('Get weekly updates.')
			// ->render();

			// // Intermediate checkbox
			// InputField::make()
			// ->type('checkbox')
			// ->name('select_all')
			// ->label('Select All')
			// ->size('md')
			// ->checked()
			// ->intermediate()
			// ->render();

			// // Disabled checkbox
			// InputField::make()
			// ->type('checkbox')
			// ->name('locked')
			// ->label('This is locked')
			// ->size('md')
			// ->disabled()
			// ->checked()
			// ->render();

			// // Small radio
			// InputField::make()
			// ->type('radio')
			// ->name('gender')
			// ->value('male')
			// ->label('Male')
			// ->help_text('Select your gender.')
			// ->render();

			// // Medium radio
			// InputField::make()
			// ->type('radio')
			// ->name('gender')
			// ->value('female')
			// ->label('Female')
			// ->size('md')
			// ->render();

			// // Disabled radio (checked)
			// InputField::make()
			// ->type('radio')
			// ->name('status')
			// ->value('active')
			// ->label('Active')
			// ->size('md')
			// ->checked()
			// ->disabled()
			// ->render();

			// // Small switch
			// InputField::make()
			// ->type('switch')
			// ->name('notifications')
			// ->label('Enable notifications?')
			// ->help_text('Get real-time alerts.')
			// ->render();

			// // Medium switch
			// InputField::make()
			// ->type('switch')
			// ->name('dark_mode')
			// ->label('Enable dark mode?')
			// ->size('md')
			// ->checked()
			// ->help_text('Switch to dark theme.')
			// ->render();

			// // Intermediate switch
			// InputField::make()
			// ->type('switch')
			// ->name('auto_save')
			// ->label('Auto-save enabled?')
			// ->size('md')
			// ->intermediate()
			// ->help_text('Partial save mode.')
			// ->render();

			// // Disabled switch (checked)
			// InputField::make()
			// ->type('switch')
			// ->name('premium')
			// ->label('Premium features')
			// ->size('md')
			// ->checked()
			// ->disabled()
			// ->help_text('Upgrade to access.')
			// ->render();
			?>
			<form 
				x-data="tutorForm({ id: 'basic-form', mode: 'onBlur', shouldFocusError: true })"
				x-bind="getFormBindings()"
				@submit="handleSubmit(
					(data) => { 
						alert('Form submitted successfully!\\n' + JSON.stringify(data, null, 2)); 
					},
					(errors) => { 
						console.log('Form errors:', errors); 
					}
				)($event)"
				class="tutor-max-w-md"
				style="border: 1px solid; padding: 20px;"
				>
				<div class="tutor-flex tutor-flex-column tutor-gap-7">
					<?php
						InputField::make()
							->type( 'text' )
							->name( 'name' )
							->label( 'Full Name' )
							->placeholder( 'Enter your full name' )
							->required()
							->clearable()
							->help_text( 'This is a helper text.' )
							->attr( 'x-bind', "register('name', { required: 'Name is required', minLength: { value: 2, message: 'Name must be at least 2 characters' } })" )
							->render();

						InputField::make()
							->type( 'checkbox' )
							->name( 'terms' )
							->label( 'Agree with terms' )
							->required()
							->clearable()
							->help_text( 'This is a helper text.' )
							->attr( 'x-bind', "register('terms', { required: 'Gender is required', minLength: { value: 2, message: 'Name must be at least 2 characters' } })" )
							->render();
					?>
				</div>
				<div>
					<button 
						type="submit" 
						class="tutor-btn tutor-btn-primary"
						:disabled="isSubmitting"
						:class="{ 'tutor-btn-loading': isSubmitting }"
					>
						<span>Submit Form</span>
					</button>
				</div>
			</form>
		</div>
</div>


</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Dynamic Components</title>
</head>
<body>
	
<?php

use Tutor\Components\Accordion;
use Tutor\Components\Avatar;
use Tutor\Components\Badge;
use Tutor\Components\Button;
use Tutor\Components\InputField;
use Tutor\Components\Modal;
use Tutor\Components\Progress;
use Tutor\Components\Tabs;

?>
<!-- button component  -->
<div class="btn-wrapper tutor-mb-12">
	<h2>Button</h2>
	<pre><code>
	&lt;?php
		echo Button::make()->label( 'I am a button' )->size( 'large' )->variant( 'primary' )->render(); // phpcs:ignore
		echo Button::make()->label( 'I am a button' )->size( 'medium' )->variant( 'primary' )->render(); // phpcs:ignore
		echo Button::make()->label( 'I am a button' )->size( 'small' )->variant( 'primary' )->render(); // phpcs:ignore
		echo Button::make()->label( 'I am a button' )->variant( 'primary-soft' )->render(); // phpcs:ignore 
		echo Button::make()->label( 'I am a button' )->variant( 'destructive' )->render(); // phpcs:ignore 
		echo Button::make()->label( 'I am a button' )->variant( 'destructive-soft' )->render(); // phpcs:ignore 
	?&gt;
	</code></pre>


	<div class="tutor-dynamic-btn-wrapper tutor-flex tutor-gap-3 tutor-items-center tutor-flex-wrap">
		<?php
        echo Button::make()->label( 'I am a button' )->size( 'large' )->variant( 'primary' )->render(); // phpcs:ignore 
        echo Button::make()->label( 'I am a button' )->size( 'medium' )->variant( 'primary' )->render(); // phpcs:ignore 
        echo Button::make()->label( 'I am a button' )->size( 'small' )->variant( 'primary' )->render(); // phpcs:ignore 
        echo Button::make()->label( 'I am a button' )->variant( 'primary-soft' )->render(); // phpcs:ignore 
        echo Button::make()->label( 'I am a button' )->variant( 'primary-soft' )->attr( 'class', 'tutor-btn-loading' )->render(); // phpcs:ignore 
        echo Button::make()->label( 'I am a button' )->variant( 'destructive' )->render(); // phpcs:ignore 
        echo Button::make()->label( 'I am a button' )->variant( 'destructive-soft' )->render(); // phpcs:ignore 
		echo Button::make()->size( 'large' )->icon(
			'<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M11.617 5.28089C12.9779 6.05395 14.0472 6.66146 14.809 7.21795C15.576 7.77827 16.1434 8.36389 16.3465 9.13605C16.4955 9.70222 16.4955 10.2979 16.3465 10.8641C16.1434 11.6362 15.576 12.2218 14.809 12.7821C14.0472 13.3386 12.9779 13.9461 11.6171 14.7192C10.3026 15.466 9.19413 16.0957 8.35263 16.4537C7.50438 16.8145 6.73103 16.9974 5.97943 16.7844C5.42706 16.6278 4.92447 16.3307 4.51959 15.9222C3.97012 15.3679 3.74955 14.6016 3.6452 13.6796C3.54161 12.7641 3.54162 11.5659 3.54163 10.0418V9.9583C3.54162 8.43422 3.54161 7.23596 3.6452 6.32059C3.74955 5.39847 3.97012 4.63223 4.51959 4.07784C4.92447 3.66936 5.42706 3.37227 5.97943 3.21574C6.73103 3.00276 7.50438 3.18563 8.35263 3.54643C9.19413 3.90435 10.3026 4.53409 11.617 5.28089Z" fill="currentColor"></path>
                            </svg>' )->variant( 'primary-soft' )->render(); // phpcs:ignore

        echo Button::make()->attr( 'class', 'tutor-btn-block' )->label( 'I am a block button' )->variant( 'primary-soft' )->render(); // phpcs:ignore
		?>
	</div>
</div>


<!-- avatar component  -->
<div class="avatar-wrapper tutor-mb-12">
	<h2>Avatar</h2>
	<pre><code>
	&lt;?php
	echo Avatar::make()->src( 'https://avatar.iran.liara.run/public/14' )->size( 'xs' )->render();
	echo Avatar::make()->src( 'https://avatar.iran.liara.run/public/14' )->size( 'sm' )->render();
	echo Avatar::make()->src( 'https://avatar.iran.liara.run/public/14' )->size( 'md' )->render();
	echo Avatar::make()->src( 'https://avatar.iran.liara.run/public/14' )->size( 'lg' )->bordered()->render();

	echo Avatar::make()->initials( 'SK' )->shape( 'square' )->size( 'md' )->render();
	echo Avatar::make()->initials( 'SK' )->shape( 'square' )->size( 'lg' )->render();
	echo Avatar::make()->initials( 'SK' )->shape( 'square' )->size( 'xl' )->render();
	?&gt;
	</code></pre>
	<div class="tutor-dynamic-btn-wrapper tutor-flex tutor-gap-3 tutor-items-center tutor-flex-wrap">
	<?php
	echo Avatar::make()->src( 'https://avatar.iran.liara.run/public/14' )->size( 'xs' )->render();
	echo Avatar::make()->src( 'https://avatar.iran.liara.run/public/14' )->size( 'sm' )->render();
	echo Avatar::make()->src( 'https://avatar.iran.liara.run/public/14' )->size( 'md' )->render();
	echo Avatar::make()->src( 'https://avatar.iran.liara.run/public/14' )->size( 'lg' )->bordered()->render();

	echo Avatar::make()->initials( 'SK' )->shape( 'square' )->size( 'md' )->render();
	echo Avatar::make()->initials( 'SK' )->shape( 'square' )->size( 'lg' )->render();
	echo Avatar::make()->initials( 'SK' )->shape( 'square' )->size( 'xl' )->render();
	?>
	</div>
</div>
<div class="badge-wrapper tutor-mb-12">
	<h2>Badge</h2>
	<pre><code>
	&lt;?php
		echo Badge::make()->label( 'Primary' )->variant( 'primary' )->icon( 'svg icon' )->render();
		echo Badge::make()->label( 'Points: 20' )->variant( 'secondary' )->render();
		echo Badge::make()->label( 'Completed' )->variant( 'completed' )->circle()->render();
		echo Badge::make()->label( 'Cancelled' )->variant( 'cancelled' )->circle()->render();
	?&gt;
	</code></pre>
	<div class="tutor-flex tutor-gap-3 tutor-items-center tutor-flex-wrap">
	<?php
		echo Badge::make()->label( 'Primary' )->variant( 'primary' )->icon( '<svg width="16" height="16" viewBox="0 0 20 20" fill="none" role="presentation" aria-hidden="true"><path fill-rule="evenodd" clip-rule="evenodd" d="M2.91699 10.0004C2.91699 6.09462 6.09458 2.91699 10.0003 2.91699C13.9061 2.91699 17.0837 6.09462 17.0837 10.0004C17.0837 13.9061 13.9061 17.0837 10.0003 17.0837C6.09458 17.0837 2.91699 13.9061 2.91699 10.0004ZM4.20448 10.0006C4.20448 13.1962 6.80428 15.796 9.99993 15.796C13.1955 15.796 15.7954 13.1962 15.7954 10.0006C15.7954 6.80501 13.1956 4.20513 9.99993 4.20513C6.80428 4.20513 4.20448 6.80501 4.20448 10.0006ZM10.0001 5.92213C9.52679 5.92213 9.14171 6.30747 9.14171 6.78111C9.14171 7.25432 9.52679 7.63931 10.0001 7.63931C10.4735 7.63931 10.8585 7.25432 10.8585 6.78111C10.8585 6.30747 10.4735 5.92213 10.0001 5.92213ZM9.35615 9.571C9.35615 9.21537 9.64446 8.92706 10.0001 8.92706C10.3557 8.92706 10.644 9.21537 10.644 9.571V13.4346C10.644 13.7903 10.3557 14.0786 10.0001 14.0786C9.64446 14.0786 9.35615 13.7903 9.35615 13.4346V9.571Z" fill="currentColor"></path></svg>' )->render();
		echo Badge::make()->label( 'Points: 20' )->variant( 'secondary' )->render();
		echo Badge::make()->label( 'Completed' )->variant( 'completed' )->circle()->render();
		echo Badge::make()->label( 'Cancelled' )->variant( 'cancelled' )->circle()->render();
	?>
	</div>
</div>
<div class="badge-wrapper tutor-mb-12">
	<h2>Progress</h2>
	<pre><code>
	&lt;?php
		echo Badge::make()->label( 'Primary' )->variant( 'primary' )->icon( 'svg icon' )->render();
		echo Badge::make()->label( 'Points: 20' )->variant( 'secondary' )->render();
		echo Badge::make()->label( 'Completed' )->variant( 'completed' )->circle()->render();
		echo Badge::make()->label( 'Cancelled' )->variant( 'cancelled' )->circle()->render();
	?&gt;
	</code></pre>
	<div class="tutor-flex tutor-gap-3 tutor-items-center tutor-flex-wrap">
	<?php
	echo Progress::make()->type( 'bar' )->value( 75 )->render();

	// Animated progress bar
	echo Progress::make()->type( 'bar' )->value( 75 )->animated()->render();

	// With custom attributes
	echo Progress::make()->type( 'bar' )->value( 50 )
	->attrs(
		array(
			'id'             => 'my-progress',
			'data-course-id' => '123',
		)
	)->render();

	echo Progress::make()->type( 'circle' )->value( 75 )->render();
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

	echo Tabs::make()
	->tabs( $tabs_data )
	->default_tab( 'quizzes' )
	->orientation( 'horizontal' )
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

	echo Tabs::make()
	->tabs( $tabs_data )
	->default_tab( 'quizzes' )
	->orientation( 'horizontal' )
	->url_params( array( 'enabled' => true ) )
	->render();

	?>
	</div>
</div>
<div class="modal-wrapper tutor-mb-12">
	<h2>Modal</h2>
	<pre><code>
	&lt;?php
	echo Modal::make()
	  ->id( 'full-modal' )
	  ->title( 'Confirm Submission' )
	  ->subtitle( 'Are you sure you want to submit?' )
	  ->body( 'This action cannot be undone.' )
	  ->footer_buttons( Button::make()->label( 'Close' )->variant( 'secondary' )->size( 'sm' )->attr( '@click', 'TutorCore.modal.closeModal("full-modal")' )->render() )
	  ->footer_alignment( 'right' )
	  ->render();

	echo Modal::make()
	  ->id( 'another-modal' )
	  ->title( 'Components' )
	  ->template( tutor()->path . 'templates/demo-components/avatar.php' )
		->footer_buttons(
			Button::make()->label( 'Close' )->variant( 'secondary' )->size( 'sm' )->attr( '@click', 'TutorCore.modal.closeModal("another-modal")' )->render()
		)
	  ->footer_alignment( 'center' )
	  ->render();

	$content = 'Hey I am headless 🤯! Footless 👣!';
	$content .= Button::make()->label( 'Close me' )->attr( 'class', 'tutor-btn-block' )->attr( '@click', 'TutorCore.modal.closeModal("headless-modal")' )->render();

	echo Modal::make()
	  ->id( 'headless-modal' )
	  ->closeable( false )
	  ->body( $content )
	  ->render();
	?&gt;
	</code></pre>
	<div class="tutor-flex tutor-gap-6">
		<?php
			echo Button::make()->label( 'Open Modal' )->attr( 'onclick', 'TutorCore.modal.showModal("full-modal")' )->render();
			echo Button::make()->label( 'Another Modal' )->variant( 'destructive' )->attr( 'onclick', 'TutorCore.modal.showModal("another-modal")' )->render();
			echo Button::make()->label( 'Headless Modal' )->variant( 'primary-soft' )->attr( 'onclick', 'TutorCore.modal.showModal("headless-modal")' )->render();
		?>
				
	</div>
	<?php
	echo Modal::make()
	  ->id( 'full-modal' )
	  ->title( 'Confirm Submission' )
	  ->subtitle( 'Are you sure you want to submit?' )
	  ->body( 'This action cannot be undone.' )
	  ->footer_buttons( Button::make()->label( 'Close' )->variant( 'secondary' )->size( 'sm' )->attr( '@click', 'TutorCore.modal.closeModal("full-modal")' )->render() )
	  ->footer_alignment( 'right' )
	  ->render();

	echo Modal::make()
	  ->id( 'another-modal' )
	  ->title( 'Components' )
	  ->template( tutor()->path . 'templates/demo-components/components/avatar.php' )
		->footer_buttons(
			Button::make()->label( 'Close' )->variant( 'secondary' )->size( 'sm' )->attr( '@click', 'TutorCore.modal.closeModal("another-modal")' )->render()
		)
	  ->footer_alignment( 'center' )
	  ->render();

	$content  = 'Hey I am headless 🤯! Footless 👣!';
	$content .= Button::make()->label( 'Close me' )->attr( 'class', 'tutor-btn-block' )->attr( '@click', 'TutorCore.modal.closeModal("headless-modal")' )->render();

	echo Modal::make()
	  ->id( 'headless-modal' )
	  ->closeable( false )
	  ->body( $content )
	  ->render();
	?>
	</div>
</div>
<div class="accordion-wrapper tutor-mb-12">
	<h2>Accordion</h2>
	<pre><code>
	&lt;?php
  echo Accordion::make()
	  ->id( 'about-course' )
	  ->title( 'About this Course' )
	  ->content( '<p>This course provides a comprehensive overview of the subject matter, covering all essential topics and practical applications.his course provides a comprehensive overview of the subject matter, covering all essential topics and practical applications.his course provides a comprehensive overview of the subject matter, covering all essential topics and practical applications.his course provides a comprehensive overview of the subject matter, covering all essential topics and practical applications.his course provides a comprehensive overview of the subject matter, covering all essential topics and practical applications.</p>' )
	  ->open()
	  ->render();
  echo Accordion::make()
	  ->id( 'about-test' )
	  ->title( 'About this Course' )
	  ->content( '<p>This course provides a comprehensive overview of the subject matter, covering all essential topics and practical applications.his course provides a comprehensive overview of the subject matter, covering all essential topics and practical applications.his course provides a comprehensive overview of the subject matter, covering all essential topics and practical applications.his course provides a comprehensive overview of the subject matter, covering all essential topics and practical applications.his course provides a comprehensive overview of the subject matter, covering all essential topics and practical applications.</p>' )
	  ->open()
	  ->render();
	?&gt;
	</code></pre>
	<?php
	echo Accordion::make()
	  ->id( 'about-course' )
	  ->title( 'About this Course' )
	  ->content( '<p>This course provides a comprehensive overview of the subject matter, covering all essential topics and practical applications.his course provides a comprehensive overview of the subject matter, covering all essential topics and practical applications.his course provides a comprehensive overview of the subject matter, covering all essential topics and practical applications.his course provides a comprehensive overview of the subject matter, covering all essential topics and practical applications.his course provides a comprehensive overview of the subject matter, covering all essential topics and practical applications.</p>', 'wp_kses_post' )
	  ->render();
	  echo "<br/>";
	echo Accordion::make()
	  ->id( 'about-test' )
	  ->title( 'About this Course' )
	  ->content( '<h3>HTML Content</h3>', 'wp_kses_post' )
	  ->open()
	  ->render();
	?>
	</div>
</div>
<div class="input-field-wrapper tutor-mb-12">
	<h2>Input Fields</h2>
	<pre><code>
	&lt;?php
	?&gt;
	</code></pre>
	<?php

// Text input with left icon
// echo InputField::make()
//     ->type('email')
//     ->name('email')
//     ->label('Email')
//     ->placeholder('Enter your email')
//     ->left_icon('<svg>...</svg>')
//     ->clearable()
//     ->help_text('We will never share your email.')
//     ->render();

// // Disabled input
// echo InputField::make()
//     ->type('text')
//     ->name('username')
//     ->label('Username')
//     ->placeholder('Enter username')
//     ->disabled()
//     ->help_text('This field is disabled.')
//     ->render();

// // Input with error
// echo InputField::make()
//     ->type('text')
//     ->name('username')
//     ->label('Username')
//     ->placeholder('Enter username')
//     ->required()
//     ->error('This field is required.')
//     ->render();

// // Textarea
// echo InputField::make()
//     ->type('textarea')
//     ->name('bio')
//     ->label('Bio')
//     ->placeholder('Tell us about yourself')
//     ->required()
//     ->clearable()
//     ->help_text('Maximum 500 characters.')
//     ->render();

// // Small checkbox
// echo InputField::make()
//     ->type('checkbox')
//     ->name('agree')
//     ->label('I agree to terms')
//     ->required()
//     ->help_text('You must agree to continue.')
//     ->render();

// // Medium checkbox (checked)
// echo InputField::make()
//     ->type('checkbox')
//     ->name('subscribe')
//     ->label('Subscribe to newsletter')
//     ->size('md')
//     ->checked()
//     ->help_text('Get weekly updates.')
//     ->render();

// // Intermediate checkbox
// echo InputField::make()
//     ->type('checkbox')
//     ->name('select_all')
//     ->label('Select All')
//     ->size('md')
//     ->checked()
//     ->intermediate()
//     ->render();

// // Disabled checkbox
// echo InputField::make()
//     ->type('checkbox')
//     ->name('locked')
//     ->label('This is locked')
//     ->size('md')
//     ->disabled()
//     ->checked()
//     ->render();

// // Small radio
// echo InputField::make()
//     ->type('radio')
//     ->name('gender')
//     ->value('male')
//     ->label('Male')
//     ->help_text('Select your gender.')
//     ->render();

// // Medium radio
// echo InputField::make()
//     ->type('radio')
//     ->name('gender')
//     ->value('female')
//     ->label('Female')
//     ->size('md')
//     ->render();

// // Disabled radio (checked)
// echo InputField::make()
//     ->type('radio')
//     ->name('status')
//     ->value('active')
//     ->label('Active')
//     ->size('md')
//     ->checked()
//     ->disabled()
//     ->render();

// // Small switch
// echo InputField::make()
//     ->type('switch')
//     ->name('notifications')
//     ->label('Enable notifications?')
//     ->help_text('Get real-time alerts.')
//     ->render();

// // Medium switch
// echo InputField::make()
//     ->type('switch')
//     ->name('dark_mode')
//     ->label('Enable dark mode?')
//     ->size('md')
//     ->checked()
//     ->help_text('Switch to dark theme.')
//     ->render();

// // Intermediate switch
// echo InputField::make()
//     ->type('switch')
//     ->name('auto_save')
//     ->label('Auto-save enabled?')
//     ->size('md')
//     ->intermediate()
//     ->help_text('Partial save mode.')
//     ->render();

// // Disabled switch (checked)
// echo InputField::make()
//     ->type('switch')
//     ->name('premium')
//     ->label('Premium features')
//     ->size('md')
//     ->checked()
//     ->disabled()
//     ->help_text('Upgrade to access.')
//     ->render();
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
			echo InputField::make()
				->type('text')
				->name('name')
				->label('Full Name')
				->placeholder('Enter your full name')
				->required()
				->clearable()
				->help_text('This is a helper text.')
				->attr( 'x-bind', "register('name', { required: 'Name is required', minLength: { value: 2, message: 'Name must be at least 2 characters' } })")
				->render();

			echo InputField::make()
				->type('checkbox')
				->name('terms')
				->label('Agree with terms')
				->required()
				->clearable()
				->help_text('This is a helper text.')
				->attr( 'x-bind', "register('terms', { required: 'Gender is required', minLength: { value: 2, message: 'Name must be at least 2 characters' } })")
				->render();
		?>
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
		</div>

	
	</form>
	</div>
</div>

</body>
</html>

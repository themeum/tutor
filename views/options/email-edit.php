<?php
/**
 * Template for editing email template
 *
 * @since v.2.0
 *
 * @author Themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Certificate
 * @version 2.0
 */

$email_back_url = add_query_arg(
	array(
		'page'     => 'tutor_settings',
		'tab_page' => 'email',
	),
	admin_url( 'admin.php' )
);

$field_data          = $this->get_field_by_key( $_GET['edit'] );
$fields_by_type      = $this->get_field_by_type( 'toggle_switch_button' );
$user                = wp_get_current_user();
$email_template_slug = $field_data['key'];

?>
<section class="tutor-backend-settings-page email-manage-page" style="margin-left: 185px-">
	<header class="header-wrapper tutor-px-30 tutor-py-25">
		<a href="<?php echo esc_url( $email_back_url ); ?>" class="prev-page d-inline-flex align-items-center">
			<span class="tutor-v2-icon-test icon-previous-line"></span>
			<span class="text-regular-caption">Back</span>
		</a>
		<div class="header-main d-flex flex-wrap align-items-center justify-content-between">
			<div class="header-left">
				<h4 class="title d-flex align-items-center text-medium-h4 tutor-mt-10">
				<?php echo esc_html__( $field_data['label'], 'tutor' ); ?>
					<label class="tutor-form-toggle tutor-ml-20">
						<input type="checkbox" class="tutor-form-toggle-input" checked="">
						<span class="tutor-form-toggle-control"></span>
					</label>
				</h4>
				<span class="subtitle tutor-mt-5 text-regular-body d-inline-flex"><?php echo $field_data['block']; ?></span>
<?php
/*
echo '<select id="email_temlplates">';
foreach ( $fields_by_type as $field ) :
	?>
		<option <?php echo $_GET['edit'] == $field['key'] ? 'selected' : ''; ?> value="<?php echo $field['key']; ?>"><?php echo $field['label']; ?></option>
	<?php
	endforeach;
echo '</select>';
 */
?>
			</div>

			<div class="header-right d-inline-flex">
				<button class="tutor-btn tutor-is-default tutor-is-sm is-text-only">
					<span class="tutor-v2-icon-test icon-send-filled"></span>
					<span>Send a Test Mail</span>
				</button>
				<button class="tutor-btn tutor-is-sm ml-0 ml-lg-4" id="email_template_save">Save Changes</button>
			</div>
		</div>
	</header>

	<!-- .main-content.content-left -->
	<main class="main-content-wrapper d-grid" style="--col: 40%">
		<div class="main-content content-left">
			<header class="tutor-mb-30">
				<div class="title text-medium-h6 tutor-mb-10 d-flex align-items-center">
					<span> Template Content </span>
					<div class="tooltip-wrap tooltip-icon">
						<span class="tooltip-txt tooltip-right">Text to appear below the main email content. Available placeholders: {site_title},
							{site_address}</span>
					</div>
				</div>
			</header>

			<div class="content-form">
				<div class="tutor-option-single-item item-logoupload">
					<h4>Title Logo</h4>
					<div class="item-wrapper">
						<div class="tutor-option-field-row d-block">
							<div class="tutor-option-field-input image-previewer is-selected mt-0">
								<div class="d-flex logo-upload mt-0 p-0">
									<div class="logo-preview">
										<span class="preview-loading"></span>
										<img src="<?php echo tutor()->icon_dir; ?>tutor-logo-course-builder.svg" alt="course builder logo">
										<span class="delete-btn"></span>
									</div>
									<div class="logo-upload-wrap">
										<p>
											Size: <strong>200x40 pixels;</strong> File Support:
											<strong>jpg, .jpeg or .png.</strong>
										</p>
										<label for="builder-logo-upload" class="tutor-btn tutor-is-sm tutor-is-outline">
											<input type="file" name="email-title-logo" id="builder-logo-upload" accept=".jpg, .jpeg, .png, .svg">
											<span class="tutor-btn-icon tutor-v2-icon-test icon-image-filled"></span>
											<span>Upload Image</span>
										</label>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- /.tutor-option-single-item.item-logoupload -->

				<!-- .tutor-option-field-input  -->
				<div class="tutor-option-field-input field-group tutor-mb-30">
					<label class="tutor-form-label">Recipients</label>
					<input type="text" name="email-recipients" class="tutor-form-control" placeholder="demomail@gmail.com">
					<span class="tutor-mt-10 d-inline-block">
						Enter recipients (comma separated) for this email. Defaults to demo@test.com.
					</span>
				</div>

				<!-- .tutor-option-field-input  -->
				<div class="tutor-option-field-input field-group tutor-mb-30">
					<label class="tutor-form-label d-flex align-items-center">
						<span> Subject</span>
						<div class="tooltip-wrap tooltip-icon">
							<span class="tooltip-txt tooltip-right">Text to appear below the main email content. Available placeholders:
								{site_title}, {site_address}</span>
						</div>
					</label>
					<input type="text" name="email-subject" class="tutor-form-control" placeholder="[{site_title}]: New order #{order_number}">
				</div>

				<!-- .tutor-option-field-input  -->
				<div class="tutor-option-field-input field-group tutor-mb-30">
					<label class="tutor-form-label d-flex align-items-center">
						<span>Email heading </span>
						<div class="tooltip-wrap tooltip-icon">
							<span class="tooltip-txt tooltip-right">Text to appear below the main email content. Available placeholders:
								{site_title}, {site_address}, {site_url}, {order_date}, {order_number}</span>
						</div>
					</label>
					<input type="text" name="email-heading" class="tutor-form-control" placeholder="New Order: #{order_number}">
				</div>

				<!-- .tutor-option-field-input  -->
				<div class="tutor-option-field-input field-group tutor-mb-30">
					<label class="tutor-form-label d-flex align-items-start">
						<span> Additional Content </span>
						<div class="tooltip-wrap tooltip-icon">
							<span class="tooltip-txt tooltip-right">Text to appear below the main email content. Available placeholders:
								{site_title}, {site_address}, {site_url}, {order_date}, {order_number}</span>
						</div>
					</label>
					<textarea name="email-additional-message" placeholder="Congratulations on the sale." class="tutor-form-control" style="height: 116px"></textarea>
				</div>

				<!-- .tutor-option-single-item.item-email-action  -->
				<div class="tutor-option-single-item item-email-action">
							<div class="item-wrapper">
								<div class="tutor-option-field-row d-block">
									<div class="d-flex align-items-center justify-content-between">
										<div class="tutor-option-field-label">
											<h5 class="label">Action Button</h5>
										</div>
										<div class="tutor-option-field-input m-0">
											<label class="tutor-form-toggle">
												<input type="checkbox" class="tutor-form-toggle-input" checked="">
												<span class="tutor-form-toggle-control"></span>
											</label>
										</div>
									</div>
									<div class="d-block">
										<div class="tutor-option-field-input field-group tutor-mb-30-">
											<label class="tutor-form-label">Recipients</label>
											<input type="text" name="email-btn-txt" class="tutor-form-control" placeholder="demomail@gmail.com">
										</div>
										<div class="tutor-option-field-input field-group tutor-mb-30-">
											<label class="tutor-form-label">Button LInk</label>
											<input type="text" name="email-btn-url" class="tutor-form-control" placeholder="https://www.example.com">
										</div>
									</div>
								</div>
							</div>
						</div>
				<!-- /.tutor-option-single-item.item-email-action -->
			</div>
		</div>
		<!-- /.main-content.content-left -->

		<!-- .main-content.content-right -->
		<div class="main-content content-right">
			<header class="d-flex align-items-center justify-content-between flex-wrap tutor-mb-30">
				<div class="mb-3 mb-xxl-0">
					<div class="title text-medium-h6 tutor-mb-10">Template Preview</div>
				</div>
			</header>

			<!-- Email .template-preview -->
			<div class="template-preview">
				<?php
					  $this->tutor_load_email_template( $email_template_slug );
				?>

			</div>
			<!-- Email /.template-preview -->
		</div>
		<!-- /.main-content.content-right -->
	</main>
</section>

<!-- <select id="email_temlplates"> -->
<?php
	/*
	foreach ( $fields_by_type as $field ) : ?>
		<option <?php echo $_GET['edit'] == $field['key'] ? 'selected' : ''; ?> value="<?php echo $field['key']; ?>"><?php echo $field['label']; ?></option>
	<?php endforeach;  */
?>
<!-- </select> -->
<script>
	let email_temlplates = document.getElementById('email_temlplates');
	let url = new URL(window.location.href);
	if(email_temlplates){
		email_temlplates.onchange = function(e){
			url.searchParams.set('edit',e.target.value);
			window.location.href = url;
		};
	}
</script>

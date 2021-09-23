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

$field_data     = $this->get_field_by_key( $_GET['edit'] );
$fields_by_type = $this->get_field_by_type( 'toggle_switch_button' );
$user           = wp_get_current_user();

?>
<section class="tutor-backend-settings-page email-manage-page tutor-grid" style="margin-left: 185px-">
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
				<button class="tutor-btn tutor-is-sm ml-0 ml-lg-4">Save Changes</button>
			</div>
		</div>
	</header>

	<!-- .main-content.content-left -->
	<main class="main-content-wrapper d-grid" style="--col: 57.3%">
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
					  $this->tutor_load_email_template( 'to_student_course_completed' );
				?>
				<div style="
					background: #ffffff;
					box-shadow: 0px 2px 2px rgba(0, 0, 0, 0.05);
					border-radius: 10px;
					max-width: 600px;
					margin: 0 auto;
					font-family: 'SF Pro Display', sans-serif;
					font-style: normal;
					font-weight: 400;
					font-size: 13px;
					line-height: 138%;
				">
				<div style="border-bottom: 1px solid #e0e2ea; padding: 20px 50px">
					<img src="<?php echo esc_url( tutor()->icon_dir ); ?>tutor-logo-course-builder.svg" alt="" style="width: 107.39px; height: 26px" data-source="email-title-logo">
				</div>
				<div style="background-image: url(<?php echo esc_url( tutor()->v2_img_dir ); ?>email-heading.svg);background-position: top right;background-repeat: no-repeat;padding: 50px;">
					<div style="margin-bottom: 50px">
						<h6 data-source="email-subject" style="
								overflow-wrap: break-word;
								font-weight: 500;
								font-size: 20px;
								line-height: 140%;
								color: #212327;
							">
							Q&amp;A message answered
						</h6>
					</div>
					<div style="color: #212327; font-weight: 400; font-size: 16px; line-height: 162%">
						<p style="margin-bottom: 7px">Hello Jhon,</p>
						<p data-source="email-heading" styele="    color: #5b616f;
						margin-bottom: 40px;">
							The instructor has answered your question on the course -
							<span>
								Your Complete Beginner to Advanced Class.
							</span>
						</p>
					</div>

					<div style="font-weight: 400; font-size: 16px; line-height: 162%">
						<p style="color: #41454f; margin-bottom: 30px">
							Here is the answer-
						</p>
						<div data-source="email-additional-message" style="
								display: flex !important;
								border-top: 1px solid #e0e2ea;
								border-bottom: 1px solid #e0e2ea;
								padding-top: 30px;
								padding-bottom: 30px;
							">
							<span style="margin-right: 12px"><img src="<?php echo esc_url( get_avatar_url( $user->ID ) ); ?>
" alt="author" width="45" height="45" style="border-radius: 50%;"></span>
							<div>
								<div style="
										margin-bottom: 20px;
										display: flex !important;
										justify-content: space-between !important;
									">
									<span style="
											color: #212327;
											font-weight: 500;
											font-size: 16px;
											line-height: 162%;
										">James Andy</span>
									<span  style="
											color: #5b616f;
											font-weight: 400;
											font-size: 15px;
											line-height: 160%;
										">1 days ago</span>
								</div>
								<p  style="color: #41454f">
									1 days ago I help ambitious graphic designers and hand letterers level-up
									their skills and creativity. Grab freebies + tutorials here! &gt;&gt;
									https://every-tuesday.com
								</p>
							</div>
						</div>
					</div>

					<div style="
							color: #41454f;
							font-weight: 400;
							font-size: 16px;
							line-height: 162%;
							margin-top: 30px;
							text-align: center;
						">
						<p>Please click on this link to reply to the question</p>
						<a href="#" data-source="email-btn-url" style="
								background-color: #1973aa;
								border-color: #1973aa;
								color: #fff;
								padding: 10px 34px;
								cursor: pointer;
								border-radius: 6px;
								text-decoration: none;
								font-weight: 500;
								border-radius: 3px;
								border: 1px solid;
								position: relative;
								box-sizing: border-box;
								transition: 0.2s;
								line-height: 26px;
								font-size: 16px;
								margin-top: 30px;
								display: inline-flex;
								justify-content: center;
								align-items: center;
							">Reply Q&amp;A</a>
					</div>
				</div>
				<!-- /.template-body -->
			</div>
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

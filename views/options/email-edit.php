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
				<!-- <select id="email_temlplates"> -->
					<?php
					/*
					foreach ( $fields_by_type as $field ) : ?>
						<option <?php echo $_GET['edit'] == $field['key'] ? 'selected' : ''; ?> value="<?php echo $field['key']; ?>"><?php echo $field['label']; ?></option>
					<?php endforeach;  */
					?>
				<!-- </select> -->
				<span class="subtitle tutor-mt-5 text-regular-body d-inline-flex">
					<?php echo esc_html__( $field_data['block'], 'tutor' ); ?>
				</span>
			</div>

			<div class="header-right d-inline-flex">
				<button class="tutor-btn tutor-is-default tutor-is-sm is-text-only">
					<span class="tutor-v2-icon-test icon-browser-filled"></span>
					<span>Preview in Browser</span>
				</button>
				<button class="tutor-btn tutor-is-default tutor-is-sm is-text-only">
					<span class="tutor-v2-icon-test icon-send-filled"></span>
					<span>Send a Test Mail</span>
				</button>
				<button class="tutor-btn tutor-is-sm ml-0 ml-lg-4">Save Changes</button>
			</div>
		</div>
	</header>

	<!-- .main-content.content-left -->
	<main class="main-content-wrapper d-grid" style="--col: 39%">
		<div class="main-content content-left">
			<header class="tutor-mb-30">
				<div class="title text-medium-h6 tutor-mb-10">Template Content</div>
				<div class="subtitle text-regular-caption">
					Text to appear below the main email content. Available placeholders:
					<span style="font-weight: 500">
						{site_title}, {site_address}, {site_url}, {order_date}, {order_number}
					</span>
				</div>
			</header>

			<div class="content-form">
				<div class="tutor-option-field-input field-group tutor-mb-30">
					<label class="tutor-form-label">Recipients</label>
					<input type="text" class="tutor-form-control" placeholder="demomail@gmail.com">
					<span class="tutor-mt-10 d-inline-block">Enter recipients (comma separated) for this email. Defaults
						to demo@test.com.</span>
				</div>
				<div class="tutor-option-field-input field-group tutor-mb-30">
					<label class="tutor-form-label">Subject</label>
					<div class="has-tooltip d-flex align-items-center">
						<input type="text" class="tutor-form-control"
							placeholder="[{site_title}]: New order #{order_number}">
						<div class="tooltip-wrap tooltip-icon">
							<span class="tooltip-txt tooltip-right">Text to appear below the main email content.
								Available placeholders:
								{site_title}, {site_address}, {site_url}, {order_date}, {order_number}</span>
						</div>
					</div>
				</div>
				<div class="tutor-option-field-input field-group tutor-mb-30">
					<label class="tutor-form-label">Email heading</label>
					<div class="has-tooltip d-flex align-items-center">
						<input type="text" class="tutor-form-control" placeholder="New Order: #{order_number}">
						<div class="tooltip-wrap tooltip-icon">
							<span class="tooltip-txt tooltip-right">Text to appear below the main email content.
								Available placeholders:
								{site_title}, {site_address}, {site_url}, {order_date}, {order_number}</span>
						</div>
					</div>
				</div>
				<div class="tutor-option-field-input field-group tutor-mb-30">
					<label class="tutor-form-label">Additional Content</label>
					<div class="has-tooltip d-flex align-items-start">
						<textarea placeholder="Congratulations on the sale." class="tutor-form-control"
							style="height: 116px"></textarea>
						<div class="tooltip-wrap tooltip-icon">
							<span class="tooltip-txt tooltip-right">Text to appear below the main email content.
								Available placeholders:
								{site_title}, {site_address}, {site_url}, {order_date}, {order_number}</span>
						</div>
					</div>
				</div>
				<div class="tutor-option-field-input field-group tutor-mb-30">
					<label class="tutor-form-label">Choose Email Type to Sendt</label>
					<select class="tutor-form-select">
						<option selected="">Choose an option</option>
						<option value="1">One</option>
						<option value="2">Two</option>
						<option value="3">Three</option>
					</select>
				</div>
			</div>
		</div>

		<!-- .main-content.content-right -->
		<div class="main-content content-right">
			<header class="d-flex align-items-center justify-content-between flex-wrap tutor-mb-30">
				<div class="mb-3 mb-xxl-0">
					<div class="title text-medium-h6 tutor-mb-10">Html Template</div>
					<div class="subtitle text-regular-caption">
						This template has been overridden by your theme and can be found in:
						<br>
						<span style="font-weight: 500">twentytwenty/woocommerce/emails/admin-new-order.php</span>
					</div>
				</div>
				<button class="tutor-btn tutor-is-outline tutor-is-sm">Reset Template File</button>
			</header>

			<div class="code-preview">
				<div id="email_heading"></div>
				<div id="email_receiver"></div>
				<div id="email_body"></div>
				<div id="email_sender"></div>
				<div id="email_buttons"></div>
			</div>
		</div>
	</main>
</section>
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

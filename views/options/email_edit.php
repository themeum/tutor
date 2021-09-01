<section class="tutor-backend-settings-page email-manage-page" style="margin-left: 185px-">
    <header class="header-wrapper tutor-px-30 tutor-py-25">
        <a href="/index.html" class="prev-page d-inline-flex align-items-center">
            <span class="tutor-v2-icon-test icon-previous-line"></span>
            <span class="text-regular-caption">Back</span>
        </a>
        <div class="header-main d-flex flex-wrap align-items-center justify-content-between">
            <div class="header-left">
                <h4 class="title d-flex align-items-center text-medium-h4 tutor-mt-10">
                    Course Enrolled
                    <label class="tutor-form-toggle tutor-ml-20">
                        <input type="checkbox" class="tutor-form-toggle-input" checked="">
                        <span class="tutor-form-toggle-control"></span>
                    </label>
                </h4>
                <span class="subtitle tutor-mt-5 text-regular-body d-inline-flex">E-Mail to Students</span>
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
                    <span class="tutor-mt-10 d-inline-block">Enter recipients (comma separated) for this email. Defaults to demo@test.com.</span>
                </div>
                <div class="tutor-option-field-input field-group tutor-mb-30">
                    <label class="tutor-form-label">Subject</label>
                    <div class="has-tooltip d-flex align-items-center">
                        <input type="text" class="tutor-form-control" placeholder="[{site_title}]: New order #{order_number}">
                        <div class="tooltip-wrap tooltip-icon">
                            <span class="tooltip-txt tooltip-right">Text to appear below the main email content. Available placeholders:
                                {site_title}, {site_address}, {site_url}, {order_date}, {order_number}</span>
                        </div>
                    </div>
                </div>
                <div class="tutor-option-field-input field-group tutor-mb-30">
                    <label class="tutor-form-label">Email heading</label>
                    <div class="has-tooltip d-flex align-items-center">
                        <input type="text" class="tutor-form-control" placeholder="New Order: #{order_number}">
                        <div class="tooltip-wrap tooltip-icon">
                            <span class="tooltip-txt tooltip-right">Text to appear below the main email content. Available placeholders:
                                {site_title}, {site_address}, {site_url}, {order_date}, {order_number}</span>
                        </div>
                    </div>
                </div>
                <div class="tutor-option-field-input field-group tutor-mb-30">
                    <label class="tutor-form-label">Additional Content</label>
                    <div class="has-tooltip d-flex align-items-start">
                        <textarea placeholder="Congratulations on the sale." class="tutor-form-control" style="height: 116px"></textarea>
                        <div class="tooltip-wrap tooltip-icon">
                            <span class="tooltip-txt tooltip-right">Text to appear below the main email content. Available placeholders:
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
                <textarea placeholder="Placeholder" class="tutor-form-control" readonly="">&lt;?php
/**
 * Template for displaying certificate
 *
 * @since v.1.5.1
 *
 * @author Themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Certificate
 * @version 1.5.1
 */

get_header(); ?&gt;

    &lt;link rel="stylesheet" href="&lt;?php echo TUTOR_CERT()-&gt;url . 'assets/css/certificate-page.css'; ?&gt;"&gt;

    &lt;div class="&lt;?php tutor_container_classes(); ?&gt;"&gt;
		&lt;?php do_action('tutor_certificate/before_content'); ?&gt;

        &lt;div class="tutor-certificate-container"&gt;
            &lt;div class="tutor-certificate-img-container"&gt;
                &lt;img id="tutor-pro-certificate-preview" src="&lt;?php echo $cert_img; ?&gt;" data-is_generated="&lt;?php echo $cert_file ? 'yes' : 'no'; ?&gt;"/&gt;
            &lt;/div&gt;

            &lt;div class="tutor-certificate-sidebar"&gt;
                &lt;div class="tutor-certificate-sidebar-btn-container"&gt;
                    &lt;div class="tutor-dropdown"&gt;
                        &lt;button class="tutor-dropbtn tutor-btn tutor-button-block download-btn"&gt;&lt;?php _e('Download Certificate', 'tutor-pro'); ?&gt; &lt;i class="tutor-icon-download"&gt;&lt;/i&gt;&lt;/button&gt;
                        &lt;div class="tutor-dropdown-content"&gt;
                            &lt;ul&gt;
                                &lt;li&gt;
                                    &lt;a id="tutor-pro-certificate-download-pdf" data-cert_hash="&lt;?php echo $cert_hash; ?&gt;" data-course_id="&lt;?php echo $course-&gt;ID; ?&gt;"&gt;
                                        &lt;i class="tutor-icon-pdf"&gt;&lt;/i&gt; &lt;?php _e('PDF', 'tutor-pro'); ?&gt;
                                    &lt;/a&gt;
                                &lt;/li&gt;
                                &lt;li&gt;
                                    &lt;a href="#" id="tutor-pro-certificate-download-image"&gt;
                                        &lt;i class="tutor-icon-jpg"&gt;&lt;/i&gt; &lt;?php _e('JPG', 'tutor-pro'); ?&gt;
                                    &lt;/a&gt;
                                &lt;/li&gt;
                            &lt;/ul&gt;
                        &lt;/div&gt;
                    &lt;/div&gt;

                    &lt;div class="tutor-certificate-btn-group tutor-dropdown"&gt;
                        &lt;button class="tutor-copy-link tutor-btn bordered-btn tutor-button-block"&gt;&lt;i class="tutor-icon-copy"&gt;&lt;/i&gt; &lt;?php _e('Copy Link', 'tutor-pro'); ?&gt;&lt;/button&gt;
                        &lt;div class="tutor-share-btn"&gt;
                            &lt;button class="tutor-dropbtn tutor-btn bordered-btn tutor-button-block"&gt;&lt;i class="tutor-icon-share"&gt;&lt;/i&gt;&lt;/button&gt;
                            &lt;div class="tutor-dropdown-content"&gt;
								&lt;?php tutor_social_share(); ?&gt;
                            &lt;/div&gt;
                        &lt;/div&gt;
                    &lt;/div&gt;
                &lt;/div&gt;

                &lt;div class="tutor-certificate-sidebar-course"&gt;
                    &lt;h3&gt;&lt;?php _e('About Course', 'tutor-pro'); ?&gt;&lt;/h3&gt;
                    &lt;div class="tutor-course-loop-level"&gt;&lt;?php echo get_tutor_course_level($course-&gt;ID); ?&gt;&lt;/div&gt;
					&lt;?php
					$course_rating = tutor_utils()-&gt;get_course_rating($course-&gt;ID);
					tutor_utils()-&gt;star_rating_generator($course_rating-&gt;rating_avg);
					?&gt;

                    &lt;h1 class="course-name"&gt;&lt;a href="&lt;?php echo $course-&gt;guid; ?&gt;" class="tutor-sidebar-course-title"&gt;&lt;?php echo $course-&gt;post_title;
                    ?&gt;&lt;/a&gt;&lt;/h1&gt;
                    &lt;div class="tutor-sidebar-course-author"&gt;
                        &lt;img src="&lt;?php echo get_avatar_url($course-&gt;post_author); ?&gt;"/&gt;
                        &lt;span&gt;
                            &lt;?php _e('by', 'tutor-pro'); ?&gt;
                            &lt;a href="&lt;?php echo tutor_utils()-&gt;profile_url($course-&gt;post_author); ?&gt;"&gt;
                                &lt;strong&gt;&lt;?php echo get_the_author_meta('display_name', $course-&gt;post_author); ?&gt;&lt;/strong&gt;
                            &lt;/a&gt;
                        &lt;/span&gt;
                    &lt;/div&gt;
                &lt;/div&gt;
            &lt;/div&gt;
        &lt;/div&gt;
    &lt;/div&gt;&lt;!-- .wrap --&gt;

&lt;?php get_footer();
						</textarea>
            </div>
        </div>
    </main>
</section>
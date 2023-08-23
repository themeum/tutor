<?php
/**
 * What's new
 *
 * @package Tutor\Views
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.2.4
 */

$changelogs = array(
	'new'    => array(
		array(
			'title'  => 'E-mail background image change support added.',
			'is_pro' => true,
		),
		array(
			'title' => 'Basic editor support for profile bio change.',
		),
		array(
			'title'  => 'Instructor\'s earning summary on instructor list.',
			'is_pro' => true,
		),
		array(
			'title' => 'Latex support to lesson and quiz editor.',
		),
		array(
			'title'  => 'Delete cancelled enrollment from enrollment list.',
			'is_pro' => true,
		),
		array(
			'title' => 'Private and Schedule filter tab in courses and course bundle listing page.',
		),
	),
	'update' => array(
		array(
			'title' => 'Email address also added to Analytics CSV data along with display name.',
		),
		array(
			'title' => 'Remove force password reset form to tutor.',
		),
	),
	'fix'    => array(
		array(
			'title' => 'Quiz attempt list showing wrong.',
		),
		array(
			'title' => "Some strings aren't translatable.",
		),
		array(
			'title' => 'In the Course List page of WP Admin, the Edit menu had design issue.',
		),
		array(
			'title' => 'Instructors can make withdrawal requests greater than their available balance.',
		),
		array(
			'title' => 'Invalid or no google client ID found for Google login.',
		),
		array(
			'title' => 'Course enrollment email to student issue.',
		),
		array(
			'title' => 'HTML code appearing on the course details page enrollment box, if user use the Restrict Content Pro.',
		),
		array(
			'title' => 'Student can complete course without passing the quiz.',
		),
	),
);

function tutor_whatnew_item( $type, $log ) {
	 $obj = (object) $log;
	?>
		<li class="tutor-fs-7"><strong><?php echo esc_html( $type ); ?>:</strong> <span><?php echo esc_html( $obj->title ); ?></span> 
		<?php
		if ( isset( $obj->is_pro ) && $obj->is_pro ) :
			?>
			<span class="tutor-pro-badge">Pro</span> <?php endif; ?></li>
		<?php
}
?>

<div class="wrap">

	<div class="tutor-whats-new-wrapper">
			
			<div class="tutor-whats-new-header">
				<div class="tutor-whats-new-header-symbols tutor-symbol-left-top">
					<svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 24 24" fill="none">
						<circle opacity="0.2" cx="12" cy="12" r="12" fill="#FFC13A"/>
					</svg>	
				</div>

				<div class="tutor-whats-new-header-symbols tutor-symbol-left-bottom">
					<svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 127 85" fill="none">
						<circle opacity="0.2" cx="-0.5" cy="42.5" r="42.5" fill="#0049F8"/>
						<path opacity="0.2" d="M127 42.5C127 48.0812 125.901 53.6077 123.765 58.764C121.629 63.9204 118.499 68.6056 114.552 72.552C110.606 76.4985 105.92 79.6291 100.764 81.7649C95.6077 83.9007 90.0812 85 84.5 85C78.9188 85 73.3923 83.9007 68.2359 81.7649C63.0796 79.629 58.3944 76.4985 54.4479 72.552C50.5015 68.6055 47.3709 63.9204 45.2351 58.764C43.0993 53.6077 42 48.0812 42 42.5L84.5 42.5L127 42.5Z" fill="#0049F8"/>
					</svg>
				</div>

				<div class="tutor-whats-new-header-symbols tutor-symbol-right-top">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 96 91" fill="none">
						<path opacity="0.2" fill-rule="evenodd" clip-rule="evenodd" d="M28.5 57C44.2401 57 57 44.2401 57 28.5C57 27.3151 56.9277 26.147 56.7872 25H141C158.673 25 173 39.3269 173 57C173 74.6731 158.673 89 141 89H51C33.3269 89 19 74.6731 19 57C19 56.4609 19.0133 55.9249 19.0397 55.3923C21.9999 56.4336 25.1838 57 28.5 57ZM17.082 54.6207C18.3033 36.9531 33.0222 23 51 23H56.4697C56.5986 23.6588 56.7047 24.3258 56.7872 25H51C33.866 25 19.8772 38.4661 19.0397 55.3923C18.3755 55.1586 17.7226 54.9011 17.082 54.6207ZM17.082 54.6207C7.02594 50.2187 0 40.1798 0 28.5C0 12.7599 12.7599 0 28.5 0C42.3586 0 53.9069 9.89172 56.4697 23H141C159.778 23 175 38.2223 175 57C175 75.7777 159.778 91 141 91H51C32.2223 91 17 75.7777 17 57C17 56.2001 17.0276 55.4067 17.082 54.6207Z" fill="#0049F8"/>
					</svg>
				</div>

				<div class="tutor-whats-new-header-symbols tutor-symbol-right-bottom">
					<svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 24 24" fill="none">
						<rect opacity="0.2" x="0" y="0" width="30" height="30" fill="#FFC13A"/>
					</svg>
				</div>

				<h1>What's New 🥳 in Tutor LMS</h1>
				<p>Congratulations! You have successfully upgraded to <br>
					the latest version of <strong>Tutor LMS (v<?php echo esc_html( TUTOR_VERSION ); ?>)</strong>
				</p>
				<div class="tutor-logo-head">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 61 80" fill="none">
					<path fill-rule="evenodd" clip-rule="evenodd" d="M43.9167 50.9466C43.4065 50.9537 42.8999 50.8591 42.4266 50.6685C41.9533 50.4779 41.5227 50.1949 41.16 49.8362C40.7973 49.4775 40.5097 49.0502 40.3141 48.5793C40.1184 48.1083 40.0186 47.6032 40.0204 47.0932V38.2799C40.0204 37.2579 40.4267 36.2778 41.1499 35.5552C41.8731 34.8326 42.8539 34.4266 43.8767 34.4266C44.8994 34.4266 45.8803 34.8326 46.6035 35.5552C47.3267 36.2778 47.733 37.2579 47.733 38.2799V47.0932C47.7347 47.5998 47.6362 48.1016 47.443 48.5699C47.2498 49.0382 46.9659 49.4637 46.6074 49.8219C46.249 50.1801 45.8232 50.4638 45.3545 50.6569C44.8858 50.8499 44.3836 50.9483 43.8767 50.9466" fill="#0049F8"/>
					<path fill-rule="evenodd" clip-rule="evenodd" d="M16.0843 50.946C15.0732 50.9095 14.1133 50.4919 13.3979 49.777C12.6825 49.0621 12.2645 48.103 12.228 47.0926V38.2793C12.228 37.2573 12.6343 36.2772 13.3575 35.5546C14.0807 34.8319 15.0616 34.426 16.0843 34.426C17.1071 34.426 18.0879 34.8319 18.8111 35.5546C19.5343 36.2772 19.9406 37.2573 19.9406 38.2793V47.0926C19.9534 47.6021 19.8624 48.109 19.6732 48.5823C19.4839 49.0556 19.2003 49.4855 18.8397 49.8459C18.479 50.2063 18.0487 50.4896 17.5751 50.6787C17.1014 50.8679 16.5942 50.9588 16.0843 50.946Z" fill="#0049F8"/>
					<path fill-rule="evenodd" clip-rule="evenodd" d="M8.36661 31.6667C9.12855 30.0292 10.3302 28.635 11.8379 27.6392C13.3456 26.6435 15.1002 26.0853 16.9065 26.0267C19.4985 26.1103 21.9525 27.2137 23.7343 29.0965C25.5162 30.9794 26.4816 33.4893 26.4204 36.08V54.12C26.5389 54.9854 26.9671 55.7787 27.6257 56.353C28.2844 56.9272 29.129 57.2437 30.0032 57.2437C30.8773 57.2437 31.7219 56.9272 32.3806 56.353C33.0392 55.7787 33.4674 54.9854 33.5859 54.12V36.08C33.5211 33.4906 34.4835 30.9806 36.2633 29.0974C38.043 27.2141 40.4956 26.1103 43.0865 26.0267C44.8254 25.9964 46.5352 26.4751 48.0052 27.4038C49.4752 28.3325 50.6412 29.6706 51.3595 31.2533C53.2193 34.8947 54.1174 38.9504 53.9686 43.0359C53.8198 47.1213 52.6291 51.1011 50.5093 54.5978C48.3896 58.0944 45.4111 60.9921 41.8563 63.0159C38.3015 65.0397 34.2883 66.1227 30.1972 66.162C26.1061 66.2013 22.0727 65.1957 18.4796 63.2406C14.8865 61.2855 11.8528 58.4457 9.66612 54.9904C7.47944 51.5352 6.2123 47.579 5.98488 43.4972C5.75746 39.4154 6.57728 35.3431 8.36661 31.6667ZM23.5249 8H37.0285V13.2133C34.7736 12.6701 32.4628 12.3926 30.1433 12.3867C27.9025 12.4089 25.6688 12.641 23.4715 13.08L23.5249 8ZM60.1796 44.8933C60.1796 44.0667 60.313 43.3733 60.313 42.5467C60.3005 37.1921 58.8661 31.9367 56.1561 27.3173C53.446 22.6978 49.5576 18.8799 44.8879 16.2533V8H50.6923C51.754 8 52.7722 7.57857 53.5229 6.82843C54.2736 6.07828 54.6954 5.06087 54.6954 4C54.6954 2.93913 54.2736 1.92172 53.5229 1.17157C52.7722 0.421427 51.754 0 50.6923 0L9.741 0C8.67381 0.053895 7.6669 0.510644 6.92381 1.27792C6.18071 2.0452 5.75686 3.06577 5.73794 4.13333C5.73794 5.1942 6.15969 6.21162 6.91041 6.96176C7.66113 7.71191 8.67932 8.13333 9.741 8.13333H15.6655V16.2667C12.0388 18.2486 8.85922 20.9554 6.32477 24.2185C3.79032 27.4816 1.95526 31.231 0.933855 35.2335C-0.08755 39.2359 -0.273433 43.4057 0.387781 47.483C1.04899 51.5603 2.54315 55.458 4.7772 58.9333C15.9991 77.9333 46.3957 79.8667 56.7369 80C57.6496 79.9722 58.5253 79.6335 59.2188 79.04C59.5394 78.7137 59.79 78.3255 59.9552 77.899C60.1204 77.4726 60.1967 77.0169 60.1796 76.56V44.8933Z" fill="#0049F8"/>
					</svg>
				</div>
			</div>
			<!-- end header section -->
			
			<div class="tutor-changelog-wrapper">
				<h3><strong>Changelog (v<?php echo esc_html( TUTOR_VERSION ); ?>)</strong></h3>
				<ul class="tutor-changelog-list">
					<?php
					foreach ( $changelogs['new'] as $log ) {
						tutor_whatnew_item( 'New', $log );
					}

					foreach ( $changelogs['update'] as $log ) {
						tutor_whatnew_item( 'Update', $log );
					}

					foreach ( $changelogs['fix'] as $log ) {
						tutor_whatnew_item( 'Fix', $log );
					}
					?>
				</ul>
			</div>
			<!-- end changelog -->

			<?php if ( ! tutor()->has_pro ) : ?>
			<!-- PRO section -->
			<div class="tutor-whats-new-pro-section">
				<h2>You are not only missing these features, you are missing your revenues too!</h2>

				<div class="tutor-whats-new-pro-banner">
					<img src="https://ysn.sya.mybluehost.me/tutor-assets/course-bundle-banner.png" alt="course bundle banner">
				</div>

				<a class="tutor-whats-new-action-btn" 
					target="_blank" 
					href="https://www.themeum.com/tutor-lms/pricing/?utm_source=get_pro&amp;utm_medium=wordpress_dashboard&amp;utm_campaign=course_bundle"> 
					<span class="tutor-icon-crown"></span> Get Tutor Pro</a>
			</div>
			<!-- end pro section -->
			<?php endif; ?>
	</div>
</div>

<style>
	#wpbody-content .notice{
		display: none!important;
	}
</style>

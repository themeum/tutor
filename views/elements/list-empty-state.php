<?php
/**
 * Course empty state views
 *
 * @package Tutor\Views
 * @subpackage Tutor\ViewElements
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.5.0
 */

$subtile       = tutor_utils()->get_list_empty_state_subtitle();
$subtitle_text = '';

if ( isset( $data['sub_title'] ) ) {
	$subtitle_text = $data['sub_title'];
} elseif ( $subtile ) {
	$subtitle_text = $subtile;
}

?>
<div class="tutor-divider tutor-radius-12 tutor-overflow-hidden">
	<div class="tutor-px-32 tutor-py-64 tutor-bg-white tutor-text-center">
		<svg width="89" height="86" viewBox="0 0 89 86" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path d="M50.8334 48.816C51.5258 49.1741 52.2514 49.4829 53.0087 49.7371C62.2001 52.824 72.5341 46.7433 76.0896 36.1556C79.6451 25.5679 75.0761 14.4821 65.8842 11.3955C63.1648 10.4822 60.3456 10.3717 57.6315 10.9426C55.0115 7.08849 51.3044 4.1083 46.7526 2.57982C35.0997 -1.33339 22.1186 5.67704 16.895 18.3554C10.2225 17.854 3.88275 21.7159 1.73297 28.1177C-0.827724 35.7433 3.5265 44.0843 11.4582 46.7479C14.6647 47.8246 17.9652 47.8078 20.9285 46.9027C23.3301 49.5662 26.353 51.6257 29.8842 52.8113" stroke="#E9E9E9" stroke-width="1.1786" stroke-linecap="round" stroke-linejoin="round" stroke-dasharray="2.36 2.36"/>
			<path d="M60.1349 16.6914H32.6205C28.538 16.6914 25.2285 20.0009 25.2285 24.0833V60.2217C25.2285 64.3041 28.538 67.6136 32.6205 67.6136H60.1349C64.2173 67.6136 67.5268 64.3041 67.5268 60.2217V24.0833C67.5268 20.0009 64.2173 16.6914 60.1349 16.6914Z" fill="#E1E1E1"/>
			<path d="M55.4506 66.3964L43.5171 83.4595C42.7192 84.5999 41.148 84.8779 40.0076 84.0804C38.8672 83.2825 38.5892 81.7113 39.3867 80.5709L51.3201 63.5078L55.4506 66.3964Z" fill="white" stroke="#9197A8" stroke-width="1.1786"/>
			<path d="M49.7585 66.5389C54.7039 71.4843 62.7219 71.4843 67.6673 66.5389C72.6127 61.5936 72.6126 53.5755 67.6673 48.6302C62.7219 43.6848 54.7039 43.6848 49.7585 48.6302C44.8131 53.5755 44.8131 61.5936 49.7585 66.5389Z" fill="white" stroke="#9197A8" stroke-width="1.1786"/>
			<path d="M50.3555 54.3836C51.0051 52.7155 52.3423 51.1135 54.2075 49.9789C56.0727 48.8446 58.1104 48.3945 59.8907 48.5855" stroke="#C3C6CB" stroke-linecap="round"/>
			<rect x="31.9365" y="23.9062" width="26.8984" height="1.2" rx="0.6" fill="white"/>
			<rect x="31.9365" y="26.5547" width="15.0088" height="1.2" rx="0.6" fill="white"/>
			<rect x="47.7988" y="26.5547" width="8.68164" height="1.2" rx="0.6" fill="white"/>
			<rect x="31.9365" y="34.7227" width="26.8984" height="1.2" rx="0.6" fill="white"/>
			<rect x="31.9365" y="38.4766" width="15.0088" height="1.2" rx="0.6" fill="white"/>
		</svg>

		<h6 class="tutor-fs-6 tutor-fw-bold tutor-mb-0 tutor-mt-32">
			<?php echo esc_html( isset( $data['title'] ) ? $data['title'] : __( 'No Data Found.', 'tutor' ) ); ?>
		</h6>
		<?php if ( ! empty( $subtitle_text ) ) : ?>
		<p class="tutor-fs-7 tutor-color-hints tutor-mt-8 tutor-mb-0">
			<?php echo esc_html( $subtitle_text ); ?>
		</p>
		<?php endif; ?>
	</div>
</div>

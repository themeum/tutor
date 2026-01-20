<?php
/**
 * Attachment card component documentation.
 *
 * @package Tutor\Templates
 */

use Tutor\Components\AttachmentCard;

defined( 'ABSPATH' ) || exit;

$card_items = array(
	array(
		'file_name'      => 'Course Outline.pdf',
		'file_size'      => '2.4 MB',
		'is_downloading' => false,
	),
	array(
		'file_name'      => 'Lesson Assets.zip',
		'file_size'      => '18.8 MB',
		'is_downloading' => true,
	),
);
?>

<section class="tutor-bg-white tutor-py-6 tutor-px-8 tutor-rounded-lg tutor-shadow-sm">
	<h1 class="tutor-text-2xl tutor-font-bold tutor-mb-6">Attachment Cards</h1>
	<p class="tutor-text-gray-600 tutor-mb-8">
		Display downloadable resources with consistent metadata and a clear download state.
	</p>

	<div class="tutor-p-6 tutor-bg-gray-50 tutor-rounded-md tutor-flex tutor-flex-col tutor-gap-2">
		<?php foreach ( $card_items as $item ) : ?>
			<?php
			AttachmentCard::make()
				->file_name( $item['file_name'] )
				->file_size( $item['file_size'] )
				->is_downloadable( $item['is_downloading'] )
				->render();
			?>
		<?php endforeach; ?>
	</div>
</section>


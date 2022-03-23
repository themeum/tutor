<?php
	$per_page = tutor_utils()->get_option( 'pagination_per_page', 10 );
	$current_page = max(1, (int)tutor_utils()->avalue_dot('current_page', $_POST));
	$lesson_id = isset($_POST['lesson_id']) ? (int)$_POST['lesson_id'] : get_the_ID();

    // The comment Query
    $comments = get_comments( array(
        'post_id' => $lesson_id,
        'parent' => 0,
		'paged' => $current_page,
		'number' => $per_page
    ) );
	
	$comment_count = get_comments( array(
        'post_id' => $lesson_id,
        'parent' => 0,
		'count' => true
    ) );
?>

<div class="tutor-pagination-wrapper-replacable tutor-single-course-lesson-comments" data-lesson_id="<?php echo $lesson_id; ?>">
	<div class="tutor-fs-6 tutor-fw-medium tutor-color-black">
		<?php _e( 'Join the conversation', 'tutor' ); ?>
	</div>
	<div class="tutor-conversation tutor-mt-12 tutor-pb-20 tutor-pb-sm-48">
		<form class="tutor-comment-box tutor-mt-32" action="<?php echo get_home_url(); ?>/wp-comments-post.php" method="post">
			<input type="hidden" name="is_lesson_comment" value="true">
			<div class="comment-avatar">
				<img src="<?php echo get_avatar_url( get_current_user_id() ); ?>" alt="">
			</div>
			<div class="tutor-comment-textarea">
				<textarea placeholder="Write a comments" class="tutor-form-control" name="comment"></textarea>
				<input type="hidden" name="comment_post_ID" value="<?php echo $lesson_id; ?>"/>
				<input type="hidden" name="comment_parent" value="0"/>
			</div>
			<div class="tutor-comment-submit-btn">
				<button type="submit" class="tutor-btn tutor-btn-primary tutor-btn-sm tutor-lesson-comment">
					<?php _e('Submit', 'tutor'); ?>
				</button>
			</div>
		</form>
		<?php if ( is_array( $comments ) && count( $comments ) ) : ?>
			<?php
			foreach ( $comments as $comment ) :
				?>
				<div class="tutor-comments-list tutor-parent-comment tutor-mt-32" id="lesson-comment-<?php echo esc_attr( $comment->comment_ID ); ?>">
					<div class="comment-avatar">
						<img src="<?php echo get_avatar_url( $comment->user_id ); ?>" alt="">
					</div>
					<div class="tutor-single-comment">
						<div class="tutor-actual-comment tutor-mb-12">
							<div class="tutor-comment-author">
								<span class="tutor-fs-6 tutor-fw-bold"><?php echo $comment->comment_author; ?></span>
								<span class="tutor-fs-7 tutor-ml-12 tutor-ml-sm-10">
									<?php echo human_time_diff( strtotime( $comment->comment_date ), tutor_time() ) . __( ' ago', 'tutor' ); ?>
								</span>
							</div>
							<div class="tutor-comment-text tutor-fs-6 tutor-mt-4">
								<?php echo $comment->comment_content; ?>
							</div>
						</div>
						<div class="tutor-comment-actions tutor-ml-24">
							<span class="tutor-fs-6 tutor-color-black-70">reply</span>
							<!-- <span class="tutor-fs-6 tutor-color-black-70">like</span>
							<span class="tutor-fs-6 tutor-color-black-70">edit</span>
							<span class="tutor-fs-6 tutor-color-black-70">delete</span> -->
						</div>

						<?php
							$replies = get_comments(
								array(
									'post_id' => $lesson_id,
									'parent'  => $comment->comment_ID,
								)
							);
						?>
						<?php if ( is_array( $replies ) && count( $replies ) ) : ?>
							<?php foreach ( $replies as $reply ) : ?>
								<div class="tutor-comments-list tutor-child-comment tutor-mt-32" id="lesson-comment-<?php echo esc_attr($reply->comment_ID)?>">
									<div class="comment-avatar">
										<img src="<?php echo get_avatar_url( $reply->user_id ); ?>" alt="">
									</div>
									<div class="tutor-single-comment">
										<div class="tutor-actual-comment tutor-mb-12">
											<div class="tutor-comment-author">
												<span class="tutor-fs-6 tutor-fw-bold">
													<?php echo $reply->comment_author; ?>
												</span>
												<span class="tutor-fs-7 tutor-ml-0 tutor-ml-sm-10">
													<?php echo human_time_diff( strtotime( $reply->comment_date ), tutor_time() ) . __( ' ago', 'tutor' ); ?>
												</span>
											</div>
											<div class="tutor-comment-text tutor-fs-6 tutor-mt-4">
												<?php echo $reply->comment_content; ?>
											</div>
										</div>
									</div>
								</div>
							<?php endforeach; ?>
						<?php endif; ?>
						<form class="tutor-comment-box tutor-reply-box tutor-mt-20" action="<?php echo get_home_url(); ?>/wp-comments-post.php" method="post">
							<input type="hidden" name="is_lesson_comment" value="true">
							<div class="comment-avatar">
								<img src="<?php echo get_avatar_url( get_current_user_id() ); ?>" alt="">
							</div>
							<div class="tutor-comment-textarea">
								<textarea placeholder="Write a comments" name="comment" class="tutor-form-control"></textarea>
								<input type="hidden" name="comment_post_ID" value="<?php echo $lesson_id; ?>"/>
								<input type="hidden" name="comment_parent" value="<?php echo $comment->comment_ID; ?>"/>
							</div>
							<div class="tutor-comment-submit-btn">
								<button type="submit" class="tutor-btn tutor-btn-primary tutor-btn-sm tutor-lesson-comment-reply">
									<?php _e('Reply', 'tutor'); ?>
								</button>
							</div>
						</form>
					</div>
					<span class="tutor-comment-line"></span>
				</div>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>

	<?php 
		$pagination_data = array(
			'total_items' => $comment_count,
			'per_page'    => $per_page,
			'paged'       => $current_page,
			'ajax'		  => array(
				'action' => 'tutor_single_course_lesson_load_more',
				'lesson_id' => $lesson_id,
				'current_page_num' => $current_page
			)
		);

		$pagination_template_frontend = tutor()->path . 'templates/dashboard/elements/pagination.php';
		tutor_load_template_from_custom_path( $pagination_template_frontend, $pagination_data );
	?>
</div>
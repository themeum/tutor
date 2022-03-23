<?php foreach ( $reviews as $review ) : ?>
    <?php $profile_url = tutor_utils()->profile_url( $review->user_id, false ); ?>
    <li>
        <div>
            <div>
                <img class="tutor-avatar-circle tutor-50" src="<?php echo get_avatar_url( $review->user_id ); ?>" alt="<?php _e( 'Student Avatar', 'tutor' ); ?>" />
            </div>

            <div class="tutor-fs-6 tutor-color-black tutor-mt-16">
                <a href="<?php echo esc_url( $profile_url ); ?>" class="tutor-reviewer-name">
                    <?php echo esc_html( $review->display_name ); ?>
                </a>
            </div>

            <div class="tutor-fs-7 tutor-color-muted">
                <span class="tutor-review-time">
                    <?php echo sprintf( __( '%s ago', 'tutor' ), human_time_diff( strtotime( $review->comment_date ) ) ); ?>
                </span>
            </div>
        </div>
        
        <div>
            <?php tutor_utils()->star_rating_generator_v2( $review->rating, null, true, 'tutor-is-sm' ); ?>
            <div class="tutor-fs-7 tutor-color-black-60 tutor-mt-12 tutor-review-comment">
                <?php echo htmlspecialchars( $review->comment_content ); ?>
            </div>
        </div>
    </li>
<?php endforeach; ?>
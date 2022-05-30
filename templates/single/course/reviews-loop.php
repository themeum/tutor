<?php foreach ( $reviews as $review ) : ?>
    <?php $profile_url = tutor_utils()->profile_url( $review->user_id, false ); ?>
    <div class="tutor-review-list-item tutor-card-list-item tutor-p-24 tutor-p-lg-40">
        <div class="tutor-row">
            <div class="tutor-col-lg-3 tutor-mb-16 tutor-mb-lg-0">
                <div class="tutor-mb-12">
                    <?php echo tutor_utils()->get_tutor_avatar( $review->user_id, 'md' ); ?>
                </div>

                <div class="tutor-reviewer-name tutor-fs-6 tutor-mb-4">
                    <a href="<?php echo esc_url( $profile_url ); ?>" class="tutor-color-black">
                        <?php echo esc_html( $review->display_name ); ?>
                    </a>
                </div>

                <div class="tutor-reviewed-on tutor-fs-7 tutor-color-muted">
                    <?php echo sprintf( __( '%s ago', 'tutor' ), human_time_diff( strtotime( $review->comment_date ) ) ); ?>
                </div>
            </div>

            <div class="tutor-col-lg-9">
                <?php if($review->comment_status=='hold') : ?>
                    <div style="position:absolute; right:15px">
                        <span class="tutor-badge-label label-warning">
                            <?php echo __('Pending', 'tutor'); ?>
                        </span>
                    </div>
                <?php endif; ?>

                <?php tutor_utils()->star_rating_generator_v2( $review->rating, null, true, 'tutor-is-sm' ); ?>
                
                <div class="tutor-fs-7 tutor-color-secondary tutor-mt-12 tutor-review-comment">
                    <?php echo htmlspecialchars( $review->comment_content ); ?>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>
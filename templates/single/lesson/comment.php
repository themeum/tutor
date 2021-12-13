<?php
    // The comment Query
    $comments = get_comments( array(
        'status' => 'approve',
        'post_id' => get_the_ID(),
        'parent' => 0
    ) );
?>
<div class="text-medium-h6 color-text-primary">
    <?php _e('Join the conversation', 'tutor'); ?>
</div>
<div class="tutor-conversation tutor-mt-12 tutor-pb-20 tutor-pb-sm-50">
    <form class="tutor-comment-box tutor-mt-32" action="<?php echo get_home_url(); ?>/wp-comments-post.php" method="post">
        <div class="comment-avatar">
            <img src="<?php echo get_avatar_url( get_current_user_id() ) ?>" alt="">
        </div>
        <div class="tutor-comment-textarea">
            <textarea placeholder="Write a comments" class="tutor-form-control" name="comment"></textarea>
            <input type="hidden" name="comment_post_ID" value="<?php echo get_the_ID(); ?>"/>
            <input type="hidden" name="comment_parent" value="0"/>
        </div>
        <div class="tutor-comment-submit-btn">
            <button type="submit" class="tutor-btn tutor-btn-primary tutor-btn-sm">Submit</button>
        </div>
    </form>
    <?php if(is_array( $comments ) && count($comments)) : ?>
        <?php foreach($comments as $comment): ?>
            <div class="tutor-comments-list tutor-parent-comment tutor-mt-30">
                <div class="comment-avatar">
                    <img src="<?php echo get_avatar_url( $comment->user_id ); ?>" alt="">
                </div>
                <div class="tutor-single-comment">
                    <div class="tutor-actual-comment tutor-mb-10">
                        <div class="tutor-comment-author">
                            <span class="text-bold-body"><?php echo $comment->comment_author; ?></span>
                            <span class="text-regular-caption tutor-ml-10 tutor-ml-sm-10">
                                <?php echo human_time_diff(strtotime($comment->comment_date), tutor_time()).__(' ago', 'tutor'); ?>
                            </span>
                        </div>
                        <div class="tutor-comment-text text-regular-body tutor-mt-5">
                            <?php echo $comment->comment_content; ?>
                        </div>
                    </div>
                    <div class="tutor-comment-actions tutor-ml-22">
                        <span class="text-regular-body color-text-title">reply</span>
                        <!-- <span class="text-regular-body color-text-title">like</span>
                        <span class="text-regular-body color-text-title">edit</span>
                        <span class="text-regular-body color-text-title">delete</span> -->
                    </div>

                    <?php 
                        $replies = get_comments(array(
                            'status' => 'approve',
                            'post_id' => get_the_ID(),
                            'parent' => $comment->comment_ID
                        )); 
                    ?>
                    <?php if(is_array($replies) && count($replies)): ?>
                        <?php foreach($replies as $reply): ?>
                            <div class="tutor-comments-list tutor-child-comment tutor-mt-30">
                                <div class="comment-avatar">
                                    <img src="<?php echo get_avatar_url( $reply->user_id ) ?>" alt="">
                                </div>
                                <div class="tutor-single-comment">
                                    <div class="tutor-actual-comment tutor-mb-10">
                                        <div class="tutor-comment-author">
                                            <span class="text-bold-body">Estella Clayton</span>
                                            <span class="text-regular-caption tutor-ml-0 tutor-ml-sm-10">
                                                <?php echo human_time_diff(strtotime($reply->comment_date), tutor_time()).__(' ago', 'tutor'); ?>
                                            </span>
                                        </div>
                                        <div class="tutor-comment-text text-regular-body tutor-mt-5">
                                            <?php echo $reply->comment_content; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <form class="tutor-comment-box tutor-reply-box tutor-mt-20" action="<?php echo get_home_url(); ?>/wp-comments-post.php" method="post">
                        <div class="comment-avatar">
                            <img src="<?php echo get_avatar_url( get_current_user_id() ) ?>" alt="">
                        </div>
                        <div class="tutor-comment-textarea">
                            <textarea placeholder="Write a comments" name="comment" class="tutor-form-control"></textarea>
                            <input type="hidden" name="comment_post_ID" value="<?php echo get_the_ID(); ?>"/>
                            <input type="hidden" name="comment_parent" value="<?php echo $comment->comment_ID; ?>"/>
                        </div>
                        <div class="tutor-comment-submit-btn">
                            <button type="submit" class="tutor-btn tutor-btn-primary tutor-btn-sm">Reply</button>
                        </div>
                    </form>
                </div>
                <span class="tutor-comment-line"></span>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php 
    $tutor_social_share_icons = tutor_utils()->tutor_social_share_icons();
    if(!tutor_utils()->count($tutor_social_share_icons)) {
        return;
    }
    
    $share_config = array(
        'title' => get_the_title(),
        'text'  => get_the_excerpt(),
        'image' => get_tutor_course_thumbnail('post-thumbnail', true),
    );
?>

<a data-tutor-modal-target="tutor-course-share-opener" href="#" class="action-btn tutor-text-regular-body tutor-color-text-primary">
    <span class="ttr-share-filled"></span> <?php _e('Share', 'tutor'); ?>
</a>
<div id="tutor-course-share-opener" class="tutor-modal">
    <span class="tutor-modal-overlay"></span>
    <div class="tutor-modal-root">
        <div class="tutor-modal-inner tutor-modal-close-inner">
            <div class="tutor-modal-body" style="padding:40px">
				<button data-tutor-modal-close class="tutor-modal-close">
					<span class="las la-times"></span>
				</button>
                <div class="tutor-text-medium-h5 color-text-primary tutor-mb-15">
                    <?php _e('Share Course', 'tutor'); ?>
                </div>
                <div class="tutor-text-regular-caption color-text-subsued tutor-mb-10">
                    <?php _e('Page Link', 'tutor') ?>
                </div>
                <div class="tutor-mb-30">
                    <input class="tutor-form-control" value="<?php echo get_permalink( get_the_ID() ); ?>" />
                </div>
                <div>
                    <div class="color-text-primary tutor-text-medium-h6 tutor-mb-15">
                        <?php _e('Share On Social Media', 'tutor'); ?>
                    </div>
                    <div class="tutor-social-share-wrap" data-social-share-config="<?php echo esc_attr(wp_json_encode($share_config)); ?>">
                        <?php
                            foreach ($tutor_social_share_icons as $icon){
                                echo '<button class="tutor_share ' . $icon['share_class'] . '" style="background:'.$icon['color'].'">'. 
                                        $icon['icon_html'] . ' <span>' . $icon['text'] . '</span>
                                    </button>';
                            }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
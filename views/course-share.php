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

<a data-tutor-modal-target="tutor-course-share-opener" href="#" class="tutor-btn tutor-btn-ghost tutor-course-share-btn">
    <span class="tutor-icon-share tutor-mr-8"></span> <?php _e('Share', 'tutor'); ?>
</a>
<div id="tutor-course-share-opener" class="tutor-modal">
    <span class="tutor-modal-overlay"></span>
    <div class="tutor-modal-window">
        <div class="tutor-modal-content tutor-modal-content-white">
            <button class="tutor-iconic-btn tutor-modal-close-o" data-tutor-modal-close>
                <span class="tutor-icon-times" area-hidden="true"></span>
            </button>
            <div class="tutor-modal-body">
                <div class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-16">
                    <?php _e('Share Course', 'tutor'); ?>
                </div>
                <div class="tutor-fs-7 tutor-color-secondary tutor-mb-12">
                    <?php _e('Page Link', 'tutor') ?>
                </div>
                <div class="tutor-mb-32">
                    <input class="tutor-form-control" value="<?php echo get_permalink( get_the_ID() ); ?>" />
                </div>
                <div>
                    <div class="tutor-color-black tutor-fs-6 tutor-fw-medium tutor-mb-16">
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
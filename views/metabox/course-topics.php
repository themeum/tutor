<?php
    $classname = '';
    if(version_compare(get_bloginfo('version'),'5.5', '>=')) {
        $classname = 'has-postbox-header';
        echo '<style> #tutor-course-topics .toggle-indicator:before { margin-top: 0; } </style>';
    }
?>

<div id="tutor-course-content-builder-root">
    <div class="tutor-course-builder-header <?php echo $classname; ?>">
        <a href="javascript:;" class="tutor-expand-all-topic"><?php _e('Expand all', 'tutor'); ?></a> |
        <a href="javascript:;" class="tutor-collapse-all-topic"><?php _e('Collapse all', 'tutor'); ?></a>
    </div>

    <?php $course_id = get_the_ID(); ?>
    <div id="tutor-course-content-wrap">
        <?php
        include  tutor()->path.'views/metabox/course-contents.php';
        ?>
    </div>

    <div class="new-topic-btn-wrap">
        <a data-tutor-modal-target="tutor-modal-add-topic" href="javascript:;" class="create_new_topic_btn tutor-btn bordered-btn"> 
            <i class="tutor-icon-text-document-add-button-with-plus-sign"></i> <?php _e('Add new topic', 'tutor'); ?>
        </a>
    </div>

    <?php 
        tutor_load_template_from_custom_path(tutor()->path.'/views/metabox/segments/topic-form-modal.php', array(
            'wrapper_id' => 'tutor-modal-add-topic',
            'topic_id' => null,
            'course_id' => $course_id,
            'wrapper_class' => 'tutor-metabox-add-topics',
            'button_text' => __('Add Topic', 'tutor'),
            'button_id' => 'tutor-add-topic-btn'
        ), false); 
    ?>

    <div class="tutor-modal-wrap tutor-quiz-builder-modal-wrap">
        <div class="tutor-modal-content">
            <div class="modal-header">
                <div class="modal-title">
                    <h1><?php _e('Quiz', 'tutor'); ?></h1>
                </div>
                <div class="modal-close-wrap">
                    <a href="javascript:;" class="modal-close-btn"><i class="tutor-icon-line-cross"></i> </a>
                </div>
            </div>
            <div class="modal-container"></div>
        </div>
    </div>

    <div class="tutor-modal modal-sticky-header-footer tutor-lesson-modal-wrap">
        <span class="tutor-modal-overlay"></span>
        <div class="tutor-modal-root">
            <div class="tutor-modal-inner">
                <div class="tutor-modal-header">
                    <h3 class="tutor-modal-title">
                        <?php _e('Lesson', 'tutor'); ?>
                        <button data-tutor-modal-close className="tutor-modal-close">
                            <span className="las la-times"></span>
                        </button>
                    </h3>
                </div>
                <div class="tutor-modal-body-alt modal-container"></div>
                <div class="tutor-modal-footer">
                    <div class="row">
                        <div class="col">
                            <button type="button" class="tutor-btn update_lesson_modal_btn">
                                <?php _e('Update Lesson', 'tutor'); ?>
                            </button>
                        </div>
                        <div class="col-auto">
                            <button data-tutor-modal-close class="tutor-btn tutor-is-default">
                                <?php _e('Cancel', 'tutor'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="tutor-modal-wrap tutor-assignment-builder-modal-wrap">
        <div class="tutor-modal-content">
            <div class="modal-header">
                <div class="modal-title">
                    <h1><?php _e('Assignments', 'tutor'); ?></h1>
                </div>
                <div class="modal-close-wrap">
                    <a href="javascript:;" class="modal-close-btn"><i class="tutor-icon-line-cross"></i> </a>
                </div>
            </div>
            <div class="modal-container"></div>
        </div>
    </div>

    <div class="tutor-modal-wrap tutor-zoom-meeting-modal-wrap">
        <div class="tutor-modal-content">
            <div class="modal-header">
                <div class="modal-title">
                    <h1><?php _e('Zoom Meeting', 'tutor'); ?></h1>
                </div>
                <div class="modal-close-wrap">
                    <a href="javascript:;" class="modal-close-btn"><i class="tutor-icon-line-cross"></i> </a>
                </div>
            </div>
            <div class="modal-container"></div>
        </div>
    </div>
</div>
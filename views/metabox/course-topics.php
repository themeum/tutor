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
        tutor_load_template_from_custom_path(tutor()->path.'/views/modal/topic-form.php', array(
            'modal_title'   => __('Add Topic', 'tutor'),
            'wrapper_id'    => 'tutor-modal-add-topic',
            'topic_id'      => null,
            'course_id'     => $course_id,
            'wrapper_class' => 'tutor-metabox-add-topics',
            'button_text'   => __('Add Topic', 'tutor'),
            'button_id'     => 'tutor-add-topic-btn'
        ), false); 
    ?>

    <div class="tutor-modal modal-sticky-header-footer tutor-quiz-builder-modal-wrap" data-target="quiz-builder-tab-quiz-info">
        <span class="tutor-modal-overlay"></span>
        <div class="tutor-modal-root">
            <div class="tutor-modal-inner">
                <div class="tutor-modal-header">
                    <h3 class="tutor-modal-title">
                        <?php _e('Quiz', 'tutor'); ?>
                        <button data-tutor-modal-close class="tutor-modal-close">
                            <span class="las la-times"></span>
                        </button>
                    </h3>
                </div>
                
                <div class="tutor-modal-steps">
                    <ul>
                        <li class="tutor-is-completed">
                            <span><?php _e('Quiz Info', 'tutor'); ?></span>
                            <button class="tutor-modal-step-btn">1</button>
                        </li>
                        <li>
                            <span><?php _e('Question', 'tutor'); ?></span>
                            <button class="tutor-modal-step-btn">2</button>
                        </li>
                        <li>
                            <span><?php _e('Settings', 'tutor'); ?></span>
                            <button class="tutor-modal-step-btn">4</button>
                        </li>
                    </ul>
                </div>

                <div class="tutor-modal-body-alt modal-container">
                </div>

                <div class="tutor-modal-footer">
                    <div class="row">
                        <div class="col">
                            <div class="tutor-btn-group">
                                <button type="button" class="tutor-btn tutor-is-default">
                                    <?php _e('Back', 'tutor'); ?>
                                </button>
                                <button type="button" class="tutor-btn tutor-is-primary">
                                    <?php _e('Save & Next', 'tutor'); ?>
                                </button>
                            </div>
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
    
    <div class="tutor-modal modal-sticky-header-footer tutor-lesson-modal-wrap">
        <span class="tutor-modal-overlay"></span>
        <div class="tutor-modal-root">
            <div class="tutor-modal-inner">
                <div class="tutor-modal-header">
                    <h3 class="tutor-modal-title">
                        <?php _e('Lesson', 'tutor'); ?>
                        <button data-tutor-modal-close class="tutor-modal-close">
                            <span class="las la-times"></span>
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
<?php

function tutor_announcement_modal($id, $title, $courses, $announcement=null) {
    $course_id = $announcement ? $announcement->post_parent : null;
    $announcment_id = $announcement ? $announcement->ID : null;
    $announcment_title = $announcement ? $announcement->post_title : '';
    $summary = $announcement ? $announcement->post_content : '';

    // Assign fallback course id
    (!$course_id && count($courses)) ? $course_id = $courses[0]->ID : 0;
    
    ?>
    <form class="tutor-modal modal-sticky-header-footer tutor-announcements-form" id="<?php echo $id; ?>">
        <span class="tutor-modal-overlay"></span>
        <div class="tutor-modal-root">
            <div class="tutor-modal-inner">
                <div class="tutor-modal-header">
                    <h3 class="tutor-modal-title">
                        <?php echo $title; ?>
                    </h3>
                    <button data-tutor-modal-close class="tutor-modal-close">
                        <span class="las la-times"></span>
                    </button>
                </div>
                
                <div class="tutor-modal-body-alt modal-container">
                    <?php tutor_nonce_field(); ?>
                    <input type="hidden" name="announcement_id" value="<?php echo $announcment_id; ?>">
                    <input type="hidden" name="action" value="tutor_announcement_create"/>
                    <input type="hidden" name="action_type" value="<?php echo $announcement ? 'update' : 'create'; ?>"/>
                    <div class="tutor-form-group">
                        <label>
                            <?php _e('Select Course', 'tutor'); ?>
                        </label>
                        <select class="tutor-form-select" name="tutor_announcement_course" id="" required>
                            <?php if ($courses) : ?>
                                <?php foreach ($courses as $course) : ?>
                                    <option value="<?php echo esc_attr($course->ID) ?>" <?php selected( $course_id, $course->ID ); ?>>
                                        <?php echo $course->post_title; ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <option value=""><?php _e('No course found', 'tutor'); ?></option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="tutor-form-group">
                        <label>
                            <?php _e('Announcement Title', 'tutor'); ?>
                        </label>
                        <input class="tutor-form-control" type="text" name="tutor_announcement_title" value="<?php echo $announcment_title; ?>" placeholder="<?php _e('Announcement title', 'tutor'); ?>" required>
                    </div>
                    <div class="tutor-form-group">
                        <label for="tutor_announcement_course">
                            <?php _e('Summary', 'tutor'); ?>
                        </label>
                        <textarea class="tutor-form-control" rows="6" type="text" name="tutor_announcement_summary" placeholder="<?php _e('Summary...', 'tutor'); ?>" required><?php echo $summary; ?></textarea>
                    </div>
                    
                    <?php do_action('tutor_announcement_editor/after'); ?>
                </div>

                <div class="tutor-modal-footer">
                    <div class="tutor-bs-row">
                        <div class="tutor-bs-col">
                            <div class="tutor-btn-group">
                                <button type="submit" data-action="next" class="tutor-btn tutor-is-primary">
                                    <?php _e('Publish', 'tutor'); ?>
                                </button>
                            </div>
                        </div>
                        <div class="tutor-bs-col-auto">
                            <button data-tutor-modal-close type="button" data-action="back" class="tutor-btn tutor-is-default">
                                <?php _e('Cancel', 'tutor'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <?php
}

function tutor_announcement_modal_details($id, $announcement, $course_title, $publish_date) {
    // TODO: Update this modal with new modal variation from Saif
    ?>
    <div class="tutor-modal modal-sticky-header-footer" id="<?php echo $id; ?>">
        <span class="tutor-modal-overlay"></span>
        <div class="tutor-modal-root">
            <div class="tutor-modal-inner">
                <div class="tutor-modal-header">
                    <h3 class="tutor-modal-title">
                        <?php _e('Announcement Details', 'tutor'); ?>
                    </h3>
                    <button data-tutor-modal-close class="tutor-modal-close">
                        <span class="las la-times"></span>
                    </button>
                </div>
                
                <div class="tutor-modal-body-alt modal-container">
                    <h4>Course: <?php echo $course_title; ?></h4>
                    <p>Publish Date: <?php echo $publish_date; ?></p>
                    <p>Announcement Title: <?php echo $announcement->post_title; ?></p>
                    <p>Announcement Summary: <?php echo $announcement->post_content; ?></p>
                    <p>Announcement Summary: <?php echo $announcement->post_content; ?></p>
                </div>
            </div>
        </div>
    </div>
    <?php
}

function tutor_announcement_modal_delete($id, $announcment_id, $row_id) {
    ?>
    <div id="<?php echo $id; ?>" class="tutor-modal">
        <span class="tutor-modal-overlay"></span>
        <button data-tutor-modal-close class="tutor-modal-close">
            <span class="las la-times"></span>
        </button>
        <div class="tutor-modal-root">
            <div class="tutor-modal-inner">
                <div class="tutor-modal-body tutor-text-center">
                    <div class="tutor-modal-icon">
                        <img src="<?php echo tutor()->url; ?>assets/images/icon-trash.svg" />
                    </div>
                    <div class="tutor-modal-text-wrap">
                        <h3 class="tutor-modal-title">
                            <?php _e('Delete This Announcement?', 'tutor'); ?>
                        </h3>
                        <p>
                            <?php _e('Are you sure you want to delete this Announcement permanently from the site? Please confirm your choice.', 'tutor'); ?>
                        </p>
                    </div>
                    <div class="tutor-modal-btns tutor-btn-group">
                        <button data-tutor-modal-close class="tutor-btn tutor-is-outline tutor-is-default">
                            <?php _e('Cancel', 'tutor'); ?>
                        </button>
                        <button class="tutor-btn tutor-announcement-delete" data-announcement-id="<?php echo $announcment_id; ?>" data-target-announcement-row-id="<?php echo $row_id; ?>">
                            <?php _e('Yes, Delete This', 'tutor'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}

$announcements = $data['announcements'];
$courses = (current_user_can('administrator')) ? tutils()->get_courses() : tutils()->get_courses_by_instructor();
?>

<?php if(count($announcements)): ?>
    <table class="tutor-ui-table tutor-bs-bg-white">
        <thead>
            <tr>
                <th class="tutor-shrink">
                    <span class="text-regular-small color-text-subsued">
                            <?php _e('Date', 'tutor'); ?>
                    </span>
                </th>
                <th>
                    <div class="inline-flex-center color-text-subsued">
                        <span class="text-regular-small"><?php _e('Announcements', 'tutor'); ?></span>
                        <span class="tutor-v2-icon-test icon-ordering-a-to-z-filled"></span>
                    </div>
                </th>
                <th class="tutor-shrink"></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($announcements as $announcement) : ?>
                <?php
                    $course = get_post($announcement->post_parent);
                    $dateObj = date_create($announcement->post_date);
                    $date_format = date_format($dateObj, 'j M, Y, h:i a'); 

                    $update_modal_id = 'tutor_announcement_' . $announcement->ID;
                    $details_modal_id = $update_modal_id . '_details';
                    $delete_modal_id = $update_modal_id . '_delete';
                    $row_id = 'tutor-announcement-tr-' . $announcement->ID;
                ?>
                <tr id="<?php echo $row_id; ?>">
                    <td data-th="<?php _e('Date', 'tutor'); ?>" class="tutor-bs-text-nowrap">
                        <?php echo $date_format; ?>
                    </td>
                    <td data-th="<?php _e('Announcement', 'tutor'); ?>" class="column-fullwidth">
                        <div class="tutor-announcement-content">
                            <h4><?php echo esc_html($announcement->post_title); ?></h4>
                            <p><?php echo $course ? $course->post_title : ''; ?></p>
                        </div>
                    </td>
                    <td data-th="<?php _e('Action', 'tutor'); ?>">
                        <div class="tutor-bs-d-flex tutor-bs-align-items-center">
                            <button class="tutor-btn tutor-is-default tutor-is-xs tutor-mr-10 tutor-announcement-details"  data-tutor-modal-target="<?php echo $details_modal_id; ?>">
                                <?php _e('Details', 'tutor'); ?>
                            </button>
                            
                            <div class="tutor-popup-opener">
                                <button type="button" class="popup-btn" data-tutor-popup-target="<?php echo $update_modal_id; ?>_action">
                                    <span class="toggle-icon"></span>
                                </button>
                                <ul class="popup-menu" id="<?php echo $update_modal_id; ?>_action">
                                    <li>
                                        <a href="#" class="tutor-quiz-open-question-form" data-tutor-modal-target="<?php echo $update_modal_id; ?>">
                                            <span class="icon tutor-v2-icon-test icon-edit-filled color-design-white"></span>
                                            <span class="text-regular-body color-text-white"><?php _e('Edit', 'tutor'); ?></span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#" class="tutor-quiz-question-trash" data-tutor-modal-target="<?php echo $delete_modal_id; ?>">
                                            <span class="icon tutor-v2-icon-test icon-delete-fill-filled color-design-white"></span>
                                            <span class="text-regular-body color-text-white"><?php _e('Delete', 'tutor'); ?></span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        
                        <?php 
                            tutor_announcement_modal($update_modal_id, __('Edit Announcment', 'tutor'), $courses, $announcement); 
                            tutor_announcement_modal_details($details_modal_id, $announcement, $course->post_title, $date_format);
                            tutor_announcement_modal_delete($delete_modal_id, $announcement->ID, $row_id);
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <span>No Announcment</span>
<?php endif; ?>

<?php 
    tutor_announcement_modal('tutor_announcement_new', __('Create Announcement'), $courses);
?>
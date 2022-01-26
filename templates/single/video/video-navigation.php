<?php if($previous_id): ?>
    <div class="tutor-lesson-prev flex-center">
        <a href="<?php echo get_the_permalink($previous_id); ?>">
            <span class="tutor-icon-angle-left-filled"></span>
        </a>
    </div>
<?php endif; ?>

<?php if($next_id): ?>
    <div class="tutor-lesson-next flex-center">
        <a href="<?php echo get_the_permalink($next_id); ?>">
            <span class="tutor-icon-angle-right-filled"></span>
        </a>
    </div>
<?php endif; ?>
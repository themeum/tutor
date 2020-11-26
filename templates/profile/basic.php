<?php
    $user_name = sanitize_text_field(get_query_var('tutor_student_username'));
    $get_user = tutor_utils()->get_user_by_login($user_name);
    $user_id = $get_user->ID;
?>

<table class="user-public-profile-basic-info">
    <tbody>
        <tr>
            <td><?php _e('Registration Date', 'tutor'); ?></td>
            <td><?php echo date( "D M, Y", strtotime( $get_user->user_registered ) ); ?></td>
        </tr>
    </tbody>
</table>
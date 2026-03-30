/**
 * WP Gutenberg Events Subscriber
 *
 * @since 2.1.7
 * @package Tutor\AdminScripts
 */
document.addEventListener('DOMContentLoaded', function() {
    wp.data.subscribe( function() {

        // Handle course move to trash event
        const courseListURL = _tutorobject.course_list_page_url;
        const coursePostType = _tutorobject.course_post_type;
        if (wp.data && wp.data.select( 'core/editor' )) {
            let postType = wp.data.select( 'core/editor' ).getEditedPostAttribute( 'type' );

            if ( postType === coursePostType ) {
                let postStatus = wp.data.select( 'core/editor' ).getEditedPostAttribute( 'status' );
                if ( postStatus === 'trash' ) {
                    // Redirect to course list page
                    window.location.href = courseListURL;
                }
            }
        }
    });
});
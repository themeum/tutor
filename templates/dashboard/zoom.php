<?php
/**
 * Zoom template as per tutor_dashboard_sub_page
 * 
 * if not subpage then load zoom main page
 * 
 * @since 1.9.3
 */
    global $wp_query;
    $query_vars     = $wp_query->query_vars;
    if( $wp_query->is_page ) {
        if( isset($query_vars['tutor_dashboard_sub_page']) &&  $query_vars['tutor_dashboard_sub_page'] == 'set_api' ) {
            tutor_zoom_instance()->zoom->tutor_zoom_settings();
        } else if ( isset($query_vars['tutor_dashboard_sub_page']) &&  $query_vars['tutor_dashboard_sub_page'] == 'settings' ) {
            tutor_zoom_instance()->zoom->tutor_zoom_settings();
        } else if ( isset($query_vars['tutor_dashboard_sub_page']) &&  $query_vars['tutor_dashboard_sub_page'] == 'help' ) {
            include_once( TUTOR_ZOOM()->path.'views/pages/help.php' );
        } else {
            tutor_zoom_instance()->zoom->tutor_zoom();
        }        
    }
?>
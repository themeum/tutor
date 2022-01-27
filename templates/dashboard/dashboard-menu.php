<?php
/**
 * Markup for Dashboard Menu
 * 
 * @since 2.0.0
 */
if ( class_exists( '\TUTOR\Utils' ) && is_user_logged_in() ) : ?>
    <style>
        .wp-site-blocks .tutor-header-profile-menu {
        position: relative;
        cursor: pointer;
        }

        .wp-site-blocks
            .tutor-header-profile-menu
            .tutor-header-profile-photo
            span.tutor-text-avatar {
            display: inline-block;
            text-align: center;
            line-height: 30px;
            height: 30px;
            width: 30px;
            border-radius: 50%;
        }

        .wp-site-blocks
            .tutor-header-profile-menu
            .tutor-header-profile-photo
            img.tutor-image-avatar {
            display: flex;
            text-align: center;
            line-height: 30px;
            height: 30px;
            width: 30px;
            border-radius: 100%;
        }

        .wp-site-blocks .tutor-header-profile-menu:hover ul {
            opacity: 1;
            visibility: visible;
        }

        .wp-site-blocks .tutor-header-profile-menu ul {
            position: absolute;
            z-index: 999;
            background: #fff;
            padding: 10px 0;
            list-style: none;
            width: 200px;
            right: 0;
            top: 49px;
            border-radius: 3px;
            opacity: 0;
            visibility: hidden;
            transition: 300ms;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-height: 510px;
            overflow-x: hidden;
            overflow-y: scroll;
        }

        .wp-site-blocks .tutor-header-profile-menu ul li {
            display: block;
        }

        .wp-site-blocks .tutor-header-profile-menu ul li a {
            display: block;
            width: 100%;
            padding: 5px 25px;
            color: #1f2949;
            transition: 300ms;
            font-size: 14px;
        }

        .wp-site-blocks .tutor-header-profile-menu ul:before {
            content: "";
            bottom: 100%;
            height: 20px;
            width: 100%;
            z-index: 2;
            left: 0;
            position: absolute;
        }
    </style>
    <div class="tutor-header-profile-menu">
        <div class="tutor-header-profile-photo">
            <?php
                if ( function_exists( 'tutor_utils' ) ){
                    echo tutor_utils()->get_tutor_avatar( get_current_user_id(), 'thumbnail' );
                } else {
                    $get_avatar_url = get_avatar_url( get_current_user_id(), 'thumbnail' );
                    echo "<img alt='' src='$get_avatar_url' />";
                }
            ?>
        </div><!-- .tutor-header-profile-photo -->
        <ul>
            <?php
                if ( function_exists( 'tutor_utils' ) ) {
                    $dashboard_page_id = tutor_utils()->get_option( 'tutor_dashboard_page_id' );
                    $dashboard_pages = tutor_utils()->tutor_dashboard_nav_ui_items();

                    foreach ( $dashboard_pages as $dashboard_key => $dashboard_page ) {
                        $menu_title = $dashboard_page;
                        $menu_link = tutils()->get_tutor_dashboard_page_permalink( $dashboard_key );
                        $separator = false;
                        if ( is_array( $dashboard_page ) ) {
                            $menu_title = tutor_utils()->array_get( 'title', $dashboard_page );
                            /**
                             * Add new menu item property "url" for custom link
                             */
                            if ( isset( $dashboard_page['url'] ) ) {
                                $menu_link = $dashboard_page['url'];
                            }
                            if ( isset( $dashboard_page['type'] ) && $dashboard_page['type'] === 'separator' ) {
                                $separator = true;
                            }
                        }
                        if ( $separator ) {
                            echo '<li class="tutor-dashboard-menu-divider"></li>';
                            if ( $menu_title ) {
                                echo "<li class='tutor-dashboard-menu-divider-header'>$menu_title</li>";
                            }
                        } else {
                            if ( $dashboard_key === 'index') $dashboard_key = '';
                            echo "<li><a href='" . esc_url( $menu_link ) . "'>" . esc_html( $menu_title ) . " </a></li>";
                        }
                    }
                }
            ?>
        </ul>
    </div><!-- .tutor-header-profile-menu -->
<?php endif; ?>
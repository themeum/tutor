<?php

/**
 * Options generator
 * @param object $this
 */

$url_page = isset($_GET['tab_page']) ? $_GET['tab_page'] : null;

?>
<!-- .tutor-backend-wrap -->
<section class="tutor-backend-settings-page" style="padding-top: 60px;">
    <header class="tutor-option-header px-3 py-2" style="position: fixed;right:0;z-index:99;width:calc(100% - 160px);top:32px;">
        <div class="title"><?php _e('Settings', 'tutor'); ?></div>
        <div class="search-field">
            <div class="tutor-input-group tutor-form-control-has-icon">
                <span class="las la-search tutor-input-group-icon"></span>
                <input type="search" autofocus id="search_settings" class="tutor-form-control" placeholder="<?php _e('Search', 'tutor'); ?>" />
                <div class="search_result">
                    <a href="#">
                        <div class="search_result_title">
                            <i class="las la-search"></i>
                            <span>Result results one</span>
                        </div>
                        <div class="search_navigation">
                            <span>General</span>
                            <i class="las la-angle-right"></i>
                            <span>Instructor</span>
                        </div>
                    </a>
                    <a href="#">
                        <div class="search_result_title">
                            <i class="las la-search"></i>
                            <span>Result results tow</span>
                        </div>
                        <div class="search_navigation">
                            <span>Design</span>
                            <i class="las la-angle-right"></i>
                            <span>Instructor</span>
                        </div>
                    </a>
                    <a href="#">
                        <div class="search_result_title">
                            <i class="las la-search"></i>
                            <span>Result results three</span>
                        </div>
                        <div class="search_navigation">
                            <span>General</span>
                            <i class="las la-angle-right"></i>
                            <span>Instructor</span>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <div class="save-button">
            <button id="save_tutor_option" class="tutor-btn"><?php _e('Save Changes', 'tutor'); ?></button>
        </div>
    </header>
    <div class="tutor-option-body">
        <form class="tutor-option-form py-4 px-3" id="tutor-option-form">
            <input type="hidden" name="action" value="tutor_option_save">
            <div class="tutor-option-tabs">
                <?php
                $i = 0;

                foreach ($this->options_attr() as $args) : ?> <ul class="tutor-option-nav">
                        <li class="tutor-option-nav-item">
                            <h4><?php echo $args['label'] ?></h4>
                        <li>
                            <?php
                            $url_exist = $this->url_exists($args['sections'], $url_page);

                            foreach ($args['sections'] as $key => $section) :
                                $i += 1;
                                $icon = tutor()->icon_dir . $section['slug'] . '.svg';
                                $is_active = $this->get_active($i, $url_page, $section['slug'], $url_exist) ? 'active' : null;
                            ?>
                        <li class="tutor-option-nav-item">
                            <a data-tab="<?php echo $section['slug'] ?>" class="<?php echo $is_active ?>">
                                <img src="<?php echo $icon ?>" alt="<?php echo $section['slug'] ?>-icon" />
                                <span class="nav-label"><?php echo $section['label'] ?></span>
                            </a>
                        </li>
                    <?php
                            endforeach; ?>
                    </ul>
                <?php
                endforeach; ?>
                <!-- end /.tutor-option-nav -->
            </div>
            <!-- end /.tutor-option-tabs -->
            <div class="tutor-option-tab-pages">
                <?php

                $i = 0;

                foreach ($this->options_attr as $args) :
                    $url_exist = $this->url_exists($args['sections'], $url_page);

                    foreach ($args['sections'] as $key => $section) :
                        $i += 1;
                        $is_active = $this->get_active($i, $url_page, $section['slug'], $url_exist) ? 'active' : null; ?>
                        <div id="<?php echo $section['slug'] ?>" class="tutor-option-nav-page <?php echo $is_active ?>">

                            <?php echo $this->template($section); ?>

                        </div>
                <?php
                    endforeach;
                endforeach;
                ?>
            </div>
            <!-- end /.tutor-option-tab-pages -->
        </form>
    </div>

    <div class="tutor-notification tutor-is-success">
        <div class="tutor-notification-icon">
            <i class="fas fa-check"></i>
        </div>
        <div class="tutor-notification-content">
            <h5>Successful</h5>
            <p>Your file was uploaded</p>
        </div>
        <button class="tutor-notification-close">
            <i class="fas fa-times"></i>
        </button>
    </div>
</section>
<style>
    .search_result {
        border: 1px solid #ddd;
        border-radius: 6px;
        box-shadow: 0 0 3px #ddd;
        width: 100%;
        position: absolute;
        top: calc(100% + 5px);
        background-color: #fff;
        font-size: 16px;
        transition: all .3s;
        opacity: 0;
        visibility: hidden;
    }

    .search_result.show {
        visibility: visible;
        opacity: 1;
    }

    .search_result_title {
        display: flex;
        vertical-align: middle;
    }

    .search_result_title span {
        line-height: 20px;
    }

    .no_item,
    .search_result a {
        padding: 12px;
        display: flex;
        width: 100%;
        color: #777;
        text-decoration: none;
        transition: all .3s;
        vertical-align: middle;
        justify-content: space-between;
    }


    .search_result .search_result_title i {
        padding-right: 10px;
        font-size: 20px;
    }

    .search_result a:hover {
        background-color: rgba(0, 0, 0, .03);
    }

    .search_navigation {
        display: flex;
        align-items: center;
        vertical-align: middle;
        font-size: 12px;
    }

    .search_navigation i {
        padding: 0 5px;
        font-size: 12px;
    }

    .tutor-notification {
        position: fixed;
        bottom: 40px;
        z-index: 999;
        visibility: hidden;
        transition: all .3s;
    }

    .tutor-notification.show {
        visibility: visible;
    }
</style>
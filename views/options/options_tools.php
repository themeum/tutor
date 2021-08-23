<?php

/**
 * Options generator
 * @param object $this
 */

$url_page = isset($_GET['tab_page']) ? $_GET['tab_page'] : null;

?>
<!-- .tutor-backend-wrap -->
<section class="tutor-backend-wrap">
    <header class="tutor-option-header px-3 py-2">
        <div class="title"><?php _e('Tools', 'tutor'); ?></div>
        <div class="search-field">
            <div class="tutor-input-group tutor-form-control-has-icon">
                <span class="las la-search tutor-input-group-icon"></span>
                <input type="search" class="tutor-form-control" placeholder="<?php _e('Search', 'tutor'); ?>" />
            </div>
        </div>
    </header>
    <div class="tutor-option-body">
        <form class="tutor-option-form py-4 px-3">
            <div class="tutor-option-tabs">
                <?php
                $i = 0;
                foreach ($this->options_tools as $args) : ?> <ul class="tutor-option-nav">
                        <li class="tutor-option-nav-item">
                            <h4><?php echo $args['label'] ?></h4>
                        <li>
                            <?php
                            $url_exist  = $this->url_exists($args['sections'], $url_page);
                            foreach ($args['sections'] as $key => $section) :
                                $i += 1;
                                $icon      = tutor()->icon_dir . $section['slug'] . '.svg';
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
                foreach ($this->options_tools as $args) :
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
</section>
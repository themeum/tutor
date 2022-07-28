<?php 
    $before = array();
    $after = array();
    $tabbed = array();

    foreach($section['blocks'] as $block) {
        if(isset($block['placement'])) {
            $block['placement']=='before' ? $before[] = $block : 0;
            $block['placement']=='after' ? $after[] = $block : 0;
        } else {
            $tabbed[] = $block;
        }
    }

    if(count($before)) {
        $section['blocks'] = $before;
        require __DIR__ . '/basic.php';
    }
?>

<div class="tutor-settings-certificate-builder">
    <ul class="tutor-nav" tutor-priority-nav>
        <?php foreach($tabbed as $index => $tab): ?>
            <li class="tutor-nav-item">
                <a href="#" class="tutor-nav-link<?php echo $index==0 ? ' is-active' : ''; ?>" data-tutor-nav-target="tutor-settings-tab-<?php echo $tab['slug']; ?>">
                    <?php echo $tab['label']; ?>
                </a>
            </li>
        <?php endforeach; ?>

        <li class="tutor-nav-item tutor-nav-more tutor-d-none">
            <a class="tutor-nav-link tutor-nav-more-item" href="#"><span class="tutor-mr-4"><?php _e("More", "tutor-pro"); ?></span> <span class="tutor-nav-more-icon tutor-icon-times"></span></a>
            <ul class="tutor-nav-more-list tutor-dropdown"></ul>
        </li>
    </ul>
    
    <div class="tutor-tab tutor-mt-32">
        <?php foreach($tabbed as $index => $tab): ?>
            <div id="tutor-settings-tab-<?php echo $tab['slug']; ?>" class="tutor-tab-item<?php echo $index==0 ? ' is-active' : ''; ?>">
                <?php
                    if(isset($tab['segments'])) {
                        foreach($tab['segments'] as $segment){
                            echo $this->blocks( $segment ); 
                        }
                    } else {
                        echo $this->blocks( $tab ); 
                    }
                ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
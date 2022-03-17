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

<?php $more_popups = array_slice($tabbed, 4); ?>
<div class="tutor-default-tab tutor-settings-details-tab">
    <div class="tab-header tutor-d-flex">
        <?php foreach($tabbed as $index => $tab): ?>
            <?php 
                if($index>=4) {
                    break;
                }
            ?>
            <div class="tab-header-item <?php echo $index==0 ? 'is-active' : ''; ?>" data-tutor-tab-target="tutor-settings-tab-<?php echo $tab['slug']; ?>">
                <span><?php echo $tab['label']; ?></span>
            </div>
        <?php endforeach; ?>
                
        <?php if(count($more_popups)): ?>
            <div class="tab-header-item-seemore tutor-ml-auto">
                <div class="tab-header-item-seemore-toggle" data-seemore-target="tutor-settings-tab-seemore-11">
                    <span class="icon-seemore tutor-icon-line-cross-line tutor-icon-20 tutor-color-text-brand"></span>
                </div>
                <div id="tutor-settings-tab-seemore-11" class="tab-header-item-seemore-popup">
                    <ul>
                        <?php foreach($more_popups as $tab): ?>
                            <li class="tab-header-item" data-tutor-tab-target="tutor-settings-tab-<?php echo $tab['key']; ?>">
                                <span class="tutor-icon-github-logo-brand tutor-icon-18 tutor-mr-8"></span>
                                <span><?php echo $tab['label']; ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="tab-body">
            <?php foreach($tabbed as $index => $tab): ?>
                <div class="tab-body-item <?php echo $index==0 ? 'is-active' : ''; ?>" id="tutor-settings-tab-<?php echo $tab['slug']; ?>">
                    <?php echo $this->blocks( $tab ); ?>
                </div>
            <?php endforeach; ?>
    </div>
</div>
<?php 
/** @var \Kirby\Cms\Page $page */
$layouts = $layout_content ?? $page->layouts();
$formData = $page->formData();
$isAvailable = $formData['available'] === null || $formData['available'] > 0;
?>

<?php if($layouts && $layouts->isNotEmpty()): ?>
    <div class="blocks-container <?= $class ?? '' ?>">
        
        <?php 
        $hasAnchors = $layouts->toLayouts()->filterBy('anchor', 'true')->isNotEmpty();
        if ($hasAnchors): ?>
            <div class="anchors-navigation">
                <div class="single-anchor">
                    <p style="text-transform: uppercase;"><strong>Contenuti</strong></p>
                </div>
                <?php foreach ($layouts->toLayouts() as $layout): ?>
                    <?php if ($layout->anchor()->isTrue()): 
                        $anchorName = $layout->anchor_name()->value();
                        $slug = Str::slug($anchorName); ?>
                        <div class="single-anchor">
                            <a href="#<?= $slug ?>">📑 <?= $anchorName ?></a>
                        </div>
                    <?php endif ?>
                <?php endforeach ?>
            </div>
        <?php endif ?>

        <div class="blocks-container-inner">
            <?php foreach ($layouts->toLayouts() as $layout): ?>
                <?php 
                $isExpiredLayout = $layout->scadenza()->isTrue();
                $isExpiredPage = $page->isExpired();
                
                // Skip if it's an "expiry" layout and page is expired or no slots available
                if ($isExpiredLayout && ($isExpiredPage || !$isAvailable)) continue;

                $anchorEnabled = $layout->anchor()->isTrue();
                $anchorName = $layout->anchor_name()->value();
                $slug = Str::slug($anchorName);
                
                $id = $layout->custom_id()->isNotEmpty() ? Str::slug($layout->custom_id()->value()) : null;
                $stickyId = $layout->sticky()->isTrue() ? 'sticky_' . generateRandomString() : null;
                ?>

                <?php if ($stickyId): ?>
                    <style>
                        #<?= $stickyId ?> {
                            position: sticky;
                            top: <?= $layout->sticky_offset()->or(0) ?>px !important;
                        }
                        * { overflow: visible !important; }
                    </style>
                <?php endif ?>

                <?php if ($anchorEnabled): ?>
                    <div class="anchor-block" id="<?= $slug ?>">
                        <div class="anchor-block-inner">
                            <p><?= $anchorName ?></p>
                        </div>
                    </div>
                <?php endif ?>

                <div <?php if($id): ?>id="<?= $id ?>"<?php endif ?> 
                     class="row <?= $anchorEnabled ? 'anchor-row' : '' ?>" 
                     style="<?= $custom_style ?? '' ?><?= $layout->custom_css() ?>">
                    <?php foreach ($layout->columns() as $column): ?>
                        <div class="column col-lg-<?= $column->span() ?> <?= $column->blocks()->isEmpty() ? 'mobile_display_none' : '' ?>">
                            <div <?php if($stickyId): ?>id="<?= $stickyId ?>"<?php endif ?> class="blocks">
                                <?= $column->blocks() ?>
                            </div>
                        </div>
                    <?php endforeach ?>
                </div>
            <?php endforeach ?>
        </div>
    </div>
<?php endif ?>
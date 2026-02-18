<?php if($item->title()->isNotEmpty() ): ?>
    <h3><?= $item->title() ?></h3> 
<?php endif; ?>
<?php if($item->descrizione()->isNotEmpty() ): ?>
    <div class="slide-bodycopy"><?= $item->descrizione()->kirbytext() ?></div>
<?php endif; ?>
<?php if($item->cta()->isNotEmpty()): ?>
    <?php snippet('block-slide-cta',[
        'block' => $block,
        'cta_items' => $item->cta(),
    ]) ?>
<?php endif; ?>

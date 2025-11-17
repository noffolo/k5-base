<?php if($item->title()->isNotEmpty() ): ?>
    <h2><?= $item->title() ?></h2> 
<?php endif; ?>
<?php if($item->descrizione()->isNotEmpty() ): ?>
    <div class="slide-bodycopy"><?= $item->descrizione()->kirbytext() ?></div>
<?php endif; ?>
<?php if($item->cta()->isNotEmpty()): ?>
    <?php snippet('cta',[
        'block' => $block,
        'cta_items' => $item->cta(),
    ]) ?>
<?php endif; ?>

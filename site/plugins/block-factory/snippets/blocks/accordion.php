<?php
/** @var \Kirby\Cms\Block $block */
?>
<details class="accordion-block">
  <summary><?= $block->summary() ?></summary>
  <div class="accordion-details">
    <?= $block->details() ?>
  </div>
</details>

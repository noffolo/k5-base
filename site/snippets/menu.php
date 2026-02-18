<?php $items = $site->menu()->toStructure() ?>
<header role="banner">
  <nav class="site-header" aria-label="Main Navigation">
    <div class="site-header-inner">
      <?php snippet('logo-object',[]); ?>
      <?php if ($items->isNotEmpty()) : ?>

      <div class="navigation navigation-desktop">
        <?php snippet('menuitem-list', ['items' => $items, 'accordion__item' => true]) ?>
      </div>
      <button class="navbar-toggler closed" aria-label="Toggle navigation" aria-expanded="false">
			  <span class="icon-bar"></span>
			  <span class="icon-bar"></span>
			  <span class="icon-bar"></span>
		  </button>

    <?php endif ?>
    </div>
    <?php if ($items->isNotEmpty()) : ?>
      <div class="navigation navigation-mobile">
        <div class="navigation-mobile-flexbox">
          <?php snippet('mobile-menuitem-list', ['items' => $items, 'accordion__item' => true]) ?>
        </div>
      </div>
    <?php endif ?>
  </nav>
</header>



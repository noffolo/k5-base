<?php snippet('header') ?>
<?php snippet('menu') ?>

<main id="main" class="main">
<?php snippet('check_banner',['posizione' => 'sopra',]); ?>
<?php snippet('page_navigator') ?>
<?php snippet('layouts', ['layout_content' => $page->contenuto(),]); ?>

<?php snippet('search',[]) ?> 

<?php snippet('check_banner',['posizione' => 'sotto',]); ?>

<?php snippet('layouts', ['layout_content' => $site->footer(), 'class' => 'footer',]); ?>
</main>
<?php snippet('footer') ?>
 
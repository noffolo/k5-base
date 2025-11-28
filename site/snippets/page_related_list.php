<?php
$correlati = $page->correlati()->toPages();
if ($correlati->count() > 0):
?>

<!-- GRIGLIA -->
<?php snippet('collection-grid',[
    'collection' => $correlati,
    'category_color' => false,
    'padding_top' => '50%',
]) ?>

<?php endif ?>

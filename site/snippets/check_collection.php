<!-- Verific a se la pagina ha dei figli (e quindi è una collection) -->
<?php if($page->hasChildren() == true): ?>
    <!-- se la COLLECTION ha una vista MAPPA -->
    <?php if($page->collection_options() == 'map'): ?>
        <?php $collection = $page->children()->listed(); ?>
        <?php snippet('collection-map-view',[
            'collection_parent' => $page,
            'collection' => $collection
        ]) ?>
    <!-- se la COLLECTION ha una vista BLOG -->
    <?php elseif($page->collection_options() == 'blog'): ?>
        <?php snippet('collection-blog-view',[
            'collection_parent' => $page,
            'collection' => $page->children()->listed()
        ]) ?>
    <?php elseif($page->collection_options() == 'calendar'): ?>
        <?php snippet('collection-calendar-view',[
            'collection_parent' => $page,
            'collection' => $page->children()->listed()
        ]) ?>
    <?php endif; ?>
<?php endif; ?>
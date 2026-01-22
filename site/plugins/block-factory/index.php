<?php
Kirby::plugin('cookbook/block-factory', [
  'blueprints' => [
    'blocks/page_title'                => __DIR__ . '/blueprints/blocks/page_title.yml',
    'blocks/variable_title'                => __DIR__ . '/blueprints/blocks/variable_title.yml',
    'blocks/map'                => __DIR__ . '/blueprints/blocks/map.yml',
    'blocks/imagetext'          => __DIR__ . '/blueprints/blocks/imagetext.yml',
    'blocks/imagetextbuttons'   => __DIR__ . '/blueprints/blocks/imagetextbuttons.yml',
    'blocks/slider'             => __DIR__ . '/blueprints/blocks/slider.yml',
    'blocks/people'             => __DIR__ . '/blueprints/blocks/people.yml',
    'blocks/cards'              => __DIR__ . '/blueprints/blocks/cards.yml',
    'blocks/collection_manager' => __DIR__ . '/blueprints/blocks/collection_manager.yml',
    'blocks/cta'                => __DIR__ . '/blueprints/blocks/cta.yml',
    'blocks/accordion'          => __DIR__ . '/blueprints/blocks/accordion.yml',
    'blocks/calendar_from_csv'       => __DIR__ . '/blueprints/blocks/calendar_from_csv.yml',
  ],
  'snippets' => [
    'blocks/page_title'                => __DIR__ . '/snippets/blocks/page_title.php',
    'blocks/variable_title'                => __DIR__ . '/snippets/blocks/variable_title.php',
    'blocks/map'                => __DIR__ . '/snippets/blocks/map.php',
    'blocks/imagetext'          => __DIR__ . '/snippets/blocks/imagetext.php',
    'blocks/slider'             => __DIR__ . '/snippets/blocks/slider.php',
    'blocks/people'             => __DIR__ . '/snippets/blocks/people.php',
    'blocks/cards'              => __DIR__ . '/snippets/blocks/cards.php',
    'blocks/collection_manager' => __DIR__ . '/snippets/blocks/collection_manager.php',
    'blocks/slidercards'        => __DIR__ . '/snippets/blocks/slidercards.php',
    'blocks/cta'                => __DIR__ . '/snippets/blocks/cta.php',
    'blocks/imagetextbuttons'   => __DIR__ . '/snippets/blocks/imagetextbuttons.php',
    'blocks/accordion'          => __DIR__ . '/snippets/blocks/accordion.php',
    'blocks/calendar_from_csv'       => __DIR__ . '/snippets/blocks/calendar_from_csv.php',
  ],
  // 👇 questa riga fa sparire "Invalid field type"
  'fields' => [
    'active-category' => [],
  ],
]);

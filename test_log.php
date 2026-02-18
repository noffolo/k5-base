<?php

require 'kirby/bootstrap.php';

$kirby = new Kirby([
    'roots' => [
        'index' => __DIR__
    ]
]);

$kirby->impersonate('kirby@getkirby.com');

try {
    $page = $kirby->page('contatti');
    if ($page) {
        $page->update([
            'title' => 'Contatti ' . time()
        ]);
        echo "Page updated successfully.\n";
    } else {
        echo "Page 'contatti' not found.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

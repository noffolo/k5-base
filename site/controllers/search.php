<?php

use NonDeterministic\Helpers\CollectionHelper;

return function ($site, $pages, $page) {

  // ====== RICERCA ======
  $query   = get('q');
  $results = $site->search($query, [
      'words'       => false,
      'minlength'   => 2,
      'fields'      => ['title', 'keywords', 'descrizione', 'child_category_selector', 'appuntamenti', 'locator', 'contenuto'],
      'score'       => ['title' => 50, 'descrizione' => 30, 'child_category_selector' => 20, 'contenuto' => 1],
      'stopwords'   => ['di','a','da','in','con','su','per','tra','fra','il','lo','la','gli','le']
  ]);
  
  // Filtra solo le pagine che hanno un genitore
  $results = $results->filter(function ($page) {
      return $page->parent();
  });

  return [
    'query'    => $query,
    'results'  => $results,
  ] + CollectionHelper::formDataFor();

};

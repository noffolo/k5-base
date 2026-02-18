<!DOCTYPE html>
<?php setlocale(LC_TIME, 'it_IT.utf8', 'it_IT'); ?>
<html class="no-js" lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Preconnect/Preload -->
  <link rel="preconnect" href="https://ajax.googleapis.com">
  <?php
  // Dynamic LCP Preload
  $lcpImage = null;
  if ($page->contenuto()->isNotEmpty()) {
    foreach ($page->contenuto()->toLayouts() as $layout) {
      foreach ($layout->columns() as $column) {
        foreach ($column->blocks() as $block) {
          if ($block->type() === 'slider') {
            if ($firstSlide = $block->slider()->toStructure()->first()) {
              if ($file = $firstSlide->pics()->toFile()) {
                 $lcpImage = $file->thumb(['format' => 'webp'])->url();
              }
            }
            break 3;
          }
        }
      }
    }
  }
  ?>
  <?php if ($lcpImage): ?>
    <link rel="preload" as="image" href="<?= $lcpImage ?>" fetchpriority="high">
  <?php endif; ?>

  <?php if($site->fb_domain_verification()->isNotEmpty()): ?>
    <meta name="facebook-domain-verification" content="<?= htmlspecialchars($site->fb_domain_verification()) ?>" />
  <?php endif; ?>

  <?php snippet('seo-schema') ?>

  <title><?= $page->seoTitle() ?></title>
  <link rel="canonical" href="<?= $page->url() ?>">

  <meta name="description" content="<?= $page->seoDescription() ?>">
  <meta name="keywords" content="<?= $page->seoKeywords() ?>">

  <meta property="og:type" content="<?= $page->isHomePage() ? 'website' : 'article' ?>" />
  <meta property="og:title" content="<?= $page->seoTitle() ?>" />
  <meta property="og:description" content="<?= $page->seoDescription() ?>" />
  <meta property="og:url" content="<?= $page->url() ?>" />
  <meta property="og:site_name" content="<?= $site->title() ?>">

  <meta name="twitter:card" content="summary" />
  <meta name="twitter:title" content="<?= $page->seoTitle() ?>" />
  <meta name="twitter:description" content="<?= $page->seoDescription() ?>" />

  <?php if($image = $page->seoImage()): ?>
    <meta property="og:image" content="<?= $image->url() ?>" />
    <meta name="twitter:image" content="<?= $image->url() ?>">
  <?php endif; ?>

  <?php if($site->account_x()->isNotEmpty()): ?>
    <meta name="twitter:creator" content="<?= $site->account_x() ?>" />
  <?php endif; ?>
  <?php if($site->fb_app_id()->isNotEmpty()): ?>
    <meta property="fb:app_id" content="<?= $site->fb_app_id() ?>" />
  <?php endif; ?>

  <link rel="shortcut icon" type="image/x-icon" href="<?= url('favicon.ico') ?>">

  <!-- Styles bundled by Vite -->
  <link rel="stylesheet" href="<?= url('assets/build/css/css.css') ?>">

  <!-- JS in HEAD solo se necessario -->
  <?= js('assets/js/lazysizes.min.js') ?>

  <!-- Analytics -->
  <?php if (isFeatureAllowed('analytics')): ?>
    
  <?php endif; ?>
</head>

<body>

<!DOCTYPE html>
<?php setlocale(LC_TIME, 'it_IT.utf8', 'it_IT'); ?>
<html class="no-js" lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Preconnect CDN -->
  <link rel="preconnect" href="https://unpkg.com">
  <link rel="preconnect" href="https://ajax.googleapis.com">

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

  <!-- Styles -->
  <?php if($page->hasChildren() && $page->collection_options() === "map"): ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A==" crossorigin="" />
  <?php endif; ?>

  <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />

  <link rel="stylesheet" href="<?= url('node_modules/bootstrap/dist/css/bootstrap.min.css') ?>">
  <link rel="stylesheet" href="<?= url('assets/build/css/css.css') ?>"> <!-- usa versionamento manuale -->

  <!-- JS in HEAD solo se necessario -->
  <?= js('assets/js/lazysizes.min.js') ?>

  <!-- Analytics -->
  <?php if (isFeatureAllowed('analytics')): ?>
    
  <?php endif; ?>
</head>

<body>

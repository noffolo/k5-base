<?php
/** @var Kirby\Cms\Page $page */
/** @var Kirby\Cms\Site $site */

$schemas = [];

// 1. Base Organization Schema (Always present)
// Using simple string replacement as in the original code for maximum safety
$siteDesc = str_replace('#','',str_replace('"',"'",str_replace("*","",$site->descrizione()->value())));

$orgSchema = [
    "@context" => "https://schema.org",
    "@type" => "Organization",
    "legalName" => (string)$site->title(),
    "alternateName" => (string)$site->alt_name(),
    "url" => (string)$site->url(),
    "description" => $siteDesc,
    "address" => [
        "@type" => "PostalAddress",
        "streetAddress" => (string)$site->address(),
        "addressLocality" => (string)$site->city(),
        "addressRegion" => (string)$site->region(),
        "postalCode" => (string)$site->cap(),
        "addressCountry" => (string)$site->country()
    ],
    "telephone" => (string)$site->tel(),
    "contactType" => "administration"
];

// Add logo if exists
if ($logo = $site->logo()->toFile()) {
    $orgSchema["logo"] = $logo->url();
}

$schemas[] = $orgSchema;

// 2. Dynamic Specific Schema based on Collection View
$collectionView = null;

// Determine collection type (from parent if child, or from self if collection landing)
if ($page->collection_toggle()->toBool()) {
    $collectionView = $page->collection_options()->value();
} elseif ($parent = $page->parent()) {
    if ($parent->collection_toggle()->toBool()) {
        $collectionView = $parent->collection_options()->value();
    }
}

$specificSchema = null;
$pageDesc = $page->descrizione()->isNotEmpty() ? $page->descrizione()->cleanText() : $siteDesc;
$imageFile = $page->thumbnail()->toFile() ?? $page->immagine()->toFile() ?? $site->seo_image()->toFile();
$imageUrl = $imageFile ? $imageFile->url() : null;

if ($collectionView == 'calendar') {
    $specificSchema = [
        "@context" => "https://schema.org",
        "@type" => "Event",
        "name" => (string)$page->title(),
        "description" => (string)$pageDesc,
        "image" => $imageUrl,
        "url" => (string)$page->url()
    ];
    
    if ($app = $page->appuntamenti()->toStructure()->first()) {
        $date = $app->giorno()->toDate('Y-m-d');
        if ($date) {
            $startTime = $app->orario_inizio()->isNotEmpty() ? $app->orario_inizio()->value() : '00:00';
            $specificSchema['startDate'] = $date . 'T' . $startTime;
            
            if ($app->orario_fine()->isNotEmpty()) {
                $specificSchema['endDate'] = $date . 'T' . $app->orario_fine()->value();
            }
        }
    }
    
    $locationStr = $page->dove()->value() ?: $page->indirizzo()->value();
    if ($locationStr) {
        $specificSchema['location'] = [
            "@type" => "Place",
            "name" => (string)$locationStr,
            "address" => (string)$locationStr
        ];
    }

} elseif ($collectionView == 'map') {
    $specificSchema = [
        "@context" => "https://schema.org",
        "@type" => "Place",
        "name" => (string)$page->title(),
        "description" => (string)$pageDesc,
        "image" => $imageUrl,
        "url" => (string)$page->url()
    ];
    
    if ($page->locator()->isNotEmpty() && $page->locator()->can('toLocation')) {
        $loc = $page->locator()->toLocation(); 
        if ($loc) {
            $specificSchema['geo'] = [
                "@type" => "GeoCoordinates",
                "latitude" => $loc->lat(),
                "longitude" => $loc->lon()
            ];
        }
    }
    
    $address = $page->indirizzo_luogo()->value() ?: $page->indirizzo()->value();
    if ($address) {
        $specificSchema['address'] = (string)$address;
    }

} elseif ($collectionView == 'blog') {
    $specificSchema = [
        "@context" => "https://schema.org",
        "@type" => "NewsArticle",
        "headline" => (string)$page->title(),
        "description" => (string)$pageDesc,
        "image" => $imageUrl,
        "datePublished" => $page->data_di_pubblicazione()->toDate('c'),
        "dateModified" => $page->modified('c'),
        "author" => [
            "@type" => "Organization",
            "name" => (string)$site->title()
        ],
        "publisher" => [
            "@type" => "Organization",
            "name" => (string)$site->title(),
            "logo" => [
                "@type" => "ImageObject",
                "url" => $imageUrl
            ]
        ]
    ];
}

if ($specificSchema) {
    $schemas[] = $specificSchema;
}

// 3. BreadcrumbList Schema
$breadcrumbs = $page->parents()->flip();
if ($breadcrumbs->count() > 0 || !$page->isHomePage()) {
    $items = [];
    
    // Home
    $items[] = [
        "@type" => "ListItem",
        "position" => 1,
        "name" => (string)$site->title(),
        "item" => (string)$site->url()
    ];
    
    $pos = 2;
    foreach ($breadcrumbs as $p) {
        $items[] = [
            "@type" => "ListItem",
            "position" => $pos++,
            "name" => (string)$p->title(),
            "item" => (string)$p->url()
        ];
    }
    
    // Current Page (if not home)
    if (!$page->isHomePage()) {
        $items[] = [
            "@type" => "ListItem",
            "position" => $pos,
            "name" => (string)$page->title(),
            "item" => (string)$page->url()
        ];
    }
    
    $schemas[] = [
        "@context" => "https://schema.org",
        "@type" => "BreadcrumbList",
        "itemListElement" => $items
    ];
}

// 4. Output scripts
foreach ($schemas as $schema) {
    $json = json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if ($json) {
        echo '<script type="application/ld+json">' . $json . '</script>' . "\n";
    }
}


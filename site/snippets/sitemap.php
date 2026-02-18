<?= '<?xml version="1.0" encoding="utf-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($pages as $p): ?>
    <?php if (in_array($p->uri(), $ignore)) continue ?>
    <?php if ($p->isHomePage()) $priority = 1.0; else $priority = number_format(max(0.1, 1.0 - ($p->depth() * 0.1)), 1); ?>
    <url>
        <loc><?= html($p->url()) ?></loc>
        <lastmod><?= $p->modified('c') ?></lastmod>
        <priority><?= $priority ?></priority>
    </url>
    
    <?php 
    // Se la pagina è un'istanza di CalendarFromCsvPage o simile, 
    // potremmo voler includere i figli virtuali. 
    // Nota: questo può essere costoso se ci sono molti CSV.
    if ($p->intendedTemplate()->name() === 'calendar-from-csv'): 
        foreach ($p->children() as $child): ?>
            <url>
                <loc><?= html($child->url()) ?></loc>
                <lastmod><?= $p->modified('c') ?></lastmod>
                <priority><?= number_format($priority - 0.2, 1) ?></priority>
            </url>
        <?php endforeach;
    endif; ?>
<?php endforeach ?>
</urlset>

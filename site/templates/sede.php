
<?php snippet('header') ?>
<?php snippet('menu') ?>

<?php snippet('check_banner',['posizione' => 'sopra',]); ?>

<!--?php snippet('search') ?--> 

<div class="sede_cover">
    <?php snippet('map') ?>

    <div class="meta_informations">
        <h2><?= str_replace("-"," ",$page->title()) ?></h2>
        <p>
        <?= $page->indirizzo() ?>
        <?php if($page->mail()->isNotEmpty()): ?>
            <br><br>
            <strong>Email:</strong><br> <?= $page->mail() ?>
        <?php endif; ?>
        <?php if($page->tel()->isNotEmpty()): ?>
            <br><br>
            <strong>Tel:</strong><br> <?= str_replace("'", "’", str_replace("-"," ", str_replace("tel.","", str_replace("_", " ", strtolower($page->tel()))))) ?>
        <?php endif; ?>
        </p>
        <?php if($page->tel()->isNotEmpty() OR $page->mail()->isNotEmpty() ): ?>
        <div class="block-cta" style="width: 100%; display: flex; justify-content: flex-start!important; align-items: flex-start!important; margin: 0; padding: 0; flex-direction: column;">
            <a  class="cta-block__item sede" 
                target="_blank"
                href="tel:<?= str_replace("'", "’", str_replace("-"," ", str_replace("tel.","", str_replace("_", " ", strtolower($page->tel()))))) ?>" 
                title="tel">CHIAMA LA LEGA</a>

            <a  class="cta-block__item sede" 
                target="_blank"
                href="mailto:<?= $page->mail() ?>" 
                title="email">SCRIVI ALLA LEGA</a>
        </div> 
        <?php endif; ?>
    </div>
</div>

<?php snippet('page-sede') ?>

<?php snippet('check_banner',['posizione' => 'sotto',]); ?>

<?php snippet('layouts', ['layout_content' => $site->footer(), 'class' => 'footer',]); ?>
<?php snippet('footer') ?>
 
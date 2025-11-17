<div class="footer_nav" style="display: flex; justify-content: space-around;">
<?php
$timestamp = time(); $currentDate = gmdate('Y', $timestamp);
?>
<footer style="">
Copyright © <?= $currentDate; ?> <?php if($site->copyright()->isNotEmpty()): ?>— <?= $site->copyright() ?><?php else: ?> <?php endif; ?>
</footer>

<footer style="">
<a style="text-decoration: none!important; color:#ff3300;  cursor: pointer!important; z-index:95!important;" class="credits" href="https://www.ff3300.com" title="FF3300 → Strategy + Design" target="_blank">FF3300</a> → Strategy + Design
</footer>
</div>

    <?php snippet('cookie-modal', [
        'assets' => true,
        'showOnFirst' => true,
        'features' => [
          'analytics' => 'Analytics',
        ]
    ]) ?>
    
<!-- JavaScript deferiti -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js" defer></script>
<script src="https://unpkg.com/swiper/swiper-bundle.min.js" defer></script>
<?= js('node_modules/bootstrap/dist/js/bootstrap.js', ['defer' => true]) ?>
<?= js('assets/build/js/js.js', ['defer' => true]) ?>

  </body>
</html>
  
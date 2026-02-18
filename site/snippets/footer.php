<?php if($site->copyright()->isNotEmpty()): ?>
  <div class="footer_nav" style="display: flex; justify-content: space-around;">
  <?php
  $timestamp = time(); $currentDate = gmdate('Y', $timestamp);
  ?>
  <footer style="">
  Copyright © <?= $currentDate; ?> — <?= $site->copyright() ?>
  </footer>

  </div>
<?php endif; ?>
    <?php snippet('cookie-modal', [
        'assets' => true,
        'showOnFirst' => true,
        'features' => [
          'analytics' => 'Analytics',
        ]
    ]) ?>
    
<!-- JavaScript bundled by Vite -->
<?= js('assets/build/js/js.js', ['defer' => true]) ?>

  </body>
</html>
  
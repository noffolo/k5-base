<?php
/** @var Kirby\Cms\Page $child */
use Kirby\Toolkit\Str;
use function Site\Helpers\Collection\formatDateItalian;

$titolo = $child->title()->value();
$desc   = $child->descrizione()->value();

$deadlineFormatted = null;
$deadline = $child->deadline()->toDate();
if ($deadline) {
    $deadlineFormatted = formatDateItalian($deadline);
}

$tag1 = null;
$categories = $child->child_category_selector()->split();
if (!empty($categories)) {
    $tag1 = implode(', ', $categories);
}

$orario = '';
if (isset($occurrence) && isset($occurrence['appointment'])) {
    $app = $occurrence['appointment'];
    $inizio = $app->orario_inizio()->toDate('H:i');
    $fine = $app->orario_fine()->toDate('H:i');
    $orario = $inizio . ($fine ? ' - ' . $fine : '');
}
?>

<a href="<?= $child->url() ?>" class="card-master" style="text-decoration: none; color: inherit; display: block; height: 100%;">
  <div class="cards-details orange" style="padding: 15px; height: 100%; display: flex; flex-direction: column;">
    <div class="cards-categories" style="margin-bottom: 15px; display: flex; flex-wrap: wrap; gap: 5px; flex-direction: row;">

      <?php if ($orario): ?>
        <span class="tag" style="max-width: fit-content; margin:0;">
          <?= esc($orario) ?>
        </span> 
      <?php endif; ?>

      <?php if ($tag1): ?>
        <?php foreach (Str::split($tag1, ',') as $t): ?>
          <span class="tag alt" style="margin:0;">
            <?= esc(trim($t)) ?>
          </span>
        <?php endforeach; ?>
      <?php endif; ?>
    
    </div>
    
    
    <div class="cards-title">
      <h2 style="font-size: 1.5rem; margin: 0; margin-bottom: 0; font-weight: 700;"><?= esc($titolo) ?></h2>
    </div>


    <div class="cards-categories" style="margin-bottom: 5px; display: flex; flex-wrap: wrap; gap: 5px; flex-direction: row;">
      
      <?php if ($desc): ?>
        <div class="description-text" style="font-size: 1rem; line-height: 1.4; margin-top: 5px; width: 100%;">
            <?= esc(Str::excerpt($desc, 150)) ?>
        </div>
      <?php endif; ?>
<hr style="border: none; border-top: 1px solid whitesmoke!important; margin: 5px 0; width: 100%;">
    <?php if ($deadlineFormatted): ?>
      <p class="deadline" style="color: black; margin: 0; margin-bottom: 5px; font-weight: bold; font-family: 'Inter', sans-serif; font-size: 1rem;">
        Application entro il giorno <strong><?= esc($deadlineFormatted) ?></strong>
      </p>
    <?php endif; ?>
    </div>
  </div>
</a>

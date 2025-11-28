<?php $parent = $item->parent() ?>
<?php $deadline_exist = "off"; ?>
<?php $deadline_toggle = "off"; ?>
<?php $deadline = $item->deadline() OR NULL ?>
<?php $facilitato = false ?>
<div class="cards-details orange" style="<?php if($page->parent() !== NULL): ?>padding: 15px; min-width: fit-content; margin: 0 auto!important;<?php else: ?>padding: 15px;<?php endif; ?> margin: 0;" class="cards-info" <?php  if($direction == "row"): ?>style="margin-left: 15px"<?php endif; ?>>

<?php if($tag_toggle == true AND $item->child_category_selector()->isNotEmpty()): ?>
<div class="cards-categories">
    <span class="tag parent"><?= $item->parent()->title() ?></span>

    <?php foreach($item->child_category_selector()->split() as $category): ?>
        <span class="tag"><?= $category ?></span>
        <?php if(strtolower($category) == "workshop"): ?>
            <?php $facilitato = true ?>
        <?php endif; ?>
    <?php endforeach; ?>    
</div>
<?php endif; ?>

<div>
    <div class="cards-dates" style="display: flex; width: 100%; flex-direction: column;">
        <?php if($item->appuntamenti()->isNotEmpty()): ?>
            <?php 
            $appuntamenti = $item->appuntamenti()->toStructure();
            $groups = [];
            $currentGroup = null;

            foreach($appuntamenti as $appuntamento) {
                $date = $appuntamento->giorno()->toDate();
                $monthYear = date('Y-m', $date);
                $startTime = $appuntamento->orario_inizio()->toDate('H:i');
                $endTime = $appuntamento->orario_fine()->isNotEmpty() ? $appuntamento->orario_fine()->toDate('H:i') : '';
                $timeString = $startTime . ($endTime ? ' → ' . $endTime : '');
                
                if ($currentGroup && $currentGroup['monthYear'] === $monthYear) {
                    $currentGroup['items'][] = [
                        'date' => $date,
                        'time' => $timeString
                    ];
                    // Check if time is different from others in group
                    if ($currentGroup['uniformTime'] && $currentGroup['commonTime'] !== $timeString) {
                        $currentGroup['uniformTime'] = false;
                    }
                } else {
                    if ($currentGroup) {
                        $groups[] = $currentGroup;
                    }
                    $currentGroup = [
                        'monthYear' => $monthYear,
                        'monthYearDate' => $date,
                        'items' => [[
                            'date' => $date,
                            'time' => $timeString
                        ]],
                        'uniformTime' => true,
                        'commonTime' => $timeString
                    ];
                }
            }
            if ($currentGroup) {
                $groups[] = $currentGroup;
            }
            ?>
            <?php foreach($groups as $group): ?>
            <div class="appuntamento" style="display: flex; width: 100%; flex-direction: row; justify-content: space-between; flex-wrap:nowrap;">
                <?php 
                $formatterDay = new IntlDateFormatter('it_IT', IntlDateFormatter::NONE, IntlDateFormatter::NONE);
                $formatterDay->setPattern('d');
                
                $formatterMonthYear = new IntlDateFormatter('it_IT', IntlDateFormatter::NONE, IntlDateFormatter::NONE);
                $formatterMonthYear->setPattern('MMM Y');
                ?>
                <span style="width: fit-content; min-width: fit-content; text-transform: uppercase;" class="">
                    <?php 
                    $count = count($group['items']);
                    $i = 0;
                    foreach($group['items'] as $groupItem): 
                        $i++;
                    ?>
                        <strong><?= $formatterDay->format($groupItem['date']) ?></strong>
                        <?php if (!$group['uniformTime']): ?>
                             (<?= $groupItem['time'] ?>)
                        <?php endif; ?>
                        <?php if ($i < $count): ?>, <?php endif; ?>
                    <?php endforeach; ?>
                    <strong> <?= $formatterMonthYear->format($group['monthYearDate']) ?></strong>
                </span>  
                <span style="width: fit-content; min-width: fit-content; text-transform: uppercase;" class="">
                    <?php if($group['uniformTime']): ?>
                        <?= $group['commonTime'] ?>
                    <?php endif; ?>
                </span>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php if($item->dove()->isNotEmpty()): ?>
        <span style="width: fit-content; min-width: fit-content;" class="">
            ⏷ <?= $item->dove() ?>
        </span>
    <?php endif; ?>
</div>

<?php if($item->appuntamenti()->isNotEmpty() OR $item->dove()->isNotEmpty()): ?>
<hr style="border: none; border-top: 1px solid; opacity: 1;">
<?php endif; ?>

<div class="cards-title" style="margin:0!important;">
<?php if($parent == "attivita"): ?>
    <h2 style="margin-bottom: 0;"><?= $item->title() ?></h2>
<?php else: ?>
    <h2><?= $item->title() ?></h2>
<?php endif; ?>
</div>

<?php if($parent == "attivita"): ?>
<?php else: ?>
<div class="cards-text">
    <?php echo $item->descrizione()->kirbytext(); ?>
</div>
<?php endif; ?>

<?php if ($item->team()->isNotEmpty()): ?>
    <?php if($facilitato == false): ?>
    <div class="team-label"><p style="margin: 0; margin-top: 15px; margin-bottom: 0;">Con la partecipazione di:
    <?php else: ?>
    <div class="team-label"><p style="margin: 0; margin-top: 15px; margin-bottom:0;">Attività facilitata da:
    <?php endif; ?>
        <?php $members = 0 ?>
        <?php foreach($item->team()->toStructure() as $team_member): ?>
            <?php $members++; ?>
        <?php endforeach; ?>
        <?php $printed_members = 0 ?>
        <?php foreach($item->team()->toStructure() as $team_member): ?>
            <?php $printed_members++; ?>
            <span><strong><?= $team_member->persona() ?></strong> (<?= $team_member->ruolo() ?>)<?php if($printed_members < $members): ?>,<?php endif; ?> </span>
        <?php endforeach; ?>
    </p></div>
    <div class="cards-team" style="display: flex; width: 100%; justify-content: center; flex-wrap:wrap; text-align: center;">
    
    </div>

<?php endif; ?>

    <?php if ($item->deadline()->isNotEmpty()): ?>
        <?php $deadline_exist = "on" ?>
    <?php endif; ?>
    <?php if ($item->deadline()->isNotEmpty() && strtotime($item->deadline()) >= strtotime('today')): ?>
        <hr style="border: none; border-top: 1px solid; opacity: 1;">
        <?php $deadline_toggle = "on" ?>
        <?php $deadline = $item->deadline() ?>
        <div class="cards-dates" style="display: flex; width: 100%; justify-content: center; flex-wrap:wrap; ">
            <?php 
            $formatter = new IntlDateFormatter('it_IT', IntlDateFormatter::NONE, IntlDateFormatter::NONE);
            $formatter->setPattern('d MMMM Y'); // Modello simile a %d – %b – %Y;
            ?>
            <span id="deadline" class="center" style="width: 100%; display: flex; justify-content: center;" class="time">
                <strong style="min-width: fit-content;">Iscriviti entro il <u><?= $formatter->format($deadline->toDate()) ?></u></strong>
            </span>
        </div>

        <?php 
        $formatter = new IntlDateFormatter('it_IT', IntlDateFormatter::NONE, IntlDateFormatter::NONE);
        $formatter->setPattern('d MMM Y'); // Modello simile a %d – %b – %Y;
        ?>

        <?php if($page->parent() !== NULL AND $page->parent()->collection_options() == "calendar"): ?>
            <?php if(strtotime($page->deadline()) >= strtotime('today')): ?>
            <?php snippet('form-request-counter',[
            'page' => $page,
            ])?>
            <?php endif; ?>
        <?php else: ?>
            <?php if(strtotime($item->deadline()) >= strtotime('today')): ?>
            <?php snippet('form-request-counter',[
            'page' => $item,
            ])?>
            <?php endif; ?>
        <?php endif; ?>

    <?php else: ?>
    <?php endif; ?>

</div>
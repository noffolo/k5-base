

<?php if($block->isNotEmpty()): ?>

<?php
$structure = $block->slider();
$counter   = 0;
$suffisso = generateRandomString();
$string = generateRandomString();
$gallery_pagination = true;
$gallery_arrows = true;
?>
<?php
$structure_data = $structure->toStructure();
$items_count = $structure_data->count();
?>
<?php if($items_count > 0): ?>
<div class="block-slider">
    <!-- Slider main container -->
    <div class="swiper">
        <!-- Additional required wrapper -->
        <div class="swiper-wrapper">
        <?php $counter = 0; ?>
            <?php foreach( $structure_data as $item ): ?>
                <?php $crop = $item->crop()->toBool(); ?>
                <!-- Slides -->
                <?php $counter++; ?>
                <div class="swiper-slide carosel">

                    <?php if($item->pics()->isNotEmpty() AND $item->video()->isEmpty()): ?>
                        <?php snippet('block-slide-image',[
                            'suffisso' => $suffisso,
                            'string' => $string,
                            'structure' => $structure,
                            'counter' => $counter,
                            'crop' => $crop,
                            'item' => $item,
                        ]); ?>

                    <?php elseif($item->pics()->isEmpty() AND $item->video()->isNotEmpty()): ?>
                        <?php snippet('block-slide-video',[
                            'suffisso' => $suffisso,
                            'string' => $string,
                            'structure' => $structure,
                            'counter' => $counter,
                            'crop' => $crop,
                            'item' => $item,
                        ]); ?>
                    <?php endif; ?>

                    <?php if( $item->descrizione()->isNotEmpty() OR $item->title()->isNotEmpty() ): ?>
                        <div class="slide-informations">
                            <?php snippet('block-slide-info',[
                                'item' => $item,
                                'block' => $block,
                             ])?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- If we need pagination -->
        <?php if(isset($gallery_pagination) AND  $gallery_pagination == true): ?>
            <div class="swiper-pagination"></div>
        <?php endif; ?>

        <!-- If we need navigation buttons -->
        <?php if(isset($gallery_arrows) AND $gallery_arrows == true): ?>
            <?php if($counter > 1): ?>
                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>
            <?php endif; ?>
        <?php endif; ?>

        </div>

    </div>

<?php endif; ?> 
<?php endif; ?> 


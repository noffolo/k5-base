<?php if($block->collection()->isNotEmpty()): ?>
    <?php $latitude = $block->latitude(); ?>
    <?php $longitude = $block->longitude(); ?>
    <?php $zoom = $block->zoom(); ?>
    <div class="block-map">
        <div class="block-map-title">
            <h1><?php echo $block->title(); ?></h1>
        </div>
        <div class="block-map-container">
            <?php 
            $parent = $block->collection()->toPage(); 
            $collection = $parent->children(); 
            $categories = $parent->parent_category_manager()->toStructure(); // Categorie definite nel parent
            $categoryMarkerMap = \NonDeterministic\Helpers\CollectionHelper::buildCategoryMarkerMap($categories);
            $defaultMarkerUrl = $parent->default_marker()->toFiles()->first()?->url() ?? $site->marker()->toFiles()->first()?->url();
            ?>

            <?php 
            $locations_array = [];
            foreach($collection as $item):
                $location = $item->locator()->toLocation();
                $child_categories = $item->child_category_selector()->split(','); // Categorie associate al child

                $item_marker = \NonDeterministic\Helpers\CollectionHelper::resolveCategoryMarker(
                    $child_categories,
                    $categoryMarkerMap,
                    $defaultMarkerUrl
                );

                if ($location && $location->lat() && $location->lon()) {
                    array_push($locations_array, [
                        'title' => $item->title()->value() ?? '',
                        'lat' => $location->lat(),
                        'lon' => $location->lon(),
                        'url' => $item->url() ?? '',
                        'marker' => $item_marker ?? '' // Aggiunge il marker specifico
                    ]);
                }
            endforeach;
            ?>

            <?php if(!empty($locations_array)): ?>

                <div class="map-container" style="position: relative;">
                    <div id="map" style="width: 100%; height: 600px"></div>

                    <script>
                        const initMapBlock = () => {
                            mapboxgl.accessToken = '<?= $parent->map_key() ?>';
                            var map = new mapboxgl.Map({
                                container: 'map',
                                style: '<?= $parent->map_style() ?>',
                                center: [<?php echo $latitude; ?>, <?php echo $longitude; ?>],
                                zoom: <?php echo $zoom; ?>
                            });

                            // Disable scroll zoom
                            map.scrollZoom.disable();

                            var geojson = {
                                type: 'FeatureCollection',
                                features: [
                                    <?php foreach($locations_array as $location): ?>
                                        {
                                            type: 'Feature',
                                            geometry: {
                                                type: 'Point',
                                                coordinates: [<?= htmlspecialchars($location['lon']) ?>, <?= htmlspecialchars($location['lat']) ?>]
                                            },
                                            properties: {
                                                title: '<?= htmlspecialchars($location['title']) ?>',
                                                url: '<?= htmlspecialchars($location['url']) ?>',
                                                marker: '<?= htmlspecialchars($location['marker']) ?>' // Marker specifico
                                            }
                                        },
                                    <?php endforeach; ?>
                                ]
                            };

                            geojson.features.forEach(function (marker) {
                                var el = document.createElement('div');
                                el.className = 'marker';
                                el.style.backgroundImage = `url('${marker.properties.marker}')`;
                                el.style.width = '30px';
                                el.style.height = '45px';

                                var mapMarker = new mapboxgl.Marker(el)
                                    .setLngLat(marker.geometry.coordinates)
                                    .addTo(map);

                                var tooltip = new mapboxgl.Popup({
                                    closeButton: false,
                                    closeOnClick: false
                                });

                                var tooltipContent = `
                                    <p><a href="${marker.properties.url}">${marker.properties.title}</a></p>
                                `;

                                el.addEventListener('mouseenter', function () {
                                    tooltip.setLngLat(marker.geometry.coordinates)
                                        .setHTML(tooltipContent)
                                        .addTo(map);
                                });

                                el.addEventListener('mouseleave', function () {
                                    tooltip.remove();
                                });

                                el.addEventListener('click', function () {
                                    window.open(marker.properties.url, '_blank');
                                });
                            });

                            map.addControl(new mapboxgl.NavigationControl());
                        };

                        if (window.mapboxgl) {
                            initMapBlock();
                        } else {
                            document.addEventListener('mapbox-ready', initMapBlock);
                        }
                    </script>

                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
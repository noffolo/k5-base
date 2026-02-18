<!-- FILTRI -->
<?php snippet('collection-filters',[
      'logic' => 'or' // oppure 'or'
]); ?>

    <div id="map" class="skeleton" style="width: 100%; height: 600px;"></div>
    
    <script>
        document.addEventListener('mapbox-ready', () => {
            mapboxgl.accessToken = '<?= $collection_parent->map_key() ?>';
            var map = new mapboxgl.Map({
                container: 'map',
                style: '<?= $collection_parent->map_style() ?>',
                center: [<?= $longitude ?>, <?= $latitude ?>],
                zoom: <?= $zoom ?>
            });
            map.on('load', () => {
                document.getElementById('map').classList.remove('skeleton');
            });
            map.scrollZoom.disable();

            var geojson = {
                type: 'FeatureCollection',
                features: [
                    <?php foreach ($locations_array as $index => $location): ?>
                    {
                        type: 'Feature',
                        geometry: { type: 'Point', coordinates: [<?= $location['lon'] ?>, <?= $location['lat'] ?>] },
                        properties: {
                            title: '<?= htmlspecialchars($location['title']) ?>',
                            url: '<?= htmlspecialchars($location['url']) ?>',
                            marker: '<?= htmlspecialchars($location['marker']) ?>'
                        }
                    }<?= $index < count($locations_array) - 1 ? ',' : '' ?>
                    <?php endforeach; ?>
                ]
            };

            geojson.features.forEach(function (marker) {
                var el = document.createElement('div');
                el.className = 'marker';
                el.style.backgroundImage = `url('${marker.properties.marker}')`;
                el.style.width = '30px';
                el.style.height = '45px';

                new mapboxgl.Marker(el)
                    .setLngLat(marker.geometry.coordinates)
                    .setPopup(new mapboxgl.Popup().setHTML(`<p><a href="${marker.properties.url}" target="_self">${marker.properties.title}</a></p>`))
                    .addTo(map);
            });

            map.addControl(new mapboxgl.NavigationControl());
        });
    </script>

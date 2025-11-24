<?php
/**
 * Mappa singola sede con stile coerente al template `sedi.php`.
 */
$parent = $page->parent();

$token = (string)(option('mapbox.token') ?: $site->mapbox_token()->or(''));

// Normalizza lo style URL come nel template `sedi.php`
$styleRaw = trim((string)$parent?->mapbox_style_url()->value());
$styleUrl = null;
if ($styleRaw !== '') {
    if (preg_match('~^mapbox://styles/[^/]+/[^/]+$~i', $styleRaw)) {
        $styleUrl = $styleRaw;
    } elseif (preg_match('~^https?://api\.mapbox\.com/styles/v1/[^/]+/[^/?#]+~i', $styleRaw)) {
        $styleUrl = $styleRaw;
    } elseif (preg_match('~^https?://studio\.mapbox\.com/styles/([^/]+)/([^/]+)/?~i', $styleRaw, $m)) {
        $styleUrl = 'mapbox://styles/' . $m[1] . '/' . $m[2];
    } else {
        $styleUrl = $styleRaw;
    }
}
if (!$styleUrl) {
    $styleUrl = 'mapbox://styles/mapbox/light-v11';
}

// Coordinate della sede
$latStr = str_replace(',', '.', trim((string)$page->lat()));
$lngStr = str_replace(',', '.', trim((string)$page->lng()));
$lat    = is_numeric($latStr) ? (float)$latStr : null;
$lng    = is_numeric($lngStr) ? (float)$lngStr : null;

// Marker: prima quello configurato sul parent, altrimenti fallback
if ($parent && $parent->marker()->isNotEmpty() && ($markerFile = $parent->marker()->toFile())) {
    $markerUrl = $markerFile->url();
} else {
    $markerUrl = url('assets/fallback/marker.svg');
}

$mapData = [
    'token'        => $token,
    'style'        => $styleUrl,
    'center'       => ['lng' => $lng, 'lat' => $lat],
    'zoom'         => 16,
    'showControls' => (bool)$parent?->mapbox_show_controls()->toBool(),
];

// Contenuto popup/marker
$titleHtml     = str_replace("'", "’", (string)$page->nome()->or($page->title()));
$indirizzoHtml = str_replace("'", "’", (string)$page->indirizzo());
$textHtml      = "<strong>{$titleHtml}</strong><br>{$indirizzoHtml}";
?>

<link href="https://api.mapbox.com/mapbox-gl-js/v3.3.0/mapbox-gl.css" rel="stylesheet">
<script src="https://api.mapbox.com/mapbox-gl-js/v3.3.0/mapbox-gl.js"></script>

<div class="map-container" style="position: relative;">
  <div id="map-sede" style="width:100%; min-height:77vh; border-radius:.5rem; overflow:hidden;"></div>
</div>

<script>
  const MAP_DATA   = <?= json_encode($mapData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
  const MARKER_URL = <?= json_encode($markerUrl, JSON_UNESCAPED_SLASHES) ?>;
  const FEATURE    = {
    type: 'Feature',
    geometry: { type: 'Point', coordinates: [MAP_DATA.center.lng, MAP_DATA.center.lat] },
    properties: { text: <?= json_encode($textHtml, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?> }
  };

  if (!MAP_DATA || !MAP_DATA.token) {
    console.error('Mapbox token mancante.');
    document.getElementById('map-sede').innerHTML = '<div style="padding:1rem;border:1px solid #eee;border-radius:.5rem;background:#fff">Token Mapbox mancante: configura il token.</div>';
  } else if (!MAP_DATA.center || MAP_DATA.center.lat === null || MAP_DATA.center.lng === null) {
    console.error('Coordinate mancanti per la sede.');
    document.getElementById('map-sede').innerHTML = '<div style="padding:1rem;border:1px solid #eee;border-radius:.5rem;background:#fff">Coordinate mancanti per questa sede.</div>';
  } else {
    mapboxgl.accessToken = MAP_DATA.token;

    const STYLE_PRIMARY   = MAP_DATA.style || 'mapbox://styles/mapbox/light-v11';
    const STYLE_FALLBACK1 = 'mapbox://styles/mapbox/light-v11';
    const STYLE_FALLBACK2 = 'mapbox://styles/mapbox/navigation-day-v1';

    const map = new mapboxgl.Map({
      container: 'map-sede',
      style: STYLE_PRIMARY,
      center: [MAP_DATA.center.lng, MAP_DATA.center.lat],
      zoom: MAP_DATA.zoom,
      minZoom: 4,
      maxZoom: 18,
      attributionControl: false
    });

    if (MAP_DATA.showControls) {
      map.addControl(new mapboxgl.NavigationControl({ showCompass: false }), 'top-right');
    }
    map.scrollZoom.disable();

    let triedFallback1 = false, triedFallback2 = false;
    map.on('error', (e) => {
      const msg = (e && e.error && (e.error.message || e.error.toString())) || e.toString();
      console.warn('Mapbox style error:', msg);
      if (!triedFallback1) { triedFallback1 = true; try { map.setStyle(STYLE_FALLBACK1); } catch(_) {} return; }
      if (!triedFallback2) { triedFallback2 = true; try { map.setStyle(STYLE_FALLBACK2); } catch(_) {} return; }
    });

    map.on('style.load', async () => {
      const isSvg = /\.svg(?:\?|#|$)/i.test(MARKER_URL);
      if (isSvg) {
        addDomMarker(FEATURE, MARKER_URL);
        return;
      }

      const iconAdded = await tryAddRasterIcon('sede-marker', MARKER_URL);
      if (iconAdded) {
        await ensureSource('sede', { type: 'geojson', data: { type: 'FeatureCollection', features: [FEATURE] } });
        addSymbolLayer('sede', 'sede-symbol', 'sede-marker');
        bindInteractions('sede-symbol');
      } else {
        addDomMarker(FEATURE, MARKER_URL);
      }
    });

    async function ensureSource(id, def) {
      if (map.getSource(id)) map.getSource(id).setData(def.data);
      else map.addSource(id, def);
    }

    async function tryAddRasterIcon(name, url) {
      try {
        const resp = await fetch(url, { cache: 'force-cache' });
        if (!resp.ok) throw new Error('HTTP ' + resp.status);
        const blob  = await resp.blob();
        const bmp   = await createImageBitmap(blob);
        if (!map.hasImage(name)) map.addImage(name, bmp, { sdf: false, pixelRatio: 2 });
        return true;
      } catch (e1) {
        return new Promise((resolve) => {
          map.loadImage(url, (err, image) => {
            if (!err && image) {
              if (!map.hasImage(name)) map.addImage(name, image);
              resolve(true);
            } else {
              console.warn('Impossibile caricare il marker come sprite, uso DOM marker.', err || '');
              resolve(false);
            }
          });
        });
      }
    }

    function addSymbolLayer(sourceId, layerId, iconName) {
      if (!map.getLayer(layerId)) {
        map.addLayer({
          id: layerId,
          type: 'symbol',
          source: sourceId,
          layout: {
            'icon-image': iconName,
            'icon-size': 0.7,
            'icon-allow-overlap': true
          }
        });
      }
    }

    function addDomMarker(feature, iconUrl) {
      document.querySelectorAll('.marker-html').forEach(el => el.remove());
      const el = document.createElement('div');
      el.className = 'marker-html';
      el.style.width = '36px';
      el.style.height = '36px';
      el.style.backgroundImage = `url('${iconUrl}')`;
      el.style.backgroundRepeat = 'no-repeat';
      el.style.backgroundPosition = 'center';
      el.style.backgroundSize = 'contain';
      el.style.cursor = 'pointer';

      new mapboxgl.Marker({ element: el, anchor: 'bottom' })
        .setLngLat(feature.geometry.coordinates)
        .addTo(map);

    }

    function bindInteractions(layerId) {
      const popup = new mapboxgl.Popup({ closeButton: false, closeOnClick: false });
      map.on('mouseenter', layerId, (e) => {
        map.getCanvas().style.cursor = 'pointer';
        const f = e.features && e.features[0];
        if (!f) return;
        popup.setLngLat(f.geometry.coordinates).setHTML(f.properties.text).addTo(map);
      });
      map.on('mouseleave', layerId, () => {
        map.getCanvas().style.cursor = '';
        popup.remove();
      });
    }
  }
</script>

<style>
  .mapboxgl-ctrl-logo, .mapboxgl-ctrl-attrib { display: none !important; }
  .marker-html { will-change: transform; }
</style>
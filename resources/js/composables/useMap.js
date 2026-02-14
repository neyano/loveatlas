import { ref, onMounted, onUnmounted } from 'vue';
import L from 'leaflet';
import 'leaflet.markercluster';

const WORK_TYPE_COLORS = {
    movie: '#3498DB',   // 青
    anime: '#E74C3C',   // 赤
    drama: '#2ECC71',   // 緑
    novel: '#9B59B6',   // 紫
    game: '#F39C12',    // オレンジ
    other: '#95A5A6',   // 灰
};

function createIcon(type) {
    const color = WORK_TYPE_COLORS[type] || WORK_TYPE_COLORS.other;
    return L.divIcon({
        className: 'map-pin',
        html: `<div style="background:${color};width:24px;height:24px;border-radius:50%;border:3px solid white;box-shadow:0 2px 6px rgba(0,0,0,.3);"></div>`,
        iconSize: [24, 24],
        iconAnchor: [12, 12],
    });
}

export function useMap(containerRef) {
    const map = ref(null);
    const markerCluster = ref(null);

    function init(options = {}) {
        const defaultCenter = [36.5, 138.0]; // 日本中央
        const defaultZoom = 6;

        map.value = L.map(containerRef.value, {
            center: options.center || defaultCenter,
            zoom: options.zoom || defaultZoom,
            zoomControl: true,
        });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19,
        }).addTo(map.value);

        markerCluster.value = L.markerClusterGroup();
        map.value.addLayer(markerCluster.value);
    }

    function getBounds() {
        if (!map.value) return null;
        const b = map.value.getBounds();
        return {
            north: b.getNorth(),
            south: b.getSouth(),
            east: b.getEast(),
            west: b.getWest(),
        };
    }

    function setMarkers(quotes) {
        if (!markerCluster.value) return;
        markerCluster.value.clearLayers();

        quotes.forEach((q) => {
            const marker = L.marker(
                [q.location.latitude, q.location.longitude],
                { icon: createIcon(q.work?.type) }
            );

            marker.bindPopup(`
                <div class="map-popup">
                    <p class="map-popup__quote">${q.quote_text.substring(0, 60)}${q.quote_text.length > 60 ? '...' : ''}</p>
                    <p class="map-popup__work">${q.work?.title || ''}</p>
                    <a href="/quotes/${q.id}" class="map-popup__link">詳細を見る</a>
                </div>
            `);

            markerCluster.value.addLayer(marker);
        });
    }

    function onMoveEnd(callback) {
        if (map.value) {
            map.value.on('moveend', callback);
        }
    }

    onUnmounted(() => {
        if (map.value) {
            map.value.remove();
            map.value = null;
        }
    });

    return { map, init, getBounds, setMarkers, onMoveEnd };
}

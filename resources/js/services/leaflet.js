import L from 'leaflet'
import 'leaflet/dist/leaflet.css'

import markerIcon from 'leaflet/dist/images/marker-icon.png'
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png'
import markerShadow from 'leaflet/dist/images/marker-shadow.png'

/**
 * Leaflet bundles marker icon URLs relative to its own CSS, which
 * webpack breaks when we re-bundle. We patch the default icon once
 * at module load so every marker the user never explicitly configures
 * still shows a visible pin.
 */
let patched = false
function patchDefaultIcon() {
    if (patched) return
    delete L.Icon.Default.prototype._getIconUrl
    L.Icon.Default.mergeOptions({
        iconRetinaUrl: markerIcon2x,
        iconUrl: markerIcon,
        shadowUrl: markerShadow,
    })
    patched = true
}

/**
 * Build a Leaflet map inside `element` using the field's configured
 * tile layer. Returns { map, tileLayer } so the caller can tear down
 * cleanly on unmount.
 */
export function createMap(element, options = {}) {
    patchDefaultIcon()

    const {
        center = [0, 0],
        zoom = 13,
        tileLayer = {},
        interactive = true,
    } = options

    const map = L.map(element, {
        zoomControl: interactive,
        dragging: interactive,
        touchZoom: interactive,
        doubleClickZoom: interactive,
        scrollWheelZoom: interactive,
        boxZoom: interactive,
        keyboard: interactive,
        tap: interactive,
    }).setView(center, zoom)

    const layer = L.tileLayer(
        tileLayer.url || 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        {
            attribution: tileLayer.attribution || '',
            maxZoom: tileLayer.maxZoom || 19,
        },
    )
    layer.addTo(map)

    return { map, tileLayer: layer, L }
}

export { L }

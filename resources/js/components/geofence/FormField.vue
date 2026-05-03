<template>
    <DefaultField
        :field="field"
        :errors="errors"
        :show-help-text="showHelpText"
        :full-width-content="true"
    >
        <template #field>
            <div class="geo-field-wrapper is-form" data-testid="geo-geofence-form">
                <div class="geo-field-toolbar">
                    <button
                        type="button"
                        class="geo-field-button"
                        data-testid="geo-geofence-draw"
                        :disabled="drawing"
                        @click="startDraw"
                    >
                        {{ drawing ? __('Drawing…') : (hasPolygon ? __('Redraw') : __('Draw polygon')) }}
                    </button>
                    <button
                        type="button"
                        class="geo-field-button is-secondary"
                        data-testid="geo-geofence-clear"
                        :disabled="!hasPolygon && !drawing"
                        @click="clear"
                    >
                        {{ __('Clear') }}
                    </button>
                    <span class="geo-field-summary" data-testid="geo-geofence-summary">{{ summary }}</span>
                </div>
                <div ref="mapEl" class="geo-field-map" data-testid="geo-geofence-map" />
            </div>
        </template>
    </DefaultField>
</template>

<script>
import { FormField, HandlesValidationErrors } from 'laravel-nova'
import { createMap, L } from '../../services/leaflet.js'
import 'leaflet-draw'

export default {
    mixins: [FormField, HandlesValidationErrors],
    props: ['resourceName', 'resourceId', 'field'],
    data() {
        return {
            map: null,
            layer: null,
            vertices: [],
            drawing: false,
            drawHandler: null,
        }
    },
    computed: {
        hasPolygon() { return this.vertices.length >= 3 },
        minVertices() { return this.field.minVertices || 3 },
        summary() {
            if (this.drawing) return this.__('Click on the map to add vertices — double-click to finish.')
            if (!this.hasPolygon) return this.__('No polygon drawn.')
            return this.__(':count vertices', { count: this.vertices.length })
        },
    },
    mounted() {
        this.$nextTick(() => this.initMap())
    },
    beforeUnmount() {
        this.drawHandler?.disable()
        this.map?.remove()
    },
    methods: {
        setInitialValue() {
            const raw = this.field.value
            if (raw && raw.type === 'Polygon' && Array.isArray(raw.coordinates?.[0])) {
                this.vertices = raw.coordinates[0].map(([lng, lat]) => [lat, lng])
                this.value = JSON.stringify(raw)
            } else {
                this.vertices = []
                this.value = ''
            }
        },
        initMap() {
            const center = this.vertices.length
                ? this.vertices[0]
                : [this.field.defaultCenter?.lat ?? 0, this.field.defaultCenter?.lng ?? 0]

            const { map } = createMap(this.$refs.mapEl, {
                center,
                zoom: this.field.defaultZoom || 14,
                tileLayer: this.field.tileLayer || {},
            })
            this.map = map
            if (this.hasPolygon) this.drawExisting()
        },
        drawExisting() {
            if (this.layer) this.map.removeLayer(this.layer)
            this.layer = L.polygon(this.vertices, { color: '#2563eb', weight: 2 }).addTo(this.map)
            this.map.fitBounds(this.layer.getBounds(), { padding: [16, 16] })
        },
        startDraw() {
            if (this.drawing) return
            if (this.layer) {
                this.map.removeLayer(this.layer)
                this.layer = null
            }
            this.vertices = []
            this.drawing = true
            this.drawHandler = new L.Draw.Polygon(this.map, {
                shapeOptions: { color: '#2563eb', weight: 2 },
                allowIntersection: false,
            })
            this.map.once(L.Draw.Event.CREATED, (event) => {
                this.layer = event.layer
                this.layer.addTo(this.map)
                const latlngs = this.layer.getLatLngs()[0]
                this.vertices = latlngs.map((p) => [p.lat, p.lng])
                this.commit()
                this.drawing = false
            })
            this.drawHandler.enable()
        },
        commit() {
            if (!this.hasPolygon) {
                this.value = ''
                return
            }
            const ring = this.vertices.map(([lat, lng]) => [lng, lat])
            const first = ring[0]
            const last = ring[ring.length - 1]
            if (first[0] !== last[0] || first[1] !== last[1]) {
                ring.push([first[0], first[1]])
            }
            this.value = JSON.stringify({ type: 'Polygon', coordinates: [ring] })
        },
        clear() {
            this.drawHandler?.disable()
            if (this.layer) this.map.removeLayer(this.layer)
            this.layer = null
            this.vertices = []
            this.drawing = false
            this.value = ''
        },
        fill(formData) {
            formData.append(this.fieldAttribute, this.value ?? '')
        },
    },
}
</script>

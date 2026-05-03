<template>
    <PanelItem :index="index" :field="field" :full-width-content="true">
        <template #value>
            <div
                class="geo-field-wrapper is-detail"
                :style="{ height: `${height}px` }"
                data-testid="geo-geofence-detail"
            >
                <div v-if="!hasPolygon" class="geo-field-empty" data-testid="geo-geofence-empty">
                    {{ __('No geofence set') }}
                </div>
                <div v-else ref="mapEl" class="geo-field-map" data-testid="geo-geofence-map" />
            </div>
        </template>
    </PanelItem>
</template>

<script>
import { createMap } from '../../services/leaflet.js'

export default {
    props: {
        index: { type: Number, required: true },
        resource: { type: Object, required: true },
        resourceName: { type: String, required: true },
        resourceId: { type: [Number, String], default: null },
        field: { type: Object, required: true },
    },
    data() {
        return { map: null, layer: null }
    },
    computed: {
        height() { return this.field.height || 360 },
        polygon() {
            const value = this.field.value
            if (!value || value.type !== 'Polygon') return null
            return value.coordinates?.[0] || null
        },
        hasPolygon() {
            return Array.isArray(this.polygon) && this.polygon.length >= 3
        },
    },
    mounted() {
        this.$nextTick(() => {
            if (!this.hasPolygon) return
            this.initMap()
        })
    },
    beforeUnmount() {
        this.map?.remove()
    },
    methods: {
        initMap() {
            const latlngs = this.polygon.map(([lng, lat]) => [lat, lng])
            const { map, L } = createMap(this.$refs.mapEl, {
                center: latlngs[0],
                zoom: this.field.defaultZoom || 14,
                tileLayer: this.field.tileLayer || {},
            })
            this.map = map
            this.layer = L.polygon(latlngs, { color: '#2563eb', weight: 2 }).addTo(map)
            map.fitBounds(this.layer.getBounds(), { padding: [16, 16] })
        },
    },
}
</script>

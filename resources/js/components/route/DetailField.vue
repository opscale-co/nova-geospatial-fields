<template>
    <PanelItem :index="index" :field="field" :full-width-content="true">
        <template #value>
            <div
                class="geo-field-wrapper is-detail"
                :style="{ height: `${height}px` }"
                data-testid="geo-route-detail"
            >
                <div v-if="!hasRoute" class="geo-field-empty" data-testid="geo-route-empty">
                    No route set
                </div>
                <template v-else>
                    <div class="geo-field-toolbar">
                        <span class="geo-field-summary" data-testid="geo-route-summary">
                            {{ coordinates.length }} points · {{ distanceKm }} km · {{ durationMin }} min
                        </span>
                    </div>
                    <div ref="mapEl" class="geo-field-map" data-testid="geo-route-map" />
                </template>
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
        coordinates() {
            const value = this.field.value
            if (!value || value.type !== 'LineString') return []
            return value.coordinates || []
        },
        hasRoute() { return this.coordinates.length >= 2 },
        distanceKm() {
            const d = this.field.value?.properties?.distance || 0
            return (d / 1000).toFixed(2)
        },
        durationMin() {
            const d = this.field.value?.properties?.duration || 0
            return Math.round(d / 60)
        },
    },
    mounted() {
        this.$nextTick(() => {
            if (!this.hasRoute) return
            this.initMap()
        })
    },
    beforeUnmount() {
        this.map?.remove()
    },
    methods: {
        initMap() {
            const latlngs = this.coordinates.map(([lng, lat]) => [lat, lng])
            const { map, L } = createMap(this.$refs.mapEl, {
                center: latlngs[0],
                zoom: this.field.defaultZoom || 13,
                tileLayer: this.field.tileLayer || {},
            })
            this.map = map
            this.layer = L.polyline(latlngs, { color: '#2563eb', weight: 4 }).addTo(map)
            map.fitBounds(this.layer.getBounds(), { padding: [16, 16] })
        },
    },
}
</script>

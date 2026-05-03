<template>
    <PanelItem :index="index" :field="field" :full-width-content="true">
        <template #value>
            <div
                class="geo-field-wrapper is-detail"
                :style="{ height: `${height}px` }"
                data-testid="geo-area-detail"
            >
                <div v-if="!point" class="geo-field-empty" data-testid="geo-area-empty">
                    {{ __('No area set') }}
                </div>
                <template v-else>
                    <div class="geo-field-toolbar">
                        <span class="geo-field-summary" data-testid="geo-area-summary">
                            {{ __('Center :lat, :lng · Radius :radius m', { lat: point.lat.toFixed(4), lng: point.lng.toFixed(4), radius }) }}
                        </span>
                    </div>
                    <div ref="mapEl" class="geo-field-map" data-testid="geo-area-map" />
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
        return { map: null, circle: null }
    },
    computed: {
        height() { return this.field.height || 360 },
        point() {
            const value = this.field.value
            if (!value || !value.coordinates) return null
            const [lng, lat] = value.coordinates
            return { lat, lng }
        },
        radius() {
            return this.field.value?.properties?.radius || 0
        },
    },
    mounted() {
        this.$nextTick(() => {
            if (!this.point) return
            this.initMap()
        })
    },
    beforeUnmount() {
        this.map?.remove()
    },
    methods: {
        initMap() {
            const { map, L } = createMap(this.$refs.mapEl, {
                center: [this.point.lat, this.point.lng],
                zoom: this.field.defaultZoom || 14,
                tileLayer: this.field.tileLayer || {},
            })
            this.map = map
            this.circle = L.circle([this.point.lat, this.point.lng], {
                radius: this.radius,
                color: '#2563eb',
                weight: 2,
                fillOpacity: 0.15,
            }).addTo(map)
            if (this.radius > 0) {
                map.fitBounds(this.circle.getBounds(), { padding: [16, 16] })
            }
        },
    },
}
</script>

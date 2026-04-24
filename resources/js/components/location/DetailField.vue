<template>
    <PanelItem :index="index" :field="field" :full-width-content="true">
        <template #value>
            <div
                class="geo-field-wrapper is-detail"
                :style="{ height: `${height}px` }"
                data-testid="geo-location-detail"
            >
                <div v-if="!point" class="geo-field-empty" data-testid="geo-location-empty">
                    No location set
                </div>
                <div v-else ref="mapEl" class="geo-field-map" data-testid="geo-location-map" />
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
        return {
            map: null,
            marker: null,
        }
    },
    computed: {
        height() {
            return this.field.height || 320
        },
        point() {
            const value = this.field.value
            if (!value || !value.coordinates) return null
            const [lng, lat] = value.coordinates
            return { lat, lng }
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
                zoom: this.field.defaultZoom || 15,
                tileLayer: this.field.tileLayer || {},
                interactive: !this.field.readonlyMap,
            })
            this.map = map
            this.marker = L.marker([this.point.lat, this.point.lng]).addTo(map)
        },
    },
}
</script>

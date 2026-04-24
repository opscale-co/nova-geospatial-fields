<template>
    <DefaultField
        :field="field"
        :errors="errors"
        :show-help-text="showHelpText"
        :full-width-content="true"
    >
        <template #field>
            <div class="geo-field-wrapper is-form" data-testid="geo-location-form">
                <div class="geo-field-toolbar">
                    <span class="geo-field-summary" data-testid="geo-location-summary">
                        {{ summary }}
                    </span>
                    <button
                        type="button"
                        class="geo-field-button is-secondary"
                        data-testid="geo-location-clear"
                        :disabled="!point"
                        @click="clear"
                    >
                        Clear
                    </button>
                    <span class="geo-field-hint">Click on the map to drop a pin, drag to adjust.</span>
                </div>
                <div ref="mapEl" class="geo-field-map" data-testid="geo-location-map" />
            </div>
        </template>
    </DefaultField>
</template>

<script>
import { FormField, HandlesValidationErrors } from 'laravel-nova'
import { createMap } from '../../services/leaflet.js'

export default {
    mixins: [FormField, HandlesValidationErrors],
    props: ['resourceName', 'resourceId', 'field'],
    data() {
        return {
            map: null,
            marker: null,
            point: null,
        }
    },
    computed: {
        summary() {
            if (!this.point) return 'No location selected'
            return `${this.point.lat.toFixed(5)}, ${this.point.lng.toFixed(5)}`
        },
    },
    mounted() {
        this.$nextTick(() => this.initMap())
    },
    beforeUnmount() {
        this.map?.remove()
    },
    methods: {
        setInitialValue() {
            const raw = this.field.value
            if (raw && raw.coordinates) {
                const [lng, lat] = raw.coordinates
                this.point = { lat, lng }
                this.value = JSON.stringify(raw)
            } else {
                this.point = null
                this.value = ''
            }
        },
        initMap() {
            const center = this.point
                ? [this.point.lat, this.point.lng]
                : [this.field.defaultCenter?.lat ?? 0, this.field.defaultCenter?.lng ?? 0]

            const { map, L } = createMap(this.$refs.mapEl, {
                center,
                zoom: this.field.defaultZoom || 13,
                tileLayer: this.field.tileLayer || {},
            })
            this.map = map

            if (this.point) {
                this.placeMarker(this.point.lat, this.point.lng, L)
            }

            map.on('click', (event) => {
                const { lat, lng } = event.latlng
                this.placeMarker(lat, lng, L)
            })
        },
        placeMarker(lat, lng, L) {
            if (!this.marker) {
                this.marker = L.marker([lat, lng], { draggable: true }).addTo(this.map)
                this.marker.on('dragend', () => {
                    const pos = this.marker.getLatLng()
                    this.commit(pos.lat, pos.lng)
                })
            } else {
                this.marker.setLatLng([lat, lng])
            }
            this.commit(lat, lng)
        },
        commit(lat, lng) {
            this.point = { lat, lng }
            this.value = JSON.stringify({
                type: 'Point',
                coordinates: [lng, lat],
            })
        },
        clear() {
            if (this.marker) {
                this.map.removeLayer(this.marker)
                this.marker = null
            }
            this.point = null
            this.value = ''
        },
        fill(formData) {
            formData.append(this.fieldAttribute, this.value ?? '')
        },
    },
}
</script>

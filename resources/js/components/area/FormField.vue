<template>
    <DefaultField
        :field="field"
        :errors="errors"
        :show-help-text="showHelpText"
        :full-width-content="true"
    >
        <template #field>
            <div class="geo-field-wrapper is-form" data-testid="geo-area-form">
                <div class="geo-field-toolbar">
                    <label class="geo-field-radius-control">
                        {{ __('Radius (m)') }}
                        <input
                            v-model.number="radius"
                            type="number"
                            :min="minRadius"
                            :max="maxRadius"
                            data-testid="geo-area-radius"
                            @input="redrawCircle"
                        >
                    </label>
                    <button
                        type="button"
                        class="geo-field-button is-secondary"
                        data-testid="geo-area-clear"
                        :disabled="!point"
                        @click="clear"
                    >
                        {{ __('Clear') }}
                    </button>
                    <span class="geo-field-hint">{{ __('Click the map to set the center, adjust radius above.') }}</span>
                </div>
                <div ref="mapEl" class="geo-field-map" data-testid="geo-area-map" />
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
            circle: null,
            point: null,
            radius: 500,
        }
    },
    computed: {
        minRadius() { return this.field.minRadius || 1 },
        maxRadius() { return this.field.maxRadius || 100000 },
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
                this.radius = raw.properties?.radius || this.field.defaultRadius || 500
                this.value = JSON.stringify(raw)
            } else {
                this.radius = this.field.defaultRadius || 500
                this.value = ''
            }
        },
        initMap() {
            const center = this.point
                ? [this.point.lat, this.point.lng]
                : [this.field.defaultCenter?.lat ?? 0, this.field.defaultCenter?.lng ?? 0]

            const { map, L } = createMap(this.$refs.mapEl, {
                center,
                zoom: this.field.defaultZoom || 14,
                tileLayer: this.field.tileLayer || {},
            })
            this.map = map
            if (this.point) this.placeAt(this.point.lat, this.point.lng, L)

            map.on('click', (event) => {
                const { lat, lng } = event.latlng
                this.placeAt(lat, lng, L)
            })
        },
        placeAt(lat, lng, L) {
            if (!this.marker) {
                this.marker = L.marker([lat, lng], { draggable: true }).addTo(this.map)
                this.marker.on('dragend', () => {
                    const pos = this.marker.getLatLng()
                    this.point = { lat: pos.lat, lng: pos.lng }
                    this.redrawCircle()
                })
            } else {
                this.marker.setLatLng([lat, lng])
            }
            this.point = { lat, lng }
            this.redrawCircle()
        },
        redrawCircle() {
            if (!this.point) return
            if (this.circle) this.map.removeLayer(this.circle)
            const Leaflet = window.L
            this.circle = Leaflet
                .circle([this.point.lat, this.point.lng], {
                    radius: this.radius,
                    color: '#2563eb',
                    weight: 2,
                    fillOpacity: 0.15,
                })
                .addTo(this.map)
            this.commit()
        },
        commit() {
            if (!this.point) {
                this.value = ''
                return
            }
            this.value = JSON.stringify({
                type: 'Point',
                coordinates: [this.point.lng, this.point.lat],
                properties: { radius: this.radius },
            })
        },
        clear() {
            if (this.marker) this.map.removeLayer(this.marker)
            if (this.circle) this.map.removeLayer(this.circle)
            this.marker = null
            this.circle = null
            this.point = null
            this.value = ''
        },
        fill(formData) {
            formData.append(this.fieldAttribute, this.value ?? '')
        },
    },
}
</script>

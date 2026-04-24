<template>
    <DefaultField
        :field="field"
        :errors="errors"
        :show-help-text="showHelpText"
        :full-width-content="true"
    >
        <template #field>
            <div class="geo-field-wrapper is-form" data-testid="geo-address-form">
                <div class="geo-field-toolbar">
                    <input
                        v-model="query"
                        type="text"
                        class="geo-field-search-input"
                        data-testid="geo-address-search"
                        :placeholder="`Search address (via ${driver})…`"
                        @keydown.enter.prevent="search"
                    >
                    <button
                        type="button"
                        class="geo-field-button"
                        data-testid="geo-address-search-btn"
                        :disabled="searching || !query.trim()"
                        @click="search"
                    >
                        {{ searching ? 'Searching…' : 'Search' }}
                    </button>
                    <button
                        type="button"
                        class="geo-field-button is-secondary"
                        data-testid="geo-address-clear"
                        :disabled="!formatted && !point"
                        @click="clear"
                    >
                        Clear
                    </button>
                </div>
                <ul
                    v-if="results.length"
                    class="geo-field-results"
                    data-testid="geo-address-results"
                >
                    <li
                        v-for="(result, idx) in results"
                        :key="idx"
                        @click="selectResult(result)"
                    >
                        {{ result.formatted }}
                    </li>
                </ul>
                <div ref="mapEl" class="geo-field-map" data-testid="geo-address-map" />
                <div v-if="error" class="geo-field-error" data-testid="geo-address-error">{{ error }}</div>
            </div>
        </template>
    </DefaultField>
</template>

<script>
import { FormField, HandlesValidationErrors } from 'laravel-nova'
import { createMap } from '../../services/leaflet.js'
import { geocode } from '../../services/geocoder.js'

export default {
    mixins: [FormField, HandlesValidationErrors],
    props: ['resourceName', 'resourceId', 'field'],
    data() {
        return {
            map: null,
            marker: null,
            point: null,
            formatted: '',
            query: '',
            results: [],
            searching: false,
            error: null,
        }
    },
    computed: {
        driver() {
            return this.field.geocoder?.driver || 'nominatim'
        },
        endpoints() {
            return this.field.geocoderEndpoints || {}
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
                this.formatted = raw.properties?.formatted || ''
                this.query = this.formatted
                this.value = JSON.stringify(raw)
            } else {
                this.value = ''
            }
        },
        initMap() {
            const center = this.point
                ? [this.point.lat, this.point.lng]
                : [this.field.defaultCenter?.lat ?? 0, this.field.defaultCenter?.lng ?? 0]

            const { map, L } = createMap(this.$refs.mapEl, {
                center,
                zoom: this.point ? 16 : (this.field.defaultZoom || 13),
                tileLayer: this.field.tileLayer || {},
            })
            this.map = map
            if (this.point) this.drawMarker(this.point.lat, this.point.lng, L)
        },
        async search() {
            this.error = null
            this.results = []
            this.searching = true
            try {
                const found = await geocode({
                    query: this.query,
                    driver: this.driver,
                    endpoints: this.endpoints,
                })
                this.results = found
                if (found.length === 0) this.error = 'No matches found.'
            } catch (err) {
                this.error = err.message || 'Geocoding failed.'
            } finally {
                this.searching = false
            }
        },
        selectResult(result) {
            this.results = []
            this.formatted = result.formatted
            this.query = result.formatted
            this.commit(result.lat, result.lng, result.formatted)
            this.map.setView([result.lat, result.lng], 16)
            this.drawMarker(result.lat, result.lng)
        },
        drawMarker(lat, lng, L) {
            const Leaflet = L || window.L
            if (!this.marker) {
                this.marker = Leaflet.marker([lat, lng]).addTo(this.map)
            } else {
                this.marker.setLatLng([lat, lng])
            }
        },
        commit(lat, lng, formatted) {
            this.point = { lat, lng }
            this.value = JSON.stringify({
                type: 'Point',
                coordinates: [lng, lat],
                properties: { formatted },
            })
        },
        clear() {
            if (this.marker) this.map.removeLayer(this.marker)
            this.marker = null
            this.point = null
            this.formatted = ''
            this.query = ''
            this.results = []
            this.value = ''
        },
        fill(formData) {
            formData.append(this.fieldAttribute, this.value ?? '')
        },
    },
}
</script>

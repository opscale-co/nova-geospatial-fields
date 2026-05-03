<template>
    <DefaultField
        :field="field"
        :errors="errors"
        :show-help-text="showHelpText"
        :full-width-content="true"
    >
        <template #field>
            <div class="geo-field-wrapper is-form" data-testid="geo-route-form">
                <div class="geo-field-toolbar">
                    <button
                        type="button"
                        class="geo-field-button"
                        data-testid="geo-route-snap"
                        :disabled="!canSnap || snapping"
                        @click="snap"
                    >
                        {{ snapping ? __('Snapping…') : __('Snap to roads') }}
                    </button>
                    <button
                        type="button"
                        class="geo-field-button is-secondary"
                        data-testid="geo-route-undo"
                        :disabled="!waypoints.length"
                        @click="undoLast"
                    >
                        {{ __('Undo') }}
                    </button>
                    <button
                        type="button"
                        class="geo-field-button is-secondary"
                        data-testid="geo-route-clear"
                        :disabled="!waypoints.length"
                        @click="clear"
                    >
                        {{ __('Clear') }}
                    </button>
                    <span class="geo-field-summary" data-testid="geo-route-summary">{{ summary }}</span>
                </div>
                <div ref="mapEl" class="geo-field-map" data-testid="geo-route-map" />
                <div v-if="error" class="geo-field-error" data-testid="geo-route-error">{{ error }}</div>
            </div>
        </template>
    </DefaultField>
</template>

<script>
import { FormField, HandlesValidationErrors } from 'laravel-nova'
import { createMap, L } from '../../services/leaflet.js'
import { fetchRoute } from '../../services/routing.js'

export default {
    mixins: [FormField, HandlesValidationErrors],
    props: ['resourceName', 'resourceId', 'field'],
    data() {
        return {
            map: null,
            markers: [],
            polyline: null,
            waypoints: [], // [[lng, lat], ...]
            coordinates: [],
            distance: 0,
            duration: 0,
            snapping: false,
            error: null,
        }
    },
    computed: {
        canSnap() {
            return this.waypoints.length >= 2 && !!this.field.osrmUrl
        },
        summary() {
            if (!this.waypoints.length) return this.__('Click on the map to add waypoints.')
            const count = this.waypoints.length
            if (!this.distance) return this.__(':count waypoints', { count })
            const distance = (this.distance / 1000).toFixed(2)
            return this.__(':count waypoints · :distance km', { count, distance })
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
            if (raw && raw.type === 'LineString' && Array.isArray(raw.coordinates)) {
                this.coordinates = raw.coordinates
                this.waypoints = raw.properties?.waypoints || raw.coordinates
                this.distance = raw.properties?.distance || 0
                this.duration = raw.properties?.duration || 0
                this.value = JSON.stringify(raw)
            } else {
                this.value = ''
            }
        },
        initMap() {
            const center = this.waypoints.length
                ? [this.waypoints[0][1], this.waypoints[0][0]]
                : [this.field.defaultCenter?.lat ?? 0, this.field.defaultCenter?.lng ?? 0]

            const { map } = createMap(this.$refs.mapEl, {
                center,
                zoom: this.field.defaultZoom || 13,
                tileLayer: this.field.tileLayer || {},
            })
            this.map = map
            this.redrawAll()

            map.on('click', (event) => {
                this.addWaypoint(event.latlng.lng, event.latlng.lat)
            })
        },
        addWaypoint(lng, lat) {
            this.waypoints.push([lng, lat])
            this.coordinates = this.waypoints.slice()
            this.distance = 0
            this.duration = 0
            const marker = L.marker([lat, lng]).addTo(this.map)
            this.markers.push(marker)
            this.drawPolyline()
            this.commit()
        },
        undoLast() {
            this.waypoints.pop()
            const marker = this.markers.pop()
            if (marker) this.map.removeLayer(marker)
            this.coordinates = this.waypoints.slice()
            this.distance = 0
            this.duration = 0
            this.drawPolyline()
            this.commit()
        },
        drawPolyline() {
            if (this.polyline) {
                this.map.removeLayer(this.polyline)
                this.polyline = null
            }
            if (this.coordinates.length < 2) return
            const latlngs = this.coordinates.map(([lng, lat]) => [lat, lng])
            this.polyline = L.polyline(latlngs, { color: '#2563eb', weight: 4 }).addTo(this.map)
        },
        redrawAll() {
            this.markers.forEach((m) => this.map.removeLayer(m))
            this.markers = []
            this.waypoints.forEach(([lng, lat]) => {
                this.markers.push(L.marker([lat, lng]).addTo(this.map))
            })
            this.drawPolyline()
        },
        async snap() {
            this.error = null
            this.snapping = true
            try {
                const result = await fetchRoute({
                    waypoints: this.waypoints,
                    profile: this.field.routingProfile || 'driving',
                    osrmUrl: this.field.osrmUrl,
                })
                if (!result) {
                    this.error = this.__('No route could be computed.')
                    return
                }
                this.coordinates = result.coordinates
                this.distance = result.distance
                this.duration = result.duration
                this.drawPolyline()
                this.commit()
            } catch (err) {
                this.error = err.message || this.__('Routing failed.')
            } finally {
                this.snapping = false
            }
        },
        commit() {
            if (this.coordinates.length < 2) {
                this.value = ''
                return
            }
            this.value = JSON.stringify({
                type: 'LineString',
                coordinates: this.coordinates,
                properties: {
                    waypoints: this.waypoints,
                    distance: this.distance,
                    duration: this.duration,
                },
            })
        },
        clear() {
            this.markers.forEach((m) => this.map.removeLayer(m))
            if (this.polyline) this.map.removeLayer(this.polyline)
            this.markers = []
            this.polyline = null
            this.waypoints = []
            this.coordinates = []
            this.distance = 0
            this.duration = 0
            this.value = ''
        },
        fill(formData) {
            formData.append(this.fieldAttribute, this.value ?? '')
        },
    },
}
</script>

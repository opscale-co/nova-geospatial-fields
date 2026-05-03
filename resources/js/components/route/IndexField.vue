<template>
    <span class="geo-field-summary" data-testid="geo-route-index">
        {{ label }}
    </span>
</template>

<script setup>
import { computed } from 'vue'
import { useLocalization } from 'laravel-nova'

const { __ } = useLocalization()

const props = defineProps({
    resource: { type: Object, required: true },
    resourceName: { type: String, required: true },
    field: { type: Object, required: true },
})

const label = computed(() => {
    const value = props.field.value
    if (!value || value.type !== 'LineString') return '—'
    const count = (value.coordinates || []).length
    const distance = value.properties?.distance || 0
    if (distance) {
        return __(':count pts · :distance km', { count, distance: (distance / 1000).toFixed(1) })
    }
    return __(':count pts', { count })
})
</script>

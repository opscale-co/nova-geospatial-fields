<template>
    <span class="geo-field-summary" data-testid="geo-location-index">
        {{ label }}
    </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    resource: { type: Object, required: true },
    resourceName: { type: String, required: true },
    field: { type: Object, required: true },
})

const label = computed(() => {
    const value = props.field.value
    if (!value || !value.coordinates) return '—'
    const [lng, lat] = value.coordinates
    return `${lat.toFixed(2)}, ${lng.toFixed(2)}`
})
</script>

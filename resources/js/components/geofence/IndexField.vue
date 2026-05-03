<template>
    <span class="geo-field-summary" data-testid="geo-geofence-index">
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
    if (!value || value.type !== 'Polygon') return '—'
    const ring = value.coordinates?.[0] || []
    const count = Math.max(0, ring.length - 1)
    return __(':count vertices', { count })
})
</script>

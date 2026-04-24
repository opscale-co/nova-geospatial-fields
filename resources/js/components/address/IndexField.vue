<template>
    <span class="geo-field-summary" data-testid="geo-address-index">
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
    if (!value) return '—'
    return value.properties?.formatted || '—'
})
</script>

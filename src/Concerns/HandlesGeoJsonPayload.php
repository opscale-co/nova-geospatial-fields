<?php

declare(strict_types=1);

namespace Opscale\Fields\Geospatial\Concerns;

use Illuminate\Contracts\Support\Arrayable;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * Normalizes payload handling for every geospatial field.
 *
 * Requests may arrive from Nova's Vue form (JSON-encoded GeoJSON in a
 * string field) or from a programmatic API caller (raw GeoJSON string,
 * or already-decoded array). This trait funnels all three into the
 * same storage format — a JSON string the application can cast with
 * Eloquent's `array` cast.
 */
trait HandlesGeoJsonPayload
{
    /**
     * Overrides Nova's default fill to accept both raw GeoJSON strings
     * and the internal JSON payload submitted by the field's Vue form.
     */
    protected function fillAttributeFromRequest(
        NovaRequest $request,
        string $requestAttribute,
        object $model,
        string $attribute,
    ): void {
        if (! $request->exists($requestAttribute)) {
            return;
        }

        $value = $request->input($requestAttribute);

        if ($value === null || $value === '') {
            $model->{$attribute} = null;

            return;
        }

        $normalized = $this->normalizeGeoJson($value);

        $model->{$attribute} = $normalized === null
            ? null
            : json_encode($normalized, JSON_THROW_ON_ERROR);
    }

    /**
     * @param  mixed  $value
     * @return array<string, mixed>|null
     */
    protected function normalizeGeoJson(mixed $value): ?array
    {
        if (is_array($value)) {
            return $value;
        }

        if ($value instanceof Arrayable) {
            return $value->toArray();
        }

        if (! is_string($value)) {
            return null;
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Decode stored values so the Vue component always receives a plain
     * object, regardless of whether the Eloquent cast returned an array
     * or the raw JSON string.
     *
     * @param  mixed  $value
     * @return array<string, mixed>|null
     */
    protected function resolveStoredValue(mixed $value): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_array($value)) {
            return $value;
        }

        if ($value instanceof Arrayable) {
            return $value->toArray();
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);

            return is_array($decoded) ? $decoded : null;
        }

        return null;
    }
}

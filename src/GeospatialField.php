<?php

declare(strict_types=1);

namespace Opscale\Fields\Geospatial;

use Laravel\Nova\Fields\Field;
use Opscale\Fields\Geospatial\Concerns\HandlesGeoJsonPayload;
use Opscale\Fields\Geospatial\Concerns\HasMapOptions;

/**
 * Base class for every geospatial field in the package.
 *
 * Subclasses set `$component` and may tack on field-specific meta, but
 * the payload and map configuration pipelines are shared here so all
 * fields behave identically on the API surface.
 */
abstract class GeospatialField extends Field
{
    use HandlesGeoJsonPayload;
    use HasMapOptions;

    /**
     * Merge the package-level map defaults into meta just before the
     * field is serialized, so the Vue component always has a usable
     * tile layer and center point even if the developer didn't call
     * any of the setters.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        $this->withMeta(array_merge($this->mapDefaults(), $this->meta));

        return parent::jsonSerialize();
    }

    /**
     * Make sure the Vue component always receives an object/array,
     * never a JSON string or model attribute wrapper.
     */
    public function resolve($resource, ?string $attribute = null): void
    {
        parent::resolve($resource, $attribute);

        $this->value = $this->resolveStoredValue($this->value);
    }
}

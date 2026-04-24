<?php

declare(strict_types=1);

namespace Opscale\Fields\Geospatial\Concerns;

/**
 * Shared map configuration exposed to every geospatial field.
 *
 * Each setter writes onto the field's `$meta` bag so the Vue component
 * receives the value via `field.tileLayer`, `field.defaultCenter`, etc.
 */
trait HasMapOptions
{
    public function tileLayer(string $url, ?string $attribution = null, int $maxZoom = 19): static
    {
        return $this->withMeta([
            'tileLayer' => [
                'url' => $url,
                'attribution' => $attribution ?? '',
                'maxZoom' => $maxZoom,
            ],
        ]);
    }

    public function defaultCenter(float $lat, float $lng): static
    {
        return $this->withMeta([
            'defaultCenter' => ['lat' => $lat, 'lng' => $lng],
        ]);
    }

    public function defaultZoom(int $zoom): static
    {
        return $this->withMeta(['defaultZoom' => $zoom]);
    }

    public function height(int $pixels): static
    {
        return $this->withMeta(['height' => $pixels]);
    }

    public function readonlyMap(bool $readonly = true): static
    {
        return $this->withMeta(['readonlyMap' => $readonly]);
    }

    /**
     * Merge package-level defaults into the field meta at resolve time,
     * so the component always has a usable tile layer and center even if
     * the developer never called the setters.
     *
     * @return array<string, mixed>
     */
    protected function mapDefaults(): array
    {
        $config = config('nova-geospatial-fields');

        return [
            'tileLayer' => $config['tile_layer'] ?? [],
            'defaultCenter' => $config['default_center'] ?? ['lat' => 0.0, 'lng' => 0.0],
            'defaultZoom' => $config['default_zoom'] ?? 13,
        ];
    }
}

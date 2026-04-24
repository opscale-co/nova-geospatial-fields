/**
 * Thin geocoder that talks to either Nominatim or Photon.
 *
 * Both services are public OpenStreetMap-based endpoints with CORS
 * enabled, so the request goes straight from the browser. Nominatim
 * asks consumers to set a descriptive User-Agent; we send `Accept-Language`
 * so localized names come back in the current browser language.
 */

const DEFAULTS = {
    nominatim: 'https://nominatim.openstreetmap.org',
    photon: 'https://photon.komoot.io',
}

export async function geocode({ query, driver = 'nominatim', endpoints = {} } = {}) {
    if (!query || !query.trim()) {
        return []
    }

    const base = endpoints[driver] || DEFAULTS[driver]

    if (driver === 'photon') {
        return geocodeWithPhoton(base, query)
    }
    return geocodeWithNominatim(base, query)
}

async function geocodeWithNominatim(base, query) {
    const url = new URL(`${stripTrailingSlash(base)}/search`)
    url.searchParams.set('q', query)
    url.searchParams.set('format', 'jsonv2')
    url.searchParams.set('limit', '5')

    const response = await fetch(url.toString(), {
        headers: {
            'Accept': 'application/json',
            'Accept-Language': navigator.language || 'en',
        },
    })
    if (!response.ok) {
        throw new Error(`Nominatim returned ${response.status}`)
    }
    const body = await response.json()
    return body.map((item) => ({
        lat: parseFloat(item.lat),
        lng: parseFloat(item.lon),
        formatted: item.display_name,
    }))
}

async function geocodeWithPhoton(base, query) {
    const url = new URL(`${stripTrailingSlash(base)}/api`)
    url.searchParams.set('q', query)
    url.searchParams.set('limit', '5')

    const response = await fetch(url.toString(), {
        headers: { 'Accept': 'application/json' },
    })
    if (!response.ok) {
        throw new Error(`Photon returned ${response.status}`)
    }
    const body = await response.json()
    return (body.features || []).map((feature) => {
        const [lng, lat] = feature.geometry.coordinates
        return {
            lat,
            lng,
            formatted: formatPhotonProperties(feature.properties),
        }
    })
}

function formatPhotonProperties(props = {}) {
    return [props.name, props.street, props.city, props.state, props.country]
        .filter(Boolean)
        .join(', ')
}

function stripTrailingSlash(url) {
    return url.replace(/\/+$/, '')
}

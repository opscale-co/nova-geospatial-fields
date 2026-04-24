/**
 * OSRM routing client — snaps an ordered list of waypoints to the road
 * network and returns a GeoJSON `LineString` together with the total
 * distance (meters) and duration (seconds).
 */

export async function fetchRoute({ waypoints, profile = 'driving', osrmUrl }) {
    if (!Array.isArray(waypoints) || waypoints.length < 2 || !osrmUrl) {
        return null
    }

    const coords = waypoints
        .map(([lng, lat]) => `${lng},${lat}`)
        .join(';')

    const url = new URL(`${stripTrailingSlash(osrmUrl)}/${profile}/${coords}`)
    url.searchParams.set('overview', 'full')
    url.searchParams.set('geometries', 'geojson')

    const response = await fetch(url.toString())
    if (!response.ok) {
        throw new Error(`OSRM returned ${response.status}`)
    }
    const body = await response.json()

    const route = body.routes && body.routes[0]
    if (!route) {
        return null
    }

    return {
        coordinates: route.geometry.coordinates,
        distance: route.distance,
        duration: route.duration,
    }
}

function stripTrailingSlash(url) {
    return url.replace(/\/+$/, '')
}

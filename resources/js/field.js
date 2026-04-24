import LocationForm from './components/location/FormField.vue'
import LocationDetail from './components/location/DetailField.vue'
import LocationIndex from './components/location/IndexField.vue'

import AddressForm from './components/address/FormField.vue'
import AddressDetail from './components/address/DetailField.vue'
import AddressIndex from './components/address/IndexField.vue'

import GeofenceForm from './components/geofence/FormField.vue'
import GeofenceDetail from './components/geofence/DetailField.vue'
import GeofenceIndex from './components/geofence/IndexField.vue'

import AreaForm from './components/area/FormField.vue'
import AreaDetail from './components/area/DetailField.vue'
import AreaIndex from './components/area/IndexField.vue'

import RouteForm from './components/route/FormField.vue'
import RouteDetail from './components/route/DetailField.vue'
import RouteIndex from './components/route/IndexField.vue'

Nova.booting((app, store) => {
    app.component('form-nova-geospatial-location', LocationForm)
    app.component('detail-nova-geospatial-location', LocationDetail)
    app.component('index-nova-geospatial-location', LocationIndex)

    app.component('form-nova-geospatial-address', AddressForm)
    app.component('detail-nova-geospatial-address', AddressDetail)
    app.component('index-nova-geospatial-address', AddressIndex)

    app.component('form-nova-geospatial-geofence', GeofenceForm)
    app.component('detail-nova-geospatial-geofence', GeofenceDetail)
    app.component('index-nova-geospatial-geofence', GeofenceIndex)

    app.component('form-nova-geospatial-area', AreaForm)
    app.component('detail-nova-geospatial-area', AreaDetail)
    app.component('index-nova-geospatial-area', AreaIndex)

    app.component('form-nova-geospatial-route', RouteForm)
    app.component('detail-nova-geospatial-route', RouteDetail)
    app.component('index-nova-geospatial-route', RouteIndex)
})

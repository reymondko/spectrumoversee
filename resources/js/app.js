
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

import 'es6-promise/auto'
require('./bootstrap');

window.Vue = require('vue');
window.Notification = require('vue-notification');

window.moment = require('moment')


/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key)))

Vue.component('example-component', require('./components/ExampleComponent.vue'));
Vue.component('ship-pack', require('./components/ShipPackComponent.vue'));
Vue.component('quality-inspector', require('./components/QualityInspectorComponent.vue'));
Vue.component('logiwa-quality-inspector', require('./components/LogiwaQualityInspectorComponent.vue'));
Vue.component('carriers', require('./components/CarriersComponent.vue'));
Vue.component('create-shipment', require('./components/CreateShipment.vue'));
Vue.component('kpi-report', require('./components/KpiReportComponent.vue'));
Vue.component('return-label-report', require('./components/ReturnLabelReportComponent.vue'));
Vue.component('shipping-cost-report', require('./components/ShippingCostReportComponent.vue'));
Vue.component('kit-return-sync', require('./components/KitReturnSyncComponent.vue'));
Vue.component('kit-boxing', require('./components/KitBoxingComponent.vue'));
Vue.component('bulk-kit-scan', require('./components/BulkKitScanComponent.vue'));
Vue.component('thirdparty-reallocate', require('./components/ReallocateComponent.vue'));

Vue.use(Notification);
/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app'
});

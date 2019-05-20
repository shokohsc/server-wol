/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you require will output into a single css file (app.css in this case)
require('../css/app.css');
require('bootstrap/dist/css/bootstrap.min.css');

// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
require('bootstrap');
import store from './store/index.js';
import App from './components/App.vue';
import Vue from 'vue';

// EventBus
Vue.prototype.$eventBus = new Vue();

/**
 * Create a fresh Vue Application instance
 */
new Vue({
    el: '#app',
    store,
    render: h => h(App)
});

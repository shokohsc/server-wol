import Vue from 'vue';
import Vuex from 'vuex';
import servers from './modules/servers';

Vue.use(Vuex);

export default new Vuex.Store({
    modules: {
        servers
    },
    strict: process.env.NODE_ENV !== 'production'
})

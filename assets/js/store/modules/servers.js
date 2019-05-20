import servers from '../../api/index'

// initial state
const state = {
    servers: [],
}

// getters
const getters = {
    servers: state => {
        return state.servers;
    },
    server: (state, getters) => id => {
        return state.servers.find(server => { server.id === id});
    }
}

// actions
const actions = {
    list({}) {
        return servers.list();
    },
    read({}, id) {
        return servers.read(id);
    },
    wake({}, id) {
        return servers.wake(id);
    },
    ping({}, id) {
        return servers.ping(id);
    },
    create({}, data) {
        return servers.create(data);
    },
    update({}, data) {
        return servers.update(data, data.id);
    },
    delete({}, id) {
        return servers.delete(id);
    },
    refresh(state) {
        this.dispatch('servers/list')
        .then(response => {
            this.commit('servers/setServers', response);
        })
        .catch(error => {
            this.commit('servers/resetServers');
        });
    }
}

// mutations
const mutations = {
    setServers(state, servers) {
        state.servers = servers;
    },
    addServer(state, server) {
        state.servers.push(server);
    },
    removeServer(state, id) {
        const servers = state.servers.filter(server => server.id !== id);
        this.commit('servers/setServers', servers);
    },
    resetServers(state) {
        state.servers = [];
    }
}

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}

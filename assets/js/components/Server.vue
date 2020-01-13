<template>
    <div class="col-lg-4 col-md-12 text-center my-2">
        <div class="card bg-secondary text-center">
            <img @click="action(server)" src="../../images/power.png" alt="power" width="50%" class="m-auto" style="cursor:grab;">
            <div class="card-body">
                <p class="text-center font-weight-bold">{{ server.status }}</p>
                <p class="text-center">{{ server.name }}</p>
                <p class="text-center">{{ server.mac }}</p>
                <p class="text-center">{{ server.ip }}</p>
                <button @click="update(server)" type="button" class="btn btn-primary btn-lg btn-block" data-toggle="modal" data-target="#exampleModal">Edit</button>
                <button @click="remove(server)" type="button" class="btn btn-danger btn-lg btn-block">Delete</button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: ['propServer'],
    data: function() {
        return {
            id: '',
            name: '',
            ip: '',
            mac: '',
            status: ''
        };
    },
    computed: {
        server: function() {
            return {
                id: this.propServer.id,
                name: this.propServer.name,
                ip: this.propServer.ip,
                mac: this.propServer.mac,
                status: this.propServer.status
            };
        }
    },
    methods: {
        reset: function() {
            this.id = '';
            this.name = '';
            this.ip = '';
            this.mac = '';
            this.status = '';
        },
        update: function(server) {
            this.id = server.id;
            this.name = server.name;
            this.ip = server.ip;
            this.mac = server.mac;
            this.status = server.status;
            this.$eventBus.$emit('load-server', server);
        },
        remove: function(server) {
            this.$store.dispatch('servers/delete', server.id)
            .then((response) => {
                this.$store.commit('servers/removeServer', server.id);
            })
            .catch((error) => {
                console.log(error);
            });
        },
        action: function(server) {
            this.$eventBus.$emit('loading');
            'asleep' == server.status ? this.wake(server) : this.ping(server);
        },
        ping: function(server) {
            this.$store.dispatch('servers/ping', server.id)
            .then((response) => {
                this.$store.dispatch('servers/refresh');
                this.$eventBus.$emit('done-loading');
            })
            .catch((error) => {
                this.$eventBus.$emit('done-loading');
                this.$store.dispatch('servers/refresh');
                console.log(error);
                this.$eventBus.$emit('done-loading');
            });
        },
        wake: function(server) {
            this.$store.dispatch('servers/wake', server.id)
            .then((response) => {
                this.$store.dispatch('servers/refresh');
                this.$eventBus.$emit('done-loading');
            })
            .catch((error) => {
                this.$eventBus.$emit('done-loading');
                this.$store.dispatch('servers/refresh');
                console.log(error);
                this.$eventBus.$emit('done-loading');
            });
        }
    }
};
</script>

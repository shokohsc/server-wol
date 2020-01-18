<template>
    <div class="col-lg-4 col-md-12 text-center my-2 py-2">
        <div class="card bg-secondary text-center">
            <div class="row">
                <div class="col-sm-12">
                    <img @click="ping(server)" src="../../images/ping.png" alt="ping" width="50%" class="m-auto py-2" style="cursor:pointer;">
                </div>
                <div class="col-sm-6">
                    <img @click="power(server)" src="../../images/power.png" alt="power" width="50%" class="m-auto py-2" style="cursor:pointer;">
                </div>
                <div class="col-sm-6">
                    <img @click="play(server)" src="../../images/parsec.png" alt="parsec" width="50%" class="m-auto py-2" style="cursor:pointer;">
                </div>
            </div>
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
        end: function(data) {
            console.log(data);
            this.$store.dispatch('servers/refresh');
            this.$eventBus.$emit('done-loading');
        },

        power: function(server) {
            this.ping(server).then((response) => {
                'asleep' == response.status ? this.wake(response) : this.sleep(response);
            });
        },
        play: function(server) {
            this.ping(server).then((response) => {
                'asleep' == response.status ? this.wake(response) : this.parsec(response);
            });
        },

        ping: function(server) {
            this.$eventBus.$emit('loading');
            return this.$store.dispatch('servers/ping', server.id)
            .always((data) => {
                this.end(data);
            });
        },
        wake: function(server) {
            this.$store.dispatch('servers/wake', server.id)
            .always((data) => {
                this.end(data);
            });
        },
        sleep: function(server) {
            this.$store.dispatch('servers/sleep', server.id)
            .always((data) => {
                this.end(data);
            });
        },
        parsec: function(server) {
            this.$store.dispatch('servers/parsec', server.id)
            .always((data) => {
                this.end(data);
            });
        }
    }
};
</script>

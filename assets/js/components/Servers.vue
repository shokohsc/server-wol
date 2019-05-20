<template>
    <div class="row">
        <table class="table table-dark">
          <thead>
            <tr>
              <th scope="col">#</th>
              <th scope="col">Name</th>
              <th scope="col">Ip</th>
              <th scope="col">Mac</th>
              <th scope="col">Status</th>
              <th scope="col">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(server, index) in servers" :key="server.id">
              <td>{{ index + 1 }}</td>
              <td>{{ server.name }}</td>
              <td>{{ server.ip }}</td>
              <td>{{ server.mac }}</td>
              <td>{{ server.status }}</td>
              <td>
                    <div class="btn-group btn-group-sm" role="group" aria-label="Basic example">
                        <button @click="update(server)" type="button" class="btn btn-secondary" data-toggle="modal" data-target="#exampleModal">Update</button>
                        <button @click="remove(server)" type="button" class="btn btn-secondary">Remove</button>
                        <button @click="ping(server)" type="button" class="btn btn-secondary">Ping</button>
                        <button @click="wake(server)" type="button" class="btn btn-secondary">Wake</button>
                    </div>
              </td>
            </tr>
          </tbody>
        </table>
        <Button :value="value"></Button>
        <Modal :server="server"></Modal>
    </div>
</template>

<script>
    import Button from './Button.vue';
    import Modal from './Modal.vue';

    export default {
        components: {
            Button,
            Modal
        },
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
            servers: function() {
                return this.$store.getters['servers/servers'];
            },
            value: function() {
                return 'Add server';
            },
            server: function() {
                return {
                    id: this.id,
                    name: this.name,
                    ip: this.ip,
                    mac: this.mac,
                    status: this.status
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
            ping: function(server) {
                this.$store.dispatch('servers/ping', server.id)
                .then((response) => {
                    this.$store.dispatch('servers/refresh');
                })
                .catch((error) => {
                    this.$store.dispatch('servers/refresh');
                    console.log(error);
                });
            },
            wake: function(server) {
                this.$store.dispatch('servers/wake', server.id)
                .then((response) => {
                    this.$store.dispatch('servers/refresh');
                })
                .catch((error) => {
                    this.$store.dispatch('servers/refresh');
                    console.log(error);
                });
            }
        },
        created: function() {
            this.$eventBus.$on('reset-form', () => this.reset());
        }
    };
</script>

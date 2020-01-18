<template>
    <form>
        <input type="hidden" class="form-control" v-model="server.id"  id="inputId" aria-describedby="idHelp">
        <div class="form-group">
          <label for="inputName">Server name</label>
          <input type="text" class="form-control" disabled v-model="server.name"  id="inputName" aria-describedby="nameHelp" placeholder="Hostname will be determined">
        </div>
        <div class="form-group">
          <label for="inputIp">Server ip address</label>
          <input type="text" class="form-control" disabled v-model="server.ip"  id="inputIp" aria-describedby="ipHelp" placeholder="IP address will be determined">
        </div>
        <div class="form-group">
          <label for="inputMac">Server mac address</label>
          <input type="text" class="form-control" v-model="server.mac"  id="inputMac" aria-describedby="macHelp" placeholder="Enter mac address" required>
          <div v-if="errors.mac.length" style="display: block;" class="invalid-feedback">
              {{ errors.mac[0] }}
          </div>
        </div>
      <button @click="submit" type="button" class="btn btn-primary">Submit</button>
    </form>
</template>

<script>
    const $ = require('jquery');
    export default {
        props: ['server'],
        computed: {
            id: {
                get: function () {
                    return this.server.id;
                },
                set: function (id) {
                    this.server.id = id;
                }
            },
            name: {
                get: function () {
                    return this.server.name;
                }
            },
            ip: {
                get: function () {
                    return this.server.ip;
                }
            },
            mac: {
                get: function () {
                    return this.server.mac;
                },
                set: function (mac) {
                    this.server.mac = mac;
                }
            }
        },
        data: function() {
            return {
                errors: {
                    name: [],
                    ip: [],
                    mac: [],
                }
            };
        },
        methods: {
            reset: function() {
                this.errors = {
                    mac: [],
                };
            },
            validate: function(server) {
                this.errors = {
                    mac: [],
                };
                const validMac = server.mac.match(/(([a-z]|[A-Z]|[0-9]){2})\:(([a-z]|[A-Z]|[0-9]){2})\:(([a-z]|[A-Z]|[0-9]){2})\:(([a-z]|[A-Z]|[0-9]){2})\:(([a-z]|[A-Z]|[0-9]){2})\:(([a-z]|[A-Z]|[0-9]){2})/g);
                if (null === validMac)
                    this.errors.mac.push('Wrong Mac address.');
            },
            submit: function() {
                this.$eventBus.$emit('loading');
                const server = {
                    id: this.server.id,
                    mac: this.server.mac
                };
                this.validate(server);
                if (0 === this.errors.mac.length) {
                    const promise = '' === server.id ? this.$store.dispatch('servers/create', server) : this.$store.dispatch('servers/update', server);
                    promise
                    .then((response) => {
                        this.reset();
                        this.$eventBus.$emit('reset-form');
                        this.$store.dispatch('servers/refresh');
                        $('#exampleModal').modal('hide');
                        this.$eventBus.$emit('done-loading');
                    })
                    .catch((error) => {
                        console.log(error);
                        $('#exampleModal').modal('hide');
                        this.$eventBus.$emit('done-loading');
                    });
                }
            }
        }
    };
</script>

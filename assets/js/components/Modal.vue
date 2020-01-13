<template>
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">{{ title }}</h5>
            <button @click="reset" type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <Form :server="server"></Form>
          </div>
        </div>
      </div>
    </div>
</template>

<script>
    import Form from './Form.vue';

    export default {
        data: function() {
            return {
                id: '',
                name: '',
                ip: '',
                mac: '',
                status: ''
            };
        },
        components: {
            Form
        },
        computed: {
            server: function() {
                return {
                    id: this.id,
                    name: this.name,
                    ip: this.ip,
                    mac: this.mac,
                    status: this.status
                };
            },
            title: function() {
                return '' === this.id ? 'Add Server' : 'Edit Server';
            }
        },
        methods: {
            load: function(server) {
                this.id = server.id;
                this.name = server.name;
                this.ip = server.ip;
                this.mac = server.mac;
                this.status = server.status;
            },
            reset: function() {
                this.id = '';
                this.name = '';
                this.ip = '';
                this.mac = '';
                this.status = '';
            }
        },
        created: function() {
            this.$eventBus.$on('reset-form', () => this.reset());
            this.$eventBus.$on('load-server', (server) => this.load(server));
        }
    };
</script>

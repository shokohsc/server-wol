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
        props: ['server'],
        components: {
            Form
        },
        computed: {
            title: function() {
                return '' === this.server.id ? 'Add Server' : 'Edit Server';
            }
        },
        methods: {
            reset: function() {
                this.$eventBus.$emit('reset-form');
            }
        }
    };
</script>

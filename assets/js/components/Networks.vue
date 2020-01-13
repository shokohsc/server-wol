<template>
    <div class="row">
        <Server :propServer="server" v-for="(server, index) in servers" :key="server.id"></Server>
        <CreateServer />
        <Modal />
    </div>
</template>

<script>
    import Modal from './Modal.vue';
    import Server from './Server.vue';
    import CreateServer from './CreateServer.vue';

    export default {
        components: {
            Modal,
            CreateServer,
            Server
        },
        computed: {
            servers: function() {
                return this.$store.getters['servers/servers'];
            }
        }
    };
</script>

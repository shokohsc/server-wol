<template>
    <div class="container">
        <nav class="navbar">
        </nav>
        <component :is="activeComponent"></component>
        <Modal />
    </div>
</template>

<script>
    import Error from './Error.vue';
    import Loading from './Loading.vue';
    import Networks from './Networks.vue';
    import Modal from './Modal.vue';

    export default {
        components: {
            Modal,
            Loading,
            Error,
            Networks
        },
        data() {
            return {
                activeComponent: Loading
            }
        },
        methods: {
            loading: function() {
                this.activeComponent = Loading;
            },
            done: function() {
                this.activeComponent = Networks;
            }
        },
        created: function() {
            this.$eventBus.$on('loading', () => this.loading());
            this.$eventBus.$on('done-loading', () => this.done());
            this.$store.dispatch('servers/list')
            .then((response) => {
                this.$store.commit('servers/setServers', response);
                this.activeComponent = Networks;
            })
            .catch((error) => {
                console.log(error);
                this.$store.commit('servers/resetServers');
                this.activeComponent = Error;
            });
        }
    };
</script>

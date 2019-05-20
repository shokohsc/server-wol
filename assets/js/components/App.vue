<template>
    <div class="container">
        <component :is="activeComponent"></component>
    </div>
</template>

<script>
    import Error from './Error.vue';
    import Loading from './Loading.vue';
    import Servers from './Servers.vue';

    export default {
        components: {
            Loading,
            Error,
            Servers
        },
        data() {
            return {
                activeComponent: Loading
            }
        },
        methods: {
            loading: function() {
                this.activeComponent = Loading;
            }
        },
        created: function() {
            this.$store.dispatch('servers/list')
            .then((response) => {
                this.$store.commit('servers/setServers', response);
                this.activeComponent = Servers;
            })
            .catch((error) => {
                console.log(error);
                this.$store.commit('servers/resetServers');
                this.activeComponent = Error;
            });
        }
    };
</script>

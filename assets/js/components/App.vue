<template>
    <div class="container">
        <nav class="navbar">
        </nav>
        <component :is="activeComponent"></component>
    </div>
</template>

<script>
    import Error from './Error.vue';
    import Loading from './Loading.vue';
    import Networks from './Networks.vue';

    export default {
        components: {
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
            }
        },
        created: function() {
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

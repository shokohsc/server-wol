<template>
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">{{ value }}</button>
</template>

<script>
    export default {
        props: ['value']
    };
</script>

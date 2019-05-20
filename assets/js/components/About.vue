<template>
    <div>
        <h1>What is this ?</h1>
        <p>Wake on LAN website</p>

        <h3>Who am I ?</h3>
        <p>I am a {{ age }} years old web developer who lives in France near Paris.</p>

        <h3>Why did I do that ?</h3>
        <p>This is Symfony/VueJs practice, check out my <a href=\"//shokohsc.github.io\">resume</a>.</p>

        <h3>Thanks</h3>
        <p>I would like to thank
            <ul class="list-unstyled">
                <li><strong><a href="//symfony.com/">Symfony</a></strong></li>
                <li><strong><a href="//vuejs.org/">VueJs</a></strong></li>
            </ul>
        </p>
    </div>
</template>

<script>
    export default {
        computed: {
            age: () => {
                let birthday = +new Date(1986, 1, 27);
                let age = ~~((Date.now() - birthday) / (31557600000));
                return age.toString();
            }
        }
    };
</script>

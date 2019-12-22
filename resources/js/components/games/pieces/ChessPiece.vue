<template>
    <div class="piece">
        <img v-if="tileData !== null && tileData.type !== 'empty'" class="piece-icon" :src="getPieceImage(tileData.type)" alt="">
    </div>

</template>

<script>

    export default {
        data() {
            return {
            }
        },

        props: {
            tileData: null,
        },

        methods: {

            getPieceImage(name) {
                if (name) {
                    return `/images/icons/chess/${name}.svg`;
                } else{
                    return `/images/icons/play.svg`;
                }
            },


        },

        mounted() {
        }
    }
</script>

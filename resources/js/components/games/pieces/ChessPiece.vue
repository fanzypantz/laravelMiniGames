<template>
    <div
        v-bind:class="['piece', possibleTarget ? 'possible-target' : '']"
        @dragover="(e) => {e.preventDefault();}"
        @drop="(e) => handleDrop(e, tileData)"
    >
        <img
            v-bind:class="[tileData.colour === 'white' ? 'white-piece' : '' , 'piece-icon']"
            v-if="tileData !== null && tileData.type !== 'empty'"
            v-bind:draggable="canDrag"
            :src="getPieceImage(tileData.type)"
            alt=""
            @dragstart="(e) => handleDragStart(e, tileData)"
        >
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
            canDrag: false,
            possibleTarget: false,
        },

        methods: {

            getPieceImage(name) {
                if (name) {
                    return `/images/icons/chess/${name}.svg`;
                } else{
                    return `/images/icons/play.svg`;
                }
            },

            handleDrop(e, tileData) {
                console.log('drop: ', tileData);
                let payloadData = JSON.parse(e.dataTransfer.getData('text'));
                console.log('payloadData: ', payloadData);


                this.removeHighlight();
            },

            handleDragStart(e, tileData) {
                console.log('dragStart: ', tileData);
                e.dataTransfer.setData('text/plain', JSON.stringify(tileData));
                this.highlightMoves(tileData);
            },

            highlightMoves(tileData) {
                this.$emit('checkPossibleMoves', tileData);
            },

            removeHighlight() {
                this.$emit('emptyPossibleMoves');
            },
        },

        mounted() {
        }
    }
</script>

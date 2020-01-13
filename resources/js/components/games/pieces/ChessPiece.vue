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
        <p class="tile-number">{{tileData.position.x}}-{{tileData.position.y}}</p>
        <p class="tile-number" v-bind:style="{
            top: '2%'
        }">{{canDrag}}</p>
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
            isPlayer1: false,
        },

        methods: {

            classType() {
                let className = '';
                if (this.isPlayer1 && this.tileData.colour === 'white') {
                    className += 'white-piece';
                } else if (!this.isPlayer1 && this.tileData.colour === 'black') {
                    className += 'white-piece';
                }
                className += ' piece-icon';
                return className;
            },

            getPieceImage(name) {
                if (name) {
                    return `/images/icons/chess/${name}.svg`;
                } else{
                    return `/images/icons/play.svg`;
                }
            },

            handleDrop(e, tileData) {
                e.preventDefault();
                console.log('dropData: ', tileData);
                let oldPiece = JSON.parse(e.dataTransfer.getData('text'));
                console.log('payloadData: ', oldPiece);

                // If this tile has been marked as a possible target continue the logic
                if (this.possibleTarget) {
                    console.log('could drop here: ', );
                    this.$emit('sendGameMove', {
                        oldPiece: oldPiece,
                        newPiece: this.tileData,
                    });
                }

                this.removeHighlight();
            },

            handleDragStart(e, tileData) {
                if (this.canDrag) {
                    console.log('dragStart: ', tileData);
                    e.dataTransfer.setData('text/plain', JSON.stringify(tileData));
                    this.highlightMoves(tileData);
                } else {
                    e.preventDefault();
                }
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

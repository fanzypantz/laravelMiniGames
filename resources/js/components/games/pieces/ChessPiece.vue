<template>
    <div
        v-bind:class="['piece', possibleTarget ? 'possible-target' : '']"
        @dragover="(e) => {e.preventDefault();}"
        @drop="(e) => handleDrop(e, tileData)"
    >
        <img
            v-bind:class="classType()"
            v-if="tileData !== null && tileData.type !== 'empty'"
            v-bind:draggable="canDrag"
            :src="getPieceImage(tileData.type)"
            alt=""
            @dragstart="(e) => handleDragStart(e, tileData)"
        >
<!--        <p class="tile-number">{{tileData.position.x + 1}}-{{tileData.position.y}}</p>-->

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
                if (this.tileData.colour === 'white') {
                    className += 'white-piece '
                }
                if (this.isPlayer1 && this.tileData.colour === 'white' && !this.canDrag) {
                    className += 'grey-piece ';
                }
                if (!this.isPlayer1 && this.tileData.colour === 'black' && !this.canDrag) {
                    className += 'grey-piece ';
                }
                className += 'piece-icon ';
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
                let oldPiece = JSON.parse(e.dataTransfer.getData('text'));
                console.log('payloadData: ', oldPiece);

                // If this tile has been marked as a possible target continue the logic
                if (this.possibleTarget) {
                    if (oldPiece.type === 'pawn' && (this.tileData.position.y === 7 || this.tileData.position.y === 0)) {
                        console.log('promoting: ', );
                        this.$emit('togglePromote', {
                            oldPiece: oldPiece,
                            newPiece: this.tileData,
                        });
                    } else {
                        console.log('normal move: ', );
                        this.$emit('sendGameMove', {
                            oldPiece: oldPiece,
                            newPiece: this.tileData,
                        });
                    }

                }

                this.removeHighlight();
            },

            handleDragStart(e, tileData) {
                if (this.canDrag) {
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

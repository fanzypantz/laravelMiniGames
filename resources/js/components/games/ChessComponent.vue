<template>
    <div v-if="game !== null && game.board" class="board chess">
        <div class="row" v-for="row in game.board">
            <chess-piece
                class="tile"
                v-for="(tile, index) in row"
                v-bind:key="index"
                v-bind:tile-data="tile"
                v-bind:can-drag="getDragPermission()"
                v-bind:possibleTarget="checkInPossibleMoves(tile)"
                @checkPossibleMoves="checkPossibleMoves"
                @emptyPossibleMoves="emptyPossibleMoves"
            />
        </div>
    </div>
</template>

<script>

    import ChessPiece from './pieces/ChessPiece';

    export default {
        data() {
            return {
                game: null,
            }
        },

        props: {
            lobby: null,
            connectedPlayers: null,
            user: null,
        },

        components: {
            ChessPiece,
        },

        methods: {

            initiateBoard()  {
                let board = new Array(8).fill(null).map(()=>new Array(8).fill(null));

                for (let x = 0; x < 8; x++) {
                    for (let y = 0; y < 8; y++) {

                        let piece = this.chessConfig.chessPieces.find( obj => (obj.position.x === x && obj.position.y === y));

                        if (piece) {
                            piece.isInInitialState = piece.type === 'pawn';
                            board[y][x] = piece;
                        } else {
                            board[y][x] = {
                                "type": "empty",
                                "position": {
                                    x: x,
                                    y: y
                                }
                            };
                        }
                    }
                }

                return board;
            },

            getDragPermission() {
                return this.game.turn === this.user.id;
            },

            startGame() {
                let game = {};
                game.board = this.initiateBoard();
                game.turn = this.user.id;

                this.game = game;
            },

            restartGame() {
                window.axios.post(`/game/restartGame/${this.lobby.url}`);
                this.game = null;
                this.addGameMessage(`The game has been reset!`);
            },

            playerWon(player) {
                console.log('player: ', player.playerNumber, ' won the game!');
                this.game.victory = player;
            },

            sendGameMove(roll){
                window.axios.post(`/game/gameMove/${this.lobby.url}`, {
                    move: roll,
                });
            },

            doGameMove(roll) {

            },

            checkInPossibleMoves() {
                return false
            },

            checkPossibleMoves() {

            },

            emptyPossibleMoves() {

            },

            addGameMessage(message) {
                console.log('message: ', message);
                this.gameMessages.push(message);
                setTimeout(() => {
                    this.gameMessages = this.gameMessages.filter(e => e !== message);
                }, 10000)
            },

        },

        mounted() {
            console.log('Game Component mounted.');
            this.startGame();

            Echo.join('game.' + this.lobby.url)
                .listen('StartGameEvent', (event) => {
                    console.log('new game: ', event.game);
                    this.game = event.game;
                    this.addGameMessage(`The game has started!`);
                })
                .listen('RestartGameEvent', (event) => {
                    console.log('restarting game');
                    this.game = null;
                    this.addGameMessage(`The game has been reset!`);
                })
                .listen('GameMoveEvent', (event) => {
                    console.log('new game move: ', event);
                    this.doGameMove(event.move);
                }).listen('GameMessageEvent', (event) => {
                    console.log('new game message: ', event.message);
                    this.gameMessages.push(event.message);
                });
        }
    }
</script>

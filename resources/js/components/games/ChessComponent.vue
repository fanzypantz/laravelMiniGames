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
                possibleMoves: [],
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

            /*
                Utility
            */

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

            convertNumber (number)  {
                if (number > 7) {
                    return 7
                } else if (number < 0) {
                    return 0
                } else {
                    return number
                }
            },

            /*
                Game control logic
            */

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

            /*
                Check possible game moves
            */

            checkInPossibleMoves(tileData) {
                return this.possibleMoves.filter(move => (move.x === tileData.position.x && move.y === tileData.position.y)).length > 0;
            },

            checkPossibleMoves(tileData) {
                let possibleMoves = [];
                console.log('tileData: ', tileData);

                switch (tileData.type) {
                    case "knight":
                        let knightPositions = [
                            {y: tileData.position.y - 1, x: tileData.position.x - 2},
                            {y: tileData.position.y + 1, x: tileData.position.x - 2},
                            {y: tileData.position.y - 2, x: tileData.position.x - 1},
                            {y: tileData.position.y - 2, x: tileData.position.x + 1},
                            {y: tileData.position.y - 1, x: tileData.position.x + 2},
                            {y: tileData.position.y + 1, x: tileData.position.x + 2},
                            {y: tileData.position.y + 2, x: tileData.position.x + 1},
                            {y: tileData.position.y + 2, x: tileData.position.x - 1},
                        ];

                        for (let i = 0; i < knightPositions.length; i++) {
                            if (knightPositions[i].y < 0 || knightPositions[i].x < 0 || knightPositions[i].y > 7 || knightPositions[i].x > 7) {
                                continue
                            }
                            if (
                                this.game.board[knightPositions[i].y][knightPositions[i].x].colour !== tileData.colour ||
                                this.game.board[knightPositions[i].y][knightPositions[i].x].type === "empty"
                            ) {
                                possibleMoves.push(knightPositions[i]);
                            }
                        }
                        break;
                    case "queen":
                        possibleMoves.push(...this.checkDiagonal(tileData));
                        possibleMoves.push(...this.checkAxis(tileData));
                    //     possibleMoves = [...possibleMoves, ...this.checkPossibleDiagonal(data)];
                    //     possibleMoves = [...possibleMoves, ...this.checkPossibleAxis(data)];
                        break;
                    case "rook":
                        // possibleMoves = [...possibleMoves, ...this.checkPossibleAxis(data)];
                        break;
                    case "bishop":
                        // possibleMoves = [...possibleMoves, ...this.checkPossibleDiagonal(data)];
                        break;
                    case "king":
                        // let kingPositions = [
                        //     {y: data.position.y - 1, x: data.position.x - 1},
                        //     {y: data.position.y - 1, x: data.position.x},
                        //     {y: data.position.y - 1, x: data.position.x + 1},
                        //     {y: data.position.y, x: data.position.x + 1},
                        //     {y: data.position.y + 1, x: data.position.x + 1},
                        //     {y: data.position.y + 1, x: data.position.x},
                        //     {y: data.position.y + 1, x: data.position.x - 1},
                        //     {y: data.position.y, x: data.position.x - 1},
                        // ];
                        // for (let i = 0; i < kingPositions.length; i++) {
                        //     if (kingPositions[i].y < 0 || kingPositions[i].x < 0 || kingPositions[i].y > 7 || kingPositions[i].x > 7) {
                        //         continue
                        //     }
                        //     if (this.state.gameState.board[kingPositions[i].y][kingPositions[i].x].colour !== data.color) {
                        //         possibleMoves.push(kingPositions[i]);
                        //     }
                        // }
                        break;
                    default:
                        possibleMoves.push(...this.checkDiagonal(tileData));
                        possibleMoves.push(...this.checkAxis(tileData));
                        // let pawnPositions = [];
                        // if (this.state.youArePlayer === 0) {
                        //     if (this.state.gameState.board[data.position.y - 1][data.position.x].piece === 'empty') {
                        //         pawnPositions.push({y: data.position.y - 1, x: data.position.x});
                        //     }
                        //     if (this.state.gameState.board[data.position.y - 1][data.position.x + 1] !== undefined) {
                        //         if (this.state.gameState.board[data.position.y - 1][data.position.x + 1].colour === 'black') {
                        //             pawnPositions.push({y: data.position.y - 1, x: data.position.x + 1});
                        //         }
                        //     }
                        //     if (this.state.gameState.board[data.position.y - 1][data.position.x - 1] !== undefined) {
                        //         if (this.state.gameState.board[data.position.y - 1][data.position.x - 1].colour === 'black') {
                        //             pawnPositions.push({y: data.position.y - 1, x: data.position.x - 1});
                        //         }
                        //     }
                        //     if (data.isInInitialState && this.state.gameState.board[data.position.y - 2][data.position.x].piece === 'empty') {
                        //         pawnPositions.push({y: data.position.y - 2, x: data.position.x });
                        //     }
                        // } else {
                        //     if (this.state.gameState.board[data.position.y + 1][data.position.x].piece === 'empty') {
                        //         pawnPositions.push({y: data.position.y + 1, x: data.position.x});
                        //     }
                        //     if (this.state.gameState.board[data.position.y + 1][data.position.x + 1] !== undefined) {
                        //         if (this.state.gameState.board[data.position.y + 1][data.position.x + 1].colour === 'white') {
                        //             pawnPositions.push({y: data.position.y + 1, x: data.position.x + 1});
                        //         }
                        //     }
                        //     if (this.state.gameState.board[data.position.y + 1][data.position.x - 1] !== undefined) {
                        //         if (this.state.gameState.board[data.position.y + 1][data.position.x - 1].colour === 'white') {
                        //             pawnPositions.push({y: data.position.y + 1, x: data.position.x - 1});
                        //         }
                        //     }
                        //     if (data.isInInitialState && this.state.gameState.board[data.position.y + 2][data.position.x].piece === 'empty') {
                        //         pawnPositions.push({y: data.position.y + 2, x: data.position.x });
                        //     }
                        // }
                        // for (let i = 0; i < pawnPositions.length; i++) {
                        //     if (pawnPositions[i].y < 0 || pawnPositions[i].x < 0 || pawnPositions[i].y > 7 || pawnPositions[i].x > 7) {
                        //         continue
                        //     }
                        //     if (this.state.gameState.board[pawnPositions[i].y][pawnPositions[i].x].colour !== data.color) {
                        //         possibleMoves.push(pawnPositions[i]);
                        //     }
                        // }
                        break;
                }

                this.possibleMoves = possibleMoves;
            },

            checkDiagonal(tileData) {
                let possibleMoves = [];
                let edgePositions = [
                    {
                        x: this.convertNumber(tileData.position.x - tileData.position.y),
                        y: this.convertNumber(tileData.position.y - tileData.position.x)
                    },
                    {
                        x: this.convertNumber(tileData.position.x + tileData.position.y),
                        y: this.convertNumber(tileData.position.x - (7 - tileData.position.y))
                    },
                    {
                        x: this.convertNumber((tileData.position.x + (7 - tileData.position.y))),
                        y: this.convertNumber(tileData.position.y + (7 - tileData.position.x))
                    },
                    {
                        x: this.convertNumber((tileData.position.x + tileData.position.y) - 7),
                        y: this.convertNumber(tileData.position.x + tileData.position.y)
                    },
                ];

                for (let i = 0; i < edgePositions.length; i++) {
                    let angle = Math.atan2(edgePositions[i].y - tileData.position.y, edgePositions[i].x - tileData.position.x) * 180 / Math.PI;
                    let distance = Math.floor(Math.sqrt( Math.pow((tileData.position.x - edgePositions[i].x), 2) + Math.pow((tileData.position.y - edgePositions[i].y), 2)));
                    possibleMoves.push(...this.checkDiagonalJump(angle, distance, tileData));
                }
                console.log('possibleMoves: ', possibleMoves );
                return possibleMoves;
            },

            checkDiagonalJump(angle, distance, tileData) {
                let x;
                let y;
                let possibleMoves = [];

                for (let i = 1; i < distance + 1; i++) {
                    switch (angle) {
                        case 135:
                            x = tileData.position.x - i;
                            y = tileData.position.y + i;
                            break;
                        case -135:
                            x = tileData.position.x - i;
                            y = tileData.position.y - i;
                            break;
                        case -45:
                            x = tileData.position.x + i;
                            y = tileData.position.y - i;
                            break;
                        case 45:
                            x = tileData.position.x + i;
                            y = tileData.position.y + i;
                            break;
                        default:
                            break;
                    }
                    if (x <= 7 && x >= 0 && y <= 7 && y >= 0) {
                        if (this.game.board[y][x].type !== "empty") {
                            if (this.game.board[y][x].colour !== tileData.colour) {
                                possibleMoves.push({y: y, x: x})
                            }
                            return possibleMoves;
                        } else {
                            possibleMoves.push({y: y, x: x});
                        }
                    } else {
                        return possibleMoves;
                    }
                }
                return possibleMoves;
            },

            checkAxis(tileData) {
                let possibleMoves = [];
                let position = tileData.position;

                for (let i = position.x; i >= 0; i--) {
                    console.log('X-: ', this.game.board[position.y][i].position);
                    if (this.game.board[position.y][i]) {

                    }
                }

            },

            emptyPossibleMoves() {
                this.possibleMoves = [];
            },

            /*
                Game message system
            */

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

            // Bind the mouse up to emptying possible moves, if the user tries to drop outside of the board.
            window.addEventListener('dragend', this.emptyPossibleMoves);

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

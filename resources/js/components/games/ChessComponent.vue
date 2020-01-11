<template>
    <div v-if="board !== null" class="board chess">
        <div class="row" v-for="row in board">
            <chess-piece
                class="tile"
                v-for="(tile, index) in row"
                v-bind:key="index"
                v-bind:tile-data="tile"
                v-bind:can-drag="getDragPermission(tile)"
                v-bind:possibleTarget="checkInPossibleMoves(tile)"
                @checkPossibleMoves="checkPossibleMoves"
                @emptyPossibleMoves="emptyPossibleMoves"
                @sendGameMove="sendGameMove"
            />
        </div>
    </div>
</template>

<script>

    import ChessPiece from './pieces/ChessPiece';

    export default {
        data() {
            return {
                board: null,
                turn: null,
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

            getDragPermission(tile) {
                return this.turn === this.user.id && tile.type !== 'empty';
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
                this.board = this.initiateBoard();
                this.turn = this.user.id;
            },

            restartGame() {
                window.axios.post(`/game/restartGame/${this.lobby.url}`);
                this.board = null;
                this.addGameMessage(`The game has been reset!`);
            },

            playerWon(player) {
                console.log('player: ', player.playerNumber, ' won the game!');
                this.victory = player;
            },

            sendGameMove(gameMove){
                console.log('sending: ', gameMove);
                window.axios.post(`/game/gameMove/${this.lobby.url}`, {
                    move: gameMove,
                });
                this.doGameMove(gameMove);
            },

            doGameMove(gameMove) {
                let gameMoveData =  JSON.parse(JSON.stringify(gameMove));
                let oldPiece = gameMoveData.oldPiece;
                let newPiece = gameMoveData.newPiece;

                Vue.set(this.board[oldPiece.position.y], oldPiece.position.x, {
                    "type": "empty",
                    "position": oldPiece.position,
                });

                oldPiece.position = newPiece.position;
                Vue.set(this.board[newPiece.position.y], newPiece.position.x, oldPiece);
            },

            /*
                Check possible game moves
            */

            checkInPossibleMoves(tileData) {
                return this.possibleMoves.filter(move => (move.x === tileData.position.x && move.y === tileData.position.y)).length > 0;
            },

            async checkPossibleMoves(tileData) {
                let possibleMoves = [];
                let diagonalMoves = [];
                let axisMoves = [];
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
                                this.board[knightPositions[i].y][knightPositions[i].x].colour !== tileData.colour ||
                                this.board[knightPositions[i].y][knightPositions[i].x].type === "empty"
                            ) {
                                possibleMoves.push(knightPositions[i]);
                            }
                        }
                        break;
                    case "queen":
                        diagonalMoves = await this.checkDiagonal(tileData);
                        axisMoves = await this.checkAxis(tileData);
                        if (diagonalMoves.length > 0) {
                            possibleMoves.push(...diagonalMoves);
                        }
                        if (axisMoves.length > 0) {
                            possibleMoves.push(...axisMoves);
                        }
                        break;
                    case "rook":
                        axisMoves = await this.checkAxis(tileData);
                        if (axisMoves.length > 0) {
                            possibleMoves.push(...axisMoves);
                        }
                        break;
                    case "bishop":
                        diagonalMoves = await this.checkDiagonal(tileData);
                        if (diagonalMoves.length > 0) {
                            possibleMoves.push(...diagonalMoves);
                        }
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
                        diagonalMoves = await this.checkDiagonal(tileData);
                        axisMoves = await this.checkAxis(tileData);
                        if (diagonalMoves.length > 0) {
                            possibleMoves.push(...diagonalMoves);
                        }
                        if (axisMoves.length > 0) {
                            possibleMoves.push(...axisMoves);
                        }
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
                return new Promise ((resolve) => {
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
                    resolve(possibleMoves);
                });
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
                        if (this.board[y][x].type !== "empty") {
                            if (this.board[y][x].colour !== tileData.colour) {
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
                return new Promise ((resolve) => {
                    let possibleMoves = [];
                    let position = tileData.position;

                    for (let i = position.x - 1; i >= 0; i--) {
                        let tile = this.board[position.y][i];
                        if (tile.type !== 'empty') {
                            if (tile.colour !== tileData.colour) {
                                possibleMoves.push(tile.position);
                            }
                            break;
                        } else {
                            possibleMoves.push(tile.position);
                        }
                    }
                    for (let i = position.x + 1; i <= 7; i++) {
                        let tile = this.board[position.y][i];
                        if (tile.type !== 'empty') {
                            if (tile.colour !== tileData.colour) {
                                possibleMoves.push(tile.position);
                            }
                            break;
                        } else {
                            possibleMoves.push(tile.position);
                        }
                    }
                    for (let i = position.y - 1; i >= 0; i--) {
                        let tile = this.board[i][position.x];
                        if (tile.type !== 'empty') {
                            if (tile.colour !== tileData.colour) {
                                possibleMoves.push(tile.position);
                            }
                            break;
                        } else {
                            possibleMoves.push(tile.position);
                        }
                    }
                    for (let i = position.y + 1; i <= 7; i++) {
                        let tile = this.board[i][position.x];
                        if (tile.type !== 'empty') {
                            if (tile.colour !== tileData.colour) {
                                possibleMoves.push(tile.position);
                            }
                            break;
                        } else {
                            possibleMoves.push(tile.position);
                        }
                    }
                    resolve(possibleMoves);
                });


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
                    this.board = null;
                    this.addGameMessage(`The game has been reset!`);
                })
                .listen('GameMoveEvent', (event) => {
                    this.doGameMove(event.move);
                }).listen('GameMessageEvent', (event) => {
                    console.log('new game message: ', event.message);
                    this.gameMessages.push(event.message);
                });
        }
    }
</script>

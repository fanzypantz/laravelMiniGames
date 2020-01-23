<template>
    <div class="game">

        <!--GAME-->
        <div v-if="board !== null" v-bind:class="['board', 'chess', this.player1 !== this.user.id ? 'player2' : '' , ]">
            <div class="row" v-for="row in board">
                <chess-piece
                    class="tile"
                    v-for="(tile, index) in row"
                    v-bind:key="index"
                    v-bind:tile-data="tile"
                    v-bind:can-drag="getDragPermission(tile)"
                    v-bind:possibleTarget="checkInPossibleMoves(tile)"
                    v-bind:isPlayer1="player1 === user.id"
                    @checkPossibleMoves="checkPossibleMoves"
                    @emptyPossibleMoves="emptyPossibleMoves"
                    @togglePromote="togglePromote"
                    @sendGameMove="sendGameMove"
                />
            </div>
        </div>

        <!--Buttons-->
        <div v-if="board === null" class="buttons">
            <button @click="initiateGame(user.id, true)">Start Game</button>
        </div>

        <!--Promote Menu-->
        <div v-if="isPromoting && gameMove !== null" class="promote">
            <h2>Select piece to promote</h2>
            <div class="promote-type">
                <img v-bind:src="getPieceImage('queen')" alt="" @click="promote('queen')"/>
                <img v-bind:src="getPieceImage('knight')" alt="" @click="promote('knight')"/>
                <img v-bind:src="getPieceImage('rook')" alt="" @click="promote('rook')"/>
                <img v-bind:src="getPieceImage('bishop')" alt="" @click="promote('bishop')"/>
            </div>
        </div>

        <div v-if="isChecked" class="pass">
            <button @click="passTurn()">Start Game</button>
        </div>
    </div>

</template>

<script>

    import ChessPiece from './pieces/ChessPiece';

    export default {
        data() {
            return {
                isChecked: false,
                isPromoting: false,
                board: null,
                turn: null,
                score: {},
                player1: null,
                possibleMoves: [],
                gameMove: null,
                debug: true,
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

            initiateBoard() {
                let board = new Array(8).fill(null).map(() => new Array(8).fill(null));

                for (let x = 0; x < 8; x++) {
                    for (let y = 0; y < 8; y++) {

                        let piece = this.chessConfig.chessPieces.find(obj => (obj.position.x === x && obj.position.y === y));

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
                return (
                    (
                        this.turn === this.user.id && tile.type !== 'empty'
                    ) &&
                    (
                        (tile.colour === 'white' && this.player1 === this.user.id) ||
                        (tile.colour === 'black' && this.player1 !== this.user.id)
                    )
                    // &&
                    // (
                    //     this.isChecked === false || (this.isChecked && tile.type === 'king')
                    // )
                )
            },

            convertNumber(number) {
                if (number > 7) {
                    return 7
                } else if (number < 0) {
                    return 0
                } else {
                    return number
                }
            },

            getOpponentUser() {
                return this.connectedPlayers.find(e => e.id !== this.user.id);
            },

            getPieceImage(name) {
                if (name) {
                    return `/images/icons/chess/${name}.svg`;
                } else{
                    return `/images/icons/play.svg`;
                }
            },

            getKing(board) {
                let kingType = this.player1 === this.user.id ? 'white' : 'black';
                return board.reduce(function (a, b) {
                    return a.concat(b);
                }).filter((row) => {
                    return row.type === 'king' && row.colour === kingType;
                })[0];
            },

            cleanCopy(object) {
                return JSON.parse(JSON.stringify(object))
            },

            /*
                Game control logic
            */

            checkGameState() {
                let gameState = JSON.parse(this.lobby.gameState);
                if (gameState !== null) {
                    this.board = gameState.board;
                    this.turn = gameState.turn;
                    this.player1 = gameState.player1;
                    this.score = gameState.score;
                }
            },

            initiateGame(player1, newGame) {
                window.axios.post(`/game/startGame/${this.lobby.url}`, {
                    game: this.startGame(player1, newGame),
                });
                this.addGameMessage(`The game has started!`)
            },

            startGame(player1, newGame) {
                this.board = this.initiateBoard();
                this.turn = player1;
                this.player1 = player1;
                if (newGame) {
                    this.score[this.user.id] = 0;
                    this.score[this.getOpponentUser().id] = 0;
                } else {
                    this.score[player1]++;
                }
                return {
                    board: this.board,
                    turn: this.turn,
                    player1: this.player1,
                    score: this.score,
                }
            },

            restartGame() {
                this.initiateGame();
            },

            sendGameMove(gameMove) {
                console.log('sending: ', gameMove);
                this.doGameMove(gameMove, false).then(() => {
                    window.axios.post(`/game/gameMove/${this.lobby.url}`, {
                        move: gameMove,
                        gameState: {
                            board: this.board,
                            turn: this.turn,
                            player1: this.player1,
                            score: this.score,
                        }
                    });
                }).catch((e) => {
                    console.log('error: ', e);
                });
            },

            togglePromote(gameMove) {
                if (gameMove !== null && gameMove !== undefined) {
                    this.gameMove = gameMove;
                    this.isPromoting = true;
                } else {
                    this.gameMove = null;
                    this.isPromoting = false;
                }
            },

            promote(type) {
                switch (type) {
                    case 'queen':
                        this.gameMove.oldPiece.type = 'queen';
                        break;
                    case 'knight':
                        this.gameMove.oldPiece.type = 'knight';
                        break;
                    case 'rook':
                        this.gameMove.oldPiece.type = 'rook';
                        break;
                    default:
                        this.gameMove.oldPiece.type = 'bishop';
                        break;
                }
                this.sendGameMove(this.gameMove);
                this.togglePromote();
            },

            doGameMove(gameMove, isReceiver) {
                return new Promise(async (resolve, reject) => {

                    // ANIMATIONS HERE

                    // Then set new data
                    let gameMoveData = this.cleanCopy(gameMove);
                    let board = this.cleanCopy(this.board);
                    let oldPiece = gameMoveData.oldPiece;
                    let newPiece = gameMoveData.newPiece;
                    let king;

                    board[oldPiece.position.y][oldPiece.position.x] = {
                        "type": "empty",
                        "position": oldPiece.position,
                    };

                    oldPiece.position = newPiece.position;
                    if (oldPiece.type === 'pawn' && oldPiece.isInInitialState) {
                        oldPiece.isInInitialState = false;
                    }
                    board[newPiece.position.y][newPiece.position.x] = oldPiece;

                    // Check if this new board state will result in the king still being in Check, reject if true
                    king = this.getKing(board);
                    if (this.isChecked && !isReceiver) {
                        // You are checked and you are the one making a move
                        let isCheckedAfterMove = await this.checkKingTiles({
                            position: king.position,
                            colour: king.colour,
                        }, board);
                        // If king is checked after trying the move reject, if not do the move and unset check
                        if (isCheckedAfterMove) {
                            reject('King is still in check');
                            this.addGameMessage("Your king will still be in check after this move!");
                            return;
                        } else {
                            this.board = board;
                            this.isChecked = false;
                        }
                    } else if (!this.isChecked && isReceiver) {
                        // You are not checked and you are just receiving opponent move
                        this.isChecked = await this.checkKingTiles({
                            position: king.position,
                            colour: king.colour,
                        }, board);
                        if (this.isChecked) {
                            this.addGameMessage("Your king is now in check!");
                        }
                        this.board = board;
                    } else if (!this.isChecked && !isReceiver) {
                        // You are not checked and you are the one making a move
                        let isCheckedAfterMove = await this.checkKingTiles({
                            position: king.position,
                            colour: king.colour,
                        }, board);
                        if (isCheckedAfterMove) {
                            reject('King will be in check after this move!');
                            this.addGameMessage("Your king will be in check after this move!");
                            return;
                        } else {
                            this.board = board;
                        }
                    }
                    // Check if the game was won before changing the turn
                    if (newPiece.type === 'king') {
                        this.handleWin(this.turn);
                        return;
                    } else {
                        if (this.turn === this.user.id) {
                            let opponent = this.getOpponentUser();
                            this.addGameMessage('Opponents Turn');
                            this.turn = opponent.id;
                        } else {
                            this.addGameMessage('My Turn');
                            this.turn = this.user.id;
                        }
                    }

                    // Check if king has been put in check or checkmate
                    this.checkMate(king, board);
                    resolve(true);
                });
            },

            handleWin(attacker) {
                // Just restart the game if game won
                if (attacker === this.user.id) {
                    // Just let the winner send the request to start a new game
                    this.initiateGame(attacker, false);
                }
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

                if (this.debug) {
                    for (let y = 0; y < this.board.length; y++) {
                        for (let x = 0; x < this.board[y].length; x++) {
                            possibleMoves.push(this.board[y][x].position);
                        }
                    }
                    this.possibleMoves = possibleMoves;
                    return;
                }

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
                        diagonalMoves = await this.checkDiagonal(tileData, this.board);
                        axisMoves = await this.checkAxis(tileData, this.board);
                        if (diagonalMoves.length > 0) {
                            possibleMoves.push(...diagonalMoves);
                        }
                        if (axisMoves.length > 0) {
                            possibleMoves.push(...axisMoves);
                        }
                        break;
                    case "rook":
                        axisMoves = await this.checkAxis(tileData, this.board);
                        if (axisMoves.length > 0) {
                            possibleMoves.push(...axisMoves);
                        }
                        break;
                    case "bishop":
                        diagonalMoves = await this.checkDiagonal(tileData, this.board);
                        if (diagonalMoves.length > 0) {
                            possibleMoves.push(...diagonalMoves);
                        }
                        break;
                    case "king":
                        let kingPositions = [
                            {y: tileData.position.y - 1, x: tileData.position.x - 1},
                            {y: tileData.position.y - 1, x: tileData.position.x},
                            {y: tileData.position.y - 1, x: tileData.position.x + 1},
                            {y: tileData.position.y, x: tileData.position.x + 1},
                            {y: tileData.position.y + 1, x: tileData.position.x + 1},
                            {y: tileData.position.y + 1, x: tileData.position.x},
                            {y: tileData.position.y + 1, x: tileData.position.x - 1},
                            {y: tileData.position.y, x: tileData.position.x - 1},
                        ];
                        for (let i = 0; i < kingPositions.length; i++) {
                            if (kingPositions[i].y < 0 || kingPositions[i].x < 0 || kingPositions[i].y > 7 || kingPositions[i].x > 7) {
                                continue
                            }
                            if (this.board[kingPositions[i].y][kingPositions[i].x].colour !== tileData.colour) {
                                possibleMoves.push(kingPositions[i]);
                            }
                        }
                        break;
                    default:
                        let pawnPositions = [];
                        if (this.player1 === this.user.id) {
                            if (this.board[tileData.position.y - 1][tileData.position.x].type === 'empty') {
                                pawnPositions.push({y: tileData.position.y - 1, x: tileData.position.x});
                            }
                            if (this.board[tileData.position.y - 1][tileData.position.x + 1] !== undefined) {
                                if (this.board[tileData.position.y - 1][tileData.position.x + 1].colour === 'black') {
                                    pawnPositions.push({y: tileData.position.y - 1, x: tileData.position.x + 1});
                                }
                            }
                            if (this.board[tileData.position.y - 1][tileData.position.x - 1] !== undefined) {
                                if (this.board[tileData.position.y - 1][tileData.position.x - 1].colour === 'black') {
                                    pawnPositions.push({y: tileData.position.y - 1, x: tileData.position.x - 1});
                                }
                            }
                            if (tileData.isInInitialState && this.board[tileData.position.y - 2][tileData.position.x].type === 'empty') {
                                pawnPositions.push({y: tileData.position.y - 2, x: tileData.position.x});
                            }
                        } else {
                            if (this.board[tileData.position.y + 1][tileData.position.x].type === 'empty') {
                                pawnPositions.push({y: tileData.position.y + 1, x: tileData.position.x});
                            }
                            if (this.board[tileData.position.y + 1][tileData.position.x + 1] !== undefined) {
                                if (this.board[tileData.position.y + 1][tileData.position.x + 1].colour === 'white') {
                                    pawnPositions.push({y: tileData.position.y + 1, x: tileData.position.x + 1});
                                }
                            }
                            if (this.board[tileData.position.y + 1][tileData.position.x - 1] !== undefined) {
                                if (this.board[tileData.position.y + 1][tileData.position.x - 1].colour === 'white') {
                                    pawnPositions.push({y: tileData.position.y + 1, x: tileData.position.x - 1});
                                }
                            }
                            if (tileData.isInInitialState && this.board[tileData.position.y + 2][tileData.position.x].type === 'empty') {
                                pawnPositions.push({y: tileData.position.y + 2, x: tileData.position.x});
                            }
                        }

                        for (let i = 0; i < pawnPositions.length; i++) {
                            if (pawnPositions[i].y < 0 || pawnPositions[i].x < 0 || pawnPositions[i].y > 7 || pawnPositions[i].x > 7) {
                                continue
                            }
                            if (this.board[pawnPositions[i].y][pawnPositions[i].x].colour !== tileData.colour) {
                                possibleMoves.push(pawnPositions[i]);
                            }
                        }
                        break;
                }

                this.possibleMoves = possibleMoves;
            },

            checkDiagonal(tileData, board) {
                return new Promise((resolve) => {
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
                        let distance = Math.floor(Math.sqrt(Math.pow((tileData.position.x - edgePositions[i].x), 2) + Math.pow((tileData.position.y - edgePositions[i].y), 2)));
                        possibleMoves.push(...this.checkDiagonalJump(angle, distance, tileData, board));
                    }
                    resolve(possibleMoves);
                });
            },

            checkDiagonalJump(angle, distance, tileData, board) {
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
                        if (board[y][x].type !== "empty") {
                            if (board[y][x].colour !== tileData.colour) {
                                possibleMoves.push({y: y, x: x, type: board[y][x].type, distance: i})
                            }
                            return possibleMoves;
                        } else {
                            possibleMoves.push({y: y, x: x, type: board[y][x].type, distance: i});
                        }
                    } else {
                        return possibleMoves;
                    }
                }
                return possibleMoves;
            },

            checkAxis(tileData, board) {
                return new Promise((resolve) => {
                    let possibleMoves = [];
                    let position = tileData.position;
                    let distance = 0;

                    for (let i = position.x - 1; i >= 0; i--) {
                        distance++;
                        let tile = board[position.y][i];
                        if (tile.type !== 'empty') {
                            if (tile.colour !== tileData.colour) {
                                possibleMoves.push({x: tile.position.x, y: tile.position.y, type: tile.type, distance: distance});
                            }
                            break;
                        } else {
                            possibleMoves.push({x: tile.position.x, y: tile.position.y, type: tile.type, distance: distance});
                        }
                    }
                    distance = 0;
                    for (let i = position.x + 1; i <= 7; i++) {
                        distance++;
                        let tile = board[position.y][i];
                        if (tile.type !== 'empty') {
                            if (tile.colour !== tileData.colour) {
                                possibleMoves.push({x: tile.position.x, y: tile.position.y, type: tile.type, distance: distance});
                            }
                            break;
                        } else {
                            possibleMoves.push({x: tile.position.x, y: tile.position.y, type: tile.type, distance: distance});
                        }
                    }
                    distance = 0;
                    for (let i = position.y - 1; i >= 0; i--) {
                        distance++;
                        let tile = board[i][position.x];
                        if (tile.type !== 'empty') {
                            if (tile.colour !== tileData.colour) {
                                possibleMoves.push({x: tile.position.x, y: tile.position.y, type: tile.type, distance: distance});
                            }
                            break;
                        } else {
                            possibleMoves.push({x: tile.position.x, y: tile.position.y, type: tile.type, distance: distance});
                        }
                    }
                    distance = 0;
                    for (let i = position.y + 1; i <= 7; i++) {
                        distance++;
                        let tile = board[i][position.x];
                        if (tile.type !== 'empty') {
                            if (tile.colour !== tileData.colour) {
                                possibleMoves.push({x: tile.position.x, y: tile.position.y, type: tile.type, distance: distance});
                            }
                            break;
                        } else {
                            possibleMoves.push({x: tile.position.x, y: tile.position.y, type: tile.type, distance: distance});
                        }
                    }
                    resolve(possibleMoves);
                });


            },

            emptyPossibleMoves() {
                this.possibleMoves = [];
            },

            async checkMate(king, board) {
                let positions = [
                    {
                        x: king.position.x - 1,
                        y: king.position.y - 1
                    },
                    {
                        x: king.position.x,
                        y: king.position.y - 1
                    },
                    {
                        x: king.position.x + 1,
                        y: king.position.y - 1
                    },
                    {
                        x: king.position.x + 1,
                        y: king.position.y
                    },
                    {
                        x: king.position.x + 1,
                        y: king.position.y + 1
                    },
                    {
                        x: king.position.x,
                        y: king.position.y + 1
                    },
                    {
                        x: king.position.x - 1,
                        y: king.position.y + 1
                    },
                    {
                        x: king.position.x - 1,
                        y: king.position.y
                    },
                ];

                let checkMateCount = 0;
                for (const position of positions) {
                    if (position.x > 7 || position.y > 7 || position.x < 0 || position.y < 0) {
                        continue;
                    }
                    // If the tile around the king will also be check, add to the count
                    if (await this.checkKingTiles({
                        position: position,
                        colour: king.colour
                    }, board)){
                        checkMateCount++;
                    }
                }

                if (checkMateCount === 8) {
                    this.handleWin(this.getOpponentUser().id);
                }
            },

            checkKingTiles(tileData, board) {
                return new Promise (async (resolve, reject) => {
                    let diagonalMoves = await this.checkDiagonal(tileData, board);
                    diagonalMoves = diagonalMoves.filter(e => e.type !== 'empty');
                    let axisMoves = await this.checkAxis(tileData, board);
                    axisMoves = axisMoves.filter(e => e.type !== 'empty');
                    // Check if any diagonal pieces can kill the king
                    if (diagonalMoves.length > 0) {
                        let count = 0;
                        let king = diagonalMoves.filter(e => e.type === 'king')[0];
                        if (king !== undefined) {
                            if (king.distance === 1) {
                                resolve(true);
                            }
                        }
                        count += diagonalMoves.filter(e => e.type ===  'queen').length;
                        count += diagonalMoves.filter(e => e.type ===  'bishop').length;
                        if (count > 0) {
                            resolve(true);
                        }
                    }
                    // Check if anything from the sides can kill the king
                    if (axisMoves.length > 0) {
                        let count = 0;
                        let king = axisMoves.filter(e => e.type === 'king')[0];
                        if (king !== undefined) {
                            if (king.distance === 1) {
                                resolve(true);
                            }
                        }
                        count += axisMoves.filter(e => e.type ===  'queen').length;
                        count += axisMoves.filter(e => e.type ===  'rook').length;
                        if (count > 0) {
                            resolve(true);
                        }
                    }

                    // Check if there is a knight that can jump on king
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
                        if (knightPositions[i].y > 7 || knightPositions[i].x > 7 || knightPositions[i].y < 0 || knightPositions[i].x < 0) {
                            continue;
                        }
                        let knight = board[knightPositions[i].y][knightPositions[i].x];
                        if (knight.type === 'knight' && knight.colour !== tileData.colour) {
                            resolve(true);
                        }
                    }


                    // Check if there are any pawns
                    let pawns = [];
                    pawns.push(...diagonalMoves.filter(e => e.type === 'pawn'));
                    pawns.push(...axisMoves.filter(e => e.type === 'pawn'));
                    if (pawns.length > 0) {
                        if (this.player1 === this.user.id) {
                            for (let i = 0; i < pawns.length; i++) {
                                if (
                                    (pawns[i].x === tileData.position.x-1 && pawns[i].y === tileData.position.y-1) ||
                                    (pawns[i].x === tileData.position.x+1 && pawns[i].y === tileData.position.y-1)
                                ) {
                                    resolve(true);
                                }
                            }
                        } else {
                            for (let i = 0; i < pawns.length; i++) {
                                if (
                                    (pawns[i].x === tileData.position.x+1 && pawns[i].y === tileData.position.y+1) ||
                                    (pawns[i].x === tileData.position.x-1 && pawns[i].y === tileData.position.y+1)
                                ) {
                                    resolve(true);
                                }
                            }
                        }
                    }

                    resolve(false)
                });
            },

            /*
                Game message system
            */

            addGameMessage(message) {
                this.$emit('addGameMessage', message);
            },

        },

        mounted() {
            console.log('Chess Component mounted.');
            this.checkGameState();

            // Bind the mouse up to emptying possible moves, if the user tries to drop outside of the board.
            window.addEventListener('dragend', this.emptyPossibleMoves);

            // Socket broadcasting listening events
            Echo.join('game.' + this.lobby.url)
                .listen('StartGameEvent', (event) => {
                    console.log('new game: ', event.game);
                    this.board = event.game.board;
                    this.turn = event.game.turn;
                    this.player1 = event.game.player1;
                    this.score = event.game.score;
                    this.addGameMessage(`The game has started!`);
                })
                .listen('RestartGameEvent', (event) => {
                    console.log('restarting game');
                    this.board = null;
                    this.addGameMessage(`The game has been reset!`);
                })
                .listen('GameMoveEvent', (event) => {
                    this.doGameMove(event.move, true);
                }).listen('GameMessageEvent', (event) => {
                    console.log('new game message: ', event.message);
                    this.addGameMessage(event.message);
                });
        }
    }
</script>

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
                    @sendGameMove="sendGameMove"
                />
            </div>
        </div>

        <!--Buttons-->
        <div v-if="board === null" class="buttons">
            <button @click="initiateGame()">Start Game</button>
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
                player1: null,
                isChecked: false,
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
                    ) &&
                    (
                        this.isChecked === false || (this.isChecked && tile.type === 'king')
                    )
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

            /*
                Game control logic
            */

            checkGameState() {
                let gameState = JSON.parse(this.lobby.gameState);
                if (gameState !== null) {
                    this.board = gameState.board;
                    this.turn = gameState.turn;
                    this.player1 = gameState.player1
                }
            },

            initiateGame() {
                window.axios.post(`/game/startGame/${this.lobby.url}`, {
                    game: this.startGame(),
                });
                this.addGameMessage(`The game has started!`)
            },

            startGame() {
                this.board = this.initiateBoard();
                this.turn = this.user.id;
                this.player1 = this.user.id;
                return {
                    board: this.board,
                    turn: this.turn,
                    player1: this.player1
                }
            },

            restartGame() {
                this.initiateGame();
            },

            playerWon(player) {
                console.log('player: ', player.playerNumber, ' won the game!');
                this.victory = player;
            },

            sendGameMove(gameMove) {
                console.log('sending: ', gameMove);
                this.doGameMove(gameMove).then(() => {
                    window.axios.post(`/game/gameMove/${this.lobby.url}`, {
                        move: gameMove,
                        gameState: {
                            board: this.board,
                            turn: this.turn,
                            player1: this.player1
                        }
                    });
                });
            },

            doGameMove(gameMove) {
                return new Promise(resolve => {

                    // ANIMATIONS HERE

                    // Then set new data
                    let gameMoveData = JSON.parse(JSON.stringify(gameMove));
                    console.log('gameMoveData: ', gameMoveData);
                    let oldPiece = gameMoveData.oldPiece;
                    let newPiece = gameMoveData.newPiece;

                    Vue.set(this.board[oldPiece.position.y], oldPiece.position.x, {
                        "type": "empty",
                        "position": oldPiece.position,
                    });

                    oldPiece.position = newPiece.position;
                    if (oldPiece.type === 'pawn' && oldPiece.isInInitialState) {
                        oldPiece.isInInitialState = false;
                    }
                    Vue.set(this.board[newPiece.position.y], newPiece.position.x, oldPiece);

                    // Check if the game was won before changing the turn
                    this.checkIfKing(newPiece.type, this.turn);

                    if (this.turn === this.user.id) {
                        let opponent = this.getOpponentUser();
                        console.log('opponent turn: ', opponent);
                        this.turn = opponent.id;
                    } else {
                        console.log('my turn: ',);
                        this.turn = this.user.id;
                    }

                    // Check if king has been put in check or checkmate
                    this.checkKingIsChecked();

                    resolve(true);
                });
            },

            checkIfKing(target, attacker) {
                if (target === 'king') {
                    this.handleWin(attacker);
                }
            },

            async checkKingIsChecked() {
                let king;
                let kingType = this.player1 === this.user.id ? 'white' : 'black';
                king = this.board.reduce(function (a, b) {
                        return a.concat(b);
                    }).filter((row) => {
                        return row.type === 'king' && row.colour === kingType;
                    })[0];

                let check = await this.checkKingTiles({
                    position: king.position,
                    colour: king.colour,
                });

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
                    })){
                        checkMateCount++;
                    }
                }

                if (checkMateCount === 8) {
                    alert("Check Mate")
                }

                this.isChecked = check;
                console.log('check: ', check);
            },

            checkKingTiles(tileData) {
                return new Promise (async (resolve, reject) => {
                    let diagonalMoves = await this.checkDiagonal(tileData);
                    diagonalMoves = diagonalMoves.filter(e => e.type !== 'empty');
                    let axisMoves = await this.checkAxis(tileData);
                    axisMoves = axisMoves.filter(e => e.type !== 'empty');
                    console.log('diagonalMoves: ', diagonalMoves);
                    console.log('axisMoves: ', axisMoves);

                    // Check if any diagonal pieces can kill the king
                    if (diagonalMoves.length > 0) {
                        let count = 0;
                        let king = diagonalMoves.filter(e => e.type === 'king')[0];
                        if (king !== undefined) {
                            console.log('found king: ', king);
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
                            console.log('found king: ', king);
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
                        if (this.board[knightPositions[i].y][knightPositions[i].x].type === 'knight') {
                            resolve(true);
                        }
                    }


                    // Check if there are any pawns
                    let pawns = axisMoves.filter(e => e.type === 'pawn');
                    if (pawns.length > 0) {
                        if (this.player1 === this.user.id) {
                            for (let i = 0; i < pawns.length; i++) {
                                if (
                                    pawns[i].position === {x: tileData.position.x-1, y: tileData.position.y-1} ||
                                    pawns[i].position === {x: tileData.position.x+1, y: tileData.position.y-1}
                                ) {
                                    resolve(true);
                                }
                            }
                        } else {
                            for (let i = 0; i < pawns.length; i++) {
                                if (
                                    pawns[i].position === {x: tileData.position.x+1, y: tileData.position.y+1} ||
                                    pawns[i].position === {x: tileData.position.x-1, y: tileData.position.y+1}
                                ) {
                                    resolve(true);
                                }
                            }
                        }
                    }

                    resolve(false)
                });
            },

            handleWin(attacker) {

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

            checkDiagonal(tileData) {
                console.log('diagonalTile: ', tileData);

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
                                possibleMoves.push({y: y, x: x, type: this.board[y][x].type, distance: i})
                            }
                            return possibleMoves;
                        } else {
                            possibleMoves.push({y: y, x: x, type: this.board[y][x].type, distance: i});
                        }
                    } else {
                        return possibleMoves;
                    }
                }
                return possibleMoves;
            },

            checkAxis(tileData) {
                return new Promise((resolve) => {
                    let possibleMoves = [];
                    let position = tileData.position;
                    let distance = 0;

                    for (let i = position.x - 1; i >= 0; i--) {
                        distance++;
                        let tile = this.board[position.y][i];
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
                        let tile = this.board[position.y][i];
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
                        let tile = this.board[i][position.x];
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
                        let tile = this.board[i][position.x];
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
                    this.addGameMessage(event.message);
                });
        }
    }
</script>

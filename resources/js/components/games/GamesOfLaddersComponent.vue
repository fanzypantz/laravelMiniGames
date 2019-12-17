<template>
    <div class="game"  @mousemove="drag">

        <div class="player-name-user" v-if="user !== null && !isSelectingCharacter && game === null">
            <img class="game-player" src="/images/icons/player-owner.svg" alt="">
            <h3>{{user.name}}</h3>
        </div>

        <div class="player-name-opponent" v-if="connectedPlayers !== null && connectedPlayers.length === 2 && !isSelectingCharacter && game === null">
            <img class="game-player" src="/images/icons/player.svg" alt="">
            <h3>{{getOpponentUser()}}</h3>
        </div>

        <!--CHARACTER SELECT-->
        <div class="dropdown" v-if="game === null">
            <div class="buttons">
                <button v-if="!isSelectingCharacter" class="dropdown-current"  @click="toggleSelect()">Select a character</button>
                <button v-if="opponentCharacter && selectedCharacter && !isSelectingCharacter" @click="startGame()">
                    <img class="button-icon" src="/images/icons/play.svg" alt="">
                    <span class="text">Start Game</span>
                </button>
            </div>

            <div v-if="isSelectingCharacter" class="dropdown-select">
                <div v-for="character in characters" @click="selectCharacter(character.data)"
                     v-bind:class="[character.name === opponentCharacter ? 'selected' : '' , 'dropdown-character']"
                     :disabled="character.name === opponentCharacter"
                >
                    <img class="dropdown-characterImage" :src="getImageUrl(character.name)" alt="">
                    <p class="dropdown-name" v-bind:title="character.name">{{ character.name }}</p>
                </div>
            </div>

        </div>

        <div class="selected-character" v-if="game === null && !isSelectingCharacter">
            <ul class="description" v-if="selectedCharacter !== null">
                <li class="description-name">{{ selectedCharacter.name }}</li>
                <li class="characterImage"><img :src="getImageUrl(selectedCharacter.name)" alt=""></li>
                <li v-if="selectedCharacter.born"><span class="description-title">Born:</span>{{ selectedCharacter.born }}</li>
                <li v-if="selectedCharacter.gender"><span class="description-title">Gender:</span>{{ selectedCharacter.gender }}</li>
                <li v-if="selectedCharacter.father"><span class="description-title">Father:</span>{{ selectedCharacter.father.name }}</li>
                <li v-if="selectedCharacter.mother"><span class="description-title">Mother:</span>{{ selectedCharacter.mother.name }}</li>
                <li v-if="selectedCharacter.spouse"><span class="description-title">Spouse:</span>{{ selectedCharacter.spouse.name }}</li>
                <li v-if="selectedCharacter.playedBy[0]"><span class="description-title">Portrayed by:</span>{{ selectedCharacter.playedBy[0] }}</li>
                <ul v-if="selectedCharacter.titles.length > 1">
                    <li><span class="description-title">Titles:</span>{{selectedCharacter.titles.length}}</li>
                    <li class="description-bullet" v-for="title in selectedCharacter.titles"> - {{ title }}</li>
                </ul>
                <ul v-if="selectedCharacter.aliases.length > 1">
                    <li><span class="description-title">Aliases:</span>{{selectedCharacter.aliases.length}}</li>
                    <li  class="description-bullet" v-for="alias in selectedCharacter.aliases"> - {{ alias }}</li>
                </ul>
            </ul>

            <ul class="description opponent" v-if="opponentCharacter !== null">
                <li class="description-name">{{ opponentCharacter }}</li>
                <li class="characterImage"><img :src="getImageUrl(opponentCharacter)" alt=""></li>
            </ul>
        </div>

        <!--GAME-->
        <div v-if="game !== null  && this.game.victory === null" class="board" ref="game">
            <div v-bind:class="tileClass(tile.id)" v-for="(tile, index) in game.board" v-bind:key="index" >
                <p>{{ tile.id }}</p>
                <img class="snake" v-if="tile.trap !== null" :src="'/images/snake.svg'" draggable="false" :style="{
                    transform: 'rotate(' + tile.trap.angle + 'deg)',
                    width: 'calc(100% * ' + tile.trap.distance + ')',
                }" >
                <img class="snake" v-if="tile.ladder !== null && tile.ladder.end !== undefined" :src="'/images/ladder.svg'" draggable="false" :style="{
                    transform: 'rotate(' + tile.ladder.angle + 'deg)',
                    width: 'calc(100% * ' + tile.ladder.distance + ')',
                }" >

                <!--PLAYER PIECE-->
                <img :id="'player-piece-' + player.id" v-bind:class="[player.name === selectedCharacter.name ? 'current-character' : '' , 'piece-character']" v-for="player in tile.pieces" :src="getImageUrl(player.name)" alt="">
            </div>
        </div>

        <!--DIE-->
        <div id="dice"
             v-if="game !== null && this.game.victory === null"
             v-bind:style="{
                    transform: 'rotateY(' + this.dieRotationY + 'deg) ' + 'rotateX(' + this.dieRotationX + 'deg) !important',
                    top: this.diePos.y + 'px',
                    left: this.diePos.x + 'px',
                 }"
             @mousedown="startDrag"
        >
            <div class="diceFace" id="front">1</div>
            <div class="diceFace" id="right">2</div>
            <div class="diceFace" id="back">3</div>
            <div class="diceFace" id="left">4</div>
            <div class="diceFace" id="top">5</div>
            <div class="diceFace" id="bottom">6</div>
        </div>

        <!--Message Cards-->
        <div class="card-container">
            <div class="game-message" v-for="message in gameMessages">
                <p>{{message}}</p>
            </div>
        </div>

        <!--Victory screen-->
        <particle-component v-if="this.game !== null && this.game.victory !== null" v-bind:winner="game.victory" ></particle-component>
        <button id="restart-game" v-if="game !== null && game.victory !== null" @click="restartGame()">Start Game</button>

    </div>

</template>

<script>

    import ParticleComponent from '../ParticleComponent.vue';

    export default {
        data() {
            return {
                isSelectingCharacter: false,
                isDragging: false,
                characters: this.gameOfLaddersConfig.characters,
                selectedCharacter: null,
                opponentCharacter: null,
                game: null,
                animationSpeed: 20,
                dieAnimRequest: undefined,
                diePos: {x: 100, y: window.innerHeight / 2},
                dieLastTime: Date.now(),
                dieRotationY: 45,
                dieRotationX: 45,
                dieSpeed: 500,
                dieRoll: 1,
                gameMessages: [],
            }
        },

        props: {
            id: null,
            position: null,
            lobbyId: null,
            connectedPlayers: null,
            user: null,
        },

        components: {
            ParticleComponent,
        },

        methods: {

            getRandomInt(min, max) {
                min = Math.ceil(min);
                max = Math.floor(max);
                return Math.floor(Math.random() * (max - min + 1)) + min;
            },

            getOpponentUser() {
                return this.connectedPlayers.filter(e => e.name !== this.user.name)[0].name;
            },

            tileClass(id) {
                let tileClass = 'tile';
                if (
                    (id >= 11 && id <= 20) ||
                    (id >= 31 && id <= 40) ||
                    (id >= 51 && id <= 60) ||
                    (id >= 71 && id <= 80) ||
                    (id >= 91 && id <= 100)
                ) {
                    return tileClass += ' oddRow'
                }
                return tileClass;
            },

            createTrap(index) {
                let trapId;

                trapId = this.getRandomInt(index * 10, (index * 10) + 10);

                // If the randomness is at the end or higher than max
                if (trapId === 0 || trapId > 99) {
                    return this.createTrap(index);
                } else {
                    let backwards = this.getRandomInt(10, 30);
                    if (trapId - backwards <= 0) {
                        return this.createTrap(index);
                    }
                    return [trapId, backwards * -1];
                }
            },

            createBoard(size) {
                let board = new Array(size).fill(null).map(()=>new Array(size).fill(null));
                let y = 0;
                let x = 0;

                for (let i = 0; i < size * size; i++) {
                    if (i < 10) {
                        x = i;
                    } else {
                        x = i % 10;
                    }
                    if (i % 10 === 0 && i !== 0) {
                        y++
                    }
                    board[i] = {
                        "id": i+1,
                        "pieces": [],
                        "trap": null,
                        "ladder": null,
                        "position": {
                            x: x,
                            y: y
                        }
                    };
                }

                // Create the traps
                for (let i = 1; i < 10; i++) {
                    let [trapId, trapEnd] = this.createTrap(i);
                    console.log('trap: ', trapId, trapEnd, trapId + trapEnd);
                    let position1 = board[trapId].position;
                    let position2 = board[trapId + trapEnd].position;

                    board[trapId].trap = {
                        start: trapEnd,
                        // Regular old vector math
                        angle: Math.abs(Math.atan2(position2.y - position1.y, position2.x - position1.x) * 180 / Math.PI),
                        distance: Math.sqrt( Math.pow((position1.x - position2.x), 2) + Math.pow((position1.y - position2.y), 2))
                    };
                }

                // Create the ladders
                for (let i = 1; i < 10; i = i + 2) {
                    let ladderId = null;
                    let ladderEnd = null;
                    // Make sure there is no trap on the same spot
                    while (ladderId === null || board[ladderId].trap !== null) {
                        [ladderId, ladderEnd] = this.createTrap(i);
                    }
                    console.log('ladder: ', ladderId, ladderEnd, ladderId + ladderEnd);
                    let position1 = board[ladderId].position;
                    let position2 = board[ladderId + ladderEnd].position;

                    board[ladderId].ladder = {
                        end: ladderEnd,
                        // Regular old vector math
                        angle: Math.abs(Math.atan2(position2.y - position1.y, position2.x - position1.x) * 180 / Math.PI),
                        distance: Math.sqrt( Math.pow((position1.x - position2.x), 2) + Math.pow((position1.y - position2.y), 2))
                    };

                    board[ladderId + ladderEnd].ladder = {
                        start: Math.abs(ladderEnd),
                    };
                }

                return board;
            },

            startGame() {
                let game = {};
                game.board = this.createBoard(10);
                game.player1 = {
                    tile: 0,
                    playerNumber: 1,
                    name: this.selectedCharacter.name,
                    position: game.board[0].position
                };
                game.player2 = {
                    tile: 0,
                    playerNumber: 2,
                    name: this.opponentCharacter,
                    position: game.board[0].position
                };
                game.board[0].pieces.push({name: game.player1.name, id: 1});
                game.board[0].pieces.push({name: game.player2.name, id: 2});
                game.turn = game.player1.name;
                game.victory = null;
                this.game = game;
                window.axios.post(`/game/startGame/${this.lobbyId}`, {
                    game: this.game,
                });
                this.addGameMessage(`The game has started!`);
            },

            restartGame() {
                window.axios.post(`/game/restartGame/${this.lobbyId}`);
                this.game = null;
                this.addGameMessage(`The game has been reset!`);
            },

            playerWon(player) {
                console.log('player: ', player.playerNumber, ' won the game!');
                this.game.victory = player;
            },

            startRoll() {
                this.dieLastTime = Date.now();
            },

            stopRoll() {
                clearInterval(this.rollInterval);
                // This makes the final die throw random regardless of what it looks like it should be
                this.dieRoll = this.getRandomInt(1, 6);
                setTimeout(() => {
                    // Stop the die animation
                    this.stopDieAnimation();
                    // finally land on the final die position
                    switch (this.dieRoll) {
                        case 1:
                            this.dieRotationY = 360;
                            this.dieRotationX = 0;
                            break;
                        case 2:
                            this.dieRotationY = -90;
                            this.dieRotationX = 0;
                            break;
                        case 3:
                            this.dieRotationY = 180;
                            this.dieRotationX = 0;
                            break;
                        case 4:
                            this.dieRotationY = 90;
                            this.dieRotationX = 0;
                            break;
                        case 5:
                            this.dieRotationY = 0;
                            this.dieRotationX = -90;
                            break;
                        case 6:
                            this.dieRotationY = 0;
                            this.dieRotationX = 90;
                            break;
                        default:
                            this.dieRotationY = 360;
                            this.dieRotationX = 0;
                            break;
                    }

                    this.doGameMove(this.dieRoll);
                }, 1000);

                // Start the die animation
                this.startDieAnimation();
            },

            rollDie() {
                // Clear request every frame
                this.dieAnimRequest = undefined;

                this.dieRotationY += this.animationSpeed;
                this.dieRotationX += this.animationSpeed / 2;

                // Request a new frame
                this.startDieAnimation();
            },

            startDieAnimation() {
                if (!this.dieAnimRequest) {
                    this.dieAnimRequest = window.requestAnimationFrame(this.rollDie);
                }
            },

            stopDieAnimation() {
                if (this.dieAnimRequest) {
                    window.cancelAnimationFrame(this.dieAnimRequest);
                    this.dieAnimRequest = undefined;
                }
            },

            sendGameMove(roll){
                window.axios.post(`/game/gameMove/${this.lobbyId}`, {
                    move: roll,
                });
            },

            doGameMove(roll) {
                if (this.game.turn === this.selectedCharacter.name) {
                    this.sendGameMove(roll);
                }

                if (this.game.turn === this.game.player1.name) {
                    if (this.game.player1.tile + roll <= 99) {
                        // Roll is lower than max tile so can move
                        this.movePlayer(this.game.player1, roll);
                        this.checkTrapLadder(this.game.player1);
                        // If roll is 6, don't change turn
                        if (roll !== 6) {
                            this.game.turn = this.game.player2.name;
                        } else {
                            this.addGameMessage(`${this.game.player1.name} rolled a 6! They get another turn.`);
                        }
                    } else {
                        // Rolled too high to win, will stay here until exact roll is met, loses his turn
                        if (roll !== 6) {
                            this.game.turn = this.game.player2.name;
                        } else {
                            this.addGameMessage(`${this.game.player1.name} rolled a 6! They get another turn.`);
                        }
                    }

                } else if (this.game.turn === this.game.player2.name) {
                    if (this.game.player2.tile + roll <= 99) {
                        // Roll is lower than max tile so can move
                        this.movePlayer(this.game.player2, roll);
                        this.checkTrapLadder(this.game.player2);
                        // If roll is 6, don't change turn.
                        if (roll !== 6) {
                            this.game.turn = this.game.player1.name;
                        } else {
                            this.addGameMessage(`${this.game.player2.name} rolled a 6! They get another turn.`);
                        }
                    } else {
                        // Rolled too high to win, will stay here until exact roll is met, loses his turn
                        if (roll !== 6) {
                            this.game.turn = this.game.player1.name;
                        } else {
                            this.addGameMessage(`${this.game.player2.name} rolled a 6! They get another turn.`);
                        }
                    }
                }
            },

            checkTrapLadder(player) {
                // If there is a trap there has to be a delay for the trap to spring
                setTimeout(() => {
                    if (this.checkTrap(player)) {
                        setTimeout(() => {
                            this.checkLadder(player);
                        }, 1100)
                    } else {
                        this.checkLadder(player);
                    }
                },1000);
            },

            movePlayer(player, roll, moveReason) {
                // Messages
                if (roll > 0) {
                    if (moveReason === undefined) {
                        this.addGameMessage(`${player.name} is moving ${roll} spaces forward.`);
                    } else {
                        this.addGameMessage(`${player.name} is moving ${roll} spaces forward because ${moveReason}.`);
                    }
                } else {
                    this.addGameMessage(`Oh no! ${player.name} is going ${Math.abs(roll)} spaces backwards because: ${moveReason}.`);
                }

                // Animate the player
                this.animatePlayer(player, roll);

                // Set all the new data after the animation is done
                setTimeout(() => {
                    // Move player to his new place on the board, remove him from the old tile
                    this.game.board[player.tile].pieces = this.game.board[player.tile].pieces.filter(e => e.name !== player.name);
                    this.game.board[player.tile + roll].pieces.push({name: player.name, id: player.playerNumber});

                    // Add the roll to his current position
                    if (player.playerNumber === 1) {
                        this.game.player1.position = this.game.board[player.tile + roll].position;
                        this.game.player1.tile += roll;
                        if (this.game.player1.tile === 99) {
                            this.playerWon(this.game.player1);
                        }
                    } else {
                        this.game.player2.position = this.game.board[player.tile + roll].position;
                        this.game.player2.tile += roll;
                        if (this.game.player2.tile === 99) {
                            this.playerWon(this.game.player1);
                        }
                    }


                }, 1000);
            },

            animatePlayer(player, roll) {
                let oldPosition = player.position;
                let newPosition = this.game.board[player.tile + roll].position;
                let tileHeight = (window.innerHeight * 0.8) / 10;

                let x = newPosition.x - oldPosition.x;
                let y = newPosition.y - oldPosition.y;

                let element = this.$el.querySelector(`#player-piece-${player.playerNumber}`);
                element.style.transition = "left ease-in-out 1000ms, bottom ease-in-out 1000ms";
                if (y > 0) {
                    element.style.bottom = ((tileHeight * y) + tileHeight * 0.5) + "px";
                } else if (y < 0) {
                    element.style.bottom = ((tileHeight * y) + (tileHeight * 0.5)) + "px";
                }
                element.style.left = ((tileHeight * x) + tileHeight * 0.5) + "px";
                setTimeout(() => {
                    // Fix for the weird way VUE applies this to the wrong player after the player has been moved to another array index
                    // For some reason, even when the element has been selected for with ID, it applies this to the player that should not move
                    // after the board has updated, making them swap places. They still do, this just makes it invisible.
                    element.style.transition = "none";
                    element.style.bottom = "50%";
                    element.style.left = "50%";
                }, 1000)
            },

            checkTrap(player) {
                let trap = this.game.board[player.tile].trap;

                if (trap !== null && trap.start !== undefined) {
                    this.movePlayer(player, trap.start, this.generateTrapMessage(player));
                    return true;
                } else {
                    return false;
                }
            },

            checkLadder(player) {
                let ladder = this.game.board[player.tile].ladder;

                if (ladder !== null && ladder.start !== undefined) {
                    this.movePlayer(player, ladder.start, this.generateLadderMessage(player));
                    return true;
                } else {
                    return false;
                }
            },

            generateTrapMessage(player) {
                let possibleTraps = this.characters.filter(e => e.name !== player.name);
                let antagonist = possibleTraps[this.getRandomInt(0, possibleTraps.length - 1)];
                let trap = antagonist.traps[this.getRandomInt(0, antagonist.traps.length - 1)];
                console.log('trap message: ', `${antagonist.name} ${trap}`);
                return `${antagonist.name} ${trap}`

            },

            generateLadderMessage(player) {
                let possibleLadders = this.characters.filter(e => e.name !== player.name);
                let helper = possibleLadders[this.getRandomInt(0, possibleLadders.length - 1)];
                let ladder = helper.ladders[this.getRandomInt(0, helper.ladders.length - 1)];
                console.log('ladder message: ', `${helper.name} ${ladder}`);
                return `${helper.name} ${ladder}`

            },

            startDrag() {
                if (this.game !== null) {
                    if (this.game.turn === this.selectedCharacter.name) {
                        this.isDragging = true;
                        this.startRoll();
                    }
                }
            },

            stopDrag() {
                if (this.game !== null && this.isDragging) {
                    if (this.game.turn === this.selectedCharacter.name) {
                        this.isDragging = false;
                        this.stopRoll();
                    }
                }
            },

            drag(e) {
                // source: http://jsfiddle.net/ER8qE/22/
                if (this.isDragging) {
                    // Set new mouse position regardless
                    let parent = this.$refs.game;
                    let oldX = this.diePos.x;
                    let oldY = this.diePos.y;
                    this.diePos = {
                        x: e.clientX - 25,
                        y: e.clientY - 25,
                    };
                    // Velocity calculation
                    let distX = this.diePos.x - oldX;
                    let distY = this.diePos.y - oldY;
                    let oldTime = this.dieLastTime;
                    let newTime = Date.now();
                    this.dieLastTime = newTime;
                    let interval = newTime - oldTime;
                    let velocity = Math.sqrt(distX*distX + distY*distY) / interval;

                    this.dieRotationY += velocity * this.animationSpeed;
                    this.dieRotationX += this.getRandomInt(0, 2);

                    if (this.dieRotationY >= 360) {
                        this.dieRotationY = 0;
                    }
                    if (this.dieRotationX >= 360) {
                        this.dieRotationX = 0;
                    }
                }
            },

            toggleSelect() {
                this.isSelectingCharacter = !this.isSelectingCharacter;
            },

            getImageUrl(name) {
                return '/images/characters/' + name.replace(/ /g,"-") + '.jpg';
            },

            selectCharacter(character) {
                if (character.name !== this.opponentCharacter) {
                    this.selectedCharacter = character;
                    if (this.selectedCharacter.father !== "" && this.selectedCharacter.father.name === undefined) {
                        axios.get(this.selectedCharacter.father)
                            .then((response) => {
                                this.selectedCharacter.father = response.data;
                            }).catch((e) => {
                            console.log('error: ', e);
                        });
                    }
                    if (this.selectedCharacter.mother !== "" && this.selectedCharacter.mother.name === undefined) {
                        axios.get(this.selectedCharacter.mother)
                            .then((response) => {
                                this.selectedCharacter.mother = response.data;
                            }).catch((e) => {
                            console.log('error: ', e);
                        });
                    }
                    if (this.selectedCharacter.spouse !== "" && this.selectedCharacter.spouse.name === undefined) {
                        axios.get(this.selectedCharacter.spouse)
                            .then((response) => {
                                this.selectedCharacter.spouse = response.data;
                            }).catch((e) => {
                            console.log('error: ', e);
                        });
                    }
                    window.axios.post(`/lobby/updateCharacter/${this.lobbyId}`, {
                        character: this.selectedCharacter.name,
                    });
                    this.toggleSelect();
                }
            },

            getCharacters() {
                // counter to check when all requests are done
                let count = 0;

                for (let i = 0; i < this.characters.length; i++) {
                    axios.get(`https://www.anapioficeandfire.com/api/characters?name=${this.characters[i].name}`)
                        .then((response) => {
                            count++;
                            this.characters[i].data = response.data[0];
                        }).catch((e) => {
                        console.log(`error: ${this.characters[i]}`, e);
                    });
                }
            },

            addGameMessage(message) {
                this.gameMessages.push(message);
                setTimeout(() => {
                    this.gameMessages = this.gameMessages.filter(e => e !== message);
                }, 10000)
            },

        },

        mounted() {
            console.log('Game Component mounted.');

            window.addEventListener('mouseup', this.stopDrag);
            this.getCharacters();

            Echo.join('game.' + this.lobbyId)
                .listen('ChangeCharacterEvent', (event) => {
                    this.opponentCharacter = event.characterName;
                })
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

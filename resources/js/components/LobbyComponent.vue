<template>
    <div class="lobby-container">
        <!--CHAT-->
        <div class="chat">
            <ul>
                <li v-for="message in messages">{{ message.name }}: {{ message.message }}</li>
            </ul>
            <input @keyup.enter="sendMessage()" v-model="message" type="text">
            <button @click="sendMessage()">Send Message</button>
        </div>

        <div v-if="gameMode === ''" class="select-game">
            <div class="game-mode" v-for="gameMode in gameModes" @click="setGameMode(gameMode)">
                <h1>{{gameMode}}</h1>
            </div>
        </div>

        <games-of-ladders v-if="gameMode === 'Game Of Ladders'" v-bind:lobby-id="lobby.url" v-bind:connected-players="connectedPlayers" v-bind:user="user"></games-of-ladders>
        <chess v-if="gameMode === 'Chess'" v-bind:lobby-id="lobby.url" v-bind:connected-players="connectedPlayers" v-bind:user="user"></chess>


    </div>
</template>

<script>

    import GamesOfLadders from './games/GamesOfLaddersComponent.vue';
    import Chess from './games/ChessComponent.vue';

    export default {
        data() {
            return {
                connectedPlayers: [],
                message: '',
                messages: [],
                gameMode: this.lobby.gameMode,
                gameModes: [
                    'Game Of Ladders',
                    'Chess',
                ]
            }
        },

        props: {
            user: null,
            lobby: null,
        },

        components: {
            GamesOfLadders,
            Chess,
        },

        methods: {

            getUserName(userId) {
                return this.connectedPlayers.find(x => x.id === userId).name;
            },

            setGameMode(gameMode) {
                this.lobby.gameMode = gameMode;
                this.gameMode = gameMode;
                window.axios.post(`/lobby/setGameMode/${this.lobby.url}`, {
                    gameMode: gameMode,
                });
            },

            sendMessage() {
                if (this.message !== '') {
                    window.axios.post(`/lobby/message`, {
                        message: this.message,
                        lobbyId: this.lobby.url,
                    });
                    this.messages.push({
                        message: this.message,
                        name: this.user.name,
                    });
                    this.message = '';
                }

            },

            addUser(user) {
                this.connectedPlayers.push(user);

                // // If there is a game state it means you joined a game in progress
                // if (this.game.game_state !== null) {
                //     let newGameState = this.game.game_state;
                //
                //     // Check if the new player should take over the old player's spot
                //     if (
                //         !(this.game.game_state.turn === this.connectedPlayers[0].id ||
                //             this.game.game_state.turn === this.connectedPlayers[1].id)
                //     ) {
                //         newGameState.turn = user.id;
                //     }
                //
                //     // Assign the new player as player1 or player 2
                //     let otherPlayer = this.connectedPlayers.filter(obj => obj.id !== user.id)[0].id;
                //     if (newGameState.player1 === otherPlayer) {
                //         newGameState.player2 = user.id;
                //     } else {
                //         newGameState.player1 = user.id;
                //     }
                //
                //     // Unpause the game
                //     console.log('Sending unpause after new person joining');
                //     window.axios.post(`/game/pause/${this.id}`, {
                //         pause: JSON.stringify(false),
                //     });
                //}
            },

            removeUser(userId) {
                this.connectedPlayers = this.connectedPlayers.filter(obj => obj.id !== userId);
                // // Pause the game when a player leaves
                // let newGameState = this.game.game_state;
                // newGameState.pauseGame = true;
                // this.updateGameState(newGameState);
            },
        },

        mounted() {
            console.log('Lobby Component mounted.');

            Echo.join('lobby.' + this.lobby.url)
                .here((users) => {
                    this.connectedPlayers = users;
                })
                .joining((user) => {
                    this.addUser(user);
                })
                .leaving((user) => {
                    this.removeUser(user.id);
                })
                .listen('NewMessageEvent', (event) => {
                    console.log(event);
                    this.messages.push(event);
                })
                .listen('ChangeGameModeEvent', (event) => {
                    console.log(event);
                    this.gameMode = event.gameMode;
                })
                .listen('StopGameEvent', (event) => {
                    this.gameMode = '';
                })
        }
    }
</script>

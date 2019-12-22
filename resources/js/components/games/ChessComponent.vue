<template>
    <div class="game">
        <h2>chess</h2>

    </div>

</template>

<script>

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

        methods: {

            startGame() {

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

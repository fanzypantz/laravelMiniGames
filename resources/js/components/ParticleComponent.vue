<template>
    <div class="container">
        <div class="victory-text">
            <h1>Good job {{ winner.name }}, for getting to the end first!</h1>
        </div>

        <div id="particleJS-container">

        </div>
    </div>
</template>

<script>

    export default {

        props: {
            winner: null,
        },

        methods: {
            initParticles() {
                particlesJS('particleJS-container', {
                    "particles": {
                        "number": {
                            "value": 80,
                            "density": {
                                "enable": true,
                                "value_area": 800
                            }
                        },
                        "color": {
                            "value": "#ffffff"
                        },
                        "shape": {
                            "type": "circle",
                            "stroke": {
                                "width": 0,
                                "color": "#000000"
                            },
                            "polygon": {
                                "nb_sides": 10
                            },
                            "image": {
                                "src": "img/github.svg",
                                "width": 100,
                                "height": 100
                            }
                        },
                        "opacity": {
                            "value": 0.5,
                            "random": false,
                            "anim": {
                                "enable": false,
                                "speed": 1,
                                "opacity_min": 0.1,
                                "sync": false
                            }
                        },
                        "size": {
                            "value": 10,
                            "random": true,
                            "anim": {
                                "enable": false,
                                "speed": 80,
                                "size_min": 0.1,
                                "sync": false
                            }
                        },
                        "line_linked": {
                            "enable": true,
                            "distance": 300,
                            "color": "#ffffff",
                            "opacity": 0.4,
                            "width": 2
                        },
                        "move": {
                            "enable": true,
                            "speed": 12,
                            "direction": "none",
                            "random": true,
                            "straight": false,
                            "out_mode": "out",
                            "bounce": true,
                            "attract": {
                                "enable": false,
                                "rotateX": 600,
                                "rotateY": 1200
                            }
                        }
                    },
                    "interactivity": {
                        "detect_on": "canvas",
                        "events": {
                            "onhover": {
                                "enable": true,
                                "mode": "grab"
                            },
                            "onclick": {
                                "enable": true,
                                "mode": "push"
                            },
                            "resize": true
                        },
                        "modes": {
                            "grab": {
                                "distance": 400,
                                "line_linked": {
                                    "opacity": 1
                                }
                            },
                            "bubble": {
                                "distance": 800,
                                "size": 80,
                                "duration": 2,
                                "opacity": 0.8,
                                "speed": 3
                            },
                            "repulse": {
                                "distance": 400,
                                "duration": 0.4
                            },
                            "push": {
                                "particles_nb": 4
                            },
                            "remove": {
                                "particles_nb": 2
                            }
                        }
                    },
                    "retina_detect": true
                });
            },
        },

        mounted() {
            require('particles.js');

            this.initParticles();
        }
    }
</script>

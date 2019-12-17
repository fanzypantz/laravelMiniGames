
let layoutModule = (function () {

    window.addEventListener('resize', function(event){
        if (window.innerWidth > 766) {
            let button = document.querySelector('#menu-button');
            let nav = document.querySelector('#nav');
            button.style.opacity = '0';
            button.style.display = 'none';
            nav.style.opacity = '1';
            nav.style.display = 'flex';
        } else {
            let button = document.querySelector('#menu-button');
            let nav = document.querySelector('#nav');
            button.style.opacity = '1';
            button.style.display = 'block';
            nav.style.opacity = '0';
            nav.style.display = 'none';
        }
    });

    function copyURL() {
        let element = document.createElement('textarea');
        element.value = window.location.href;
        // Make element readonly
        element.setAttribute('readonly', '');
        // Hide the element
        element.style = {
            display: "none",
            position: "absolute",
            top: "-500px",
        };
        document.body.appendChild(element);
        element.select();
        document.execCommand('copy');
        document.body.removeChild(element);
    }

    function toggleMenu () {
        let button = document.querySelector('#menu-button');
        let nav = document.querySelector('#nav');
        if (button.classList.contains('open')) {
            nav.style.opacity = '0';
            setTimeout(() => {
                nav.style.display = 'flex';
                button.classList.remove('open');
            }, 250);
        } else {
            nav.style.display = 'flex';
            nav.style.opacity = '1';
            setTimeout(() => {
                button.classList.add('open');
            }, 250)
        }
    }

    function stopGame() {
        let lobbyId = window.location.pathname.split("/").pop();
        window.axios.post(`/game/stopGame/${lobbyId}`);
        return false;
    }

    return {
        copyURL: copyURL,
        toggleMenu: toggleMenu,
        stopGame: stopGame,
    }
})();

export default layoutModule

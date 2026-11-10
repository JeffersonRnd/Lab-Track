(function () {
    var btn = document.getElementById('navToggle');
    var nav = document.getElementById('nav');
    if (btn && nav) {
        btn.addEventListener('click', function () {
            var abierto = nav.classList.toggle('abierto');
            btn.setAttribute('aria-expanded', abierto ? 'true' : 'false');
        });
    }
})();

/* Home page extras: subtle scroll parallax on the hero stage and float cards. */
(function () {
    "use strict";
    var stage = document.querySelector(".hero-stage");
    var cards = document.querySelectorAll(".float-card");
    if (!stage) return;

    var ticking = false;
    function update() {
        var y = window.scrollY;
        if (y < 900) {
            stage.style.transform = "translateY(" + (y * 0.06) + "px)";
            cards.forEach(function (c, i) {
                c.style.marginTop = (y * (i === 0 ? -0.04 : 0.05)) + "px";
            });
        }
        ticking = false;
    }
    window.addEventListener("scroll", function () {
        if (!ticking) { window.requestAnimationFrame(update); ticking = true; }
    }, { passive: true });
})();

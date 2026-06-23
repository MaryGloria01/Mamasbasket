/* Dashboard helpers: image preview + delete confirmation. */
(function () {
    "use strict";

    // Image preview inside an .img-drop label
    document.querySelectorAll('input[type="file"][data-preview]').forEach(function (input) {
        input.addEventListener("change", function () {
            var target = document.getElementById(input.getAttribute("data-preview"));
            var f = input.files && input.files[0];
            if (!target || !f) return;
            target.innerHTML = "";
            var img = document.createElement("img");
            img.src = URL.createObjectURL(f);
            target.appendChild(img);
        });
    });

    // Confirm destructive actions
    document.querySelectorAll("[data-confirm]").forEach(function (el) {
        el.addEventListener("submit", function (e) {
            if (!window.confirm(el.getAttribute("data-confirm"))) e.preventDefault();
        });
    });
})();

/* Adds a show/hide eye toggle to every password field on the page. */
(function () {
    "use strict";

    var EYE = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.6-6.5 10-6.5S22 12 22 12s-3.6 6.5-10 6.5S2 12 2 12Z"/><circle cx="12" cy="12" r="2.6"/></svg>';
    var EYE_OFF = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9.6 5.4A9.4 9.4 0 0 1 12 5.5c6.4 0 10 6.5 10 6.5a17 17 0 0 1-3.2 3.9M6.2 6.3A17 17 0 0 0 2 12s3.6 6.5 10 6.5a9.4 9.4 0 0 0 3.6-.7"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/><path d="M3 3l18 18"/></svg>';

    function init() {
        var inputs = document.querySelectorAll('input[type="password"]');
        Array.prototype.forEach.call(inputs, function (input) {
            if (input.getAttribute("data-pw-toggle")) return;
            input.setAttribute("data-pw-toggle", "1");

            var wrap = document.createElement("div");
            wrap.className = "pw-wrap";
            input.parentNode.insertBefore(wrap, input);
            wrap.appendChild(input);
            input.classList.add("has-pw-toggle");

            var btn = document.createElement("button");
            btn.type = "button";
            btn.className = "pw-toggle";
            btn.setAttribute("aria-label", "Show password");
            btn.setAttribute("tabindex", "-1");
            btn.innerHTML = EYE;
            wrap.appendChild(btn);

            btn.addEventListener("click", function () {
                var reveal = input.type === "password";
                input.type = reveal ? "text" : "password";
                btn.innerHTML = reveal ? EYE_OFF : EYE;
                btn.setAttribute("aria-label", reveal ? "Hide password" : "Show password");
                input.focus();
            });
        });
    }

    if (document.readyState !== "loading") init();
    else document.addEventListener("DOMContentLoaded", init);
})();

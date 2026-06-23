/* Mama's Basket - global UI behaviour (no localStorage anywhere) */
(function () {
    "use strict";

    /* ---- Sticky nav state ---- */
    var nav = document.getElementById("nav");
    function onScroll() {
        if (!nav) return;
        nav.classList.toggle("scrolled", window.scrollY > 12);
    }
    window.addEventListener("scroll", onScroll, { passive: true });
    onScroll();

    /* ---- Mobile menu ---- */
    var toggle = document.getElementById("navToggle");
    var links = document.getElementById("navLinks");
    if (toggle && links) {
        toggle.addEventListener("click", function () {
            links.classList.toggle("open");
        });
        links.querySelectorAll("a").forEach(function (a) {
            a.addEventListener("click", function () { links.classList.remove("open"); });
        });
    }

    /* ---- Scroll reveal (graceful, no dependency required) ---- */
    var reveals = document.querySelectorAll(".reveal");
    if ("IntersectionObserver" in window && reveals.length) {
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (en) {
                if (en.isIntersecting) {
                    var el = en.target;
                    var d = el.getAttribute("data-delay");
                    if (d) el.style.transitionDelay = d + "ms";
                    el.classList.add("in");
                    io.unobserve(el);
                }
            });
        }, { threshold: 0.14 });
        reveals.forEach(function (el) { io.observe(el); });
    } else {
        reveals.forEach(function (el) { el.classList.add("in"); });
    }

    /* ---- Animated number counters ---- */
    var counters = document.querySelectorAll("[data-count]");
    if ("IntersectionObserver" in window && counters.length) {
        var co = new IntersectionObserver(function (entries) {
            entries.forEach(function (en) {
                if (!en.isIntersecting) return;
                var el = en.target;
                var target = parseFloat(el.getAttribute("data-count"));
                var suffix = el.getAttribute("data-suffix") || "";
                var dec = (target % 1 !== 0) ? 1 : 0;
                var start = null, dur = 1400;
                function step(ts) {
                    if (!start) start = ts;
                    var p = Math.min((ts - start) / dur, 1);
                    var eased = 1 - Math.pow(1 - p, 3);
                    el.textContent = (target * eased).toFixed(dec).replace(/\B(?=(\d{3})+(?!\d))/g, ",") + suffix;
                    if (p < 1) requestAnimationFrame(step);
                }
                requestAnimationFrame(step);
                co.unobserve(el);
            });
        }, { threshold: 0.5 });
        counters.forEach(function (el) { co.observe(el); });
    }

    /* ---- Copy to clipboard (MoMo code, etc.) ---- */
    document.addEventListener("click", function (e) {
        var btn = e.target.closest("[data-copy]");
        if (!btn) return;
        var text = btn.getAttribute("data-copy");
        var done = function () {
            btn.classList.add("copied");
            var lbl = btn.querySelector(".copy-label");
            if (lbl) { var old = lbl.textContent; lbl.textContent = "Copied"; setTimeout(function () { lbl.textContent = old; btn.classList.remove("copied"); }, 1600); }
        };
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(done).catch(fallbackCopy);
        } else { fallbackCopy(); }
        function fallbackCopy() {
            var t = document.createElement("textarea");
            t.value = text; t.style.position = "fixed"; t.style.opacity = "0";
            document.body.appendChild(t); t.select();
            try { document.execCommand("copy"); done(); } catch (err) {}
            document.body.removeChild(t);
        }
    });

    /* ---- Add to cart (server session via fetch) ---- */
    document.addEventListener("click", function (e) {
        var btn = e.target.closest("[data-add]");
        if (!btn) return;
        e.preventDefault();
        var id = btn.getAttribute("data-add");
        btn.disabled = true;
        var body = new URLSearchParams();
        body.set("id", id);
        body.set("qty", "1");
        body.set("action", "add");
        fetch("/api/cart.php", { method: "POST", body: body, headers: { "X-Requested-With": "fetch" } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data && typeof data.count !== "undefined") {
                    var badge = document.getElementById("cartBadge");
                    if (badge) badge.textContent = data.count;
                    flashAdded(btn);
                }
            })
            .catch(function () {})
            .finally(function () { btn.disabled = false; });
    });

    function flashAdded(btn) {
        btn.classList.add("added");
        setTimeout(function () { btn.classList.remove("added"); }, 900);
    }
})();

/* Mama's Basket - 3D animated hero (Three.js r128, global THREE build).
   Renders a stylised basket of fresh produce that gently floats and rotates,
   with pointer parallax. Falls back gracefully when WebGL or motion is off. */
(function () {
    "use strict";

    var canvas = document.getElementById("heroCanvas");
    var fallback = document.getElementById("heroFallback");
    if (!canvas) return;

    var reduceMotion = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;

    function showFallback() {
        if (canvas) canvas.style.display = "none";
        if (fallback) fallback.style.display = "grid";
    }

    // Quick WebGL capability check
    try {
        var test = document.createElement("canvas");
        if (!(test.getContext("webgl") || test.getContext("experimental-webgl"))) { showFallback(); return; }
    } catch (e) { showFallback(); return; }

    var THREE_URL = "https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js";

    function load(src, cb) {
        var s = document.createElement("script");
        s.src = src; s.onload = cb; s.onerror = showFallback;
        document.head.appendChild(s);
    }

    if (window.THREE) { init(); } else { load(THREE_URL, init); }

    function init() {
        if (!window.THREE) { showFallback(); return; }
        var THREE = window.THREE;

        var renderer = new THREE.WebGLRenderer({ canvas: canvas, antialias: true, alpha: true });
        renderer.setClearColor(0x000000, 0);
        renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));

        var scene = new THREE.Scene();
        var camera = new THREE.PerspectiveCamera(40, 1, 0.1, 100);
        camera.position.set(0, 1.1, 8.4);
        camera.lookAt(0, 0.4, 0);

        // Lighting (warm, soft, studio-like)
        scene.add(new THREE.HemisphereLight(0xffffff, 0x88a06a, 0.95));
        var key = new THREE.DirectionalLight(0xffffff, 1.05);
        key.position.set(4, 7, 5);
        scene.add(key);
        var warm = new THREE.PointLight(0xffcf8a, 0.6, 30);
        warm.position.set(-5, 2, 4);
        scene.add(warm);

        var root = new THREE.Group();
        scene.add(root);

        function mat(color, rough, metal, emissive) {
            return new THREE.MeshStandardMaterial({
                color: color, roughness: rough == null ? 0.5 : rough,
                metalness: metal == null ? 0.05 : metal,
                emissive: emissive || 0x000000, emissiveIntensity: emissive ? 0.18 : 0
            });
        }

        /* ---- Basket ---- */
        var basket = new THREE.Group();
        var greenMat = mat(0x2e7d32, 0.6, 0.08);
        var goldMat = mat(0xe8a33d, 0.45, 0.2);

        var body = new THREE.Mesh(new THREE.CylinderGeometry(1.65, 1.25, 1.5, 40, 1, true), greenMat);
        body.material.side = THREE.DoubleSide;
        body.position.y = -0.2;
        basket.add(body);

        var base = new THREE.Mesh(new THREE.CylinderGeometry(1.27, 1.27, 0.18, 40), greenMat);
        base.position.y = -0.95;
        basket.add(base);

        var rim = new THREE.Mesh(new THREE.TorusGeometry(1.66, 0.13, 16, 48), goldMat);
        rim.rotation.x = Math.PI / 2;
        rim.position.y = 0.55;
        basket.add(rim);

        // Vertical weave ribs
        var ribMat = mat(0x256b29, 0.6, 0.06);
        for (var r = 0; r < 18; r++) {
            var a = (r / 18) * Math.PI * 2;
            var rib = new THREE.Mesh(new THREE.BoxGeometry(0.08, 1.5, 0.12), ribMat);
            rib.position.set(Math.cos(a) * 1.45, -0.2, Math.sin(a) * 1.45);
            rib.lookAt(0, -0.2, 0);
            basket.add(rib);
        }
        root.add(basket);

        /* ---- Produce ---- */
        var produce = [];
        function addSphere(color, rad, x, y, z, emissive) {
            var m = new THREE.Mesh(new THREE.SphereGeometry(rad, 26, 22), mat(color, 0.4, 0.04, emissive));
            m.position.set(x, y, z);
            root.add(m);
            produce.push({ mesh: m, baseY: y, speed: 0.6 + Math.random() * 0.8, phase: Math.random() * Math.PI * 2, spin: (Math.random() - 0.5) * 0.01 });
            return m;
        }
        function addCone(color, rad, h, x, y, z) {
            var m = new THREE.Mesh(new THREE.ConeGeometry(rad, h, 22), mat(color, 0.45));
            m.position.set(x, y, z);
            m.rotation.z = Math.PI;
            root.add(m);
            produce.push({ mesh: m, baseY: y, speed: 0.6 + Math.random() * 0.8, phase: Math.random() * Math.PI * 2, spin: (Math.random() - 0.5) * 0.012 });
            return m;
        }

        // Inside basket
        addSphere(0xe23b2e, 0.55, -0.55, 0.7, 0.2);          // apple
        addSphere(0xf47b20, 0.55, 0.55, 0.65, -0.1);         // orange
        addSphere(0xd11f1f, 0.42, 0.1, 0.85, 0.55);          // tomato
        addSphere(0x7cb342, 0.4, -0.2, 0.8, -0.55);          // lime
        addCone(0xf08a24, 0.32, 0.9, 0.9, 0.7, 0.5);         // carrot
        var apple = addSphere(0x9b3fb0, 0.46, -0.95, 0.65, -0.3); // eggplant-ish

        // Floating above
        addSphere(0xe8a33d, 0.34, 1.9, 1.7, 0.3, 0xe8a33d);  // floating gold
        addSphere(0x66bb3a, 0.3, -1.9, 2.0, -0.4, 0x66bb3a); // floating green
        addSphere(0xe23b2e, 0.26, 0.2, 2.4, 0.8);            // floating cherry

        // Little leaves (flat cones)
        function leaf(x, y, z, rot) {
            var l = new THREE.Mesh(new THREE.ConeGeometry(0.18, 0.5, 4), mat(0x3c9142, 0.5));
            l.position.set(x, y, z); l.rotation.set(rot, 0.4, 0.3);
            l.scale.set(1, 1, 0.35);
            root.add(l);
            produce.push({ mesh: l, baseY: y, speed: 0.9, phase: Math.random() * 6, spin: 0.004 });
        }
        leaf(0.0, 1.15, 0.0, -0.4);
        leaf(-0.6, 0.95, 0.4, -0.2);

        root.position.y = -0.2;

        /* ---- Interaction & loop ---- */
        var pointer = { x: 0, y: 0 }, target = { x: 0, y: 0 };
        var stage = canvas.parentElement;
        stage.addEventListener("pointermove", function (ev) {
            var rect = stage.getBoundingClientRect();
            target.x = ((ev.clientX - rect.left) / rect.width - 0.5) * 0.6;
            target.y = ((ev.clientY - rect.top) / rect.height - 0.5) * 0.4;
        });
        stage.addEventListener("pointerleave", function () { target.x = 0; target.y = 0; });

        function resize() {
            var w = canvas.clientWidth || stage.clientWidth;
            var h = canvas.clientHeight || stage.clientHeight;
            if (!w || !h) return;
            renderer.setSize(w, h, false);
            camera.aspect = w / h;
            camera.updateProjectionMatrix();
        }
        window.addEventListener("resize", resize);
        resize();

        var t0 = performance.now();
        function frame(now) {
            var t = (now - t0) / 1000;
            pointer.x += (target.x - pointer.x) * 0.05;
            pointer.y += (target.y - pointer.y) * 0.05;

            root.rotation.y = t * 0.22 + pointer.x;
            root.rotation.x = pointer.y * 0.5;

            for (var i = 0; i < produce.length; i++) {
                var p = produce[i];
                p.mesh.position.y = p.baseY + Math.sin(t * p.speed + p.phase) * 0.12;
                p.mesh.rotation.y += p.spin;
            }
            renderer.render(scene, camera);
            if (!reduceMotion) requestAnimationFrame(frame);
        }

        if (reduceMotion) { resize(); renderer.render(scene, camera); }
        else requestAnimationFrame(frame);

        // Fade canvas in
        canvas.style.opacity = "0";
        canvas.style.transition = "opacity .8s ease";
        requestAnimationFrame(function () { canvas.style.opacity = "1"; });
    }
})();

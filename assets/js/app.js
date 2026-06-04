/* =========================================================
   Portfolio — David Burgos
   Vanilla JS re-implementation of the Magic UI components:
     1. AnimatedThemeToggler
     2. MorphingText
     3. ScrollBasedVelocity
     4. DiaTextReveal
   The RainbowButton is pure CSS.
   ========================================================= */

(() => {
    'use strict';

    /* Single source of truth for the low-power flag.
       The class is set in <head> before any of this runs. */
    const isLowPowerDevice = () =>
        document.documentElement.classList.contains('low-power');

    /* ---------------------------------------------------------
       1. AnimatedThemeToggler
       --------------------------------------------------------- */
    function initThemeToggler() {
        const btn = document.getElementById('theme-toggler');
        if (!btn) return;

        const apply = (dark) => {
            document.documentElement.classList.toggle('dark', dark);
            localStorage.setItem('theme', dark ? 'dark' : 'light');
        };

        btn.addEventListener('click', () => {
            const isDark = document.documentElement.classList.contains('dark');

            // Use View Transitions API if supported for a smooth swap
            if (document.startViewTransition) {
                document.startViewTransition(() => apply(!isDark));
            } else {
                apply(!isDark);
            }
        });
    }

    /* ---------------------------------------------------------
       2. MorphingText
       Two stacked spans cross-fade with a blur+threshold filter,
       mimicking magicui's morphing-text.
       --------------------------------------------------------- */
    function initMorphingText() {
        // Low-power devices (incl. all phones) use a pure-CSS cross-fade
        // between the two pre-filled name spans — no rAF loop, no SVG filter.
        if (isLowPowerDevice()) return;
        document.querySelectorAll('.morphing-text').forEach((root) => {
            let texts;
            try {
                texts = JSON.parse(root.dataset.texts || '[]');
            } catch {
                texts = [];
            }
            if (!texts.length) return;

            const morph1 = root.querySelector('.morph-1');
            const morph2 = root.querySelector('.morph-2');
            if (!morph1 || !morph2) return;

            const morphTime    = 1.2;   // seconds for transition
            const cooldownTime = 1.4;   // seconds visible

            let textIndex   = texts.length - 1;
            let time        = new Date();
            let morph       = 0;
            let cooldown    = cooldownTime;

            morph1.textContent = texts[textIndex % texts.length];
            morph2.textContent = texts[(textIndex + 1) % texts.length];

            // Low-power devices choke on the per-frame blur + SVG threshold
            // filter. Detected once at boot via the html.low-power flag.
            const isLowPower = isLowPowerDevice();

            const setMorph = (fraction) => {
                if (isLowPower) {
                    // Cheap cross-fade only — no blur, no SVG filter math
                    morph2.style.opacity = String(fraction);
                    morph1.style.opacity = String(1 - fraction);
                    return;
                }
                // Desktop: gooey blob effect via per-frame blur
                morph2.style.filter = `blur(${Math.min(8 / fraction - 8, 100)}px)`;
                morph2.style.opacity = `${Math.pow(fraction, 0.4) * 100}%`;

                const inv = 1 - fraction;
                morph1.style.filter = `blur(${Math.min(8 / inv - 8, 100)}px)`;
                morph1.style.opacity = `${Math.pow(inv, 0.4) * 100}%`;
            };

            const doCooldown = () => {
                morph = 0;
                morph2.style.filter = '';
                morph2.style.opacity = '100%';
                morph1.style.filter = '';
                morph1.style.opacity = '0%';
            };

            const doMorph = () => {
                morph -= cooldown;
                cooldown = 0;
                let fraction = morph / morphTime;
                if (fraction > 1) {
                    cooldown = cooldownTime;
                    fraction = 1;
                }
                setMorph(fraction);
            };

            // Pause the rAF loop when the hero is off-screen — saves
            // ~60 frames/sec of timer + DOM work while the user is reading
            // the rest of the page.
            let visible = true;
            if ('IntersectionObserver' in window) {
                const io = new IntersectionObserver((entries) => {
                    entries.forEach((e) => { visible = e.isIntersecting; });
                }, { rootMargin: '50px' });
                io.observe(root);
            }

            const tick = () => {
                requestAnimationFrame(tick);
                if (!visible) {
                    time = new Date(); // reset clock so dt doesn't spike on re-entry
                    return;
                }
                const now = new Date();
                const dt = (now - time) / 1000;
                time = now;
                cooldown -= dt;
                if (cooldown <= 0) {
                    if (morph === 0) {
                        // start a new morph cycle
                        textIndex = (textIndex + 1) % texts.length;
                        morph1.textContent = texts[textIndex % texts.length];
                        morph2.textContent = texts[(textIndex + 1) % texts.length];
                    }
                    morph += dt;
                    doMorph();
                } else {
                    doCooldown();
                }
            };

            tick();
        });
    }

    /* ---------------------------------------------------------
       3. ScrollBasedVelocity
       Two infinitely-scrolling rows whose speed responds to
       the user's scroll velocity, like magicui scroll-based-velocity.
       --------------------------------------------------------- */
    function initScrollVelocity() {
        // The skills marquee is now a pure-CSS animation for ALL devices —
        // constant speed, GPU-composited, zero per-frame JS. The old
        // scroll-reactive version ran 3 rAF loops and lagged low-resource
        // desktops, so it's retired. See .scroll-velocity-* in style.css.
    }

    /* ---------------------------------------------------------
       4. DiaTextReveal
       Splits text into letters, reveals on intersection with a
       brief tint from a palette of colors, like dia-text-reveal.
       --------------------------------------------------------- */
    function initDiaTextReveal() {
        const nodes = document.querySelectorAll('.dia-text-reveal');
        if (!nodes.length) return;

        nodes.forEach((node) => {
            const text = node.dataset.text || node.textContent || '';
            let colors = ['#22d3ee', '#818cf8', '#f472b6', '#34d399'];
            try {
                const parsed = JSON.parse(node.dataset.colors || '');
                if (Array.isArray(parsed) && parsed.length) colors = parsed;
            } catch {}

            node.textContent = '';
            const words = text.split(/\s+/);
            let letterIndex = 0;

            words.forEach((word) => {
                const wordEl = document.createElement('span');
                wordEl.className = 'dia-word';
                for (const ch of word) {
                    const letter = document.createElement('span');
                    letter.className = 'dia-letter';
                    letter.textContent = ch;
                    letter.style.setProperty('--letter-color', colors[letterIndex % colors.length]);
                    letter.style.transitionDelay =
                        `${letterIndex * 35}ms, ${letterIndex * 35}ms, ${letterIndex * 35 + 200}ms`;
                    wordEl.appendChild(letter);
                    letterIndex++;
                }
                node.appendChild(wordEl);
            });
        });

        const io = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    io.unobserve(entry.target);
                }
            });
        }, { threshold: 0.35 });

        nodes.forEach((n) => io.observe(n));
    }

    /* ---------------------------------------------------------
       5. IconCloud
       Canvas-based rotating sphere of brand icons. Points are
       distributed via a Fibonacci spiral on the unit sphere, then
       projected to 2D each frame. Back icons fade/shrink for depth.
       --------------------------------------------------------- */
    function initIconCloud() {
        document.querySelectorAll('.icon-cloud-canvas').forEach((canvas) => {
            let slugs;
            try {
                slugs = JSON.parse(canvas.dataset.slugs || '[]');
            } catch {
                slugs = [];
            }
            if (!slugs.length) return;

            const ctx = canvas.getContext('2d');
            // Lower DPR on low-power devices halves the per-frame paint cost
            // of the rotating icon sphere with no visible quality loss.
            const DPR = Math.min(window.devicePixelRatio || 1, isLowPowerDevice() ? 1 : 2);

            const resize = () => {
                const rect = canvas.getBoundingClientRect();
                canvas.width  = Math.max(1, Math.floor(rect.width  * DPR));
                canvas.height = Math.max(1, Math.floor(rect.height * DPR));
                ctx.setTransform(DPR, 0, 0, DPR, 0, 0);
            };
            resize();
            window.addEventListener('resize', resize);

            // Icon slots. Images are loaded LAZILY (see loadIcons() below) only
            // when the cloud nears the viewport — saves N network requests on
            // the initial page load. Site is dark-only, so we fetch just the
            // white variants (half the requests vs. brand + white).
            const icons = slugs.map(() => ({ img: null }));

            let imagesRequested = false;
            const loadIcons = () => {
                if (imagesRequested) return;
                imagesRequested = true;
                slugs.forEach((slug, idx) => {
                    const img = new Image();
                    img.decoding = 'async';
                    img.src = `https://cdn.simpleicons.org/${slug}/ffffff`;
                    img.addEventListener('load',  start, { once: true });
                    img.addEventListener('error', start, { once: true });
                    icons[idx].img = img;
                });
            };

            // Fibonacci sphere distribution
            const N = icons.length;
            const goldenAngle = Math.PI * (3 - Math.sqrt(5));
            const points = [];
            for (let i = 0; i < N; i++) {
                const y = 1 - (i / Math.max(N - 1, 1)) * 2;
                const r = Math.sqrt(Math.max(0, 1 - y * y));
                const theta = goldenAngle * i;
                points.push({
                    x: Math.cos(theta) * r,
                    y: y,
                    z: Math.sin(theta) * r,
                });
            }

            // Rotation state
            let rotX = -0.2;
            let rotY = 0;
            let velX = 0;
            let velY = 0.006;          // gentle autospin
            let dragging = false;
            let lastX = 0, lastY = 0;
            let interactedAt = 0;

            const onDown = (e) => {
                dragging = true;
                lastX = e.clientX;
                lastY = e.clientY;
                velX = velY = 0;
                interactedAt = performance.now();
                canvas.setPointerCapture?.(e.pointerId);
            };
            const onMove = (e) => {
                if (!dragging) return;
                const dx = e.clientX - lastX;
                const dy = e.clientY - lastY;
                rotY += dx * 0.008;
                rotX += dy * 0.008;
                velX = dy * 0.005;
                velY = dx * 0.005;
                lastX = e.clientX;
                lastY = e.clientY;
            };
            const onUp = (e) => {
                dragging = false;
                interactedAt = performance.now();
                canvas.releasePointerCapture?.(e.pointerId);
            };
            canvas.addEventListener('pointerdown', onDown);
            canvas.addEventListener('pointermove', onMove);
            canvas.addEventListener('pointerup', onUp);
            canvas.addEventListener('pointercancel', onUp);
            canvas.addEventListener('pointerleave', () => { dragging = false; });

            // Pause the projection/draw loop when off-screen, AND lazy-load the
            // icon images the first time the cloud approaches the viewport.
            let cloudVisible = true;
            if ('IntersectionObserver' in window) {
                cloudVisible = false; // stays paused until first intersection
                const io = new IntersectionObserver((entries) => {
                    entries.forEach((e) => {
                        cloudVisible = e.isIntersecting;
                        if (e.isIntersecting) loadIcons();
                    });
                }, { rootMargin: '300px' });
                io.observe(canvas);
            } else {
                loadIcons(); // no IO support → load immediately
            }

            // Cap the icon sphere to ~30fps — it spins slowly, so the visual
            // difference vs 60fps is imperceptible but it halves CPU/paint cost
            // (helps low-resource desktops noticeably).
            const ICON_MIN_FRAME_MS = 33;
            let iconLastFrame = 0;
            const render = (ts) => {
                requestAnimationFrame(render);
                if (!cloudVisible) return;
                if (ts - iconLastFrame < ICON_MIN_FRAME_MS) return;
                iconLastFrame = ts;

                const w = canvas.clientWidth;
                const h = canvas.clientHeight;
                const radius = Math.min(w, h) * 0.42;
                const cx = w / 2;
                const cy = h / 2;

                // Physics: decay momentum, then settle into gentle autospin
                if (!dragging) {
                    velX *= 0.96;
                    velY *= 0.96;
                    // ease back to baseline horizontal spin after 1.5s idle
                    const idle = performance.now() - interactedAt > 1500;
                    if (idle) {
                        velY += (0.006 - velY) * 0.02;
                        velX += (0       - velX) * 0.05;
                    }
                }
                rotY += velY;
                rotX += velX;
                // Clamp vertical rotation
                rotX = Math.max(-Math.PI * 0.45, Math.min(Math.PI * 0.45, rotX));

                ctx.clearRect(0, 0, w, h);

                const cosX = Math.cos(rotX), sinX = Math.sin(rotX);
                const cosY = Math.cos(rotY), sinY = Math.sin(rotY);

                // Project every point with current rotation
                const projected = new Array(points.length);
                for (let i = 0; i < points.length; i++) {
                    const p = points[i];
                    // Rotate around Y axis
                    let x = p.x * cosY + p.z * sinY;
                    let z = -p.x * sinY + p.z * cosY;
                    let y = p.y;
                    // Then rotate around X axis
                    const y2 = y * cosX - z * sinX;
                    const z2 = y * sinX + z * cosX;
                    projected[i] = {
                        sx: cx + x * radius,
                        sy: cy + y2 * radius,
                        sz: z2,
                        i: i,
                    };
                }

                // Painter's algorithm: draw back to front
                projected.sort((a, b) => a.sz - b.sz);

                const baseSize = Math.max(24, Math.min(w, h) * 0.09);

                for (let k = 0; k < projected.length; k++) {
                    const p = projected[k];
                    const img = icons[p.i].img;
                    if (!img || !img.complete || img.naturalWidth === 0) continue;

                    const depth = (p.sz + 1) * 0.5;       // 0 (back) .. 1 (front)
                    const size = baseSize * (0.55 + depth * 0.55);
                    const alpha = 0.25 + depth * 0.75;

                    ctx.globalAlpha = alpha;
                    ctx.drawImage(img, p.sx - size / 2, p.sy - size / 2, size, size);
                }
                ctx.globalAlpha = 1;
                // (rAF chain happens at the top of render now)
            };

            // Kick off the render loop once the first icon has data. `start`
            // is referenced by loadIcons() (declared above) via hoisting of
            // this function declaration.
            let started = false;
            function start() {
                if (started) return;
                started = true;
                requestAnimationFrame(render);
            }
        });
    }

    /* ---------------------------------------------------------
       6. Glass Buttons & Cards — cursor tracking
       Updates CSS vars --mx/--my so the radial light sweep
       follows the pointer.
       --------------------------------------------------------- */
    function initGlassCursorTracking() {
        const targets = document.querySelectorAll('.glass-button, .project-card');
        targets.forEach((el) => {
            el.addEventListener('pointermove', (e) => {
                const rect = el.getBoundingClientRect();
                el.style.setProperty('--mx', `${e.clientX - rect.left}px`);
                el.style.setProperty('--my', `${e.clientY - rect.top}px`);
            });
        });
    }

    /* ---------------------------------------------------------
       7. Copy-to-clipboard buttons
       Any button with [data-copy="..."] copies its value and
       shows a brief "Copiado!" confirmation.
       --------------------------------------------------------- */
    function initCopyButtons() {
        document.querySelectorAll('[data-copy]').forEach((btn) => {
            btn.addEventListener('click', async () => {
                const value = btn.dataset.copy;
                if (!value) return;
                try {
                    await navigator.clipboard.writeText(value);
                } catch {
                    // Fallback for older browsers / non-secure contexts
                    const ta = document.createElement('textarea');
                    ta.value = value;
                    ta.style.position = 'fixed';
                    ta.style.opacity = '0';
                    document.body.appendChild(ta);
                    ta.select();
                    try { document.execCommand('copy'); } catch {}
                    document.body.removeChild(ta);
                }

                const label = btn.querySelector('.copy-label');
                const original = label ? label.textContent : null;
                if (label) label.textContent = 'Copiado!';
                btn.classList.add('copy-success');
                setTimeout(() => {
                    btn.classList.remove('copy-success');
                    if (label && original) label.textContent = original;
                }, 1400);
            });
        });
    }

    /* ---------------------------------------------------------
       8. Projects Horizontal Scroller
       - Drag to scroll with mouse / pointer
       - Prev/next nav buttons
       - Progress dots stay in sync with current centered card
       --------------------------------------------------------- */
    function initProjectsScroller() {
        const scroller = document.querySelector('[data-projects-scroller]');
        if (!scroller) return;

        const cards = scroller.querySelectorAll('.project-card');
        const navButtons = document.querySelectorAll('[data-scroll-dir]');
        const dotsContainer = document.querySelector('[data-projects-dots]');
        const dots = dotsContainer ? dotsContainer.querySelectorAll('.project-dot') : [];

        if (!cards.length) return;

        // ---- helpers ------------------------------------------------
        const cardCenter = (card) => {
            const rect = card.getBoundingClientRect();
            const parentRect = scroller.getBoundingClientRect();
            return rect.left - parentRect.left + scroller.scrollLeft + rect.width / 2;
        };
        const scrollToIndex = (idx) => {
            const card = cards[Math.max(0, Math.min(cards.length - 1, idx))];
            if (!card) return;
            const target = cardCenter(card) - scroller.clientWidth / 2;
            scroller.scrollTo({ left: target, behavior: 'smooth' });
        };
        const currentIndex = () => {
            const center = scroller.scrollLeft + scroller.clientWidth / 2;
            let best = 0;
            let bestDist = Infinity;
            cards.forEach((c, i) => {
                const dist = Math.abs(cardCenter(c) - center);
                if (dist < bestDist) { bestDist = dist; best = i; }
            });
            return best;
        };
        const updateDots = () => {
            const idx = currentIndex();
            dots.forEach((d, i) => d.classList.toggle('is-active', i === idx));
        };

        // ---- nav buttons --------------------------------------------
        navButtons.forEach((btn) => {
            btn.addEventListener('click', () => {
                const dir = btn.dataset.scrollDir === 'next' ? 1 : -1;
                scrollToIndex(currentIndex() + dir);
            });
        });

        // ---- dots ---------------------------------------------------
        dots.forEach((dot) => {
            dot.addEventListener('click', () => {
                const idx = parseInt(dot.dataset.scrollTo || '0', 10);
                scrollToIndex(idx);
            });
        });

        // ---- drag to scroll (pointer) -------------------------------
        let isDown = false;
        let startX = 0;
        let startScroll = 0;
        let moved = false;

        scroller.addEventListener('pointerdown', (e) => {
            // Don't hijack clicks on interactive children
            if (e.target.closest('a, button')) return;
            isDown = true;
            moved = false;
            startX = e.clientX;
            startScroll = scroller.scrollLeft;
            scroller.classList.add('is-dragging');
            scroller.setPointerCapture?.(e.pointerId);
        });
        scroller.addEventListener('pointermove', (e) => {
            if (!isDown) return;
            const dx = e.clientX - startX;
            if (Math.abs(dx) > 4) moved = true;
            scroller.scrollLeft = startScroll - dx;
        });
        const endDrag = (e) => {
            if (!isDown) return;
            isDown = false;
            scroller.classList.remove('is-dragging');
            scroller.releasePointerCapture?.(e.pointerId);
            // Snap to nearest after drag
            if (moved) scrollToIndex(currentIndex());
        };
        scroller.addEventListener('pointerup', endDrag);
        scroller.addEventListener('pointercancel', endDrag);
        scroller.addEventListener('pointerleave', endDrag);

        // ---- update dots on scroll (debounced via rAF) --------------
        let rafPending = false;
        scroller.addEventListener('scroll', () => {
            if (rafPending) return;
            rafPending = true;
            requestAnimationFrame(() => {
                updateDots();
                rafPending = false;
            });
        });

        // ---- keyboard arrows when scroller has focus ----------------
        scroller.setAttribute('tabindex', '0');
        scroller.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowRight') { e.preventDefault(); scrollToIndex(currentIndex() + 1); }
            if (e.key === 'ArrowLeft')  { e.preventDefault(); scrollToIndex(currentIndex() - 1); }
        });

        updateDots();
    }

    /* ---------------------------------------------------------
       9. Site header — transparent over the dark hero,
       glass when scrolled past the hero.
       --------------------------------------------------------- */
    function initHeaderScroll() {
        const header = document.getElementById('site-header');
        const hero   = document.getElementById('hero');
        if (!header || !hero) return;

        const update = () => {
            // Switch to "scrolled" once the hero is mostly out of view
            const heroBottom = hero.getBoundingClientRect().bottom;
            header.classList.toggle('is-scrolled', heroBottom < 80);
        };

        update();
        window.addEventListener('scroll', update, { passive: true });
        window.addEventListener('resize', update);
    }

    /* ---------------------------------------------------------
       10. Nav indicator — pill that slides between the active
       section's link in the header. Active section is detected
       via scroll position.
       --------------------------------------------------------- */
    function initNavIndicator() {
        const nav = document.getElementById('header-nav');
        if (!nav) return;

        const indicator = nav.querySelector('.header-nav-indicator');
        const links = Array.from(nav.querySelectorAll('a[data-section]'));
        if (!indicator || !links.length) return;

        const sectionOf = (link) => document.getElementById(link.dataset.section);

        const placeIndicator = (link) => {
            if (!link) {
                indicator.style.width = '0px';
                return;
            }
            const linkRect = link.getBoundingClientRect();
            const navRect  = nav.getBoundingClientRect();
            indicator.style.transform = `translateX(${linkRect.left - navRect.left}px)`;
            indicator.style.width = `${linkRect.width}px`;
        };

        const setActive = (link) => {
            links.forEach((l) => l.classList.toggle('is-active', l === link));
            placeIndicator(link);
        };

        // Click → set active immediately (the section's smooth scroll
        // will trigger the scroll observer too, which would land on it)
        links.forEach((link) => {
            link.addEventListener('click', () => setActive(link));
        });

        // Scroll-based detection: find which section straddles
        // ~40% of the viewport from the top.
        const detect = () => {
            const trigger = window.innerHeight * 0.4;
            let current = null;
            for (const link of links) {
                const sec = sectionOf(link);
                if (!sec) continue;
                const r = sec.getBoundingClientRect();
                if (r.top <= trigger && r.bottom >= trigger) {
                    current = link;
                    break;
                }
            }
            if (current) setActive(current);
            else if (window.scrollY < 100) setActive(null);  // hero (no nav link)
        };

        window.addEventListener('scroll', detect, { passive: true });
        window.addEventListener('resize', () => {
            const active = nav.querySelector('a.is-active');
            placeIndicator(active);
        });
        detect();
    }

    /* ---------------------------------------------------------
       11. Scroll progress — fills the bar under the header
       proportional to how far the user has scrolled.
       --------------------------------------------------------- */
    function initScrollProgress() {
        const bar = document.getElementById('header-progress-bar');
        if (!bar) return;

        const update = () => {
            const max = document.documentElement.scrollHeight - window.innerHeight;
            const pct = max > 0 ? (window.scrollY / max) * 100 : 0;
            bar.style.width = `${Math.min(100, Math.max(0, pct))}%`;
        };

        update();
        window.addEventListener('scroll', update, { passive: true });
        window.addEventListener('resize', update);
    }

    /* ---------------------------------------------------------
       12. Projects backdrop.
       The animated WebGL nebula (Three.js fbm shader) was retired in
       favour of a static CSS nebula + starfield (see .projects-section
       in style.css) for ALL devices. That removes a constant GPU render
       loop AND the 150KB Three.js dependency — the single biggest perf
       win for low-resource desktops. We just keep the empty mount hidden.
       --------------------------------------------------------- */
    function initCelestialSphere() {
        const mount = document.getElementById('celestial-mount');
        if (mount) mount.classList.add('celestial-mount--static');
    }

    /* ---------------------------------------------------------
       About — Globe (Magic UI Globe via cobe, ESM dynamic import)
       --------------------------------------------------------- */
    function initAboutGlobe() {
        const canvas = document.querySelector('[data-about-globe]');
        if (!canvas) return;

        let phi = 0;
        let pointerInteracting = null;
        let pointerOffset = 0;

        const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const isLowPower = isLowPowerDevice();
        // Cap DPR at 1 for everyone — the globe is small (in a card), so the
        // retina sharpness gain wasn't worth ~4× the fragments to shade.
        const dpr = 1;

        const sizePx = () => {
            const w = canvas.clientWidth || 0;
            const h = canvas.clientHeight || 0;
            const m = Math.min(w || Infinity, h || Infinity);
            return Math.max(260, Number.isFinite(m) && m > 0 ? m : 360);
        };

        // Drag-to-rotate
        canvas.addEventListener('pointerdown', (e) => {
            pointerInteracting = e.clientX - pointerOffset;
        });
        const release = () => { pointerInteracting = null; };
        canvas.addEventListener('pointerup', release);
        canvas.addEventListener('pointerleave', release);
        canvas.addEventListener('pointermove', (e) => {
            if (pointerInteracting === null) return;
            pointerOffset = (e.clientX - pointerInteracting) / 200;
        });

        import('/assets/js/vendor/cobe.js').then((mod) => {
            const createGlobe = mod.default;
            if (typeof createGlobe !== 'function') {
                console.warn('[about-globe] createGlobe is not a function', mod);
                return;
            }

            // cobe drives its own internal rAF loop with no fps cap, so the
            // only way to stop the GPU cost when the About section is off-screen
            // is to destroy the instance and recreate it when it returns.
            // phi lives in the outer scope, so rotation resumes seamlessly.
            let globe = null;
            const buildGlobe = () => {
                if (globe) return;
                globe = createGlobe(canvas, {
                    devicePixelRatio: dpr,
                    width: sizePx() * dpr,
                    height: sizePx() * dpr,
                    phi: 0,
                    theta: 0.2,
                    dark: 1,
                    diffuse: 1.2,
                    mapSamples: isLowPower ? 5000 : 8000,
                    mapBrightness: 6,
                    baseColor: [0.3, 0.3, 0.32],
                    markerColor: [129 / 255, 140 / 255, 248 / 255], // indigo accent
                    glowColor: [1, 1, 1],
                    markers: [
                        { location: [4.5709, -74.2973], size: 0.08 },   // Colombia
                        { location: [40.4637, -3.7492], size: 0.08 },   // España
                    ],
                    onRender: (state) => {
                        if (pointerInteracting === null && !reduced) phi += 0.0035;
                        state.phi = phi + pointerOffset;
                        const s = sizePx();
                        state.width = s * dpr;
                        state.height = s * dpr;
                    },
                });
            };
            const destroyGlobe = () => {
                if (!globe) return;
                try { globe.destroy(); } catch (_) {}
                globe = null;
            };

            if ('IntersectionObserver' in window) {
                const io = new IntersectionObserver((entries) => {
                    entries.forEach((e) => {
                        if (e.isIntersecting) buildGlobe();
                        else destroyGlobe();
                    });
                }, { rootMargin: '150px' });
                io.observe(canvas);
            } else {
                buildGlobe();
            }
        }).catch((err) => {
            console.error('[about-globe] cobe load failed', err);
        });
    }

    /* ---------------------------------------------------------
       Testimonials — Scroll-pinned stack (scrollytelling)
       The outer .testimonials-scroll-container is N viewports tall;
       inside, a position:sticky wrapper pins the visible content.
       We map the user's scroll progress through the tall container
       to the active card index, so each viewport of scroll advances
       one testimonial.
       --------------------------------------------------------- */
    function initTestimonialStack() {
        const stack = document.querySelector('[data-testimonial-stack]');
        if (!stack) return;
        const cards = Array.from(stack.querySelectorAll('.testimonial-card'));
        if (!cards.length) return;

        const dotsHost = document.querySelector('[data-testimonial-dots]');
        const dots = dotsHost ? Array.from(dotsHost.querySelectorAll('.testimonial-dot')) : [];
        const section = stack.closest('.testimonials-section');
        const scrollContainer = document.querySelector('[data-testimonial-scroll]');

        const VISIBLE_BEHIND = 2;
        const total = cards.length;
        const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        let activeIndex = 0;

        const render = () => {
            cards.forEach((card, i) => {
                const displayOrder = ((i - activeIndex) % total + total) % total;
                if (displayOrder === 0) {
                    card.style.transform = 'translateX(0)';
                    card.style.opacity = '1';
                    card.style.zIndex = String(total + 1);
                    card.style.pointerEvents = 'auto';
                    card.setAttribute('aria-hidden', 'false');
                } else if (displayOrder <= VISIBLE_BEHIND) {
                    const scale = 1 - 0.05 * displayOrder;
                    const ty = -1.6 * displayOrder; // rem
                    card.style.transform = `scale(${scale}) translateY(${ty}rem)`;
                    card.style.opacity = String(1 - 0.28 * displayOrder);
                    card.style.zIndex = String(total - displayOrder);
                    card.style.pointerEvents = 'none';
                    card.setAttribute('aria-hidden', 'true');
                } else {
                    card.style.transform = 'scale(0.82) translateY(-5rem)';
                    card.style.opacity = '0';
                    card.style.zIndex = '0';
                    card.style.pointerEvents = 'none';
                    card.setAttribute('aria-hidden', 'true');
                }
            });
            dots.forEach((d, i) => d.classList.toggle('is-active', i === activeIndex));
        };

        // Scroll-driven active index
        let rafId = null;
        const onScroll = () => {
            if (rafId !== null || !scrollContainer) return;
            rafId = requestAnimationFrame(() => {
                rafId = null;
                const rect = scrollContainer.getBoundingClientRect();
                const scrollable = scrollContainer.offsetHeight - window.innerHeight;
                if (scrollable <= 0) return;
                const scrolled = -rect.top;
                const progress = Math.max(0, Math.min(1, scrolled / scrollable));
                // progress 0..1 → index 0..total-1. Math.min handles the inclusive 1.0 case.
                const newIndex = Math.min(total - 1, Math.floor(progress * total));
                if (newIndex !== activeIndex) {
                    activeIndex = newIndex;
                    render();
                }
            });
        };

        if (scrollContainer && !reducedMotion) {
            window.addEventListener('scroll', onScroll, { passive: true });
            window.addEventListener('resize', onScroll, { passive: true });
            onScroll();
        }

        // Dots scroll the page to the middle of the corresponding card range.
        dots.forEach((dot, i) => {
            dot.addEventListener('click', () => {
                if (!scrollContainer) {
                    activeIndex = i;
                    render();
                    return;
                }
                const scrollable = scrollContainer.offsetHeight - window.innerHeight;
                const containerTop = scrollContainer.getBoundingClientRect().top + window.scrollY;
                const targetProgress = (i + 0.5) / total;
                window.scrollTo({
                    top: containerTop + targetProgress * scrollable,
                    behavior: 'smooth',
                });
            });
        });

        // First-time fade/slide-in of the pinned wrapper
        if (section) {
            if (reducedMotion || !('IntersectionObserver' in window)) {
                section.classList.add('is-in-view');
            } else {
                const io = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            section.classList.add('is-in-view');
                            io.disconnect();
                        }
                    });
                }, { threshold: 0.08 });
                io.observe(section);
            }
        }

        render();
    }

    /* ---------------------------------------------------------
       Boot
       --------------------------------------------------------- */
    const boot = () => {
        // Theme is locked to dark — no toggler to init.
        initMorphingText();
        initScrollVelocity();
        initDiaTextReveal();
        initIconCloud();
        initGlassCursorTracking();
        initCopyButtons();
        initProjectsScroller();
        initHeaderScroll();
        initNavIndicator();
        initScrollProgress();
        initCelestialSphere();
        initAboutGlobe();
        initTestimonialStack();
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();

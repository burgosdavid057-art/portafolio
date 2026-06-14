

(() => {
    'use strict';

    
    const isLowPowerDevice = () =>
        document.documentElement.classList.contains('low-power');

    
    function initThemeToggler() {
        const btn = document.getElementById('theme-toggler');
        if (!btn) return;

        const apply = (dark) => {
            document.documentElement.classList.toggle('dark', dark);
            localStorage.setItem('theme', dark ? 'dark' : 'light');
        };

        btn.addEventListener('click', () => {
            const isDark = document.documentElement.classList.contains('dark');
            if (document.startViewTransition) {
                document.startViewTransition(() => apply(!isDark));
            } else {
                apply(!isDark);
            }
        });
    }

    
    function initMorphingText() {
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
            const isLowPower = isLowPowerDevice();

            const setMorph = (fraction) => {
                if (isLowPower) {
                    morph2.style.opacity = String(fraction);
                    morph1.style.opacity = String(1 - fraction);
                    return;
                }
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

    
    function initScrollVelocity() {
    }

    
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
            const DPR = Math.min(window.devicePixelRatio || 1, isLowPowerDevice() ? 1 : 2);

            const resize = () => {
                const rect = canvas.getBoundingClientRect();
                canvas.width  = Math.max(1, Math.floor(rect.width  * DPR));
                canvas.height = Math.max(1, Math.floor(rect.height * DPR));
                ctx.setTransform(DPR, 0, 0, DPR, 0, 0);
            };
            resize();
            window.addEventListener('resize', resize);
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
                if (!dragging) {
                    velX *= 0.96;
                    velY *= 0.96;
                    const idle = performance.now() - interactedAt > 1500;
                    if (idle) {
                        velY += (0.006 - velY) * 0.02;
                        velX += (0       - velX) * 0.05;
                    }
                }
                rotY += velY;
                rotX += velX;
                rotX = Math.max(-Math.PI * 0.45, Math.min(Math.PI * 0.45, rotX));

                ctx.clearRect(0, 0, w, h);

                const cosX = Math.cos(rotX), sinX = Math.sin(rotX);
                const cosY = Math.cos(rotY), sinY = Math.sin(rotY);
                const projected = new Array(points.length);
                for (let i = 0; i < points.length; i++) {
                    const p = points[i];
                    let x = p.x * cosY + p.z * sinY;
                    let z = -p.x * sinY + p.z * cosY;
                    let y = p.y;
                    const y2 = y * cosX - z * sinX;
                    const z2 = y * sinX + z * cosX;
                    projected[i] = {
                        sx: cx + x * radius,
                        sy: cy + y2 * radius,
                        sz: z2,
                        i: i,
                    };
                }
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
            };
            let started = false;
            function start() {
                if (started) return;
                started = true;
                requestAnimationFrame(render);
            }
        });
    }

    
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

    
    function initCopyButtons() {
        document.querySelectorAll('[data-copy]').forEach((btn) => {
            btn.addEventListener('click', async () => {
                const value = btn.dataset.copy;
                if (!value) return;
                try {
                    await navigator.clipboard.writeText(value);
                } catch {
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

    
    function initProjectsScroller() {
        const scroller = document.querySelector('[data-projects-scroller]');
        if (!scroller) return;

        const cards = scroller.querySelectorAll('.project-card');
        const navButtons = document.querySelectorAll('[data-scroll-dir]');
        const dotsContainer = document.querySelector('[data-projects-dots]');
        const dots = dotsContainer ? dotsContainer.querySelectorAll('.project-dot') : [];

        if (!cards.length) return;
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
        navButtons.forEach((btn) => {
            btn.addEventListener('click', () => {
                const dir = btn.dataset.scrollDir === 'next' ? 1 : -1;
                scrollToIndex(currentIndex() + dir);
            });
        });
        dots.forEach((dot) => {
            dot.addEventListener('click', () => {
                const idx = parseInt(dot.dataset.scrollTo || '0', 10);
                scrollToIndex(idx);
            });
        });
        let isDown = false;
        let startX = 0;
        let startScroll = 0;
        let moved = false;

        scroller.addEventListener('pointerdown', (e) => {
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
            if (moved) scrollToIndex(currentIndex());
        };
        scroller.addEventListener('pointerup', endDrag);
        scroller.addEventListener('pointercancel', endDrag);
        scroller.addEventListener('pointerleave', endDrag);
        let rafPending = false;
        scroller.addEventListener('scroll', () => {
            if (rafPending) return;
            rafPending = true;
            requestAnimationFrame(() => {
                updateDots();
                rafPending = false;
            });
        });
        scroller.setAttribute('tabindex', '0');
        scroller.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowRight') { e.preventDefault(); scrollToIndex(currentIndex() + 1); }
            if (e.key === 'ArrowLeft')  { e.preventDefault(); scrollToIndex(currentIndex() - 1); }
        });

        updateDots();
    }

    
    function initHeaderScroll() {
        const header = document.getElementById('site-header');
        const hero   = document.getElementById('hero');
        if (!header || !hero) return;

        const update = () => {
            const heroBottom = hero.getBoundingClientRect().bottom;
            header.classList.toggle('is-scrolled', heroBottom < 80);
        };

        update();
        window.addEventListener('scroll', update, { passive: true });
        window.addEventListener('resize', update);
    }

    
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
        links.forEach((link) => {
            link.addEventListener('click', () => setActive(link));
        });
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

    
    function initCelestialSphere() {
        const mount = document.getElementById('celestial-mount');
        if (mount) mount.classList.add('celestial-mount--static');
    }

    
    function initAboutGlobe() {
        const canvas = document.querySelector('[data-about-globe]');
        if (!canvas) return;

        let phi = 0;
        let pointerInteracting = null;
        let pointerOffset = 0;

        const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const isLowPower = isLowPowerDevice();
        const dpr = 1;

        const sizePx = () => {
            const w = canvas.clientWidth || 0;
            const h = canvas.clientHeight || 0;
            const m = Math.min(w || Infinity, h || Infinity);
            return Math.max(260, Number.isFinite(m) && m > 0 ? m : 360);
        };
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

    
    const boot = () => {
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

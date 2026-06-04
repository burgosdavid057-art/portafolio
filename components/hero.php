<section id="hero" class="hero-geometric">

    <!-- Subtle indigo→rose gradient haze -->
    <div class="hero-geometric-bg"></div>

    <!-- Floating elegant shapes -->
    <div class="hero-shapes" aria-hidden="true">
        <div class="elegant-shape" style="
            --w: 600px; --h: 140px;
            --rotate-start: -3deg; --rotate: 12deg;
            --delay: 0.3s;
            --grad-from: rgb(99 102 241 / 0.15);
            top: 20%; left: -5%;">
            <div class="elegant-shape-float"><div class="elegant-shape-fill"></div></div>
        </div>

        <div class="elegant-shape" style="
            --w: 500px; --h: 120px;
            --rotate-start: -30deg; --rotate: -15deg;
            --delay: 0.5s;
            --grad-from: rgb(244 63 94 / 0.15);
            top: 75%; right: 0%;">
            <div class="elegant-shape-float"><div class="elegant-shape-fill"></div></div>
        </div>

        <div class="elegant-shape" style="
            --w: 300px; --h: 80px;
            --rotate-start: -23deg; --rotate: -8deg;
            --delay: 0.4s;
            --grad-from: rgb(139 92 246 / 0.15);
            bottom: 10%; left: 10%;">
            <div class="elegant-shape-float"><div class="elegant-shape-fill"></div></div>
        </div>

        <div class="elegant-shape" style="
            --w: 200px; --h: 60px;
            --rotate-start: 5deg; --rotate: 20deg;
            --delay: 0.6s;
            --grad-from: rgb(245 158 11 / 0.15);
            top: 15%; right: 20%;">
            <div class="elegant-shape-float"><div class="elegant-shape-fill"></div></div>
        </div>

        <div class="elegant-shape" style="
            --w: 150px; --h: 40px;
            --rotate-start: -40deg; --rotate: -25deg;
            --delay: 0.7s;
            --grad-from: rgb(34 211 238 / 0.15);
            top: 10%; left: 25%;">
            <div class="elegant-shape-float"><div class="elegant-shape-fill"></div></div>
        </div>
    </div>

    <!-- Content -->
    <div class="hero-content">
        <div class="hero-badge">
            <span class="hero-badge-dot"></span>
            <span><?= htmlspecialchars($t['hero']['badge']) ?></span>
        </div>

        <!-- MorphingText (Magic UI) — rotates through name variants.
             Spans are pre-filled server-side so the low-power CSS-only
             cross-fade has content without any JS. On capable devices,
             initMorphingText() takes over and animates the gooey morph. -->
        <div class="morphing-text morphing-text-hero"
             data-texts='<?= htmlspecialchars(json_encode($morphing_texts), ENT_QUOTES) ?>'>
            <span class="morph morph-1"><?= htmlspecialchars($morphing_texts[0] ?? '') ?></span>
            <span class="morph morph-2"><?= htmlspecialchars($morphing_texts[1] ?? ($morphing_texts[0] ?? '')) ?></span>
        </div>

        <p class="hero-tagline">
            <?= htmlspecialchars($t['hero']['tagline_1']) ?><br class="hidden md:inline" />
            <?= htmlspecialchars($t['hero']['tagline_2']) ?>
        </p>

        <div class="hero-ctas">
            <a href="#projects" class="glass-button glass-button-primary glass-on-dark">
                <span><?= htmlspecialchars($t['hero']['cta_work']) ?></span>
                <svg class="arrow w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14M13 5l7 7-7 7"/>
                </svg>
            </a>

            <a href="#contact" class="glass-button glass-on-dark">
                <span><?= htmlspecialchars($t['hero']['cta_contact']) ?></span>
            </a>
        </div>
    </div>

    <!-- Top/bottom vignette for depth -->
    <div class="hero-vignette" aria-hidden="true"></div>

    <!-- Soft fade into the next section's theme bg -->
    <div class="section-bridge section-bridge-bottom" aria-hidden="true"></div>
</section>

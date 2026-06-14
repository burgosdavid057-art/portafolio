<section id="about" class="about-section">
    
    <div class="section-bridge section-bridge-top" aria-hidden="true"></div>

    
    <div class="ethereal-shadow" aria-hidden="true">
        
        <svg class="ethereal-svg" width="0" height="0">
            <defs>
                <filter id="ethereal-filter" x="-20%" y="-20%" width="140%" height="140%" color-interpolation-filters="sRGB">
                    <feTurbulence
                        result="undulation"
                        numOctaves="2"
                        baseFrequency="0.0005, 0.002"
                        seed="0"
                        type="turbulence" />
                    <feColorMatrix
                        in="undulation"
                        type="hueRotate"
                        values="0">
                        <animate attributeName="values"
                                 from="0" to="360"
                                 dur="5.8s"
                                 repeatCount="indefinite" />
                    </feColorMatrix>
                    <feColorMatrix
                        in="dist"
                        result="circulation"
                        type="matrix"
                        values="4 0 0 0 1
                                4 0 0 0 1
                                4 0 0 0 1
                                1 0 0 0 0" />
                    <feDisplacementMap
                        in="SourceGraphic"
                        in2="circulation"
                        scale="100"
                        result="dist" />
                    <feDisplacementMap
                        in="dist"
                        in2="undulation"
                        scale="100"
                        result="output" />
                </filter>
            </defs>
        </svg>

        
        <div class="ethereal-shadow-filtered">
            <div class="ethereal-shadow-fill"></div>
        </div>

        
        <div class="ethereal-shadow-noise"></div>

        
        <div class="ethereal-shadow-tint"></div>
    </div>

    
    <div class="about-inner max-w-5xl mx-auto">
        <p class="about-eyebrow">
            <span class="about-eyebrow-line"></span>
            <span><?= htmlspecialchars($t['about']['eyebrow']) ?></span>
        </p>

        <div class="about-grid">
            <div class="about-bio">
                <h2 class="about-headline">
                    <?= htmlspecialchars($t['about']['headline_1']) ?>
                    <span class="about-headline-muted"><?= htmlspecialchars($t['about']['headline_2']) ?></span>
                </h2>

                <div class="about-paragraphs">
                    <p><?= $t['about']['p1_html']  ?></p>
                    <p><?= htmlspecialchars($t['about']['p2']) ?></p>
                    <p><?= htmlspecialchars($t['about']['p3']) ?></p>
                </div>

                <div class="about-stats">
                    <div class="about-stat">
                        <div class="about-stat-value">3<span class="about-stat-plus">+</span></div>
                        <div class="about-stat-label"><?= htmlspecialchars($t['about']['stats']['sites']) ?></div>
                    </div>
                    <div class="about-stat">
                        <div class="about-stat-value">15<span class="about-stat-plus">+</span></div>
                        <div class="about-stat-label"><?= htmlspecialchars($t['about']['stats']['tech']) ?></div>
                    </div>
                    <div class="about-stat">
                        <div class="about-stat-value">2026</div>
                        <div class="about-stat-label"><?= htmlspecialchars($t['about']['stats']['year']) ?></div>
                    </div>
                    <div class="about-stat">
                        <div class="about-stat-value">∞</div>
                        <div class="about-stat-label"><?= htmlspecialchars($t['about']['stats']['coffees']) ?></div>
                    </div>
                </div>
            </div>

            <aside class="about-card about-card--globe">
                <span class="about-globe-title" aria-hidden="true">Globe</span>
                <div class="about-globe-stage">
                    <canvas class="about-globe-canvas"
                            data-about-globe
                            aria-label="Globo terráqueo con marcadores en Colombia y España"
                            role="img"></canvas>
                </div>
                <div class="about-globe-vignette" aria-hidden="true"></div>
                <p class="about-globe-caption"><?= htmlspecialchars($t['about']['globe_caption']) ?></p>
            </aside>
        </div>
    </div>
</section>

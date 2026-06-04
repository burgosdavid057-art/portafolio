<header id="site-header" class="site-header">
    <div class="site-header-bar">
        <a href="#hero" class="header-brand" aria-label="<?= htmlspecialchars($t['header']['home_aria']) ?>">
            <svg class="header-brand-mark" viewBox="0 0 200 200" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M 25 100 A 75 28 0 1 1 175 100 A 75 28 0 1 1 25 100"></path>
                <g transform="rotate(60 100 100)">
                    <path d="M 25 100 A 75 28 0 1 1 175 100 A 75 28 0 1 1 25 100"></path>
                </g>
                <g transform="rotate(-60 100 100)">
                    <path d="M 25 100 A 75 28 0 1 1 175 100 A 75 28 0 1 1 25 100"></path>
                </g>
                <circle cx="100" cy="100" r="9" fill="currentColor" stroke="none"></circle>
                <circle cx="100" cy="100" r="16" stroke-width="1" opacity="0.4"></circle>
                <g>
                    <circle r="11" cx="100" cy="100" stroke="currentColor" stroke-width="1.4" fill="none" transform="translate(-75 0)"></circle>
                    <text x="25" y="100.5" text-anchor="middle" dominant-baseline="central" font-family="ui-monospace, 'JetBrains Mono', Menlo, monospace" font-weight="700" font-size="13" fill="currentColor" stroke="none">d</text>
                </g>
                <g transform="rotate(60 100 100)">
                    <circle r="11" cx="25" cy="100" stroke="currentColor" stroke-width="1.4" fill="none"></circle>
                    <text x="25" y="100.5" text-anchor="middle" dominant-baseline="central" font-family="ui-monospace, 'JetBrains Mono', Menlo, monospace" font-weight="700" font-size="13" fill="currentColor" stroke="none" transform="rotate(-60 25 100)">b</text>
                </g>
            </svg>
            <span>David</span><span class="header-brand-dot">.</span>
        </a>

        <nav class="header-nav" id="header-nav">
            <span class="header-nav-indicator" aria-hidden="true"></span>
            <a href="#about"    data-section="about"><?= htmlspecialchars($t['nav']['about']) ?></a>
            <a href="#skills"   data-section="skills"><?= htmlspecialchars($t['nav']['skills']) ?></a>
            <a href="#projects" data-section="projects"><?= htmlspecialchars($t['nav']['projects']) ?></a>
            <a href="#contact"  data-section="contact"><?= htmlspecialchars($t['nav']['contact']) ?></a>
        </nav>

        <div class="header-actions">
            <a href="<?= htmlspecialchars(lang_switch_url($lang)) ?>"
               class="glass-pill header-lang-btn"
               aria-label="<?= htmlspecialchars($t['header']['lang_aria']) ?>"
               rel="alternate"
               hreflang="<?= $lang === 'es' ? 'en' : 'es' ?>">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="2" y1="12" x2="22" y2="12"></line>
                    <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                </svg>
                <span class="header-lang-label"><?= htmlspecialchars($t['header']['lang_label']) ?></span>
            </a>

            <?php
                // Cache-bust the CV by its mtime so a re-uploaded PDF is fetched fresh.
                $cv_abs = __DIR__ . '/../' . $cv_path;
                $cv_href = $cv_path . (is_file($cv_abs) ? '?v=' . filemtime($cv_abs) : '');
            ?>
            <a href="<?= htmlspecialchars($cv_href) ?>"
               download="CV-David-Burgos.pdf"
               class="glass-pill header-cv-btn"
               aria-label="<?= htmlspecialchars($t['header']['cv_aria']) ?>">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                <span class="header-cv-label"><?= htmlspecialchars($t['header']['cv']) ?></span>
            </a>
        </div>

    </div>

    <!-- Scroll progress bar -->
    <div class="header-progress" aria-hidden="true">
        <div class="header-progress-bar" id="header-progress-bar"></div>
    </div>
</header>

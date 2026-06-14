<section id="projects" class="projects-section force-dark">
    
    <div id="celestial-mount" class="celestial-mount" aria-hidden="true"></div>
    
    <div class="projects-vignette" aria-hidden="true"></div>
    
    <div class="section-bridge section-bridge-top section-bridge-dark" aria-hidden="true"></div>
    <div class="section-bridge section-bridge-bottom section-bridge-dark" aria-hidden="true"></div>

    <div class="projects-inner">
        <div class="max-w-6xl mx-auto px-6 mb-12 flex flex-col md:flex-row md:items-end md:justify-between gap-6">
            <div>
                <div class="dia-text-reveal mb-3"
                     data-text="Proyectos"
                     data-colors='["#22d3ee","#818cf8","#f472b6","#34d399"]'></div>
                <p class="text-muted max-w-md">
                    Lo que he construido y lo que está en camino. Arrastra o usa las flechas para navegar.
                </p>
            </div>

            
            <div class="flex items-center gap-3">
                <button type="button" class="glass-button glass-icon" data-scroll-dir="prev" aria-label="Anterior">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                </button>
                <button type="button" class="glass-button glass-icon" data-scroll-dir="next" aria-label="Siguiente">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>

        
        <div class="projects-scroller" data-projects-scroller>
            <div class="projects-track">
                <?php foreach ($projects as $idx => $project): ?>
                    <article class="project-card group"
                             style="--accent-rgb: <?= htmlspecialchars($project['accent']) ?>">

                        
                        <div class="project-card-bg bg-gradient-to-br <?= htmlspecialchars($project['gradient']) ?>"></div>

                        
                        <?php
                        if (empty($project['image']) && !empty($project['icon'])):
                            $iconPath = dirname(__DIR__) . '/assets/svg/projects/' . $project['icon'] . '.svg';
                            if (is_file($iconPath)):
                        ?>
                            <div class="project-icon-bg" aria-hidden="true">
                                <?= file_get_contents($iconPath) ?>
                            </div>
                        <?php endif; endif; ?>

                        
                        <?php if (!empty($project['image'])): ?>
                            <div class="project-image-badge" aria-hidden="true">
                                <img src="<?= htmlspecialchars($project['image']) ?>"
                                     alt=""
                                     loading="lazy">
                            </div>
                        <?php endif; ?>

                        
                        <div class="project-card-spotlight"></div>

                        <div class="project-card-inner">
                            
                            <div class="flex items-start justify-between mb-auto gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="project-number">0<?= $idx + 1 ?></span>
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <span class="glass-pill glass-pill-sm">
                                        <?= htmlspecialchars($project['tag']) ?>
                                    </span>
                                    <span class="glass-pill glass-pill-sm">
                                        <?= htmlspecialchars($project['year']) ?>
                                    </span>
                                </div>
                            </div>

                            
                            <div class="flex items-center gap-2 text-xs uppercase tracking-[0.2em] text-muted mt-auto">
                                <?php if ($project['status'] === 'En desarrollo'): ?>
                                    <span class="status-dot status-dot-amber"></span>
                                <?php else: ?>
                                    <span class="status-dot"></span>
                                <?php endif; ?>
                                <?= htmlspecialchars($project['status']) ?>
                            </div>

                            
                            <h3 class="project-title mt-3">
                                <?= htmlspecialchars($project['title']) ?>
                            </h3>

                            
                            <p class="project-description">
                                <?= htmlspecialchars($project['description']) ?>
                            </p>

                            
                            <div class="flex flex-wrap gap-2 mt-5">
                                <?php foreach ($project['stack'] as $tech): ?>
                                    <span class="project-tech-chip"><?= htmlspecialchars($tech) ?></span>
                                <?php endforeach; ?>
                            </div>

                            
                            <div class="flex flex-wrap items-center gap-2.5 mt-7">
                                <?php if ($project['url'] !== '#'): ?>
                                    <a href="<?= htmlspecialchars($project['url']) ?>"
                                       target="_blank" rel="noopener noreferrer"
                                       class="glass-button glass-button-primary">
                                        <span>Visitar sitio</span>
                                        <svg class="arrow w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M7 17L17 7M7 7h10v10"/>
                                        </svg>
                                    </a>
                                    <button type="button" class="glass-button"
                                            data-copy="<?= htmlspecialchars($project['url']) ?>">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                                        </svg>
                                        <span class="copy-label">Copiar URL</span>
                                    </button>
                                <?php else: ?>
                                    <span class="glass-button glass-button-disabled">
                                        <span class="relative flex h-2 w-2">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-accent opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-2 w-2 bg-accent"></span>
                                        </span>
                                        <span>Próximamente</span>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>

                <div class="projects-track-spacer" aria-hidden="true"></div>
            </div>
        </div>

        
        <div class="flex items-center justify-center gap-2 mt-8 relative z-10" data-projects-dots>
            <?php foreach ($projects as $idx => $project): ?>
                <button type="button" class="project-dot<?= $idx === 0 ? ' is-active' : '' ?>"
                        data-scroll-to="<?= $idx ?>"
                        aria-label="Ir al proyecto <?= $idx + 1 ?>"></button>
            <?php endforeach; ?>
        </div>
    </div>
</section>

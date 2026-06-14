<?php

$render_stat_icon = function (string $name): string {
    switch ($name) {
        case 'clock':
            return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>';
        case 'check':
            return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>';
        case 'sparkles':
            return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l1.9 5.1L19 10l-5.1 1.9L12 17l-1.9-5.1L5 10l5.1-1.9z"></path><path d="M19 17l.7 1.8L21.5 19.5l-1.8.7L19 22l-.7-1.8L16.5 19.5l1.8-.7z"></path></svg>';
        case 'rocket':
            return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"></path><path d="M12 15l-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"></path><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"></path><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"></path></svg>';
        case 'thumbs':
            return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"></path></svg>';
    }
    return '';
};
?>
<section id="testimonials" class="testimonials-section">
    
    <div class="section-bridge section-bridge-top section-bridge-from-dark" aria-hidden="true"></div>

    
    <div class="testimonials-scroll-container"
         data-testimonial-scroll
         style="--testimonial-count: <?= count($testimonials) ?>;">

        
        <div class="testimonials-sticky">

            <div class="testimonials-inner">
                <div class="testimonials-header">
                    <div class="dia-text-reveal"
                         data-text="<?= htmlspecialchars($t['testimonials']['title']) ?>"
                         data-colors='["#22d3ee","#818cf8","#f472b6","#34d399"]'></div>
                    <p class="testimonials-subtitle text-muted max-w-md mx-auto text-center">
                        <?= htmlspecialchars($t['testimonials']['subtitle']) ?>
                    </p>
                </div>

                <div class="testimonial-stack" data-testimonial-stack>
            <?php foreach ($testimonials as $i => $tm):  ?>
                <article class="testimonial-card" data-index="<?= $i ?>" aria-roledescription="testimonial">
                    <div class="testimonial-head">
                        <div class="testimonial-avatar" style="background: <?= htmlspecialchars($tm['avatar_gradient']) ?>;">
                            <?= htmlspecialchars($tm['initials']) ?>
                        </div>
                        <div class="testimonial-id">
                            <div class="testimonial-name"><?= htmlspecialchars($tm['name']) ?></div>
                            <div class="testimonial-role"><?= htmlspecialchars($tm['role']) ?></div>
                        </div>
                    </div>

                    <blockquote class="testimonial-quote">
                        “<?= htmlspecialchars($tm['quote']) ?>”
                    </blockquote>

                    <div class="testimonial-footer">
                        <div class="testimonial-tags">
                            <?php foreach ($tm['tags'] as $tag): ?>
                                <span class="testimonial-tag <?= $tag['type'] === 'featured' ? 'testimonial-tag--featured' : '' ?>">
                                    <?= htmlspecialchars($tag['text']) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                        <div class="testimonial-stats">
                            <?php foreach ($tm['stats'] as $stat): ?>
                                <span class="testimonial-stat">
                                    <?= $render_stat_icon($stat['icon']) ?>
                                    <span><?= htmlspecialchars($stat['text']) ?></span>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

                <div class="testimonial-dots" data-testimonial-dots aria-label="<?= htmlspecialchars($t['testimonials']['aria']) ?>">
                    <?php foreach ($testimonials as $i => $_t_dot): ?>
                        <button type="button"
                                class="testimonial-dot<?= $i === 0 ? ' is-active' : '' ?>"
                                data-scroll-to="<?= $i ?>"
                                aria-label="<?= htmlspecialchars($t['testimonials']['aria_dot']) ?> <?= $i + 1 ?>"></button>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>
    </div>
</section>

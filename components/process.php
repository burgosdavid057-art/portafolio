<?php

$step_count = count($process_steps);
$ring_radius_pct = 38; // % of the ring container's half-size
?>
<section id="process" class="process-section">
    <header class="process-header">
        <div class="dia-text-reveal mb-3"
             data-text="<?= htmlspecialchars($t['process']['title']) ?>"
             data-colors='["#a855f7","#ec4899","#22d3ee","#6366f1"]'></div>
        <p class="text-muted max-w-md mx-auto text-center">
            <?= htmlspecialchars($t['process']['subtitle']) ?>
        </p>
    </header>

    
    <div class="process-ring-wrapper" aria-hidden="true">
        <div class="process-ring-glow"></div>

        <div class="process-ring">
            <?php foreach ($process_steps as $i => $step): ?>
                <?php
                $angle_deg = ($i / $step_count) * 360 - 90;
                $angle_rad = deg2rad($angle_deg);
                $x_pct = 50 + $ring_radius_pct * cos($angle_rad);
                $y_pct = 50 + $ring_radius_pct * sin($angle_rad);
                ?>
                <div class="process-step"
                     style="
                         left: <?= round($x_pct, 2) ?>%;
                         top:  <?= round($y_pct, 2) ?>%;
                         --step-color: <?= htmlspecialchars($step['color']) ?>;
                     ">
                    
                    <div class="process-step-rotator">
                        <div class="process-step-inner">
                            <div class="process-step-icon">
                                <?= $step['icon'] ?>
                            </div>
                            <span class="process-step-label"><?= htmlspecialchars($step['title']) ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="process-center">
            <span class="process-center-eyebrow"><?= htmlspecialchars($t['process']['center_eyebrow']) ?></span>
            <span class="process-center-title"><?= htmlspecialchars($t['process']['center_title']) ?></span>
            <span class="process-center-foot"><?= htmlspecialchars($t['process']['center_foot']) ?></span>
        </div>
    </div>

    
    <ol class="process-timeline" aria-label="<?= htmlspecialchars($t['process']['aria_label']) ?>">
        <?php foreach ($process_steps as $i => $step): ?>
            <li class="process-timeline-step"
                style="--step-color: <?= htmlspecialchars($step['color']) ?>;">
                <div class="process-timeline-rail" aria-hidden="true"></div>
                <div class="process-timeline-marker">
                    <span class="process-timeline-icon"><?= $step['icon'] ?></span>
                    <span class="process-timeline-num"><?= sprintf('%02d', $i + 1) ?></span>
                </div>
                <div class="process-timeline-content">
                    <h3 class="process-timeline-title"><?= htmlspecialchars($step['title']) ?></h3>
                    <p class="process-timeline-desc"><?= htmlspecialchars($step['desc']) ?></p>
                </div>
            </li>
        <?php endforeach; ?>
    </ol>
</section>

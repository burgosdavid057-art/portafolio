<section id="skills" class="py-28">
    <div class="max-w-4xl mx-auto px-6 mb-14">
        <div class="dia-text-reveal text-center"
             data-text="<?= htmlspecialchars($t['skills']['title']) ?>"
             data-colors='["#22d3ee","#818cf8","#f472b6","#34d399"]'></div>
        <p class="text-center text-muted mt-4 max-w-xl mx-auto">
            <?= htmlspecialchars($t['skills']['subtitle']) ?>
        </p>
    </div>

    <div class="scroll-velocity-container relative w-full overflow-hidden">
        <!-- Row 1, direction +1 -->
        <div class="scroll-velocity-row flex whitespace-nowrap" data-direction="1" data-base-velocity="20">
            <div class="scroll-velocity-track flex">
                <?php foreach (array_merge($skills_row_1, $skills_row_1, $skills_row_1, $skills_row_1) as $skill): ?>
                    <span class="skill-item"><?= htmlspecialchars($skill) ?></span>
                    <span class="skill-dot">•</span>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Row 2, direction -1 -->
        <div class="scroll-velocity-row flex whitespace-nowrap mt-4" data-direction="-1" data-base-velocity="20">
            <div class="scroll-velocity-track flex">
                <?php foreach (array_merge($skills_row_2, $skills_row_2, $skills_row_2, $skills_row_2) as $skill): ?>
                    <span class="skill-item"><?= htmlspecialchars($skill) ?></span>
                    <span class="skill-dot">•</span>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- gradient fades on sides -->
        <div class="pointer-events-none absolute inset-y-0 left-0 w-1/4 bg-gradient-to-r from-background to-transparent"></div>
        <div class="pointer-events-none absolute inset-y-0 right-0 w-1/4 bg-gradient-to-l from-background to-transparent"></div>
    </div>

    <!-- IconCloud (Magic UI) -->
    <div class="max-w-4xl mx-auto px-6 mt-20 flex flex-col items-center">
        <p class="text-center text-muted mb-6 text-sm uppercase tracking-[0.25em]">
            <?= htmlspecialchars($t['skills']['stack_label']) ?>
        </p>

        <div class="icon-cloud-wrapper">
            <canvas class="icon-cloud-canvas"
                    data-slugs='<?= htmlspecialchars(json_encode($icon_cloud_slugs), ENT_QUOTES) ?>'
                    aria-label="Nube interactiva de tecnologías"
                    role="img"></canvas>
        </div>

        <p class="text-xs text-muted/70 mt-4"><?= htmlspecialchars($t['skills']['drag_hint']) ?></p>
    </div>
</section>

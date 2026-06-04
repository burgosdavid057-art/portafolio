<footer class="border-t border-border/40 py-10 px-6">
    <div class="max-w-6xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4 text-sm text-muted">
        <p>© <?= date('Y') ?> David Burgos. <?= htmlspecialchars($t['footer']['made_with']) ?></p>
        <a href="mailto:<?= htmlspecialchars($email) ?>" class="hover:text-foreground transition-colors">
            <?= htmlspecialchars($email) ?>
        </a>
    </div>
</footer>

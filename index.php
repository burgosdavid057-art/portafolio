<?php
require __DIR__ . '/lang/i18n.php';   // exposes $lang and $t

$page_title = $t['meta']['title'];
$author     = 'David Burgos';
$role       = $t['meta']['role'];
$email      = 'burgosalvarez.jhossmandavid.113@gmail.com';
$whatsapp_number  = '573024893987';                      // wa.me requires no '+' or spaces
$whatsapp_display = '+57 302 489 3987';
$whatsapp_link    = 'https://wa.me/' . $whatsapp_number
    . '?text=' . rawurlencode($t['contact']['wa_message']);
$cv_path = 'assets/docs/CV-David-Burgos.pdf';

$morphing_texts = ['David', 'Burgos'];

$skills_row_1 = ['PHP', 'JavaScript', 'Python', 'HTML', 'CSS', 'Tailwind', 'MySQL', 'Git'];
$skills_row_2 = ['React', 'Next.js', 'Node.js', 'PHP', 'Laravel', 'WordPress', 'Figma', 'UI/UX'];
$icon_cloud_slugs = [
    'php', 'javascript', 'typescript', 'python', 'java',
    'html5', 'css3', 'tailwindcss', 'bootstrap',
    'react', 'nextdotjs', 'nodedotjs', 'laravel', 'wordpress',
    'mysql', 'mongodb', 'postgresql', 'firebase',
    'git', 'github', 'gitlab', 'docker', 'vercel',
    'visualstudiocode', 'figma', 'androidstudio',
    'npm', 'linux',
];


$projects_meta = [
    [
        'icon'     => 'horseshoe',
        'image'    => null,
        'title'    => 'suplementosequinosgm.co',
        'url'      => 'https://suplementosequinosgm.co',
        'status'   => $t['projects']['status_prod'],
        'year'     => '2025',
        'stack'    => ['PHP', 'MySQL', 'Tailwind', 'JS'],
        'gradient' => 'from-emerald-500/40 via-lime-400/20 to-transparent',
        'accent'   => '52 211 153',
    ],
    [
        'icon'     => 'star',
        'image'    => null,
        'title'    => 'starmodel.com.co',
        'url'      => 'https://starmodel.com.co',
        'status'   => $t['projects']['status_prod'],
        'year'     => '2025',
        'stack'    => ['HTML', 'CSS', 'JS', 'PHP'],
        'gradient' => 'from-fuchsia-500/40 via-pink-500/20 to-transparent',
        'accent'   => '244 114 182',
    ],
    [
        'icon'     => 'tommzon',
        'image'    => null,
        'title'    => 'tommzon.com',
        'url'      => 'https://tommzon.com',
        'status'   => $t['projects']['status_prod'],
        'year'     => '2025',
        'stack'    => ['HTML', 'CSS', 'JS', 'PHP'],
        'gradient' => 'from-amber-500/40 via-yellow-400/20 to-transparent',
        'accent'   => '251 191 36',
    ],
    [
        'icon'     => 'desarrollo',
        'image'    => null,
        'title'    => 'Aplicaciones en desarrollo',  // overridden by translation
        'url'      => '#',
        'status'   => $t['projects']['status_dev'],
        'year'     => '2026',
        'stack'    => ['React', 'Node', 'Flutter'],
        'gradient' => 'from-sky-500/40 via-indigo-500/20 to-transparent',
        'accent'   => '129 140 248',
    ],
];
$projects = [];
foreach ($projects_meta as $i => $meta) {
    $projects[] = array_merge($meta, $t['projects']['list'][$i] ?? []);
}



$testimonials_meta = [
    [
        'initials'        => 'JR',
        'name'            => 'Juan Restrepo',
        'stat_icons'      => ['clock', 'check'],
        'avatar_gradient' => 'linear-gradient(135deg, #10b981, #34d399)',
    ],
    [
        'initials'        => 'AS',
        'name'            => 'Andrea Solano',
        'stat_icons'      => ['sparkles', 'check'],
        'avatar_gradient' => 'linear-gradient(135deg, #f472b6, #ec4899)',
    ],
    [
        'initials'        => 'TM',
        'name'            => 'Tomás Marín',
        'stat_icons'      => ['rocket', 'thumbs'],
        'avatar_gradient' => 'linear-gradient(135deg, #fbbf24, #f59e0b)',
    ],
];

$testimonials = [];
foreach ($testimonials_meta as $i => $meta) {
    $tr = $t['testimonials']['list'][$i] ?? [];
    $stats = [];
    foreach ($meta['stat_icons'] as $j => $icon_name) {
        $stats[] = [
            'icon' => $icon_name,
            'text' => $tr['stats'][$j] ?? '',
        ];
    }
    $testimonials[] = [
        'initials'        => $meta['initials'],
        'name'            => $meta['name'],
        'role'            => $tr['role']  ?? '',
        'quote'           => $tr['quote'] ?? '',
        'tags'            => $tr['tags']  ?? [],
        'stats'           => $stats,
        'avatar_gradient' => $meta['avatar_gradient'],
    ];
}



$process_meta = [
    ['color' => '129 140 248', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path><line x1="8" y1="9" x2="16" y2="9"></line><line x1="8" y1="13" x2="13" y2="13"></line></svg>'],
    ['color' => '244 114 182', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19l7-7 3 3-7 7-3-3z"></path><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"></path><path d="M2 2l7.586 7.586"></path><circle cx="11" cy="11" r="2"></circle></svg>'],
    ['color' => '52 211 153',  'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>'],
    ['color' => '251 191 36',  'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"></path><path d="M12 15l-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"></path><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"></path><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"></path></svg>'],
    ['color' => '34 211 238',  'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="12" r="4"></circle><line x1="4.93" y1="4.93" x2="9.17" y2="9.17"></line><line x1="14.83" y1="14.83" x2="19.07" y2="19.07"></line><line x1="14.83" y1="9.17" x2="19.07" y2="4.93"></line><line x1="14.83" y1="9.17" x2="18.36" y2="5.64"></line><line x1="4.93" y1="19.07" x2="9.17" y2="14.83"></line></svg>'],
];
$process_steps = [];
foreach ($process_meta as $i => $meta) {
    $process_steps[] = array_merge($meta, $t['process']['steps'][$i] ?? []);
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>" class="scroll-smooth">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="<?= htmlspecialchars($t['meta']['description']) ?>" />
    <title><?= htmlspecialchars($page_title) ?></title>

    <link rel="icon" type="image/svg+xml" href="assets/img/favicon.svg" />
    <link rel="alternate icon" href="assets/img/favicon.svg" />

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <?php
        $asset = function (string $rel): string {
            $abs = __DIR__ . '/' . $rel;
            $v   = is_file($abs) ? filemtime($abs) : time();
            return $rel . '?v=' . $v;
        };
    ?>
    
    <link rel="stylesheet" href="<?= $asset('assets/css/tailwind.css') ?>" />
    <link rel="stylesheet" href="<?= $asset('assets/css/style.css') ?>" />
    <script>
        document.documentElement.classList.add('dark');
        try { localStorage.removeItem('theme'); } catch (_) {}
        (function () {
            var smallScreen  = window.matchMedia('(max-width: 640px)').matches;
            var fewCores     = navigator.hardwareConcurrency && navigator.hardwareConcurrency <= 6;
            var lowMemory    = navigator.deviceMemory && navigator.deviceMemory <= 4;
            var saveData     = navigator.connection && navigator.connection.saveData;
            if (smallScreen || fewCores || lowMemory || saveData) {
                document.documentElement.classList.add('low-power');
            }
        })();
    </script>
</head>
<body class="bg-background text-foreground font-sans antialiased selection:bg-accent/30">

    
    <svg class="absolute h-0 w-0" aria-hidden="true">
        <defs>
            <filter id="threshold">
                <feColorMatrix in="SourceGraphic" type="matrix"
                    values="1 0 0 0 0
                            0 1 0 0 0
                            0 0 1 0 0
                            0 0 0 255 -140" />
            </filter>
        </defs>
    </svg>

    <?php include __DIR__ . '/components/header.php'; ?>

    <main class="relative">
        <?php include __DIR__ . '/components/hero.php'; ?>
        <?php include __DIR__ . '/components/about.php'; ?>
        <?php include __DIR__ . '/components/skills.php'; ?>
        <?php include __DIR__ . '/components/projects.php'; ?>
        <?php include __DIR__ . '/components/process.php'; ?>
        <?php include __DIR__ . '/components/testimonials.php'; ?>
        <?php include __DIR__ . '/components/contact.php'; ?>
    </main>

    <?php include __DIR__ . '/components/footer.php'; ?>

    
    <script src="<?= $asset('assets/js/app.js') ?>" defer></script>
</body>
</html>

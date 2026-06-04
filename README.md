# Portafolio — David Burgos

Portafolio personal hecho con **PHP + Tailwind CSS** y los componentes de
**Magic UI** recreados en vanilla JS / CSS (porque Magic UI es React).

## Componentes incluidos

| Magic UI                  | Implementación aquí                                |
|---------------------------|----------------------------------------------------|
| `RainbowButton`           | CSS puro (gradiente animado + glow)                |
| `MorphingText`            | Vanilla JS + filtro SVG `<feColorMatrix>`          |
| `ScrollBasedVelocity`     | Vanilla JS con `requestAnimationFrame`             |
| `DiaTextReveal`           | Vanilla JS + `IntersectionObserver`                |
| `AnimatedThemeToggler`    | CSS + `View Transitions API`                       |

## Estructura

```
portfolio/
├── index.php                 entry point
├── components/               PHP partials
│   ├── header.php
│   ├── hero.php
│   ├── about.php
│   ├── skills.php
│   ├── projects.php
│   ├── contact.php
│   └── footer.php
└── assets/
    ├── css/style.css         tokens + animaciones
    └── js/app.js             todos los componentes
```

## Cómo correrlo

Necesitas PHP 7.4+ instalado. En la carpeta `portfolio/`:

```powershell
php -S localhost:8000
```

Luego abre <http://localhost:8000> en el navegador.

## Personalización rápida

Toda la data está en `index.php` arriba del HTML:

```php
$morphing_texts = ['David', 'Burgos', 'DBR', ...];
$skills_row_1   = ['PHP', 'JavaScript', ...];
$skills_row_2   = ['React', 'Next.js', ...];
$projects       = [ ... ];
```

- **Hero text:** edita `$morphing_texts` para los textos que rotan.
- **Habilidades:** dos filas, dirección opuesta. Edita `$skills_row_1` / `$skills_row_2`.
- **Proyectos:** array `$projects` con title, description, url, tag, status.
- **Email:** `$email`.

Los colores del tema viven en `assets/css/style.css` (`:root` y `html.dark`).

## Notas

- Tailwind se carga vía Play CDN (perfecto para desarrollo y demos).
  Para producción, instala Tailwind como build local y enlaza el CSS compilado.
- El botón de tema usa `document.startViewTransition` cuando el navegador lo
  soporta; si no, hace el swap directo sin animación de página completa.
- Las animaciones respetan `prefers-reduced-motion`.

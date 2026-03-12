---
name: web-design-guidelines
description: Senior-level web design system. Apply when building, reviewing, or refactoring any UI — covers layout, design tokens, typography, responsive, accessibility, forms, navigation, performance, components, animation, and UX patterns.
disable-model-invocation: false
argument-hint: "[aspect: layout|tokens|typography|responsive|a11y|forms|navigation|performance|components|animation|ux|all]"
---

# Senior Web Design Guidelines

You are a **Senior UI/UX Engineer** with 15+ years of experience shipping production interfaces at scale.
Every decision you make must be intentional, systematic, and rooted in design principles — never decorative guesswork.

When building or reviewing UI, follow these rules as non-negotiable standards.

---

## 0. Design Philosophy

- **Content-first**: Design serves content. Never let decoration compete with information.
- **Consistency over creativity**: A boring but predictable interface beats a beautiful but confusing one.
- **Progressive disclosure**: Show only what the user needs at each step. Complexity is revealed, never dumped.
- **Invisible design**: The best UI is one the user never notices — they just accomplish their goal.
- **Every pixel earns its place**: If an element doesn't serve a clear purpose, remove it.
- **Design with data**: Real content, real edge cases. Never design with "Lorem ipsum" and hope it works.

---

## 1. Design Tokens & Variables

Define a single source of truth. Every value used more than once must be a token.

```css
:root {
  /* --- Spacing scale (8px base grid) --- */
  --space-2xs: 0.25rem;   /* 4px  */
  --space-xs:  0.5rem;    /* 8px  */
  --space-sm:  0.75rem;   /* 12px */
  --space-md:  1rem;      /* 16px */
  --space-lg:  1.5rem;    /* 24px */
  --space-xl:  2rem;      /* 32px */
  --space-2xl: 3rem;      /* 48px */
  --space-3xl: 4rem;      /* 64px */

  /* --- Color system (HSL for easy manipulation) --- */
  --color-primary-h: 220;
  --color-primary-s: 65%;
  --color-primary-l: 50%;
  --color-primary: hsl(var(--color-primary-h), var(--color-primary-s), var(--color-primary-l));
  --color-primary-light: hsl(var(--color-primary-h), var(--color-primary-s), 95%);
  --color-primary-dark: hsl(var(--color-primary-h), var(--color-primary-s), 35%);

  --color-neutral-50:  #fafafa;
  --color-neutral-100: #f5f5f5;
  --color-neutral-200: #e5e5e5;
  --color-neutral-300: #d4d4d4;
  --color-neutral-400: #a3a3a3;
  --color-neutral-500: #737373;
  --color-neutral-600: #525252;
  --color-neutral-700: #404040;
  --color-neutral-800: #262626;
  --color-neutral-900: #171717;

  --color-success: #16a34a;
  --color-warning: #d97706;
  --color-danger:  #dc2626;
  --color-info:    #2563eb;

  /* --- Semantic surfaces --- */
  --surface-page:       var(--color-neutral-50);
  --surface-card:       #ffffff;
  --surface-elevated:   #ffffff;
  --surface-overlay:    rgba(0, 0, 0, 0.5);
  --text-primary:       var(--color-neutral-900);
  --text-secondary:     var(--color-neutral-600);
  --text-muted:         var(--color-neutral-400);
  --text-inverse:       #ffffff;
  --border-default:     var(--color-neutral-200);
  --border-strong:      var(--color-neutral-400);

  /* --- Typography scale (1.25 Major Third) --- */
  --font-family-sans:  'Inter', system-ui, -apple-system, sans-serif;
  --font-family-mono:  'JetBrains Mono', 'Fira Code', monospace;
  --text-xs:   0.75rem;    /* 12px */
  --text-sm:   0.875rem;   /* 14px */
  --text-base: 1rem;       /* 16px */
  --text-lg:   1.125rem;   /* 18px */
  --text-xl:   1.25rem;    /* 20px */
  --text-2xl:  1.563rem;   /* 25px */
  --text-3xl:  1.953rem;   /* 31px */
  --text-4xl:  2.441rem;   /* 39px */
  --text-5xl:  3.052rem;   /* 49px */
  --line-height-tight:   1.2;
  --line-height-normal:  1.5;
  --line-height-relaxed: 1.75;
  --font-weight-normal:  400;
  --font-weight-medium:  500;
  --font-weight-semibold: 600;
  --font-weight-bold:    700;

  /* --- Shadows (elevation system) --- */
  --shadow-xs:  0 1px 2px rgba(0,0,0,0.05);
  --shadow-sm:  0 1px 3px rgba(0,0,0,0.1), 0 1px 2px rgba(0,0,0,0.06);
  --shadow-md:  0 4px 6px rgba(0,0,0,0.1), 0 2px 4px rgba(0,0,0,0.06);
  --shadow-lg:  0 10px 15px rgba(0,0,0,0.1), 0 4px 6px rgba(0,0,0,0.05);
  --shadow-xl:  0 20px 25px rgba(0,0,0,0.1), 0 10px 10px rgba(0,0,0,0.04);

  /* --- Border radius --- */
  --radius-sm:   0.25rem;  /* 4px  */
  --radius-md:   0.5rem;   /* 8px  */
  --radius-lg:   0.75rem;  /* 12px */
  --radius-xl:   1rem;     /* 16px */
  --radius-full: 9999px;

  /* --- Transitions --- */
  --transition-fast:   150ms cubic-bezier(0.4, 0, 0.2, 1);
  --transition-normal: 250ms cubic-bezier(0.4, 0, 0.2, 1);
  --transition-slow:   350ms cubic-bezier(0.4, 0, 0.2, 1);
  --transition-spring: 500ms cubic-bezier(0.34, 1.56, 0.64, 1);

  /* --- Z-index scale --- */
  --z-dropdown:  100;
  --z-sticky:    200;
  --z-overlay:   300;
  --z-modal:     400;
  --z-popover:   500;
  --z-toast:     600;
  --z-tooltip:   700;

  /* --- Layout --- */
  --container-sm:  640px;
  --container-md:  768px;
  --container-lg:  1024px;
  --container-xl:  1280px;
  --container-max: 1400px;
  --sidebar-width: 280px;
  --header-height: 64px;
}
```

**Rules:**
- NEVER use magic numbers. Every value must reference a token or be derived from one.
- HSL for brand colors (easy to generate tints/shades), hex for neutrals.
- Shadows express elevation hierarchy — more shadow = closer to user.
- Z-index is a managed scale, not arbitrary numbers like `z-index: 99999`.

---

## 2. Layout & Spatial System

### Grid Architecture
```css
/* Page-level layout: CSS Grid */
.app-layout {
  display: grid;
  grid-template-columns: var(--sidebar-width) 1fr;
  grid-template-rows: var(--header-height) 1fr auto;
  grid-template-areas:
    "sidebar header"
    "sidebar main"
    "sidebar footer";
  min-height: 100dvh; /* dvh, not vh — respects mobile browser chrome */
}

/* Content layout: constrained container */
.container {
  width: 100%;
  max-width: var(--container-xl);
  margin-inline: auto;
  padding-inline: var(--space-lg);
}

/* Card grid: auto-fill responsive */
.card-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(min(300px, 100%), 1fr));
  gap: var(--space-lg);
}
```

### Spacing Rules
- **Margin** = space between unrelated elements (sections, cards)
- **Padding** = space within a container (card content, button text)
- Use `gap` in Grid/Flex — never margin hacks for grid spacing
- Vertical rhythm: consistent spacing between sections (`--space-2xl` or `--space-3xl`)
- Related items are closer together, unrelated items are further apart (Law of Proximity)
- Use `margin-block`, `padding-inline` (logical properties) for internationalization support

### Layout Anti-patterns (NEVER do these)
- `float` for layout (only for wrapping text around images)
- Negative margins to fix alignment issues (fix the root cause)
- `position: absolute` for layout (only for overlays and tooltips)
- Fixed pixel heights on content containers (let content breathe)
- `overflow: hidden` to "fix" layout bugs (find the real problem)

---

## 3. Typography

### Hierarchy System
```css
/* Each level has: size, weight, line-height, color, letter-spacing */
.heading-1 { font: var(--font-weight-bold) var(--text-4xl)/var(--line-height-tight) var(--font-family-sans); letter-spacing: -0.025em; color: var(--text-primary); }
.heading-2 { font: var(--font-weight-bold) var(--text-3xl)/var(--line-height-tight) var(--font-family-sans); letter-spacing: -0.02em; color: var(--text-primary); }
.heading-3 { font: var(--font-weight-semibold) var(--text-2xl)/var(--line-height-tight) var(--font-family-sans); color: var(--text-primary); }
.heading-4 { font: var(--font-weight-semibold) var(--text-xl)/var(--line-height-tight) var(--font-family-sans); color: var(--text-primary); }
.body-lg    { font: var(--font-weight-normal) var(--text-lg)/var(--line-height-relaxed) var(--font-family-sans); color: var(--text-primary); }
.body       { font: var(--font-weight-normal) var(--text-base)/var(--line-height-normal) var(--font-family-sans); color: var(--text-primary); }
.body-sm    { font: var(--font-weight-normal) var(--text-sm)/var(--line-height-normal) var(--font-family-sans); color: var(--text-secondary); }
.caption    { font: var(--font-weight-medium) var(--text-xs)/var(--line-height-normal) var(--font-family-sans); color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; }
```

### Rules
- **One `h1` per page** — it's the page title, period.
- Never skip heading levels (`h1` -> `h3`). Assistive tech uses heading hierarchy.
- Body text: `max-width: 65ch` (the optimal reading line length).
- Negative letter-spacing on large text (> 24px), slight positive on small caps/labels.
- `font-display: swap` on all `@font-face` — text must never be invisible while fonts load.
- Use `rem` for font sizes (respects user's browser font-size setting).

---

## 4. Responsive Design

### Breakpoint System (Mobile-First)
```css
/* Base: mobile (0px+) */
/* sm: @media (min-width: 640px)  — large phones landscape */
/* md: @media (min-width: 768px)  — tablets */
/* lg: @media (min-width: 1024px) — small desktops / tablets landscape */
/* xl: @media (min-width: 1280px) — desktops */
/* 2xl: @media (min-width: 1536px) — large desktops */
```

### Container Query (modern approach)
```css
/* When the card's container is narrow, stack vertically */
.card-container { container-type: inline-size; }

@container (max-width: 400px) {
  .card { flex-direction: column; }
  .card__image { aspect-ratio: 16/9; width: 100%; }
}
```

### Responsive Rules
- Write mobile styles first, then layer complexity with `min-width` queries.
- Use `clamp()` for fluid typography: `font-size: clamp(1rem, 0.5rem + 1.5vw, 1.5rem);`
- Use `min()`, `max()`, `clamp()` instead of media queries when possible.
- Touch targets: minimum `44px x 44px` (WCAG 2.5.5 AAA) with adequate spacing between them.
- Test at every 50px interval from 320px to 1920px — not just at breakpoints.
- Images: always use `max-width: 100%; height: auto;` as baseline.
- Tables: use horizontal scroll wrapper on mobile, or restructure as stacked cards.
- Never use `display: none` to "hide" content on mobile — rethink the information architecture.
  If content isn't important enough for mobile, question why it exists at all.

---

## 5. Accessibility (WCAG 2.2 AA Minimum)

### Semantic HTML (the foundation)
```html
<!-- CORRECT: semantic structure -->
<header role="banner">
  <nav aria-label="Main navigation">...</nav>
</header>
<main id="main-content">
  <article>
    <h1>Page Title</h1>
    <section aria-labelledby="section-heading">
      <h2 id="section-heading">Section</h2>
    </section>
  </article>
</main>
<aside aria-label="Related information">...</aside>
<footer role="contentinfo">...</footer>

<!-- WRONG: div soup -->
<div class="header"><div class="nav">...</div></div>
<div class="main"><div class="content">...</div></div>
```

### Critical Checklist
- **Contrast**: 4.5:1 for normal text, 3:1 for large text (18px+ bold or 24px+), 3:1 for UI components.
- **Focus visible**: Every interactive element must have a visible focus ring. Never `outline: none` without a replacement.
```css
:focus-visible {
  outline: 2px solid var(--color-primary);
  outline-offset: 2px;
}
```
- **Skip link**: First focusable element must be "Skip to main content".
```html
<a href="#main-content" class="skip-link">Skip to main content</a>
```
- **Keyboard**: All functionality available via keyboard. Tab order must be logical. No keyboard traps.
- **ARIA**: Use only when HTML semantics are insufficient. Incorrect ARIA is worse than no ARIA.
  - `aria-label` for elements without visible text
  - `aria-describedby` for supplementary instructions
  - `aria-live="polite"` for dynamic content updates (toasts, counters)
  - `aria-expanded` for collapsible sections
  - `role="alert"` for error messages that need immediate attention
- **Images**: `alt=""` for decorative images, descriptive alt for informational images.
- **Motion**: Respect `prefers-reduced-motion`. Disable animations for users who request it.
```css
@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after {
    animation-duration: 0.01ms !important;
    transition-duration: 0.01ms !important;
  }
}
```

---

## 6. Forms — The Most Critical UI

Forms are where users give you their data and trust. Get this wrong, and nothing else matters.

### Structure
```html
<form novalidate> <!-- novalidate = use custom validation, not browser default -->
  <fieldset>
    <legend>Account Information</legend>

    <div class="form-group">
      <label for="email">
        Email address <span class="required" aria-hidden="true">*</span>
      </label>
      <input
        type="email"
        id="email"
        name="email"
        required
        autocomplete="email"
        aria-describedby="email-hint email-error"
        aria-invalid="false"
      />
      <small id="email-hint" class="form-hint">We'll never share your email.</small>
      <span id="email-error" class="form-error" role="alert" hidden>
        Please enter a valid email address.
      </span>
    </div>
  </fieldset>

  <button type="submit">Create Account</button>
</form>
```

### Validation UX
- Validate on `blur` (when user leaves field), not on every keystroke.
- Show errors only after first interaction — don't show errors on page load.
- Error messages must be: specific, helpful, and near the field.
  - Bad: "Invalid input"
  - Good: "Password must be at least 8 characters with one number"
- On submit error, focus the first invalid field and announce errors with `aria-live`.
- On success, clearly communicate what happened and what's next.
- Use `autocomplete` attributes — they save users enormous time.

### Form Anti-patterns (NEVER do these)
- Placeholder text as the only label
- Clearing the form on validation error
- Disabling the submit button before all fields are valid (users won't know what's wrong)
- Custom-styled selects that break keyboard navigation
- Asking for information you don't need

---

## 7. Navigation & Information Architecture

### Patterns by Depth
| Site Depth | Pattern | Implementation |
|---|---|---|
| Flat (3-5 pages) | Single horizontal nav | `<nav>` with links |
| Medium (10-30 pages) | Nav + dropdown menus | Mega menu or grouped links |
| Deep (50+ pages) | Sidebar nav + breadcrumbs | Collapsible tree + breadcrumb trail |

### Rules
- Current page **must** be visually indicated AND communicated to screen readers (`aria-current="page"`).
- Breadcrumbs use `<nav aria-label="Breadcrumb">` with `<ol>` (ordered list — order matters).
- Mobile nav: hamburger icon must have `aria-label="Open menu"` and `aria-expanded="false/true"`.
- Max 7 top-level items (Miller's Law). If more are needed, restructure the information architecture.
- Links look like links (underlined or clearly distinct). Buttons look like buttons.
  Never make a `<div>` or `<span>` act as a link or button — use the correct element.

---

## 8. Component Patterns

### Buttons
```css
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: var(--space-xs);
  padding: var(--space-xs) var(--space-lg);
  min-height: 44px;               /* touch target */
  font: var(--font-weight-medium) var(--text-sm)/1 var(--font-family-sans);
  border-radius: var(--radius-md);
  border: 2px solid transparent;
  cursor: pointer;
  transition: all var(--transition-fast);
  text-decoration: none;
  white-space: nowrap;
}
.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  pointer-events: none;
}
.btn-primary { background: var(--color-primary); color: var(--text-inverse); }
.btn-primary:hover { filter: brightness(1.1); }
.btn-secondary { background: transparent; border-color: var(--border-strong); color: var(--text-primary); }
.btn-danger { background: var(--color-danger); color: var(--text-inverse); }
```

### Cards
```css
.card {
  background: var(--surface-card);
  border: 1px solid var(--border-default);
  border-radius: var(--radius-lg);
  padding: var(--space-lg);
  box-shadow: var(--shadow-sm);
  transition: box-shadow var(--transition-fast);
}
.card:hover { box-shadow: var(--shadow-md); }
.card__header { margin-bottom: var(--space-md); }
.card__title { font: var(--font-weight-semibold) var(--text-lg)/var(--line-height-tight) var(--font-family-sans); }
.card__body { color: var(--text-secondary); }
.card__footer {
  margin-top: var(--space-lg);
  padding-top: var(--space-md);
  border-top: 1px solid var(--border-default);
  display: flex;
  justify-content: flex-end;
  gap: var(--space-xs);
}
```

### Tables (Data-dense)
```css
.table-wrapper { overflow-x: auto; -webkit-overflow-scrolling: touch; }
.table {
  width: 100%;
  border-collapse: collapse;
  font-size: var(--text-sm);
}
.table th {
  text-align: left;
  font-weight: var(--font-weight-semibold);
  padding: var(--space-sm) var(--space-md);
  background: var(--color-neutral-50);
  border-bottom: 2px solid var(--border-strong);
  white-space: nowrap;
  position: sticky;
  top: 0;
}
.table td {
  padding: var(--space-sm) var(--space-md);
  border-bottom: 1px solid var(--border-default);
  vertical-align: middle;
}
.table tbody tr:hover { background: var(--color-primary-light); }
```

### Modals / Dialogs
```html
<dialog id="confirm-dialog" aria-labelledby="dialog-title" aria-describedby="dialog-desc">
  <h2 id="dialog-title">Confirm Deletion</h2>
  <p id="dialog-desc">This action cannot be undone. Are you sure?</p>
  <div class="dialog-actions">
    <button class="btn btn-secondary" data-close>Cancel</button>
    <button class="btn btn-danger" data-confirm>Delete</button>
  </div>
</dialog>
```
- Use native `<dialog>` element — handles focus trapping, backdrop, and escape key.
- Destructive action is NEVER the default/auto-focused button.
- Always provide a clear way to dismiss (Cancel button + Escape key + backdrop click).

### Toast / Notifications
```css
.toast-container {
  position: fixed;
  bottom: var(--space-lg);
  right: var(--space-lg);
  z-index: var(--z-toast);
  display: flex;
  flex-direction: column-reverse;
  gap: var(--space-sm);
}
.toast {
  padding: var(--space-md) var(--space-lg);
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-lg);
  animation: slide-in var(--transition-spring) forwards;
  display: flex;
  align-items: center;
  gap: var(--space-sm);
}
```
- Auto-dismiss after 5s for success, persist until dismissed for errors.
- Use `aria-live="polite"` on the toast container.
- Never stack more than 3 toasts — queue them.

---

## 9. Animation & Motion

### Principles
- **Purpose**: Every animation must have a reason (guide attention, show relationships, provide feedback).
- **Duration**: 150-300ms for micro-interactions, 300-500ms for larger transitions. Never > 700ms.
- **Easing**: Use `cubic-bezier(0.4, 0, 0.2, 1)` (ease-out) for entrances, `cubic-bezier(0.4, 0, 1, 1)` (ease-in) for exits.
- **Properties**: Only animate `transform` and `opacity` for 60fps performance. Never animate `width`, `height`, `top`, `left`, `margin`, or `padding`.

### Standard Animations
```css
@keyframes fade-in {
  from { opacity: 0; }
  to { opacity: 1; }
}
@keyframes slide-up {
  from { opacity: 0; transform: translateY(8px); }
  to { opacity: 1; transform: translateY(0); }
}
@keyframes slide-in {
  from { opacity: 0; transform: translateX(100%); }
  to { opacity: 1; transform: translateX(0); }
}
@keyframes scale-in {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}
```

### ALWAYS respect user preference
```css
@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
    scroll-behavior: auto !important;
  }
}
```

---

## 10. Performance & Loading UX

### Critical Rendering
- Inline critical CSS in `<head>` (above-the-fold styles).
- `<link rel="preload">` for fonts and hero images.
- `<link rel="preconnect">` for third-party origins.
- Defer non-critical JS with `defer` or `type="module"`.

### Image Optimization
```html
<img
  src="image-800.webp"
  srcset="image-400.webp 400w, image-800.webp 800w, image-1200.webp 1200w"
  sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw"
  alt="Descriptive alt text"
  loading="lazy"
  decoding="async"
  width="800"
  height="600"
/>
```
- Always set `width` and `height` to prevent layout shift (CLS).
- Use `loading="lazy"` for below-the-fold images.
- Prefer WebP/AVIF formats with JPEG/PNG fallback.

### Loading States
- **Skeleton screens** > spinners (they feel faster and reduce perceived load time).
- Show skeleton immediately, never after a delay.
- Never show a spinner for operations < 300ms.
- For long operations (> 2s), show progress or a status message.
- Empty states are not errors — design them with helpful actions ("No data yet. Create your first report.").

---

## 11. UX Patterns & Psychological Principles

### Heuristics (internalize these)
| Principle | Application |
|---|---|
| **Fitts's Law** | Important targets (CTAs) must be large and easy to reach. Corner/edge placement on desktop. |
| **Hick's Law** | Reduce choices. 3-5 options max per decision point. Use progressive disclosure for more. |
| **Miller's Law** | Chunk information into groups of 5-7 items. |
| **Jakob's Law** | Users spend most time on OTHER sites. Follow conventions — don't reinvent standard patterns. |
| **Aesthetic-Usability** | Beautiful interfaces are perceived as more usable. Polish matters. |
| **Von Restorff** | Make the most important element visually distinct (size, color, position). |
| **Zeigarnik Effect** | Incomplete tasks are remembered. Show progress bars for multi-step flows. |
| **Peak-End Rule** | Users judge experience by the peak moment and the end. Nail the success state. |

### Error Handling UX
- Error pages (404, 500) must be helpful: explain what happened, offer next steps, maintain navigation.
- Inline errors > alert boxes > page-level errors (in order of preference).
- Never blame the user. "We couldn't find that page" not "You entered a wrong URL".
- Provide recovery paths: undo, retry, go back, contact support.

### Data Display
- Use appropriate formats: dates relative ("2 hours ago") for recent, absolute for historical.
- Numbers: use locale-aware formatting (`toLocaleString()`).
- Empty states: illustration + explanation + primary action.
- Truncate long text with `...` and provide a way to see the full content.
- Sort data meaningfully by default (most recent, most relevant — not random).

---

## 12. CSS Architecture Rules

- **No `!important`** except for accessibility overrides (`prefers-reduced-motion`) and utility classes.
- **BEM naming**: `.block__element--modifier` for predictable, flat selectors.
- **Specificity**: keep it low. Prefer classes over IDs, never chain more than 3 selectors.
- **No inline styles** in HTML (except dynamic values from JS, like `--progress: 75%`).
- **Logical properties**: use `margin-block`, `padding-inline`, `inset-inline` for RTL support.
- **Custom properties** for anything that changes (themes, responsive values, states).
- **`box-sizing: border-box`** globally — non-negotiable.

```css
*, *::before, *::after { box-sizing: border-box; }
```

---

## Execution Protocol

When the user invokes `/web-design-guidelines $ARGUMENTS`:

1. **Identify scope**: Which aspect(s) to review — `$ARGUMENTS` or `all`.
2. **Audit existing code**: Read `assets/css/style.css` and relevant page files.
3. **Check against these standards** systematically.
4. **Provide verdicts**: For each issue found, give:
   - What's wrong (with file path and line reference)
   - Why it matters (which principle it violates)
   - How to fix it (concrete code example using the token system above)
5. **Prioritize**: Critical (accessibility, broken layout) > Important (inconsistency, performance) > Nice-to-have (polish, micro-interactions).

Never give vague feedback like "improve the spacing." Always give exact values, exact code, exact reasoning.

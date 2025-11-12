## Menu Ghost

Menu Ghost is a conditional menu visibility plugin for WordPress. This repo contains the development code, build tooling, and tests. The production plugin is published at:

**https://wordpress.org/plugins/menu-ghost/**

### Feature Snapshot

- Audience targeting (role, login status, device, browser language)
- Page/post/taxonomy/archive conditions
- Scheduling windows (weekday, date range, time of day)
- Campaign-aware rules for query strings & UTM parameters
- React-powered UI inside `Appearance -> Menus`

### Requirements

- PHP 8.0+
- Node.js 18+
- Composer 2.x

### Setup

```bash
git clone git@github.com:sarailoo/menu-ghost.git
cd menu-ghost
composer install
npm install
```

Watch mode / dev build:

```bash
npm start
```

Production build:

```bash
npm run build
```

Create zip file:

```bash
npm run plugin-zip
```

### Development Commands

Common tasks during development:

```bash
npm run lint:js          # ESLint for the React code
npm run lint:css         # Stylelint for SCSS
composer run phpcs       # WordPress Coding Standards
composer run language    # Regenerate languages/menu-ghost.pot
composer install --no-dev --optimize-autoloader   # Production autoload dump
```

### Project Structure

- `src/` – React admin UI source.
- `build/` – Compiled JS/CSS loaded by WordPress.
- `includes/` – Namespaced PHP classes (`MenuGhost\`).
- `languages/` – POT file generated via `composer language`.
- `tests/` – PHPUnit + Brain Monkey utilities.
- `readme.txt` – WordPress.org-facing documentation.

### Contributing

Issues and PRs are welcome. Please follow WordPress coding standards (PHPCS/WPCS) and keep JS changes aligned with the existing React WordPress Components stack. Update docs and release steps if the build pipeline changes.

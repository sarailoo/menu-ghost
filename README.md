## WP Menu Control

This repository contains the development version of the WP Menu Control plugin.

If you're looking for the formatted WordPress.org documentation, please read [`readme.txt`](./readme.txt). It follows the official plugin directory guidelines and is bundled in release zips.

### Development

Install dependencies:

```bash
composer install
npm install
```

Run the build when you're ready to test:

```bash
npm run build
```

Create a distributable zip (excludes dev files via `.distignore`):

```bash
npm run plugin-zip
```

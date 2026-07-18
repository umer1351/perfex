# Perfect ERP

## Setup

1. Copy `application/config/app-config-sample.php` to `application/config/app-config.php` and fill in
   your base URL, encryption key, and database credentials, **or** open `/install` in a browser to run
   the guided installer.
2. Install PHP dependencies where needed: the bundled modules (`einvoice`, `openai`, `surveys`, etc.)
   ship their own `vendor/` directories already, so no root `composer install` is required.
3. `npm install && npm run production` to build front-end assets (only needed if you plan to edit the
   source SCSS/JS — precompiled assets are already included under `assets/builds/`).

## Hosting

This is a PHP/CodeIgniter application — it requires a PHP runtime and a MySQL/MariaDB database.
**It cannot be deployed on Vercel**, which only supports static sites and JS/serverless functions.
Use a PHP-capable host instead, for example:

- Shared/managed PHP hosting (cPanel-based hosts, etc.)
- DigitalOcean App Platform / a VPS with PHP + MySQL
- Railway or a similar PaaS with PHP buildpacks

## Payment gateway keys

`modules/mypos_gateway/libraries/keys/` ships with the module's default sandbox keys. Replace these
with your own myPOS store credentials before processing real payments in production.

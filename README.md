# Synapse

AI-first business management platform built with Laravel.

## Modules

- **AI Assistant** — tasks and natural-language workspace
- **Accounting** — transactions, budgets, and reports
- **Distribution** — media library and publish queue

## Local setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm install && npm run build
php artisan serve
```

Default admin: `admin@synapse.local` / `password`

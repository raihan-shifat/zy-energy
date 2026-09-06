# ZY Energy - B2B Catalogue & eCommerce Platform

A Laravel-based B2B manufacturer/exporter catalogue and storefront for wind turbines, solar panels, solar street lights, inverters, batteries, and complete off-grid/on-grid system solutions.

## Features

- Multi-language storefront (19 locales) with per-language product/category translations
- Product catalogue with variants, attributes (size, color, custom attributes), series and type classification
- Quotation maker with multi-currency support (RMB base), exchange rates, and PDF export
- Enquiry system with WeChat / WhatsApp contact integration
- Admin panel: products, categories, brands, banners, menus, pages, news, certifications, orders, customers, vendors
- Role-based staff access: Super Admin / Admin / Manager with delete guards and security logging
- Site status control: live / maintenance / no-index modes

## Tech Stack

- Laravel 10
- MySQL / MariaDB
- Bootstrap 5 + custom design system
- DataTables, CKEditor 5, dompdf

## Installation

```bash
composer install
npm install

# configure .env, then:
php artisan migrate
php artisan db:seed
php artisan storage:link
php artisan serve
```

Optional installer command:

```bash
php artisan install:app --with-import
```

## Supported Languages

English (primary), Chinese, Arabic, German, Spanish, French, Hindi, Indonesian, Italian, Japanese, Korean, Dutch, Polish, Portuguese, Russian, Thai, Turkish, Vietnamese, Farsi.

Non-English translations are optional; missing translations fall back to English.

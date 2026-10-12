# ServisKu — Deployment & Setup Guide

## 1. Local setup (Laragon)

1. Extract the project to `C:/laragon/www/servisku`.
2. Start Apache and MySQL/MariaDB.
3. Create a **new/empty** database named `servisku`.
4. Import `database/servisku.sql`. It is a destructive fresh-install script and drops existing tables. Back up data first.
5. For local defaults, `config/database.php` uses `127.0.0.1`, database `servisku`, user `root`, and an empty password. Override these with server environment variables if needed.
6. Open `http://localhost/servisku/`.
7. Optional Google OAuth: copy `config/google.example.php` to `config/google.local.php`, fill your own credentials, and register the exact callback URI in Google Cloud. Never commit `google.local.php`.

## 2. Configuration variables

The application reads these process/server environment variables. `.env.example` documents the variable names, but a `.env` file is **not automatically loaded by PHP** in this project:

- `APP_ENV`: set `production` on a live server.
- `APP_DEBUG`: set `0` on a live server.
- `APP_BASE_URL`: full canonical base URL, e.g. `https://servisku.example`.
- `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`.
- `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI` (if OAuth is enabled).

Use a dedicated database user with only the permissions required by the app. Do not use the MySQL root account in production. Use a long, unique database password. Configure environment variables in the hosting control panel/server, not in a public file.

## 3. Before public deployment

- Rotate any OAuth client secret that has ever been included in a shared ZIP or public repository. Do not reuse it.
- Set `APP_ENV=production` and `APP_DEBUG=0`.
- Configure a production database and import the SQL into a fresh staging database first.
- Remove or reset all demo accounts and change default passwords. Do not expose demo accounts on a live site.
- Configure HTTPS and the exact OAuth redirect URI. Update `APP_BASE_URL` to the HTTPS domain.
- Ensure `config/`, `includes/`, `database/`, and private uploads cannot be served directly. Root and directory `.htaccess` files protect Apache deployments; add equivalent rules for Nginx or other servers.
- Ensure the web server can write to `uploads/payment_proofs/` but cannot execute scripts there. Do not grant broad world-writable permissions.
- Confirm the PHP version supports the code and enable PDO MySQL, cURL, Fileinfo, and mbstring if required by the host.
- Confirm scheduled backups, restore procedure, HTTPS, error logging, and file-size limits.
- Test registration/login, all role dashboards, booking, mechanic assignment, sparepart stock, completion, invoice, payment submission/rejection/approval, reports, notifications, activity log, QR tracking, and API access.
- Test with an empty database and populated database.

## 4. Payment limitations

The payment workflow is a manual/demo verification flow. It is not a real payment gateway. Bank account details and QRIS assets in the project are placeholders; replace them with authorized business payment details or remove them before live use. Do not collect real payments until the business and payment process are properly configured.

## 5. QR tracking and external service

Invoice QR image generation currently uses an external QR image service. That service receives the tracking URL. For privacy-sensitive or high-availability production use, replace it with a locally generated QR library and review the public tracking fields.

## 6. Database upgrades

`database/servisku.sql` is for fresh installs only. Do not import it to upgrade a live database. Make a backup and apply a reviewed migration for schema changes. The application does not create/alter tables on every request. Use `database/migrations/001_activity_notifications.sql` only for an existing database missing those tables; the fresh installer already includes them.

## 7. Hosting checklist

- [ ] Fresh database import succeeds on staging
- [ ] Production environment variables set; no secrets committed
- [ ] Demo users removed or passwords reset
- [ ] OAuth callback tested on HTTPS domain
- [ ] Apache protections or equivalent Nginx rules applied
- [ ] Payment proof upload and admin-only access tested
- [ ] Full workflow tested with empty and populated data
- [ ] Backups and restore tested
- [ ] Error messages do not expose SQL, paths, tokens, or credentials
- [ ] Mobile and desktop UI tested

## 8. GitHub hygiene

`config/google.php` is a safe tracked loader. Keep actual credentials in environment variables or `config/google.local.php` (ignored by Git). Scan the full Git history for secrets before publishing. If a secret was committed or shared, rotate it first; deleting the file alone does not revoke it.

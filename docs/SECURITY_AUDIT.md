# ServisKu — Source Review & Release Risks

## Scope

This document records static checks and deployment risks for the current source. It is **not** a penetration test, database integration test, or certification that the application is production-ready.

## Static checks performed in this package

- PHP syntax check across all PHP files.
- Review of environment/configuration handling and direct access to sensitive directories.
- Review of SQL installer and deployment instructions.

## Important release requirements

- Rotate any OAuth secret previously included in a ZIP, shared file, or repository. This package must not contain a live client secret.
- Run the full workflow against a fresh MySQL/MariaDB database and a populated database. Static syntax checks do not detect SQL schema mismatches or runtime query errors.
- Review authorization/ownership and CSRF handling on every state-changing route.
- Test payment-proof upload and admin-only file serving on the actual hosting web server. Apache `.htaccess` rules do not protect Nginx deployments.
- Keep `APP_DEBUG=0` and `APP_ENV=production` in production. Review logs so they contain enough detail for maintainers without leaking secrets to users.
- Remove demo users and placeholder payment details before live deployment.
- Replace or explicitly accept external QR image generation, which sends the tracking URL to a third-party service.

## Runtime test checklist

1. Import `database/servisku.sql` into a brand-new empty database.
2. Verify each table, foreign key, index, and seed account.
3. Test login/register/logout and role access.
4. Test vehicle CRUD and booking creation.
5. Test admin confirmation and mechanic assignment.
6. Test service record, sparepart stock, and completion.
7. Test payment proof upload, rejection/re-upload, and approval.
8. Test invoice totals, tracking fields, notifications, activity log, reports, analytics, and API access.
9. Test invalid input, unauthorized IDs, missing data, and empty dashboards.
10. Repeat on the actual staging host using HTTPS.

## Status

Static syntax check can be reported separately; **runtime status remains unverified until the checklist above is completed in Laragon/staging**. Do not claim production-ready solely from static checks.

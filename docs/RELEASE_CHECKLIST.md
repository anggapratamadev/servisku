# Release Checklist — ServisKu

## Before pushing to a public GitHub repository

- [ ] Rotate any OAuth client secret that has been shared or committed previously.
- [ ] Confirm `config/google.php` is only the safe loader and contains no credentials.
- [ ] Confirm `config/google.local.php`, `.env`, uploads, logs, and private files are not tracked.
- [ ] Scan the full Git history for credentials; deleting a secret in a later commit does not remove it from history.
- [ ] Confirm README, ERD, API, deployment instructions, and license are accurate.
- [ ] Do not publish personal data, genuine payment proof, or live bank account information.

## Before hosting

- [ ] Set `APP_ENV=production` and `APP_DEBUG=0`.
- [ ] Configure environment variables and least-privilege database account.
- [ ] Set HTTPS `APP_BASE_URL` and exact Google OAuth callback URI.
- [ ] Remove or reset demo accounts and replace placeholder payment details.
- [ ] Apply access restrictions for config/includes/database/docs and uploads on the chosen web server.
- [ ] Import SQL into a fresh staging database; never run the reset installer on live data.
- [ ] Run the full workflow with empty and populated data.
- [ ] Verify uploads, admin-only proof access, backup/restore, and error logging.

## Checks performed for this source package

- [x] PHP syntax check (`php -l`) for all PHP files.
- [x] Static scan for the previously embedded Google OAuth secret pattern.
- [x] ZIP archive integrity check.
- [ ] MySQL/MariaDB integration test in Laragon/staging — must be performed by the project owner.
- [ ] Browser workflow test — must be performed by the project owner.

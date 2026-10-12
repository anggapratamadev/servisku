# ServisKu — Software Requirements

## 1. Project Identity
- **Name:** ServisKu
- **Title:** ServisKu — Vehicle Service Management & Booking System
- **Repository:** `servisku`
- **Stack:** PHP Native, MySQL/MariaDB, HTML, CSS, JavaScript
- **Environment:** Laragon
- **Primary goal:** portfolio-ready vehicle service management and booking system.

## 2. User Roles

### Customer
- Register, login, logout, and manage session.
- Manage profile data.
- Add, edit, and delete owned vehicles.
- Browse active services.
- Create service bookings.
- View booking status and estimated/current cost.
- View payment status.
- View service history.

### Admin
- Login as administrator.
- View operational dashboard.
- Manage customers.
- Manage vehicles.
- Manage services.
- Manage mechanics.
- Review and update bookings.
- Assign mechanics to bookings.
- Manage payment records.
- View reports.

### Mechanic
- Login as mechanic.
- View assigned jobs.
- View customer and vehicle details.
- Record inspection and diagnosis.
- Add service actions and notes.
- Add used spareparts.
- Update service progress.
- Mark work as completed.

## 3. Functional Scope by Phase

| Phase | Scope | Status |
|---|---|---|
| 1 | Foundation, database, authentication, roles, dashboards | Existing foundation |
| 2 | Customer profile, vehicles, booking, booking history | Partially implemented |
| 3 | Admin management and mechanic assignment | Next |
| 4 | Mechanic workflow and service records | Planned |
| 5 | Estimates, invoices, payments, transaction history | Planned |
| 6 | QR, charts, PDF, photos, search/filter, activity log, API, notifications, responsive/mobile, dark mode | Portfolio upgrade |

## 4. Core Business Workflow

1. Customer registers/logs in.
2. Customer registers one or more vehicles.
3. Customer chooses an active service and requests a booking date/time.
4. Booking starts with `pending` status.
5. Admin verifies the booking and may assign an available mechanic.
6. Mechanic performs inspection and records diagnosis/actions/parts.
7. Booking progresses to `completed` / `waiting_payment`.
8. Payment is recorded and the booking becomes `paid` internally when payment is confirmed; the UI displays payment as **Lunas**.
9. Customer can view the completed service in history.

## 5. Booking Statuses

- `pending`: new request awaiting review.
- `confirmed`: booking accepted.
- `waiting`: customer/vehicle is waiting for service.
- `in_progress`: mechanic is working.
- `completed`: mechanical work completed.
- `waiting_payment`: final amount is ready for payment.
- `paid`: payment confirmed.
- `cancelled`: booking cancelled.

## 6. Non-Functional Requirements

- Passwords must never be stored as plaintext.
- Database access must use PDO prepared statements.
- Role-restricted pages must verify the authenticated role server-side.
- Customer actions must be scoped to the logged-in customer's records.
- Foreign keys should preserve referential integrity.
- The UI should be responsive before portfolio release.
- Production secrets must be kept outside source control (`.env` or server configuration).

## 7. Out of Scope for the Current Foundation

Do not add payment gateways, REST API, QR code, PDF export, notifications, or advanced analytics until the core workflow is stable and tested.

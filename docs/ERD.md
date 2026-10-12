# ServisKu — Entity Relationship Diagram

The database is intentionally centered on the booking as the main operational transaction.

```mermaid
erDiagram
    USERS ||--o{ VEHICLES : owns
    USERS ||--o{ BOOKINGS : creates
    USERS ||--o| MECHANICS : can_be
    VEHICLES ||--o{ BOOKINGS : used_in
    SERVICES ||--o{ BOOKINGS : selected_for
    MECHANICS ||--o{ BOOKINGS : assigned_to
    BOOKINGS ||--o| SERVICE_RECORDS : has
    MECHANICS ||--o{ SERVICE_RECORDS : performs
    SERVICE_RECORDS ||--o{ SERVICE_PARTS : consumes
    SPAREPARTS ||--o{ SERVICE_PARTS : used_as
    BOOKINGS ||--o| PAYMENTS : has

    USERS {
        int id PK
        varchar name
        varchar email UK
        varchar password
        varchar phone
        enum role
        timestamp created_at
    }

    VEHICLES {
        int id PK
        int user_id FK
        varchar plate_number
        varchar brand
        varchar model
        year year
        varchar color
        timestamp created_at
    }

    SERVICES {
        int id PK
        varchar name
        text description
        decimal price
        int duration
        enum status
        timestamp created_at
    }

    MECHANICS {
        int id PK
        int user_id FK,UK
        varchar specialization
        enum status
    }

    BOOKINGS {
        int id PK
        int user_id FK
        int vehicle_id FK
        int service_id, service_price FK
        int mechanic_id FK
        datetime booking_date
        text complaint
        enum status
        timestamp created_at
    }

    SERVICE_RECORDS {
        int id PK
        int booking_id FK,UK
        int mechanic_id FK
        text inspection
        text diagnosis
        text action_taken
        text notes
        datetime completed_at
    }

    SPAREPARTS {
        int id PK
        varchar name
        varchar part_number
        int stock
        decimal price
        enum status
        timestamp created_at
    }

    SERVICE_PARTS {
        int id PK
        int service_record_id FK
        int sparepart_id FK
        int quantity
        decimal price
    }

    PAYMENTS {
        int id PK
        int booking_id FK,UK
        decimal amount
        enum method
        enum status
        datetime paid_at
        timestamp created_at
    }
```

## Relationship Notes

- One `users` record can own many `vehicles` and create many `bookings`.
- A user can have one mechanic profile when their role is `mechanic`.
- A booking belongs to one customer, one vehicle, and one service.
- A booking may optionally be assigned to one mechanic.
- A booking can have at most one service record and one payment record.
- A service record can contain many sparepart usage rows.
- `service_parts.price` stores the price used at the time of service, so later sparepart price changes do not rewrite historical transactions.

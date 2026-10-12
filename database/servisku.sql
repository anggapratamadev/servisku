CREATE DATABASE IF NOT EXISTS servisku
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE servisku;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS activity_logs;
DROP TABLE IF EXISTS service_parts;
DROP TABLE IF EXISTS service_records;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS mechanics;
DROP TABLE IF EXISTS spareparts;
DROP TABLE IF EXISTS services;
DROP TABLE IF EXISTS vehicles;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    google_id VARCHAR(255) NULL UNIQUE,
    phone VARCHAR(20) NULL,
    role ENUM('customer', 'admin', 'mechanic') NOT NULL DEFAULT 'customer',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE vehicles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    plate_number VARCHAR(20) NOT NULL,
    brand VARCHAR(50) NOT NULL,
    model VARCHAR(50) NOT NULL,
    year YEAR NULL,
    color VARCHAR(30) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_vehicles_user FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_vehicles_user (user_id),
    INDEX idx_vehicles_plate (plate_number)
) ENGINE=InnoDB;

CREATE TABLE services (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    price DECIMAL(12,2) NOT NULL DEFAULT 0,
    duration INT UNSIGNED NOT NULL DEFAULT 60 COMMENT 'Duration in minutes',
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_services_status (status)
) ENGINE=InnoDB;

CREATE TABLE mechanics (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL UNIQUE,
    specialization VARCHAR(100) NULL,
    status ENUM('available', 'busy', 'inactive') NOT NULL DEFAULT 'available',
    CONSTRAINT fk_mechanics_user FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_mechanics_status (status)
) ENGINE=InnoDB;

CREATE TABLE bookings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    vehicle_id INT UNSIGNED NOT NULL,
    service_id INT UNSIGNED NOT NULL,
    service_price DECIMAL(12,2) NOT NULL DEFAULT 0,
    mechanic_id INT UNSIGNED NULL,
    booking_date DATETIME NOT NULL,
    complaint TEXT NULL,
    status ENUM(
        'pending', 'confirmed', 'waiting', 'in_progress',
        'waiting_payment', 'paid', 'cancelled'
    ) NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_bookings_user FOREIGN KEY (user_id) REFERENCES users(id),
    CONSTRAINT fk_bookings_vehicle FOREIGN KEY (vehicle_id) REFERENCES vehicles(id),
    CONSTRAINT fk_bookings_service FOREIGN KEY (service_id) REFERENCES services(id),
    CONSTRAINT fk_bookings_mechanic FOREIGN KEY (mechanic_id) REFERENCES mechanics(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_bookings_user (user_id),
    INDEX idx_bookings_vehicle (vehicle_id),
    INDEX idx_bookings_service (service_id),
    INDEX idx_bookings_mechanic (mechanic_id),
    INDEX idx_bookings_status (status),
    INDEX idx_bookings_date (booking_date),
    INDEX idx_bookings_user_status (user_id, status)
) ENGINE=InnoDB;

CREATE TABLE service_records (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id INT UNSIGNED NOT NULL UNIQUE,
    mechanic_id INT UNSIGNED NOT NULL,
    inspection TEXT NULL,
    diagnosis TEXT NULL,
    action_taken TEXT NULL,
    notes TEXT NULL,
    completed_at DATETIME NULL,
    CONSTRAINT fk_service_records_booking FOREIGN KEY (booking_id) REFERENCES bookings(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_service_records_mechanic FOREIGN KEY (mechanic_id) REFERENCES mechanics(id),
    INDEX idx_service_records_mechanic (mechanic_id)
) ENGINE=InnoDB;

CREATE TABLE spareparts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    part_number VARCHAR(50) NULL,
    stock INT NOT NULL DEFAULT 0,
    price DECIMAL(12,2) NOT NULL DEFAULT 0,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_spareparts_status (status)
) ENGINE=InnoDB;

CREATE TABLE service_parts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    service_record_id INT UNSIGNED NOT NULL,
    sparepart_id INT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL DEFAULT 1,
    price DECIMAL(12,2) NOT NULL,
    CONSTRAINT fk_service_parts_record FOREIGN KEY (service_record_id) REFERENCES service_records(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_service_parts_sparepart FOREIGN KEY (sparepart_id) REFERENCES spareparts(id),
    INDEX idx_service_parts_record (service_record_id),
    INDEX idx_service_parts_sparepart (sparepart_id)
) ENGINE=InnoDB;

CREATE TABLE payments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id INT UNSIGNED NOT NULL UNIQUE,
    amount DECIMAL(12,2) NOT NULL,
    method ENUM('cash', 'transfer', 'qris') NOT NULL,
    status ENUM('pending', 'paid', 'failed') NOT NULL DEFAULT 'pending',
    proof_file VARCHAR(255) NULL,
    submitted_at DATETIME NULL,
    verified_by INT UNSIGNED NULL,
    verified_at DATETIME NULL,
    verification_note VARCHAR(500) NULL,
    paid_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_payments_booking FOREIGN KEY (booking_id) REFERENCES bookings(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_payments_verified_by FOREIGN KEY (verified_by) REFERENCES users(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_payments_status (status)
) ENGINE=InnoDB;


CREATE TABLE activity_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50) NULL,
    entity_id INT UNSIGNED NULL,
    description VARCHAR(500) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_activity_logs_user FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_activity_logs_user (user_id),
    INDEX idx_activity_logs_action (action),
    INDEX idx_activity_logs_entity (entity_type, entity_id),
    INDEX idx_activity_logs_created (created_at)
) ENGINE=InnoDB;

INSERT INTO services (name, description, price, duration) VALUES
('Ganti Oli Mesin', 'Penggantian oli mesin kendaraan', 75000, 30),
('Servis Ringan', 'Pemeriksaan dan servis ringan kendaraan', 150000, 60),
('Servis Berkala', 'Pemeriksaan kendaraan secara menyeluruh', 300000, 120),
('Tune Up', 'Pemeriksaan dan penyetelan mesin', 250000, 120);

INSERT INTO spareparts (name, part_number, stock, price) VALUES
('Oli Mesin 10W-40', 'OLI-001', 20, 65000),
('Filter Oli', 'FLT-001', 15, 35000),
('Busi', 'BUS-001', 30, 25000),
('Filter Udara', 'FLT-002', 10, 75000);

-- Default admin for local development.
-- Username: admin
-- Password: admin123
INSERT INTO users (name, email, password, phone, role) VALUES
(
    'admin',
    'admin@servisku.local',
    '$2y$12$T5LjQk4QMpxNA1rusOnn4.fBlkEA8Ze9UnoVDWBKzoXW4XPjzumjm',
    NULL,
    'admin'
),
(
    'Budi Santoso',
    'customer@servisku.local',
    '$2y$12$f41kUOiznWGl7Aru/5yRd.TIQ32HVgy4Nljnc./jqVyj4HnbokgH.',
    '081234567890',
    'customer'
),
(
    'Andi Pratama',
    'mechanic@servisku.local',
    '$2y$12$wI7eCyE8WXJrQBzq74.v9uJ/r126tsVY0TXpDTlu5WfPkMM2VaGaa',
    '081298765432',
    'mechanic'
);

INSERT INTO mechanics (user_id, specialization, status)
SELECT id, 'Engine & Tune Up', 'available'
FROM users
WHERE email = 'mechanic@servisku.local'
LIMIT 1;


CREATE TABLE notifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    title VARCHAR(150) NOT NULL,
    message VARCHAR(500) NOT NULL,
    type VARCHAR(30) NOT NULL DEFAULT 'info',
    entity_type VARCHAR(50) NULL,
    entity_id INT UNSIGNED NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_notifications_user_read (user_id, is_read),
    INDEX idx_notifications_entity (entity_type, entity_id),
    INDEX idx_notifications_created (created_at)
) ENGINE=InnoDB;

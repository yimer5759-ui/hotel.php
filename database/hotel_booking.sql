-- ============================================================
-- Hotel Booking Management System — Database Schema
-- MySQL 8+ | UTF-8 Unicode
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
CREATE DATABASE IF NOT EXISTS `hotel_booking` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `hotel_booking`;

-- ============================================================
-- 1. ROLES
-- ============================================================
CREATE TABLE IF NOT EXISTS `roles` (
    `id`         TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(30)  NOT NULL,
    `slug`       VARCHAR(30)  NOT NULL UNIQUE,
    `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;

INSERT INTO `roles` (`name`, `slug`) VALUES
('Admin',        'admin'),
('Receptionist', 'receptionist'),
('Customer',     'customer');

-- ============================================================
-- 2. USERS
-- ============================================================
CREATE TABLE IF NOT EXISTS `users` (
    `id`                INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `role_id`           TINYINT UNSIGNED NOT NULL DEFAULT 3,
    `first_name`        VARCHAR(60)   NOT NULL,
    `last_name`         VARCHAR(60)   NOT NULL,
    `email`             VARCHAR(150)  NOT NULL UNIQUE,
    `phone`             VARCHAR(20)   NULL,
    `password`          VARCHAR(255)  NOT NULL,
    `avatar`            VARCHAR(255)  NULL,
    `status`            ENUM('active','inactive','banned') NOT NULL DEFAULT 'active',
    `email_verified`    TINYINT(1)    NOT NULL DEFAULT 0,
    `reset_token`       VARCHAR(100)  NULL,
    `reset_expires`     DATETIME      NULL,
    `remember_token`    VARCHAR(100)  NULL,
    `last_login`        DATETIME      NULL,
    `created_at`        TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_email`   (`email`),
    KEY `idx_role_id` (`role_id`),
    CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`)
) ENGINE=InnoDB;

-- Seed: admin / receptionist / customer (password: Password123!)
INSERT INTO `users` (`role_id`,`first_name`,`last_name`,`email`,`phone`,`password`,`status`,`email_verified`) VALUES
(1, 'System',  'Admin',      'admin@hotel.com',        '+1-555-0001', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active', 1),
(2, 'John',    'Receptionist','receptionist@hotel.com', '+1-555-0002', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active', 1),
(3, 'Jane',    'Doe',         'customer@hotel.com',     '+1-555-0003', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active', 1),
(3, 'Michael', 'Smith',       'michael@example.com',   '+1-555-0004', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active', 1),
(3, 'Sarah',   'Johnson',     'sarah@example.com',     '+1-555-0005', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active', 1);

-- ============================================================
-- 3. ROOM CATEGORIES
-- ============================================================
CREATE TABLE IF NOT EXISTS `room_categories` (
    `id`          TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(60)  NOT NULL,
    `slug`        VARCHAR(60)  NOT NULL UNIQUE,
    `description` TEXT         NULL,
    `base_price`  DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;

INSERT INTO `room_categories` (`name`,`slug`,`description`,`base_price`) VALUES
('Standard',  'standard',  'Comfortable rooms with essential amenities.',          89.00),
('Deluxe',    'deluxe',    'Spacious rooms with upgraded furnishings and views.', 149.00),
('Executive', 'executive', 'Premium rooms with business facilities.',             219.00),
('Suite',     'suite',     'Luxurious suites with separate living areas.',        349.00);

-- ============================================================
-- 4. AMENITIES
-- ============================================================
CREATE TABLE IF NOT EXISTS `amenities` (
    `id`   SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(80)  NOT NULL,
    `icon` VARCHAR(50)  NOT NULL DEFAULT 'bi-check-circle',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;

INSERT INTO `amenities` (`name`,`icon`) VALUES
('Free Wi-Fi',        'bi-wifi'),
('Air Conditioning',  'bi-thermometer-snow'),
('Flat-Screen TV',    'bi-tv'),
('Mini Bar',          'bi-cup-hot'),
('Room Service',      'bi-bell'),
('Safe Deposit Box',  'bi-shield-lock'),
('Hair Dryer',        'bi-wind'),
('Bathrobe',          'bi-person'),
('King Bed',          'bi-house-heart'),
('Sea View',          'bi-water'),
('Balcony',           'bi-door-open'),
('Jacuzzi',           'bi-droplet'),
('Work Desk',         'bi-briefcase'),
('Coffee Maker',      'bi-cup'),
('Kitchenette',       'bi-house');

-- ============================================================
-- 5. ROOMS
-- ============================================================
CREATE TABLE IF NOT EXISTS `rooms` (
    `id`           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `category_id`  TINYINT UNSIGNED NOT NULL,
    `room_number`  VARCHAR(10)   NOT NULL UNIQUE,
    `floor`        TINYINT       NOT NULL DEFAULT 1,
    `name`         VARCHAR(100)  NOT NULL,
    `description`  TEXT          NULL,
    `price_per_night` DECIMAL(10,2) NOT NULL,
    `capacity`     TINYINT       NOT NULL DEFAULT 2,
    `size_sqft`    SMALLINT      NULL,
    `thumbnail`    VARCHAR(255)  NULL,
    `status`       ENUM('available','booked','maintenance','inactive') NOT NULL DEFAULT 'available',
    `is_featured`  TINYINT(1)    NOT NULL DEFAULT 0,
    `created_at`   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_category`  (`category_id`),
    KEY `idx_status`    (`status`),
    KEY `idx_price`     (`price_per_night`),
    CONSTRAINT `fk_rooms_category` FOREIGN KEY (`category_id`) REFERENCES `room_categories`(`id`)
) ENGINE=InnoDB;

INSERT INTO `rooms` (`category_id`,`room_number`,`floor`,`name`,`description`,`price_per_night`,`capacity`,`size_sqft`,`status`,`is_featured`) VALUES
(1, '101', 1, 'Standard Single',  'Cozy single room with city view.',           89.00,  1, 250, 'available', 0),
(1, '102', 1, 'Standard Double',  'Comfortable double room for couples.',        99.00,  2, 300, 'available', 0),
(1, '103', 1, 'Standard Twin',    'Twin room ideal for friends travelling.',    95.00,  2, 300, 'available', 0),
(2, '201', 2, 'Deluxe King',      'Spacious deluxe king room with pool view.', 149.00,  2, 400, 'available', 1),
(2, '202', 2, 'Deluxe Double',    'Elegant double room with garden view.',     139.00,  2, 380, 'available', 0),
(2, '203', 2, 'Deluxe Family',    'Large family room with extra amenities.',   179.00,  4, 500, 'available', 1),
(3, '301', 3, 'Executive Suite',  'Executive room with business lounge access.',219.00, 2, 550, 'available', 1),
(3, '302', 3, 'Executive King',   'Premium king room with panoramic views.',   249.00,  2, 600, 'available', 0),
(4, '401', 4, 'Penthouse Suite',  'Luxurious penthouse with private terrace.', 549.00,  4, 900, 'available', 1),
(4, '402', 4, 'Presidential Suite','Our finest suite with butler service.',    749.00,  6,1200, 'available', 1),
(1, '104', 1, 'Standard Queen',   'Bright queen room near pool.',               92.00,  2, 280, 'maintenance', 0),
(2, '204', 2, 'Deluxe Twin',      'Modern twin deluxe room.',                  159.00,  2, 420, 'available', 0);

-- ============================================================
-- 6. ROOM IMAGES
-- ============================================================
CREATE TABLE IF NOT EXISTS `room_images` (
    `id`        INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `room_id`   INT UNSIGNED  NOT NULL,
    `image`     VARCHAR(255)  NOT NULL,
    `caption`   VARCHAR(150)  NULL,
    `sort_order` TINYINT      NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_room_id` (`room_id`),
    CONSTRAINT `fk_rimages_room` FOREIGN KEY (`room_id`) REFERENCES `rooms`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 7. ROOM AMENITIES (pivot)
-- ============================================================
CREATE TABLE IF NOT EXISTS `room_amenities` (
    `room_id`     INT UNSIGNED     NOT NULL,
    `amenity_id`  SMALLINT UNSIGNED NOT NULL,
    PRIMARY KEY (`room_id`,`amenity_id`),
    CONSTRAINT `fk_ra_room`    FOREIGN KEY (`room_id`)    REFERENCES `rooms`(`id`)     ON DELETE CASCADE,
    CONSTRAINT `fk_ra_amenity` FOREIGN KEY (`amenity_id`) REFERENCES `amenities`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO `room_amenities` (`room_id`,`amenity_id`) VALUES
(1,1),(1,2),(1,3),(1,6),(1,7),
(2,1),(2,2),(2,3),(2,5),(2,6),(2,7),(2,13),
(3,1),(3,2),(3,3),(3,6),(3,13),
(4,1),(4,2),(4,3),(4,4),(4,5),(4,6),(4,7),(4,9),(4,10),(4,11),
(5,1),(5,2),(5,3),(5,4),(5,5),(5,6),(5,7),
(6,1),(6,2),(6,3),(6,4),(6,5),(6,6),(6,7),(6,9),(6,11),
(7,1),(7,2),(7,3),(7,4),(7,5),(7,6),(7,7),(7,9),(7,10),(7,11),(7,13),
(8,1),(8,2),(8,3),(8,4),(8,5),(8,6),(8,7),(8,9),(8,10),(8,11),
(9,1),(9,2),(9,3),(9,4),(9,5),(9,6),(9,7),(9,8),(9,9),(9,10),(9,11),(9,12),(9,14),
(10,1),(10,2),(10,3),(10,4),(10,5),(10,6),(10,7),(10,8),(10,9),(10,10),(10,11),(10,12),(10,14),(10,15);

-- ============================================================
-- 8. COUPONS
-- ============================================================
CREATE TABLE IF NOT EXISTS `coupons` (
    `id`             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `code`           VARCHAR(30)   NOT NULL UNIQUE,
    `type`           ENUM('percent','fixed') NOT NULL DEFAULT 'percent',
    `value`          DECIMAL(10,2) NOT NULL,
    `min_nights`     TINYINT       NOT NULL DEFAULT 1,
    `max_uses`       SMALLINT      NULL,
    `used_count`     SMALLINT      NOT NULL DEFAULT 0,
    `expires_at`     DATE          NULL,
    `is_active`      TINYINT(1)    NOT NULL DEFAULT 1,
    `created_at`     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;

INSERT INTO `coupons` (`code`,`type`,`value`,`min_nights`,`max_uses`,`expires_at`) VALUES
('WELCOME10', 'percent', 10.00, 1, 100, DATE_ADD(CURDATE(), INTERVAL 6 MONTH)),
('SUMMER20',  'percent', 20.00, 2,  50, DATE_ADD(CURDATE(), INTERVAL 3 MONTH)),
('FLAT50',    'fixed',   50.00, 3, NULL, NULL);

-- ============================================================
-- 9. BOOKINGS
-- ============================================================
CREATE TABLE IF NOT EXISTS `bookings` (
    `id`              INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `booking_ref`     VARCHAR(20)   NOT NULL UNIQUE,
    `user_id`         INT UNSIGNED  NOT NULL,
    `room_id`         INT UNSIGNED  NOT NULL,
    `created_by`      INT UNSIGNED  NULL COMMENT 'receptionist/admin user id',
    `coupon_id`       INT UNSIGNED  NULL,
    `check_in`        DATE          NOT NULL,
    `check_out`       DATE          NOT NULL,
    `nights`          TINYINT       NOT NULL,
    `guests`          TINYINT       NOT NULL DEFAULT 1,
    `room_rate`       DECIMAL(10,2) NOT NULL,
    `subtotal`        DECIMAL(10,2) NOT NULL,
    `discount`        DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `tax_rate`        DECIMAL(5,2)  NOT NULL DEFAULT 0.00,
    `tax_amount`      DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `total_amount`    DECIMAL(10,2) NOT NULL,
    `special_requests` TEXT         NULL,
    `status`          ENUM('pending','confirmed','checked_in','checked_out','cancelled','no_show') NOT NULL DEFAULT 'pending',
    `payment_status`  ENUM('pending','paid','partial','refunded') NOT NULL DEFAULT 'pending',
    `cancelled_at`    DATETIME      NULL,
    `cancel_reason`   TEXT          NULL,
    `checked_in_at`   DATETIME      NULL,
    `checked_out_at`  DATETIME      NULL,
    `created_at`      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user`         (`user_id`),
    KEY `idx_room`         (`room_id`),
    KEY `idx_checkin`      (`check_in`),
    KEY `idx_status`       (`status`),
    KEY `idx_booking_ref`  (`booking_ref`),
    CONSTRAINT `fk_bk_user`   FOREIGN KEY (`user_id`)   REFERENCES `users`(`id`),
    CONSTRAINT `fk_bk_room`   FOREIGN KEY (`room_id`)   REFERENCES `rooms`(`id`),
    CONSTRAINT `fk_bk_coupon` FOREIGN KEY (`coupon_id`) REFERENCES `coupons`(`id`)
) ENGINE=InnoDB;

-- Seed bookings
INSERT INTO `bookings`
    (`booking_ref`,`user_id`,`room_id`,`check_in`,`check_out`,`nights`,`guests`,
     `room_rate`,`subtotal`,`tax_rate`,`tax_amount`,`total_amount`,`status`,`payment_status`) VALUES
('BK-20250001', 3, 4, DATE_ADD(CURDATE(),-10), DATE_ADD(CURDATE(),-8), 2, 2, 149.00, 298.00, 10.00, 29.80, 327.80, 'checked_out', 'paid'),
('BK-20250002', 4, 7, DATE_ADD(CURDATE(),-5),  DATE_ADD(CURDATE(),-3), 2, 1, 219.00, 438.00, 10.00, 43.80, 481.80, 'checked_out', 'paid'),
('BK-20250003', 3, 2, DATE_ADD(CURDATE(), 2),  DATE_ADD(CURDATE(), 5), 3, 2,  99.00, 297.00, 10.00, 29.70, 326.70, 'confirmed',   'pending'),
('BK-20250004', 5, 9, DATE_ADD(CURDATE(), 7),  DATE_ADD(CURDATE(),10), 3, 3, 549.00,1647.00, 10.00,164.70,1811.70, 'confirmed',   'paid'),
('BK-20250005', 4, 5, CURDATE(),                DATE_ADD(CURDATE(), 2), 2, 2, 139.00, 278.00, 10.00, 27.80, 305.80, 'checked_in',  'paid');

-- ============================================================
-- 10. BOOKING SERVICES (extra services)
-- ============================================================
CREATE TABLE IF NOT EXISTS `booking_services` (
    `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `booking_id`  INT UNSIGNED  NOT NULL,
    `service`     VARCHAR(80)   NOT NULL,
    `quantity`    TINYINT       NOT NULL DEFAULT 1,
    `unit_price`  DECIMAL(10,2) NOT NULL,
    `total`       DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_booking` (`booking_id`),
    CONSTRAINT `fk_bs_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO `booking_services` (`booking_id`,`service`,`quantity`,`unit_price`,`total`) VALUES
(1, 'Breakfast',      2, 15.00, 30.00),
(1, 'Airport Pickup', 1, 25.00, 25.00),
(4, 'Breakfast',      3, 15.00, 45.00),
(4, 'Spa Session',    2, 80.00,160.00);

-- ============================================================
-- 11. PAYMENTS
-- ============================================================
CREATE TABLE IF NOT EXISTS `payments` (
    `id`             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `booking_id`     INT UNSIGNED  NOT NULL,
    `amount`         DECIMAL(10,2) NOT NULL,
    `method`         ENUM('cash','credit_card','paypal','stripe','bank_transfer') NOT NULL DEFAULT 'cash',
    `status`         ENUM('pending','completed','failed','refunded') NOT NULL DEFAULT 'pending',
    `transaction_id` VARCHAR(100)  NULL,
    `notes`          TEXT          NULL,
    `paid_at`        DATETIME      NULL,
    `created_at`     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_booking` (`booking_id`),
    CONSTRAINT `fk_pay_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`id`)
) ENGINE=InnoDB;

INSERT INTO `payments` (`booking_id`,`amount`,`method`,`status`,`transaction_id`,`paid_at`) VALUES
(1, 327.80, 'credit_card', 'completed', 'TXN-001-2025', NOW()),
(2, 481.80, 'cash',        'completed', 'TXN-002-2025', NOW()),
(4,1811.70, 'stripe',      'completed', 'TXN-004-2025', NOW()),
(5, 305.80, 'credit_card', 'completed', 'TXN-005-2025', NOW());

-- ============================================================
-- 12. INVOICES
-- ============================================================
CREATE TABLE IF NOT EXISTS `invoices` (
    `id`           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `invoice_no`   VARCHAR(20)   NOT NULL UNIQUE,
    `booking_id`   INT UNSIGNED  NOT NULL,
    `issued_at`    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `due_date`     DATE          NULL,
    `notes`        TEXT          NULL,
    `pdf_path`     VARCHAR(255)  NULL,
    PRIMARY KEY (`id`),
    KEY `idx_booking` (`booking_id`),
    CONSTRAINT `fk_inv_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`id`)
) ENGINE=InnoDB;

INSERT INTO `invoices` (`invoice_no`,`booking_id`,`issued_at`) VALUES
('INV-2025-001', 1, NOW()),
('INV-2025-002', 2, NOW()),
('INV-2025-004', 4, NOW()),
('INV-2025-005', 5, NOW());

-- ============================================================
-- 13. REVIEWS
-- ============================================================
CREATE TABLE IF NOT EXISTS `reviews` (
    `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `booking_id`  INT UNSIGNED  NOT NULL,
    `user_id`     INT UNSIGNED  NOT NULL,
    `room_id`     INT UNSIGNED  NOT NULL,
    `rating`      TINYINT       NOT NULL DEFAULT 5,
    `title`       VARCHAR(120)  NULL,
    `body`        TEXT          NULL,
    `status`      ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    `created_at`  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_room`   (`room_id`),
    KEY `idx_user`   (`user_id`),
    KEY `idx_status` (`status`),
    CONSTRAINT `fk_rev_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`id`),
    CONSTRAINT `fk_rev_user`    FOREIGN KEY (`user_id`)    REFERENCES `users`(`id`),
    CONSTRAINT `fk_rev_room`    FOREIGN KEY (`room_id`)    REFERENCES `rooms`(`id`)
) ENGINE=InnoDB;

INSERT INTO `reviews` (`booking_id`,`user_id`,`room_id`,`rating`,`title`,`body`,`status`) VALUES
(1, 3, 4, 5, 'Absolutely fantastic stay!', 'The Deluxe King room exceeded all expectations. Pool view was stunning.', 'approved'),
(2, 4, 7, 4, 'Great executive room',       'Very comfortable and professional. Would recommend for business trips.',  'approved');

-- ============================================================
-- 14. NOTIFICATIONS
-- ============================================================
CREATE TABLE IF NOT EXISTS `notifications` (
    `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `user_id`    INT UNSIGNED  NOT NULL,
    `type`       VARCHAR(60)   NOT NULL,
    `title`      VARCHAR(150)  NOT NULL,
    `message`    TEXT          NOT NULL,
    `link`       VARCHAR(255)  NULL,
    `is_read`    TINYINT(1)    NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user`    (`user_id`),
    KEY `idx_is_read` (`is_read`),
    CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 15. ACTIVITY LOGS
-- ============================================================
CREATE TABLE IF NOT EXISTS `activity_logs` (
    `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `user_id`     INT UNSIGNED  NULL,
    `action`      VARCHAR(100)  NOT NULL,
    `description` TEXT          NULL,
    `ip_address`  VARCHAR(45)   NULL,
    `user_agent`  VARCHAR(255)  NULL,
    `created_at`  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user`   (`user_id`),
    KEY `idx_action` (`action`)
) ENGINE=InnoDB;

-- ============================================================
-- 16. SETTINGS
-- ============================================================
CREATE TABLE IF NOT EXISTS `settings` (
    `id`         SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `key`        VARCHAR(60)   NOT NULL UNIQUE,
    `value`      TEXT          NULL,
    `group`      VARCHAR(40)   NOT NULL DEFAULT 'general',
    `updated_at` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;

INSERT INTO `settings` (`key`,`value`,`group`) VALUES
('hotel_name',          'Grand Azure Hotel',          'general'),
('hotel_email',         'info@grandazure.com',        'general'),
('hotel_phone',         '+1-800-555-HOTEL',           'general'),
('hotel_address',       '1 Ocean Drive, Miami, FL',   'general'),
('hotel_description',   'Experience luxury at its finest — Grand Azure Hotel offers unparalleled comfort and world-class amenities.', 'general'),
('hotel_logo',          '',                           'general'),
('hotel_favicon',       '',                           'general'),
('currency',            'USD',                        'billing'),
('currency_symbol',     '$',                          'billing'),
('tax_rate',            '10',                         'billing'),
('check_in_time',       '14:00',                      'general'),
('check_out_time',      '11:00',                      'general'),
('timezone',            'America/New_York',            'general'),
('smtp_host',           '',                           'email'),
('smtp_port',           '587',                        'email'),
('smtp_user',           '',                           'email'),
('smtp_pass',           '',                           'email'),
('smtp_from',           'no-reply@grandazure.com',    'email'),
('smtp_from_name',      'Grand Azure Hotel',          'email'),
('booking_auto_confirm','0',                          'booking'),
('cancellation_hours',  '24',                         'booking'),
('facebook_url',        '#',                          'social'),
('twitter_url',         '#',                          'social'),
('instagram_url',       '#',                          'social'),
('tripadvisor_url',     '#',                          'social');

-- ============================================================
-- 17. NEWSLETTER
-- ============================================================
CREATE TABLE IF NOT EXISTS `newsletter` (
    `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `email`      VARCHAR(150)  NOT NULL UNIQUE,
    `created_at` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- End of hotel_booking.sql
-- ============================================================

-- Waterlift Solar Expo Data Collection System
-- Schema source of truth. Any structural change must be made here first,
-- then reflected in code — never let code and this file diverge.
--
-- Charset/engine: InnoDB + utf8mb4 everywhere (see CLAUDE.md Tech Stack).

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------------------------
-- admin_users — internal dashboard accounts only. Public visitors never
-- get a row here (see CLAUDE.md Core Business Rules).
-- --------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS admin_users (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username       VARCHAR(100) NOT NULL,
    password_hash  VARCHAR(255) NOT NULL,
    created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_login_at  TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY uq_admin_users_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------------
-- expos — one row per physical expo. slug drives the public QR URL and
-- is the isolation key for every submission (CLAUDE.md Core Business Rules).
-- --------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS expos (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(150) NOT NULL,
    slug          VARCHAR(150) NOT NULL,
    location      VARCHAR(255) NULL,
    start_date    DATE NULL,
    end_date      DATE NULL,
    is_active     TINYINT(1) NOT NULL DEFAULT 1,
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_expos_slug (slug),
    KEY idx_expos_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------------
-- interests — lookup table. Adding an option is a data change, never a
-- code change (CLAUDE.md Database Rules).
-- --------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS interests (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    is_active   TINYINT(1) NOT NULL DEFAULT 1,
    sort_order  INT NOT NULL DEFAULT 0,
    UNIQUE KEY uq_interests_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------------
-- expo_submissions — one row per visitor form submission, always tied to
-- exactly one expo. is_possible_duplicate is set at insert time when
-- (phone, expo_id) already exists for that expo — flagged, never blocked.
-- --------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS expo_submissions (
    id                     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    expo_id                INT UNSIGNED NOT NULL,
    full_name              VARCHAR(150) NOT NULL,
    phone                  VARCHAR(30) NOT NULL,
    project_location       VARCHAR(255) NOT NULL,
    follow_up_method       ENUM('phone_call', 'whatsapp', 'email') NOT NULL,
    email                  VARCHAR(150) NULL,
    message                TEXT NULL,
    is_possible_duplicate  TINYINT(1) NOT NULL DEFAULT 0,
    ip_address             VARCHAR(45) NULL,
    submitted_at           TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_submissions_expo FOREIGN KEY (expo_id)
        REFERENCES expos(id) ON DELETE CASCADE,
    KEY idx_submissions_expo_id (expo_id),
    KEY idx_submissions_phone (phone),
    KEY idx_submissions_submitted_at (submitted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------------
-- submission_interests — many-to-many junction. other_text only holds a
-- value on the row linking to the "Other" interest (free-text reveal).
-- --------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS submission_interests (
    submission_id  INT UNSIGNED NOT NULL,
    interest_id    INT UNSIGNED NOT NULL,
    other_text     VARCHAR(255) NULL,
    PRIMARY KEY (submission_id, interest_id),
    CONSTRAINT fk_si_submission FOREIGN KEY (submission_id)
        REFERENCES expo_submissions(id) ON DELETE CASCADE,
    CONSTRAINT fk_si_interest FOREIGN KEY (interest_id)
        REFERENCES interests(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- --------------------------------------------------------------------------
-- Seed data: baseline interest options, "Other" always last.
-- --------------------------------------------------------------------------
INSERT IGNORE INTO interests (name, sort_order) VALUES
    ('Solar Water Pumping', 1),
    ('Solar Power Systems', 2),
    ('Borehole Drilling', 3),
    ('Irrigation Solutions', 4),
    ('Maintenance & Support', 5),
    ('Other', 99);

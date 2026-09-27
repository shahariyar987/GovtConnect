-- GovConnect database schema
-- Rebuilt from the queries used in the PHP code.
-- Import in phpMyAdmin (Import tab) or run: mysql -u root -p < database/schema.sql

CREATE DATABASE IF NOT EXISTS gov_response_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gov_response_system;

-- Citizens, admins and response teams all live in one table (see `role`)
CREATE TABLE IF NOT EXISTS users (
    user_id          INT AUTO_INCREMENT PRIMARY KEY,
    name             VARCHAR(100) NOT NULL,
    email            VARCHAR(150) NOT NULL UNIQUE,
    phone            VARCHAR(20),
    nid              VARCHAR(30),
    dob              DATE,
    location         VARCHAR(255),
    latitude         DECIMAL(10,7),
    longitude        DECIMAL(10,7),
    password         VARCHAR(255) NOT NULL,          -- bcrypt hash
    role             ENUM('user','admin','response') NOT NULL DEFAULT 'user',
    status           ENUM('active','pending') NOT NULL DEFAULT 'active',
    -- response-team fields
    category         ENUM('police','fire','medical','gov') NULL,
    incharge_name    VARCHAR(100),
    incharge_id      VARCHAR(50),
    incharge_email   VARCHAR(150),
    incharge_phone   VARCHAR(20),
    identification   VARCHAR(100),
    employee_number  VARCHAR(50),
    profile_pic      VARCHAR(255),
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS problems (
    problem_id        INT AUTO_INCREMENT PRIMARY KEY,
    user_id           INT NOT NULL,
    category          ENUM('police','fire','medical','gov','other') NOT NULL,
    description       TEXT NOT NULL,
    suggestion        TEXT,
    location          VARCHAR(255),
    location_name     VARCHAR(255),
    latitude          DECIMAL(10,7),
    longitude         DECIMAL(10,7),
    status            ENUM('pending','verified','rejected','assigned','working','resolved') NOT NULL DEFAULT 'pending',
    priority          ENUM('low','medium','high') NOT NULL DEFAULT 'medium',
    media_path        TEXT,
    assigned_team_id  INT NULL,
    created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_team_id) REFERENCES users(user_id) ON DELETE SET NULL
);

-- Log of actions taken by response teams
CREATE TABLE IF NOT EXISTS responses (
    response_id      INT AUTO_INCREMENT PRIMARY KEY,
    problem_id       INT NOT NULL,
    responder_id     INT NOT NULL,
    response_action  VARCHAR(255),
    status_update    VARCHAR(50),
    accepted_at      DATETIME,
    FOREIGN KEY (problem_id) REFERENCES problems(problem_id) ON DELETE CASCADE,
    FOREIGN KEY (responder_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Citizen feedback after a problem is handled
CREATE TABLE IF NOT EXISTS feedbacks (
    feedback_id  INT AUTO_INCREMENT PRIMARY KEY,
    problem_id   INT NOT NULL,
    user_id      INT NOT NULL,
    rating       TINYINT NOT NULL,
    comment      TEXT,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (problem_id) REFERENCES problems(problem_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Admin accounts cannot be created from the Register page.
-- To create one: open http://localhost/govconnect/generate_hash.php, copy a hash, then run:
-- INSERT INTO users (name, email, password, role, status)
-- VALUES ('Admin', 'admin@govconnect.local', 'PASTE_HASH_HERE', 'admin', 'active');

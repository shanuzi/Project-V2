-- ============================================================
-- USJ-R School Management System - Database Setup
-- Import this file in phpMyAdmin to create the database
-- ============================================================

CREATE DATABASE IF NOT EXISTS usjr_sms;
USE usjr_sms;

CREATE TABLE IF NOT EXISTS schools (
    school_id   INT PRIMARY KEY,
    school_full_name  VARCHAR(150) NOT NULL,
    school_short_name VARCHAR(20)  NOT NULL
);


CREATE TABLE IF NOT EXISTS departments (
    dept_id         INT PRIMARY KEY,
    dept_full_name  VARCHAR(150) NOT NULL,
    dept_short_name VARCHAR(20)  NOT NULL,
    school_id       INT NOT NULL,
    FOREIGN KEY (school_id) REFERENCES schools(school_id)
);

-- ------------------------------------------------------------
--  programs
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS programs (
    prog_id         INT PRIMARY KEY,
    prog_full_name  VARCHAR(150) NOT NULL,
    prog_short_name VARCHAR(20)  NOT NULL,
    dept_id         INT NOT NULL,
    FOREIGN KEY (dept_id) REFERENCES departments(dept_id)
);

-- ------------------------------------------------------------
-- students
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS students (
    student_id          BIGINT PRIMARY KEY,
    student_first_name  VARCHAR(80)  NOT NULL,
    student_middle_name VARCHAR(80),
    student_last_name   VARCHAR(80)  NOT NULL,
    student_year        TINYINT      NOT NULL CHECK (student_year BETWEEN 1 AND 6),
    prog_id             INT          NOT NULL,
    FOREIGN KEY (prog_id) REFERENCES programs(prog_id)
);

-- ------------------------------------------------------------
-- Table: users
-- Roles: Administrator, Creator, Updater, Remover, Viewer
-- Types: Administrator, User
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    user_id       INT AUTO_INCREMENT PRIMARY KEY,
    user_name     VARCHAR(50)  NOT NULL UNIQUE,
    user_password VARCHAR(255) NOT NULL,
    user_type     VARCHAR(20)  NOT NULL DEFAULT 'User',
    user_role     VARCHAR(20)  NOT NULL DEFAULT 'Viewer'
);

-- ------------------------------------------------------------
-- Default admin account  (password: admin123)
-- ------------------------------------------------------------
INSERT INTO users (user_name, user_password, user_type, user_role)
VALUES ('admin', SHA2('admin123', 256), 'Administrator', 'Administrator');

-- ------------------------------------------------------------
-- Sample schools
-- ------------------------------------------------------------
INSERT INTO schools VALUES
(3,  'School of Business and Management',   'SBM'),
(4,  'School of Engineering',               'SoENG'),
(5,  'School of Education',                 'SED'),
(6,  'School of Arts and Sciences',         'SAS'),
(11, 'School of Computer Studies',          'SCS'),
(20, 'School of Allied Medical Sciences',   'SAMS'),
(21, 'School of Fine Arts and Multimedia',  'SOFA');

-- Sample departments under SOFA (id=21)
INSERT INTO departments VALUES
(21001, 'Department of Fine Arts',      'DOFA',  21),
(21002, 'Department of Multimedia Arts','DOMA',  21);

-- Sample program under DOFA (id=21001)
INSERT INTO programs VALUES
(2121001001, 'Bachelor of Arts in Fine Arts', 'ABFINARTS', 21001);
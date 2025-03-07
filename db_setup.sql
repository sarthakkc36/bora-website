-- Create the database
CREATE DATABASE IF NOT EXISTS bh_employment;
USE bh_employment;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(20),
    role ENUM('admin', 'employer', 'job_seeker') NOT NULL,
    company_name VARCHAR(100),
    is_verified TINYINT(1) DEFAULT 0,
    subscription_start DATE NULL,
    subscription_end DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Jobs table
CREATE TABLE IF NOT EXISTS jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    company_name VARCHAR(100) NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    requirements TEXT NOT NULL,
    location VARCHAR(100) NOT NULL,
    job_type ENUM('full-time', 'part-time', 'contract', 'temporary', 'internship') NOT NULL,
    salary_min DECIMAL(10, 2),
    salary_max DECIMAL(10, 2),
    experience_level ENUM('entry', 'mid', 'senior', 'executive') NOT NULL,
    application_instructions TEXT,
    contact_email VARCHAR(100) NOT NULL,
    contact_phone VARCHAR(20),
    views INT DEFAULT 0,
    applications INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Job Applications table
CREATE TABLE IF NOT EXISTS job_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    job_id INT NOT NULL,
    user_id INT NOT NULL,
    resume_path VARCHAR(255) NOT NULL,
    cover_letter TEXT,
    status ENUM('pending', 'reviewed', 'interviewed', 'offered', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Saved Jobs table
CREATE TABLE IF NOT EXISTS saved_jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    job_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_saved_job (job_id, user_id)
);

-- Services table
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    icon VARCHAR(50) NOT NULL,
    order_position INT NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Contact Messages table
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Site Settings table
CREATE TABLE IF NOT EXISTS site_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) NOT NULL UNIQUE,
    setting_value TEXT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Initial data for Services table
INSERT INTO services (title, description, icon, order_position) VALUES
('Job Placement', 'We match qualified candidates with suitable job opportunities across various industries, ensuring a perfect fit for both parties.', 'fas fa-search', 1),
('Resume Building', 'Our experts help you create professional resumes that highlight your skills and experience, increasing your chances of getting hired.', 'fas fa-file-alt', 2),
('Recruitment Services', 'We offer comprehensive recruitment solutions for businesses looking to hire talented professionals for their team.', 'fas fa-users', 3),
('Career Counseling', 'Get professional guidance on career development, job transitions, and skill enhancement to advance in your professional journey.', 'fas fa-comments', 4),
('Interview Preparation', 'We prepare candidates for successful interviews with mock sessions, feedback, and industry-specific guidance.', 'fas fa-handshake', 5),
('HR Consulting', 'We provide businesses with expert HR consulting services to optimize their workforce management and recruitment processes.', 'fas fa-chart-line', 6);

-- Initial admin user (password: admin123)
-- The password hash below is for 'admin123' using PASSWORD_DEFAULT
INSERT INTO users (username, email, password, first_name, last_name, role, is_verified) VALUES
('admin', 'admin@bh.com', '$2y$10$cBNlTb1WQ5aJ2RMrVK4UXeRc5JKtNKJW2kLa1PZLzp0eRnF8loTnO', 'Admin', 'User', 'admin', 1);

-- Initial site settings
INSERT INTO site_settings (setting_key, setting_value) VALUES
('site_title', 'B&H Employment & Consultancy Inc'),
('site_description', 'Professional employment agency connecting qualified candidates with top employers'),
('contact_email', 'bh.jobagency@gmail.com'),
('contact_phone', '(1)347680-2869'),
('contact_address', '37-51 75th St.1A, Jackson Heights, NY 11372'),
('social_facebook', 'https://facebook.com/bhemployment'),
('social_twitter', 'https://twitter.com/bhemployment'),
('social_linkedin', 'https://linkedin.com/company/bhemployment'),
('social_instagram', 'https://instagram.com/bhemployment');
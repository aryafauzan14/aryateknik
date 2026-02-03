-- Create Database
CREATE DATABASE IF NOT EXISTS borewell_db;
USE borewell_db;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20) NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    address TEXT,
    role ENUM('admin', 'customer') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Services Table
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    description TEXT
);

-- Insert Default Services
INSERT INTO services (slug, name, price, description) VALUES
('drilling', 'Borewell Drilling', 5000000.00, 'Complete borewell drilling service'),
('maintenance', 'Maintenance', 1500000.00, 'Regular maintenance checkup'),
('repair', 'Emergency Repair', 2000000.00, 'Urgent repair services'),
('installation', 'Pump Installation', 3000000.00, 'New pump installation')
ON DUPLICATE KEY UPDATE name=VALUES(name), price=VALUES(price), description=VALUES(description);

-- Orders Table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(20) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    customer_name VARCHAR(100) NOT NULL,
    customer_email VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(20) NOT NULL,
    service_type VARCHAR(50) NOT NULL,
    location TEXT NOT NULL,
    preferred_date DATE NOT NULL,
    notes TEXT,
    total_price DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert Admin User (Password: admin123)
-- Hash generated: $2y$10$yuwds.92eRPh9hr9roy8j.lDJj8zerTThZK7vJRJYt6CsK0v26nPy
INSERT INTO users (name, email, phone, username, password, role) VALUES
('Admin User', 'admin@borewell.com', '0000000000', 'admin', '$2y$10$yuwds.92eRPh9hr9roy8j.lDJj8zerTThZK7vJRJYt6CsK0v26nPy', 'admin')
ON DUPLICATE KEY UPDATE password=VALUES(password), role='admin';

-- Insert Standard User (Password: user123)
-- Hash generated: $2y$10$viJXOo6aiAXgKK8qGSVDt.nTSFE3vCRfJnaD7alv/tUQ9wzBCeKPe
INSERT INTO users (name, email, phone, username, password, role) VALUES
('Standard User', 'user@borewell.com', '0000000000', 'user', '$2y$10$viJXOo6aiAXgKK8qGSVDt.nTSFE3vCRfJnaD7alv/tUQ9wzBCeKPe', 'customer')
ON DUPLICATE KEY UPDATE password=VALUES(password), role='customer';

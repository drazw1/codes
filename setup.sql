CREATE DATABASE IF NOT EXISTS pharmacy_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE pharmacy_db;

-- ── Categories ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS categories (
    category_id   INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL,
    description   VARCHAR(255)
);

-- ── Medicines ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS medicines (
    medicine_id            INT AUTO_INCREMENT PRIMARY KEY,
    medicine_name          VARCHAR(150) NOT NULL,
    category_id            INT NULL,
    supplier_id            INT NULL,
    price                  DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    stock                  INT NOT NULL DEFAULT 0,
    prescription_required  ENUM('YES','NO') NOT NULL DEFAULT 'NO',
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE SET NULL
);

-- ── Sample Categories ────────────────────────────────────────
INSERT INTO categories (category_name, description) VALUES
    ('Analgesics',     'Pain relief medications'),
    ('Antibiotics',    'Bacterial infection treatments'),
    ('Antivirals',     'Viral infection treatments'),
    ('Vitamins',       'Nutritional supplements'),
    ('Cardiovascular', 'Heart and blood pressure medications');

-- ── Sample Medicines ─────────────────────────────────────────
INSERT INTO medicines (medicine_name, category_id, supplier_id, price, stock, prescription_required) VALUES
    ('Paracetamol 500mg',      1, 1,  3.50,  200, 'NO'),
    ('Ibuprofen 400mg',        1, 1,  5.00,  150, 'NO'),
    ('Amoxicillin 250mg',      2, 2, 12.00,   45, 'YES'),
    ('Ciprofloxacin 500mg',    2, 2, 18.50,   30, 'YES'),
    ('Acyclovir 400mg',        3, 3, 22.00,    8, 'YES'),   -- low stock
    ('Vitamin C 1000mg',       4, 1,  6.75,  300, 'NO'),
    ('Vitamin D3 5000IU',      4, 1,  9.99,  250, 'NO'),
    ('Amlodipine 5mg',         5, 4, 15.00,    5, 'YES'),   -- low stock
    ('Atenolol 50mg',          5, 4, 11.25,   60, 'YES'),
    ('Metformin 500mg',        5, 4,  8.00,    0, 'YES'),   -- out of stock
    ('Aspirin 81mg',           1, 1,  4.25,  180, 'NO'),
    ('Azithromycin 250mg',     2, 2, 25.00,   20, 'YES'),
    ('Omeprazole 20mg',        1, 3, 14.50,   90, 'NO'),
    ('Cetirizine 10mg',        1, 1,  7.00,  110, 'NO'),
    ('Losartan 50mg',          5, 4, 16.00,   35, 'YES');

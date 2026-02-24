-- Base de datos Finanzas Asesores
-- Estructura completa de tablas

-- 1. Tabla de usuarios
CREATE TABLE user_asesores (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    dni VARCHAR(20) UNIQUE NOT NULL,
    phone VARCHAR(20) NULL,
    role VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    direccion VARCHAR(255) NULL
);

-- 2. Tabla de estados de préstamos
CREATE TABLE loan_statuses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    description VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 3. Tabla de clientes
CREATE TABLE clients (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    advisor_id BIGINT UNSIGNED NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    dni VARCHAR(20) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20) NULL,
    address TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    negocio text null,
    zona text null,
    FOREIGN KEY (advisor_id) REFERENCES user_asesores(id) ON DELETE CASCADE
);

-- 4. Tabla de préstamos
CREATE TABLE loans (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id BIGINT UNSIGNED NOT NULL,
    advisor_id BIGINT UNSIGNED NOT NULL,
    status_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    interest_rate DECIMAL(5,2) NOT NULL,
    term_months INT NOT NULL,
    monthly_payment DECIMAL(10,2) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    codigo VARCHAR(50) UNIQUE NOT NULL,
    tipo_credito VARCHAR(50) NULL,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (advisor_id) REFERENCES user_asesores(id) ON DELETE CASCADE,
    FOREIGN KEY (status_id) REFERENCES loan_statuses(id) ON DELETE CASCADE
);

-- 5. Tabla de ahorros
CREATE TABLE savings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    daily_contribution DECIMAL(10,2) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    currency VARCHAR(3) NOT NULL DEFAULT 'PEN',
    status VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    tipo_ahorro varchar(255) null,
    periodo int null,
    tasa DECIMAL(10,2) null,
    tipo_aportacion varchar(255) null,
    monto_aportacion varchar(255) null,
    codigo VARCHAR(50) UNIQUE NOT NULL,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
);

-- 6. Tabla de cuotas
CREATE TABLE installments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    loan_id BIGINT UNSIGNED NOT NULL,
    installment_number INT NOT NULL,
    due_date DATE NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    paid_amount DECIMAL(10,2) DEFAULT 0.00,
    status VARCHAR(50) NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    payment_date DATETIME NULL,
    payment_proof TEXT NULL,
    payment_method VARCHAR(50) NULL,
    paid_at TIMESTAMP NULL,
    notes TEXT NULL,
    FOREIGN KEY (loan_id) REFERENCES loans(id) ON DELETE CASCADE
);

-- 7. Tabla de pagos
CREATE TABLE payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    loan_id BIGINT UNSIGNED NULL,
    installment_id BIGINT UNSIGNED NULL,
    savings_id BIGINT UNSIGNED NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    notes TEXT NULL,
    payment_proof TEXT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (loan_id) REFERENCES loans(id) ON DELETE CASCADE,
    FOREIGN KEY (installment_id) REFERENCES installments(id) ON DELETE CASCADE,
    FOREIGN KEY (savings_id) REFERENCES savings(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES user_asesores(id) ON DELETE CASCADE
);

-- 8. Tabla de cobros
CREATE TABLE collections (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
);

-- 9. Tabla de cuotas de ahorro
CREATE TABLE savings_installments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    savings_id BIGINT UNSIGNED NOT NULL,
    installment_number INT NOT NULL,
    due_date DATE NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    paid_amount DECIMAL(10,2) DEFAULT 0.00,
    status VARCHAR(50) NOT NULL DEFAULT 'pending',
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    payment_date DATETIME NULL,
    payment_proof TEXT NULL,
    payment_method VARCHAR(50) NULL,
    FOREIGN KEY (savings_id) REFERENCES savings(id) ON DELETE CASCADE
);

-- 10. Tabla de reportes
CREATE TABLE reports (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    advisor_id BIGINT UNSIGNED NOT NULL,
    type VARCHAR(50) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (advisor_id) REFERENCES user_asesores(id) ON DELETE CASCADE
);

-- 11. Tabla de cierres de caja diarios
CREATE TABLE daily_cash_closings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    advisor_id BIGINT UNSIGNED NOT NULL,
    closing_date DATE NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    yape_amount DECIMAL(10,2) DEFAULT 0.00,
    cash_amount DECIMAL(10,2) DEFAULT 0.00,
    transfer_method VARCHAR(50) NULL,
    transfer_proof TEXT NULL,
    notes TEXT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'pending',
    confirmed_by BIGINT UNSIGNED NULL,
    confirmed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    payment_type VARCHAR(50) NULL,
    FOREIGN KEY (advisor_id) REFERENCES user_asesores(id) ON DELETE CASCADE,
    FOREIGN KEY (confirmed_by) REFERENCES user_asesores(id) ON DELETE SET NULL
);

-- Índices para mejor rendimiento
CREATE INDEX idx_clients_advisor_id ON clients(advisor_id);
CREATE INDEX idx_loans_client_id ON loans(client_id);
CREATE INDEX idx_loans_advisor_id ON loans(advisor_id);
CREATE INDEX idx_loans_status_id ON loans(status_id);
CREATE INDEX idx_savings_client_id ON savings(client_id);
CREATE INDEX idx_installments_loan_id ON installments(loan_id);
CREATE INDEX idx_installments_due_date ON installments(due_date);
CREATE INDEX idx_payments_loan_id ON payments(loan_id);
CREATE INDEX idx_payments_created_at ON payments(created_at);
CREATE INDEX idx_collections_client_id ON collections(client_id);
CREATE INDEX idx_savings_installments_savings_id ON savings_installments(savings_id);
CREATE INDEX idx_reports_advisor_id ON reports(advisor_id);
CREATE INDEX idx_daily_cash_closings_advisor_id ON daily_cash_closings(advisor_id);
CREATE INDEX idx_daily_cash_closings_closing_date ON daily_cash_closings(closing_date);

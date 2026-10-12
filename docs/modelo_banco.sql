-- GestCar - Modelo Lógico do Banco de Dados (MySQL / MariaDB)
-- Atualizado em 05/10/2026 conforme as migrations do projeto e o DVP 7.0.
-- O bloco PREVISTAS no fim do arquivo ainda não foi implementado (ver docs/BACKLOG.md).

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  phone VARCHAR(20) NULL,
  birthday DATE NULL,
  preferred_contact VARCHAR(20) NULL COMMENT 'whatsapp | telefone | email',
  preferences TEXT NULL,
  role VARCHAR(20) NOT NULL DEFAULT 'cliente' COMMENT 'cliente | admin',
  active BOOLEAN NOT NULL DEFAULT TRUE,
  created_at DATETIME NULL,
  updated_at DATETIME NULL
);

CREATE TABLE vehicles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  plate VARCHAR(10) NOT NULL,
  model VARCHAR(100) NOT NULL,
  color VARCHAR(50) NOT NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE services (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  type VARCHAR(20) NOT NULL DEFAULT 'principal' COMMENT 'principal | extra',
  description TEXT NULL,
  price DECIMAL(10,2) NOT NULL,
  duration_minutes INT NOT NULL,
  blocks_period BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'ocupa o periodo inteiro',
  active BOOLEAN NOT NULL DEFAULT TRUE,
  created_at DATETIME NULL,
  updated_at DATETIME NULL
);

CREATE TABLE appointments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  vehicle_id INT NOT NULL,
  service_id INT NOT NULL,
  scheduled_at DATETIME NOT NULL,
  period VARCHAR(10) NOT NULL DEFAULT 'noite' COMMENT 'manha | tarde | noite',
  status VARCHAR(20) NOT NULL DEFAULT 'pendente' COMMENT 'pendente | confirmado | concluido | cancelado',
  notes TEXT NULL,
  admin_notes TEXT NULL,
  reminder_1day_sent BOOLEAN NOT NULL DEFAULT FALSE,
  reminder_1hour_sent BOOLEAN NOT NULL DEFAULT FALSE,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (vehicle_id) REFERENCES vehicles(id),
  FOREIGN KEY (service_id) REFERENCES services(id)
);

CREATE TABLE appointment_extras (
  id INT AUTO_INCREMENT PRIMARY KEY,
  appointment_id INT NOT NULL,
  service_id INT NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  UNIQUE (appointment_id, service_id),
  FOREIGN KEY (appointment_id) REFERENCES appointments(id),
  FOREIGN KEY (service_id) REFERENCES services(id)
);

CREATE TABLE client_notes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  client_id INT NOT NULL,
  admin_id INT NOT NULL,
  content TEXT NOT NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  FOREIGN KEY (client_id) REFERENCES users(id),
  FOREIGN KEY (admin_id) REFERENCES users(id)
);

CREATE TABLE follow_ups (
  id INT AUTO_INCREMENT PRIMARY KEY,
  client_id INT NOT NULL,
  type VARCHAR(255) NOT NULL COMMENT 'inativo | retorno | aniversario | personalizado',
  title VARCHAR(255) NOT NULL,
  description TEXT NULL,
  due_date DATE NOT NULL,
  completed BOOLEAN NOT NULL DEFAULT FALSE,
  completed_at DATETIME NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  FOREIGN KEY (client_id) REFERENCES users(id)
);

CREATE TABLE settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  `key` VARCHAR(50) NOT NULL UNIQUE COMMENT 'ex.: max_simple_per_period',
  value VARCHAR(255) NOT NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL
);

CREATE TABLE password_reset_codes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) NOT NULL,
  code VARCHAR(6) NOT NULL,
  expires_at DATETIME NOT NULL,
  created_at DATETIME NULL
);

-- ============ PREVISTAS ============

CREATE TABLE washes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  appointment_id INT NULL COMMENT 'nulo = lavagem avulsa',
  user_id INT NOT NULL,
  vehicle_id INT NOT NULL,
  service_id INT NOT NULL,
  amount DECIMAL(10,2) NOT NULL COMMENT 'valor cobrado',
  notes TEXT NULL,
  performed_at DATETIME NOT NULL,
  FOREIGN KEY (appointment_id) REFERENCES appointments(id),
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (vehicle_id) REFERENCES vehicles(id),
  FOREIGN KEY (service_id) REFERENCES services(id)
);

CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL UNIQUE,
  unit VARCHAR(10) NOT NULL,
  quantity DECIMAL(10,2) NOT NULL DEFAULT 0,
  min_quantity DECIMAL(10,2) NOT NULL,
  active BOOLEAN NOT NULL DEFAULT TRUE
);

CREATE TABLE stock_movements (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  type VARCHAR(10) NOT NULL COMMENT 'entrada | saida',
  quantity DECIMAL(10,2) NOT NULL,
  notes TEXT NULL,
  created_at DATETIME NOT NULL,
  FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Colunas previstas em users (B-05 e B-06 — LGPD, WhatsApp e anonimização)
ALTER TABLE users ADD COLUMN privacy_accepted_at DATETIME NULL COMMENT 'aceite da politica de privacidade';
ALTER TABLE users ADD COLUMN whatsapp_opt_in BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'autorizou avisos pelo WhatsApp';
ALTER TABLE users ADD COLUMN whatsapp_opt_in_at DATETIME NULL COMMENT 'data da ultima escolha sobre o WhatsApp';
ALTER TABLE users ADD COLUMN anonymized_at DATETIME NULL COMMENT 'preenchida quando o cliente exclui a conta';

-- Registro de cada envio de notificação, por canal (B-08)
CREATE TABLE notification_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  appointment_id INT NULL,
  user_id INT NOT NULL,
  channel VARCHAR(10) NOT NULL COMMENT 'email | whatsapp',
  type VARCHAR(30) NOT NULL COMMENT 'reminder_1day | reminder_1hour | status_confirmado | status_cancelado | status_concluido',
  status VARCHAR(10) NOT NULL COMMENT 'sent | failed',
  error TEXT NULL,
  created_at DATETIME NOT NULL,
  UNIQUE (appointment_id, channel, type),
  FOREIGN KEY (appointment_id) REFERENCES appointments(id),
  FOREIGN KEY (user_id) REFERENCES users(id)
);

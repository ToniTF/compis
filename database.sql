CREATE DATABASE IF NOT EXISTS compis_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE compis_app;

-- Tabla de Usuarios
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- Se almacenará el hash de la contraseña
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de Planes
CREATE TABLE IF NOT EXISTS plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL, -- Quién creó el plan
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    plan_date DATETIME NOT NULL,
    location VARCHAR(255) NOT NULL,
    max_capacity INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE -- Si se borra un usuario, se borran sus planes
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de Inscripciones a Planes (Relación Muchos-a-Muchos entre Usuarios y Planes)
CREATE TABLE IF NOT EXISTS plan_registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL, -- Usuario que se apunta al plan
    plan_id INT NOT NULL, -- Plan al que se apunta
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES plans(id) ON DELETE CASCADE,
    UNIQUE KEY unique_registration (user_id, plan_id) -- Un usuario no puede apuntarse dos veces al mismo plan
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Índices para mejorar el rendimiento de las búsquedas comunes
CREATE INDEX idx_plan_date ON plans(plan_date);
CREATE INDEX idx_plan_location ON plans(location);

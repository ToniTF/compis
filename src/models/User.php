<?php

class User {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Encuentra un usuario por su dirección de correo electrónico.
     *
     * @param string $email El correo electrónico del usuario.
     * @return array|false Los datos del usuario como un array asociativo, o false si no se encuentra.
     */
    public function findByEmail(string $email): array|false {
        try {
            $stmt = $this->pdo->prepare("SELECT id, email, password, created_at FROM users WHERE email = ?");
            $stmt->execute([$email]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // En una aplicación real, podrías loggear este error
            // error_log("Error en findByEmail: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Encuentra un usuario por su ID.
     *
     * @param int $id El ID del usuario.
     * @return array|false Los datos del usuario como un array asociativo, o false si no se encuentra.
     */
    public function findById(int $id): array|false {
        try {
            $stmt = $this->pdo->prepare("SELECT id, email, created_at FROM users WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // error_log("Error en findById: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crea un nuevo usuario en la base de datos.
     *
     * @param string $email El correo electrónico del usuario.
     * @param string $hashedPassword La contraseña ya hasheada del usuario.
     * @return int|false El ID del usuario recién creado, o false en caso de error.
     */
    public function create(string $email, string $hashedPassword): int|false {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
            $stmt->execute([$email, $hashedPassword]);
            return (int)$this->pdo->lastInsertId();
        } catch (PDOException $e) {
            // Si es un error de duplicado de email (código 23000 para MySQL)
            // if ($e->getCode() == 23000) {
            //     return false; // O lanzar una excepción específica
            // }
            // error_log("Error en create user: " . $e->getMessage());
            return false;
        }
    }

    // Podrías añadir más métodos aquí según sea necesario, por ejemplo:
    // - updatePassword(int $userId, string $newHashedPassword)
    // - deleteUser(int $userId)
    // - etc.
}

?>
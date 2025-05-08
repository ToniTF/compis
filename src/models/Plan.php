<?php

class Plan {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Crea un nuevo plan.
     *
     * @param int $userId ID del usuario creador.
     * @param string $title Título del plan.
     * @param string $description Descripción del plan.
     * @param string $planDate Fecha y hora del plan (formato YYYY-MM-DD HH:MM:SS).
     * @param string $location Lugar del plan.
     * @param int $maxCapacity Capacidad máxima del plan.
     * @return int|false El ID del plan recién creado, o false en caso de error.
     */
    public function create(int $userId, string $title, string $description, string $planDate, string $location, int $maxCapacity): int|false {
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO plans (user_id, title, description, plan_date, location, max_capacity) VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([$userId, $title, $description, $planDate, $location, $maxCapacity]);
            return (int)$this->pdo->lastInsertId();
        } catch (PDOException $e) {
            // error_log("Error al crear plan: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene los últimos N planes creados, con soporte para paginación.
     *
     * @param int $limit Número de planes a obtener por página.
     * @param int $offset Número de planes a saltar (para paginación).
     * @return array Lista de planes.
     */
    public function getLatestPlans(int $limit = 10, int $offset = 0): array {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT id, title, description, plan_date, location, max_capacity, created_at 
                 FROM plans 
                 ORDER BY created_at DESC 
                 LIMIT :limit OFFSET :offset"
            );
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // error_log("Error al obtener últimos planes: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene el número total de planes activos.
     *
     * @return int Número total de planes.
     */
    public function getTotalPlansCount(): int {
        try {
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM plans");
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            // error_log("Error al obtener el conteo total de planes: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtiene los detalles completos de un plan por su ID, incluyendo el email del creador.
     *
     * @param int $planId ID del plan.
     * @return array|false Detalles del plan o false si no se encuentra.
     */
    public function findByIdWithCreator(int $planId): array|false {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT p.id, p.user_id, p.title, p.description, p.plan_date, p.location, p.max_capacity, p.created_at, u.email AS creator_email 
                 FROM plans p 
                 JOIN users u ON p.user_id = u.id 
                 WHERE p.id = ?"
            );
            $stmt->execute([$planId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // error_log("Error al obtener plan por ID con creador: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene todos los participantes de un plan.
     *
     * @param int $planId ID del plan.
     * @return array Lista de emails de los participantes.
     */
    public function getPlanParticipants(int $planId): array {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT u.email 
                 FROM plan_registrations pr
                 JOIN users u ON pr.user_id = u.id
                 WHERE pr.plan_id = ?
                 ORDER BY pr.registration_date ASC"
            );
            $stmt->execute([$planId]);
            return $stmt->fetchAll(PDO::FETCH_COLUMN, 0); // Devuelve solo la columna email
        } catch (PDOException $e) {
            // error_log("Error al obtener participantes del plan: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Verifica si un usuario ya está apuntado a un plan.
     *
     * @param int $userId ID del usuario.
     * @param int $planId ID del plan.
     * @return bool True si ya está apuntado, false en caso contrario.
     */
    public function hasUserJoined(int $userId, int $planId): bool {
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM plan_registrations WHERE user_id = ? AND plan_id = ?");
            $stmt->execute([$userId, $planId]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            // error_log("Error al verificar si usuario está apuntado: " . $e->getMessage());
            return false; // Asumir que no está apuntado en caso de error
        }
    }

    /**
     * Obtiene el número actual de inscritos en un plan.
     *
     * @param int $planId ID del plan.
     * @return int Número de inscritos.
     */
    public function getParticipantCount(int $planId): int {
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM plan_registrations WHERE plan_id = ?");
            $stmt->execute([$planId]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            // error_log("Error al contar participantes: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Inscribe a un usuario en un plan.
     *
     * @param int $userId ID del usuario.
     * @param int $planId ID del plan.
     * @return bool True si la inscripción fue exitosa, false en caso contrario.
     */
    public function joinPlan(int $userId, int $planId): bool {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO plan_registrations (user_id, plan_id) VALUES (?, ?)");
            return $stmt->execute([$userId, $planId]);
        } catch (PDOException $e) {
            // error_log("Error al apuntarse al plan: " . $e->getMessage());
            // Podría ser un error de clave duplicada si se intenta insertar de nuevo (aunque ya validamos antes)
            return false;
        }
    }

    /**
     * Obtiene los planes creados por un usuario específico.
     *
     * @param int $userId ID del usuario.
     * @return array Lista de planes creados por el usuario.
     */
    public function getPlansByUserId(int $userId): array {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT id, title, description, plan_date, location, max_capacity, created_at 
                 FROM plans 
                 WHERE user_id = ? 
                 ORDER BY plan_date DESC"
            );
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // error_log("Error al obtener planes por usuario: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene los planes a los que un usuario se ha apuntado.
     *
     * @param int $userId ID del usuario.
     * @return array Lista de planes a los que el usuario se ha apuntado.
     */
    public function getJoinedPlansByUserId(int $userId): array {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT p.id, p.title, p.description, p.plan_date, p.location, p.max_capacity, p.created_at, u.email AS creator_email
                 FROM plans p
                 JOIN plan_registrations pr ON p.id = pr.plan_id
                 JOIN users u ON p.user_id = u.id
                 WHERE pr.user_id = ?
                 ORDER BY p.plan_date DESC"
            );
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // error_log("Error al obtener planes apuntados por usuario: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Permite a un usuario cancelar su asistencia a un plan.
     *
     * @param int $userId ID del usuario.
     * @param int $planId ID del plan.
     * @return bool True si la cancelación fue exitosa, false en caso contrario.
     */
    public function leavePlan(int $userId, int $planId): bool {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM plan_registrations WHERE user_id = ? AND plan_id = ?");
            return $stmt->execute([$userId, $planId]);
        } catch (PDOException $e) {
            // error_log("Error al cancelar asistencia al plan: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina un plan y todas sus inscripciones asociadas.
     * Solo el creador del plan puede eliminarlo.
     *
     * @param int $planId ID del plan a eliminar.
     * @param int $userId ID del usuario que intenta eliminar el plan.
     * @return bool True si la eliminación fue exitosa, false en caso contrario.
     */
    public function deletePlanById(int $planId, int $userId): bool {
        $this->pdo->beginTransaction();
        try {
            // Verificar si el usuario es el creador del plan
            $stmt = $this->pdo->prepare("SELECT user_id FROM plans WHERE id = ?");
            $stmt->execute([$planId]);
            $creatorId = $stmt->fetchColumn();

            if ($creatorId === false || $creatorId != $userId) {
                // No es el creador o el plan no existe
                $this->pdo->rollBack();
                return false;
            }

            // Eliminar inscripciones asociadas al plan
            $stmt = $this->pdo->prepare("DELETE FROM plan_registrations WHERE plan_id = ?");
            $stmt->execute([$planId]);

            // Eliminar el plan
            $stmt = $this->pdo->prepare("DELETE FROM plans WHERE id = ?");
            $result = $stmt->execute([$planId]);

            if ($result) {
                $this->pdo->commit();
                return true;
            } else {
                $this->pdo->rollBack();
                return false;
            }
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            // error_log("Error al eliminar el plan: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza un plan existente.
     * Solo el creador del plan puede editarlo.
     *
     * @param int $planId ID del plan a actualizar.
     * @param int $userId ID del usuario que intenta actualizar el plan.
     * @param string $title Nuevo título del plan.
     * @param string $description Nueva descripción del plan.
     * @param string $planDate Nueva fecha y hora del plan.
     * @param string $location Nuevo lugar del plan.
     * @param int $maxCapacity Nueva capacidad máxima del plan.
     * @return bool True si la actualización fue exitosa, false en caso contrario.
     */
    public function update(int $planId, int $userId, string $title, string $description, string $planDate, string $location, int $maxCapacity): bool {
        try {
            // Verificar si el usuario es el creador del plan
            $stmt = $this->pdo->prepare("SELECT user_id FROM plans WHERE id = ?");
            $stmt->execute([$planId]);
            $creatorId = $stmt->fetchColumn();

            if ($creatorId === false || $creatorId != $userId) {
                // No es el creador o el plan no existe
                return false;
            }

            $stmt = $this->pdo->prepare(
                "UPDATE plans 
                 SET title = ?, description = ?, plan_date = ?, location = ?, max_capacity = ?
                 WHERE id = ?"
            );
            return $stmt->execute([$title, $description, $planDate, $location, $maxCapacity, $planId]);
        } catch (PDOException $e) {
            // error_log("Error al actualizar plan: " . $e->getMessage());
            return false;
        }
    }

}
?>
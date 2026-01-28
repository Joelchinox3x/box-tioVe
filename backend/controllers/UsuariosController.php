<?php
/**
 * Controlador de Usuarios
 * Gestiona registro de espectadores y autenticación
 */
class UsuariosController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Registrar un nuevo espectador
     */
    public function registrarEspectador($data) {
        // Validar datos requeridos
        $required = ['nombre', 'email', 'password', 'telefono', 'club_id'];

        foreach ($required as $field) {
            if (!isset($data[$field]) || $data[$field] === '' || $data[$field] === null) {
                http_response_code(400);
                return [
                    "success" => false,
                    "message" => "El campo $field es requerido"
                ];
            }
        }

        // Validar email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            return [
                "success" => false,
                "message" => "Email inválido"
            ];
        }

        // Validar contraseña
        if (strlen($data['password']) < 6) {
            http_response_code(400);
            return [
                "success" => false,
                "message" => "La contraseña debe tener al menos 6 caracteres"
            ];
        }

        try {
            $this->db->beginTransaction();

            // Verificar si el email ya existe
            $queryCheck = "SELECT COUNT(*) as count FROM usuarios WHERE email = :email";
            $stmtCheck = $this->db->prepare($queryCheck);
            $stmtCheck->bindParam(':email', $data['email']);
            $stmtCheck->execute();
            $result = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if ($result['count'] > 0) {
                $this->db->rollBack();
                http_response_code(409);
                return [
                    "success" => false,
                    "message" => "Este email ya está registrado"
                ];
            }

            // Procesar foto_perfil si existe
            $foto_perfil = null;
            if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../files/usuarios';

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $fileTmp = $_FILES['foto_perfil']['tmp_name'];
                $fileName = basename($_FILES['foto_perfil']['name']);
                $imageInfo = getimagesize($fileTmp);

                if ($imageInfo !== false) {
                    $ext = pathinfo($fileName, PATHINFO_EXTENSION);
                    if (empty($ext)) $ext = 'jpg';

                    $newFileName = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                    $destination = $uploadDir . '/' . $newFileName;

                    if (move_uploaded_file($fileTmp, $destination)) {
                        $foto_perfil = 'files/usuarios/' . $newFileName;
                        error_log("✅ FOTO USUARIO SUBIDA: $foto_perfil");
                    } else {
                        error_log("❌ ERROR MOVIENDO ARCHIVO a $destination");
                    }
                } else {
                    error_log("❌ ERROR: El archivo no es una imagen válida");
                }
            }

            // Crear usuario espectador
            // tipo_id: 3 = espectador
            $queryUsuario = "INSERT INTO usuarios
                (nombre, apellidos, email, password_hash, telefono, tipo_id, club_id, foto_perfil)
                VALUES
                (:nombre, :apellidos, :email, :password, :telefono, 3, :club_id, :foto_perfil)";

            $stmtUsuario = $this->db->prepare($queryUsuario);
            $password_hash = password_hash($data['password'], PASSWORD_BCRYPT);
            $apellidos = $data['apellidos'] ?? null;
            $club_id = $data['club_id'] ?? null;

            $stmtUsuario->bindParam(':nombre', $data['nombre']);
            $stmtUsuario->bindParam(':apellidos', $apellidos);
            $stmtUsuario->bindParam(':email', $data['email']);
            $stmtUsuario->bindParam(':password', $password_hash);
            $stmtUsuario->bindParam(':telefono', $data['telefono']);
            $stmtUsuario->bindParam(':club_id', $club_id);
            $stmtUsuario->bindParam(':foto_perfil', $foto_perfil);
            $stmtUsuario->execute();

            $usuario_id = $this->db->lastInsertId();

            $this->db->commit();

            return [
                "success" => true,
                "message" => "Registro exitoso",
                "usuario_id" => $usuario_id,
                "foto_perfil" => $foto_perfil
            ];

        } catch (PDOException $e) {
            $this->db->rollBack();

            error_log("Error en registro de espectador: " . $e->getMessage());
            error_log("Trace: " . $e->getTraceAsString());

            if ($e->getCode() == 23000) {
                http_response_code(409);
                return [
                    "success" => false,
                    "message" => "El email ya está registrado"
                ];
            }

            http_response_code(500);
            return [
                "success" => false,
                "message" => "Error al procesar el registro",
                "error_detail" => $e->getMessage()
            ];
        }
    }

    /**
     * Iniciar sesión
     */
    public function login($data) {
        // Validar datos requeridos
        if (!isset($data['email']) || !isset($data['password'])) {
            http_response_code(400);
            return [
                "success" => false,
                "message" => "Email y contraseña son requeridos"
            ];
        }

        try {
            // Buscar usuario por email
            $query = "SELECT u.*, t.nombre as tipo_nombre
                FROM usuarios u
                JOIN tipos_usuario t ON u.tipo_id = t.id
                WHERE u.email = :email AND u.estado = 'activo'";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':email', $data['email']);
            $stmt->execute();
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$usuario) {
                http_response_code(401);
                return [
                    "success" => false,
                    "message" => "Email o contraseña incorrectos"
                ];
            }

            // Verificar contraseña
            if (!password_verify($data['password'], $usuario['password_hash'])) {
                http_response_code(401);
                return [
                    "success" => false,
                    "message" => "Email o contraseña incorrectos"
                ];
            }

            // Remover password_hash de la respuesta
            unset($usuario['password_hash']);

            // Si es peleador, obtener datos adicionales
            if ($usuario['tipo_id'] == 2) {
                $queryPeleador = "SELECT * FROM peleadores WHERE usuario_id = :usuario_id";
                $stmtPeleador = $this->db->prepare($queryPeleador);
                $stmtPeleador->bindParam(':usuario_id', $usuario['id']);
                $stmtPeleador->execute();
                $peleador = $stmtPeleador->fetch(PDO::FETCH_ASSOC);
                $usuario['peleador'] = $peleador;
            }

            return [
                "success" => true,
                "message" => "Inicio de sesión exitoso",
                "usuario" => $usuario,
                "token" => bin2hex(random_bytes(32)) // Token simple para ejemplo
            ];

        } catch (PDOException $e) {
            error_log("Error en login: " . $e->getMessage());

            http_response_code(500);
            return [
                "success" => false,
                "message" => "Error al procesar la solicitud"
            ];
        }
    }

    /**
     * Obtener usuario por ID
     */
    public function getById($id) {
        try {
            // Buscar usuario por ID
            $query = "SELECT u.*, t.nombre as tipo_nombre
                FROM usuarios u
                JOIN tipos_usuario t ON u.tipo_id = t.id
                WHERE u.id = :id";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$usuario) {
                http_response_code(404);
                return [
                    "success" => false,
                    "message" => "Usuario no encontrado"
                ];
            }

            // Remover password_hash
            unset($usuario['password_hash']);

            // Si es peleador, obtener datos adicionales
            if ($usuario['tipo_id'] == 2) {
                $queryPeleador = "SELECT * FROM peleadores WHERE usuario_id = :usuario_id";
                $stmtPeleador = $this->db->prepare($queryPeleador);
                $stmtPeleador->bindParam(':usuario_id', $usuario['id']);
                $stmtPeleador->execute();
                $peleador = $stmtPeleador->fetch(PDO::FETCH_ASSOC);
                $usuario['peleador'] = $peleador;
            }

            // Si es Club (tipo 1 o 3, depende implementacion, aqui lo dejamos simple)
            // Futuro: agregar info club si es admin de club

            return [
                "success" => true,
                "usuario" => $usuario
            ];

        } catch (PDOException $e) {
            error_log("Error obteniendo usuario $id: " . $e->getMessage());
            http_response_code(500);
            return [
                "success" => false,
                "message" => "Error al obtener perfil"
            ];
        }
    }

    /**
     * Verificar si un email ya existe
     */
    public function verificarEmail($email) {
        $query = "SELECT COUNT(*) as count FROM usuarios WHERE email = :email";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            "success" => true,
            "disponible" => $result['count'] == 0,
            "mensaje" => $result['count'] == 0
                ? "Email disponible"
                : "Este email ya está registrado"
        ];
    }
}

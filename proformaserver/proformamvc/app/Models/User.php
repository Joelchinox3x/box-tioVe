<?php
// app/Models/User.php

namespace App\Models;

use Core\Model;

class User extends Model {
    protected $table = 'users';

    // Buscar usuario por username
    public function findByUsername($username) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    // Buscar usuario por email
    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    // Verificar si el usuario está activo
    public function isActive($userId) {
        $user = $this->find($userId);
        return $user && $user['activo'] == 1;
    }

    // Actualizar último acceso
    public function updateLastAccess($userId) {
        $sql = "UPDATE {$this->table} SET ultimo_acceso = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$userId]);
    }

    // Verificar credenciales
    public function verifyCredentials($username, $password) {
        $user = $this->findByUsername($username);

        if (!$user) {
            return false;
        }

        // Verificar contraseña
        if (password_verify($password, $user['password'])) {
            return $user;
        }

        return false;
    }

    // Crear usuario con contraseña hasheada
    public function createUser($data) {
        // Hashear la contraseña
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        return $this->create($data);
    }

    // Contar usuarios totales
    public function countUsers() {
        return $this->count();
    }
}

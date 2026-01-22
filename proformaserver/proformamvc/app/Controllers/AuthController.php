<?php
// app/Controllers/AuthController.php

namespace App\Controllers;

use Core\Controller;
use App\Models\User;

class AuthController extends Controller {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    // Mostrar formulario de login
    public function showLogin() {
        // Si ya está autenticado, redirigir al home
        if ($this->isAuthenticated()) {
            $this->redirect('/');
        }

        // Verificar si el registro está habilitado para mostrar/ocultar el enlace
        $settingModel = new \App\Models\Setting();
        $registrationEnabled = $settingModel->get('enable_registration', '1');

        $this->view('auth/login', [
            'page_title' => 'Iniciar Sesión',
            'error' => $this->get('error'),
            'success' => $this->get('success'),
            'registrationEnabled' => $registrationEnabled
        ]);
    }

    // Procesar login
    public function login() {
        // Validar datos
        $errors = $this->validate($this->post(), [
            'username' => 'required',
            'password' => 'required'
        ]);

        if (!empty($errors)) {
            return $this->redirect('/login', ['error' => 'Usuario y contraseña son requeridos']);
        }

        $username = $this->post('username');
        $password = $this->post('password');

        // Verificar credenciales
        $user = $this->userModel->verifyCredentials($username, $password);

        if (!$user) {
            return $this->redirect('/login', ['error' => 'Usuario o contraseña incorrectos']);
        }

        // Verificar si el usuario está activo
        if ($user['activo'] != 1) {
            return $this->redirect('/login', ['error' => 'Usuario inactivo. Contacte al administrador']);
        }

        // Actualizar último acceso
        $this->userModel->updateLastAccess($user['id']);

        // Guardar en sesión
        $this->setSession('user_id', $user['id']);
        $this->setSession('username', $user['username']);
        $this->setSession('nombre', $user['nombre']);
        $this->setSession('rol', $user['rol']);
        $this->setSession('authenticated', true);

        // Redirigir al home
        $this->redirect('/');
    }

    // Mostrar formulario de registro
    public function showRegister() {
        // Verificar si el registro está habilitado
        $settingModel = new \App\Models\Setting();
        $registrationEnabled = $settingModel->get('enable_registration', '1');

        // Debug: registrar el valor obtenido
        error_log("Registration enabled value: " . var_export($registrationEnabled, true));

        // Usar comparación no estricta para manejar string y números
        if ($registrationEnabled != '1' && $registrationEnabled != 1) {
            return $this->redirect('/login', ['error' => 'El registro de nuevos usuarios está deshabilitado.']);
        }

        // Si ya está autenticado, redirigir al home
        if ($this->isAuthenticated()) {
            $this->redirect('/');
        }

        $this->view('auth/register', [
            'page_title' => 'Registro',
            'error' => $this->get('error')
        ]);
    }

    // Procesar registro
    public function register() {
        // Verificar si el registro está habilitado
        $settingModel = new \App\Models\Setting();
        $registrationEnabled = $settingModel->get('enable_registration', '1');

        // Usar comparación no estricta para manejar string y números
        if ($registrationEnabled != '1' && $registrationEnabled != 1) {
            return $this->redirect('/login', ['error' => 'El registro de nuevos usuarios está deshabilitado.']);
        }

        // Validar datos
        $errors = $this->validate($this->post(), [
            'nombre' => 'required',
            'email' => 'required|email',
            'username' => 'required|min:3',
            'password' => 'required|min:6',
            'password_confirm' => 'required|min:6'
        ]);

        if (!empty($errors)) {
            return $this->redirect('/register', ['error' => 'Todos los campos son requeridos']);
        }

        $nombre = $this->post('nombre');
        $email = $this->post('email');
        $username = $this->post('username');
        $password = $this->post('password');
        $passwordConfirm = $this->post('password_confirm');

        // Verificar que las contraseñas coincidan
        if ($password !== $passwordConfirm) {
            return $this->redirect('/register', ['error' => 'Las contraseñas no coinciden']);
        }

        // Verificar que el username no exista
        $existingUser = $this->userModel->findByUsername($username);
        if ($existingUser) {
            return $this->redirect('/register', ['error' => 'El nombre de usuario ya está en uso']);
        }

        // Verificar que el email no exista
        $existingEmail = $this->userModel->findByEmail($email);
        if ($existingEmail) {
            return $this->redirect('/register', ['error' => 'El email ya está registrado']);
        }

        // Crear el usuario
        $userId = $this->userModel->create([
            'username' => $username,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'nombre' => $nombre,
            'email' => $email,
            'rol' => 'user',
            'activo' => 1
        ]);

        if ($userId) {
            // Redirigir al login con mensaje de éxito
            return $this->redirect('/login', ['success' => 'Usuario creado correctamente. Ya puedes iniciar sesión.']);
        } else {
            return $this->redirect('/register', ['error' => 'Error al crear el usuario. Inténtalo de nuevo.']);
        }
    }

    // Cerrar sesión
    public function logout() {
        // Destruir sesión
        session_unset();
        session_destroy();

        // Redirigir al login
        $this->redirect('/login', ['success' => 'Sesión cerrada correctamente']);
    }

    // Verificar si está autenticado
    private function isAuthenticated() {
        return $this->session('authenticated') === true;
    }
}

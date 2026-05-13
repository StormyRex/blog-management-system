<?php

namespace Shive\BlogManagementSystem\Controllers;

use Shive\BlogManagementSystem\Models\UserModel;
use Shive\BlogManagementSystem\Validators\AuthValidator;

class AuthController extends BaseController
{
    public function login(): void
    {
        $errors = [];
        $old = [
            'identity' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'identity' => trim($_POST['identity'] ?? ''),
                'password' => $_POST['password'] ?? ''
            ];

            $old['identity'] = $data['identity'];

            $errors = AuthValidator::validateLogin($data);

            if (!$errors) {
                try {
                    $model = new UserModel();
                    $isEmail = filter_var(
                        $data['identity'],
                        FILTER_VALIDATE_EMAIL
                    ) !== false;

                    $user = $isEmail
                        ? $model->findByEmail($data['identity'])
                        : $model->findByUsername($data['identity']);

                    if (!$user || !password_verify($data['password'], $user['password'])) {
                        $errors['credentials'] = 'Invalid email/username or password.';
                    } else {
                        if (session_status() === PHP_SESSION_NONE) {
                            session_start();
                        }

                        $_SESSION['user'] = [
                            'id' => $user['id'],
                            'name' => $user['name'],
                            'email' => $user['email']
                        ];

                        $baseUrl = defined('BASE_URL') ? BASE_URL : '';
                        header('Location: ' . $baseUrl . '/user/profile');
                        exit;
                    }
                } catch (\Throwable $exception) {
                    $errors['general'] = 'Unable to login right now. Please try again.';
                }
            }
        }

        $this->view('auth/login', [
            'errors' => $errors,
            'old' => $old
        ]);
    }

    public function register(): void
    {
        $errors = [];
        $old = [
            'name' => '',
            'email' => '',
            'username' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => trim($_POST['name'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'username' => trim($_POST['username'] ?? ''),
                'password' => $_POST['password'] ?? '',
                'confirm_password' => $_POST['confirm_password'] ?? ''
            ];

            $old['name'] = $data['name'];
            $old['email'] = $data['email'];
            $old['username'] = $data['username'];

            $errors = AuthValidator::validateRegister($data);

            if (!$errors) {
                try {
                    $model = new UserModel();

                    if ($model->findByEmail($data['email'])) {
                        $errors['email'] = 'Email is already registered.';
                    }

                    if ($model->findByUsername($data['username'])) {
                        $errors['username'] = 'Username is already taken.';
                    }

                    if (!$errors) {
                        $hashedPassword = password_hash(
                            $data['password'],
                            PASSWORD_DEFAULT
                        );

                        $model->createUser([
                            'name' => $data['name'],
                            'email' => $data['email'],
                            'username' => $data['username'],
                            'password' => $hashedPassword
                        ]);

                        $baseUrl = defined('BASE_URL') ? BASE_URL : '';
                        header('Location: ' . $baseUrl . '/login');
                        exit;
                    }
                } catch (\Throwable $exception) {
                    $errors['general'] = 'Unable to register right now. Please try again.';
                }
            }
        }

        $this->view('auth/register', [
            'errors' => $errors,
            'old' => $old
        ]);
    }

    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();

        $baseUrl = defined('BASE_URL') ? BASE_URL : '';
        header('Location: ' . $baseUrl . '/');
        exit;
    }
}

<?php

namespace Shive\BlogManagementSystem\Validators;

use Shive\BlogManagementSystem\Helpers\PatternHelper;

class AuthValidator
{
    public static function validateRegister(array $data): array
    {
        $errors = [];

        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $username = trim($data['username'] ?? '');
        $password = $data['password'] ?? '';
        $confirmPassword = $data['confirm_password'] ?? '';

        if ($name === '') {
            $errors['name'] = 'Name is required.';
        }

        if ($email === '') {
            $errors['email'] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address.';
        }

        if ($username === '') {
            $errors['username'] = 'Username is required.';
        } elseif (!preg_match(PatternHelper::username(), $username)) {
            $errors['username'] = 'Username must be 3-30 characters and use letters, numbers, or underscores.';
        }

        if ($password === '') {
            $errors['password'] = 'Password is required.';
        } elseif (strlen($password) < 6) {
            $errors['password'] = 'Password must be at least 6 characters.';
        }

        if ($confirmPassword === '') {
            $errors['confirm_password'] = 'Please confirm your password.';
        } elseif ($password !== $confirmPassword) {
            $errors['confirm_password'] = 'Passwords do not match.';
        }

        return $errors;
    }

    public static function validateLogin(array $data): array
    {
        $errors = [];

        $identity = trim($data['identity'] ?? '');
        $password = $data['password'] ?? '';

        if ($identity === '') {
            $errors['identity'] = 'Email or username is required.';
        }

        if ($password === '') {
            $errors['password'] = 'Password is required.';
        }

        return $errors;
    }
}

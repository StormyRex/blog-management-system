<?php

namespace Shive\BlogManagementSystem\Controllers;

class AuthController extends BaseController
{
    public function login(): void
    {
        $this->view('auth/login');
    }

    public function register(): void
    {
        $this->view('auth/register');
    }
}

<?php

namespace Shive\BlogManagementSystem\Controllers;

class UserController extends BaseController
{
    public function dashboard(): void
    {
        $this->view('user/dashboard');
    }

    public function profile(): void
    {
        $this->view('user/profile');
    }
}

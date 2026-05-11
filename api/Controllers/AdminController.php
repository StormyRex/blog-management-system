<?php

namespace Shive\BlogManagementSystem\Controllers;

class AdminController extends BaseController
{
    public function dashboard(): void
    {
        $this->view('admin/dashboard');
    }
}

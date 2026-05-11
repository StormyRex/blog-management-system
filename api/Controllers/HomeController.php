<?php

namespace Shive\BlogManagementSystem\Controllers;

class HomeController extends BaseController
{
    public function index(): void
    {
        $this->view('home');
    }
}

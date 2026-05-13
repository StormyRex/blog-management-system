<?php

namespace Shive\BlogManagementSystem\Controllers;

class HomeController extends BaseController
{
    public function index(): void
    {
        $this->view('home');
    }

    public function explore(): void
    {
        $this->view('explore');
    }

    public function contact(): void
    {
        $this->view('contact');
    }
}

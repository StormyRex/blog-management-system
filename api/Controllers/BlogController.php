<?php

namespace Shive\BlogManagementSystem\Controllers;

class BlogController extends BaseController
{
    public function myBlogs(): void
    {
        $this->view('blogs/my-blogs');
    }

    public function create(): void
    {
        $this->view('blogs/create');
    }
}

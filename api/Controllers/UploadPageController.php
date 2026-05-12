<?php

namespace Shive\BlogManagementSystem\Controllers;

class UploadPageController extends BaseController
{
    public function index(): void
    {
        $this->view('upload-page');
    }
}

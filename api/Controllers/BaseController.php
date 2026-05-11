<?php

namespace Shive\BlogManagementSystem\Controllers;

class BaseController
{
    protected function view(string $view, array $data = []): void
    {
        if (strpos($view, '..') !== false) {
            $this->renderNotFound();
            return;
        }

        $rootPath = dirname(__DIR__, 2);
        $viewPath = $rootPath . '/pages/' . ltrim($view, '/') . '.php';

        if ($data) {
            extract($data, EXTR_SKIP);
        }

        if (is_file($viewPath)) {
            require $viewPath;
            return;
        }

        $this->renderNotFound();
    }

    protected function renderNotFound(): void
    {
        $rootPath = dirname(__DIR__, 2);
        $errorPath = $rootPath . '/pages/errors/404.php';
        http_response_code(404);

        if (is_file($errorPath)) {
            require $errorPath;
            return;
        }

        echo '404 Not Found';
    }
}

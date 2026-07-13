<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Http\Response;

/**
 * BaseController — shared rendering helpers for all controllers.
 * Every controller extends this. No business logic lives here.
 */
abstract class BaseController
{
    /**
     * Render a view inside a layout and return a Response.
     *
     * @param string $view     Dot-notation path relative to app/Views/  e.g. 'frontend.webcast.landing'
     * @param array  $data     Variables extracted into the view scope
     * @param string $layout   Layout file relative to app/Views/  e.g. 'frontend/layouts/main'
     * @param int    $status   HTTP status code
     */
    protected function view(
        string $view,
        array  $data   = [],
        string $layout = 'frontend/layouts/main',
        int    $status = 200
    ): Response {
        $viewFile   = APP_PATH . '/Views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewFile)) {
            throw new \Core\Exceptions\HttpException(500, "View not found: $viewFile");
        }

        // Extract data into local scope for the view
        extract($data, EXTR_SKIP);

        // Render inner view content
        ob_start();
        include $viewFile;
        $content = ob_get_clean();

        // If no layout specified, return content directly
        if (empty($layout)) {
            return Response::make($content, $status);
        }

        $layoutFile = APP_PATH . '/Views/' . $layout . '.php';

        if (!file_exists($layoutFile)) {
            return Response::make($content, $status);
        }

        // Render layout with $content available inside it
        ob_start();
        include $layoutFile;
        $html = ob_get_clean();

        return Response::make($html ?: '', $status);
    }

    /**
     * Render a partial view snippet (no layout).
     */
    protected function partial(string $view, array $data = []): string
    {
        $file = APP_PATH . '/Views/' . str_replace('.', '/', $view) . '.php';
        if (!file_exists($file)) {
            return '';
        }
        extract($data, EXTR_SKIP);
        ob_start();
        include $file;
        return ob_get_clean() ?: '';
    }
}

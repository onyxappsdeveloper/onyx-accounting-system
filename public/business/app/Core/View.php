<?php
/**
 * ONYX Accounting System - View Class
 * Handles template rendering
 */

namespace App\Core;

class View
{
    private $view;
    private $data = [];

    /**
     * Constructor
     */
    public function __construct($view, $data = [])
    {
        $this->view = $view;
        $this->data = $data;
    }

    /**
     * Magic toString method
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * Render view
     */
    public function render()
    {
        $viewPath = APP_PATH . 'Views/' . str_replace('.', '/', $this->view) . '.php';

        if (!file_exists($viewPath)) {
            throw new \Exception("View not found: $this->view");
        }

        extract($this->data);
        ob_start();
        include $viewPath;
        $content = ob_get_clean();

        // Load layout
        if (isset($this->data['layout']) && $this->data['layout']) {
            $layoutPath = APP_PATH . 'Views/layouts/' . $this->data['layout'] . '.php';
            if (file_exists($layoutPath)) {
                ob_start();
                include $layoutPath;
                $content = ob_get_clean();
            }
        }

        return $content;
    }
}

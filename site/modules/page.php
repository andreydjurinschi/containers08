<?php

namespace site\modules\page;
    class Page{
        public $layout = 'layout';
        public $viewDirectory;

        public function __construct($layout, $viewDirectory) {
            $this->$layout = $viewDirectory . '/' . $layout . '.php';
            $this->viewDirectory = $viewDirectory;
        }

        public function render($templateName, $data = []) {
            extract($data);
            ob_start();
            include $this->viewDirectory . '/' . $templateName . '.php';
            $content = ob_get_clean();
            include $this->layout;
        }
    }
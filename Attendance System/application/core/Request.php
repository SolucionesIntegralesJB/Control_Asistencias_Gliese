<?php
// Request Class - Handles URL parsing
class Request {
    
    private $controller;
    private $method;
    private $arguments;

    public function __construct() {
        if (isset($_GET['url'])) {
            $url = filter_input(INPUT_GET, 'url', FILTER_SANITIZE_URL);
            $url = urldecode($url);
            $url = explode('/', $url);
            if (is_array($url)) {
                $url = array_map('trim', $url);
                $url = array_filter($url, static function ($segment) {
                    return $segment !== '';
                });
            }
            
            $this->controller = array_shift($url);
            $this->method = array_shift($url);
            $this->arguments = $url;
        }

        if (!$this->controller) {
            $this->controller = DEFAULT_CONTROLLER;
        } else {
            $this->controller = trim($this->controller);
        }

        if (!$this->method) {
            $this->method = 'index';
        } else {
            $this->method = trim($this->method);
        }

        if (!isset($this->arguments)) {
            $this->arguments = array();
        }
    }

    public function get_controller() {
        return $this->controller;
    }

    public function get_method() {
        return $this->method;
    }

    public function get_arguments() {
        return $this->arguments;
    }
}

<?php
// Controller Class - Base class for all controllers
abstract class Controller {
    
    protected $view;
    protected $functions;
    protected $session;

    public function __construct() {
        $this->view = new View(new Request);
        $this->functions = new Functions();
        $this->session = new Session();
    }

    abstract public function index();

    public function load_model($model) {
        $model = 'M_' . $model;
        $route_model = ROOT . 'application' . DS . 'models' . DS . $model . '.php';

        if (is_readable($route_model)) {
            require_once $route_model;
            $model = new $model;
            return $model;
        } else {
            throw new Exception('Error of model');
        }
    }
}

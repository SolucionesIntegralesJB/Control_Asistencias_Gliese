<?php
// View Class - Handles view rendering
class View {
    
    private $controller;
    private $js;
    private $data;

    public function __construct(Request $request) {
        $this->controller = strtolower($request->get_controller());
        $this->js = array();
        $this->data = array();
    }

    public function set_view($view, $partial = false) {
        $js = array();
        $data = array();

        if (count($this->js)) {
            $js = $this->js; 
        }

        if (count($this->data)) {
            $data = $this->data;
        }

        $params = array(
            'js' => $js
        );

        if (is_array($data) && count($data)) {
            extract($data, EXTR_SKIP);
        }
        
        $route_view = ROOT . 'application/views' . DS . $this->controller . DS . $view . '.php';
        
        if ($view === 'default') {
            $route_view = ROOT . 'application/views' . DS . $this->controller . DS . 'index' . '.php';
            if (is_readable($route_view)) {
                include_once $route_view;
            } else {
                throw new Exception('Error of view');
            }
        } else {
            if (is_readable($route_view)) {
                if (!$partial) {
                    include_once ROOT . 'application/views' . DS . 'layouts' . DS . DEFAULT_LAYOUT . DS . 'head.php';
                    include_once ROOT . 'application/views' . DS . 'layouts' . DS . DEFAULT_LAYOUT . DS . 'header.php';
                    include_once ROOT . 'application/views' . DS . 'layouts' . DS . DEFAULT_LAYOUT . DS . 'footer.php';
                    include_once $route_view;
                } else {
                    include_once $route_view;
                }
            } else {
                throw new Exception('Error of view');
            }
        }        
    }

    public function set_js($js) {
        if ($js) {
            $version = filemtime(ROOT . 'application/views/' . $this->controller . '/js/' . $js . '.js');
            $this->js[] = BASE_URL . 'application/views/' . $this->controller . '/js/' . $js . '.js?v=' . $version;
        } else {
            throw new Exception('Error of js');
        }
    }

    public function set_data($data) {
        if (is_array($data)) {
            $this->data = $data;
        } else {
            throw new Exception('Error of params');
        }
    }
}

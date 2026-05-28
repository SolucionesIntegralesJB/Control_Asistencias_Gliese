<?php
// Functions Class - Helper functions
class Functions {
    
    public function encrypt_password($string) {
        // Usar password_hash para hashing de contraseñas (buena práctica de seguridad)
        // Este método ahora usa password_hash() estándar de PHP
        return password_hash($string, PASSWORD_DEFAULT);
    }

    public function validate_session($val) {
        if (!$val) {
            header('Location: ' . BASE_URL);
            die();
            exit();
        }
    }

    public function check_session($val) {
        if ($val) {
            header('Location: ' . BASE_URL . 'Dashboard');
        }
    }

    public function clean_string($string) {
        $string = trim($string);
        $string = strip_tags($string);
        $string = htmlspecialchars($string); 
        return $string;
    }

    public function exit_app() {
        header('Location: ' . BASE_URL);
        die();
        exit();
    }
}

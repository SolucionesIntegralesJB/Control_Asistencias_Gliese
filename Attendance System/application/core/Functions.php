<?php
// Functions Class - Helper functions
class Functions {
    
    public function encrypt_password($string) {
        $salt = '$6$rOuNdS=75fd3@15f%9f&ds8$s@l/$';
        $data = bin2hex(base64_encode(md5(crypt($string, $salt))));
        return $data;
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

<?php
// Session Class - Handles session management for Attendance System
class Session {
    
    private $session_name = 'ATTENDANCE_SESSION';
    
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_name($this->session_name);
            session_start();
        }
    }

    public function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    public function get($key) {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }

    public function remove($key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    public function destroy() {
        $_SESSION = array();
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 42000, '/');
        }
        session_destroy();
    }

    public function exists($key) {
        return isset($_SESSION[$key]);
    }
}

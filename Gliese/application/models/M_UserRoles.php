<?php 
// --
class M_UserRoles extends Model {
    // --
    public function __construct() {
        parent::__construct();
    }
    
    // --
    public function get_user_roles() {
        // --
        try {
            // --
            $sql = 'SELECT 
                    id,
                    description,
                    status
                FROM user_roles WHERE status = 1';
            // --
            $result = $this->pdo->fetchAll($sql);
            // --
            if ($result) {
                // --
                $response = array('status' => 'OK', 'result' => $result);
            } else {
                // --
                $response = array('status' => 'ERROR', 'result' => array());
            }
        } catch (PDOException $e) {
            // --
            $response = array('status' => 'EXCEPTION', 'result' => $e);
        }
        // --
        return $response;
    }

    // --
    public function get_user_role($bind) {
        // --
        try {
            // --
            $sql = 'SELECT 
                    id,
                    description,
                    status
                FROM user_roles WHERE id = :id_user_role AND status = 1';
            // --
            $result = $this->pdo->fetchOne($sql, $bind);
            // --
            if ($result) {
                // --
                $response = array('status' => 'OK', 'result' => $result);
            } else {
                // --
                $response = array('status' => 'ERROR', 'result' => array());
            }
        } catch (PDOException $e) {
            // --
            $response = array('status' => 'EXCEPTION', 'result' => $e);
        }
        // --
        return $response;
    }

    // --
    public function get_user_role_by_description($bind) {
        try {
            $sql = 'SELECT id, description, status FROM user_roles WHERE UPPER(TRIM(description)) = UPPER(TRIM(:description)) LIMIT 1';
            $result = $this->pdo->fetchOne($sql, $bind);
            if ($result) {
                $response = array('status' => 'OK', 'result' => $result);
            } else {
                $response = array('status' => 'ERROR', 'result' => array());
            }
        } catch (PDOException $e) {
            $response = array('status' => 'EXCEPTION', 'result' => $e);
        }
        return $response;
    }

    // --
    public function create_user_role($bind) {
        // --
        try {
            // --
            $sql = 'INSERT INTO user_roles (description) VALUES (:description)';
            // --
            $bind_role = array('description' => $bind['description']);
            // --
            $result = $this->pdo->perform($sql, $bind_role);
            // --
            if ($result) {
                $id_user_role = $this->pdo->lastInsertId();
                $response = array('status' => 'OK', 'result' => array('id' => $id_user_role));
            } else {
                $response = array('status' => 'ERROR', 'result' => array());
            }
        } catch (PDOException $e) {
            $response = array('status' => 'EXCEPTION', 'result' => $e);
        }
        // --
        return $response;
    }

    // --
    public function update_user_role($bind) {
        // --
        try {
            // --
            $sql = 'UPDATE user_roles 
                    SET 
                        description = :description
                    WHERE id = :id_user_role';
            // --
            $bind_role = array(
                'id_user_role' => $bind['id_user_role'],
                'description' => $bind['description']
            );
            // --
            $result = $this->pdo->perform($sql, $bind_role);
            // --
            if ($result) {
                $response = array('status' => 'OK', 'result' => array());
            } else {
                $response = array('status' => 'ERROR', 'result' => array());
            }
        } catch (PDOException $e) {
            $response = array('status' => 'EXCEPTION', 'result' => $e);
        }
        // --
        return $response;
    }

    // --
    public function delete_user_role($bind) {
        // --
        try {
            // --
            $sql = 'DELETE FROM user_roles where id = :id_user_role';
            // --
            $result = $this->pdo->perform($sql, $bind);
            // --
            if ($result) {
                $response = array('status' => 'OK', 'result' => array());
            } else {
                $response = array('status' => 'ERROR', 'result' => array());
            }
        } catch (PDOException $e) {
            // --
            $response = array('status' => 'EXCEPTION', 'result' => $e);
        }
        // --
        return $response;
    }

    // ============================================
    // MÉTODOS PARA GESTIÓN DE PERMISOS
    // ============================================

    // -- Obtener permisos de un user_role específico
    public function get_permissions_by_user_role($bind) {
        try {
            $sql = 'SELECT
                        p.id,
                        p.id_user_role,
                        p.id_sub_menu,
                        p.status,
                        sm.description as sub_menu_description,
                        sm.url,
                        m.id as id_menu,
                        m.description as menu_description
                    FROM permission_user_roles p
                    INNER JOIN sub_menu sm ON sm.id = p.id_sub_menu
                    INNER JOIN menu m ON m.id = sm.id_menu
                    WHERE p.id_user_role = :id_user_role
                    ORDER BY m.order, sm.order';

            $result = $this->pdo->fetchAll($sql, $bind);

            if ($result) {
                $response = array('status' => 'OK', 'result' => $result);
            } else {
                $response = array('status' => 'ERROR', 'result' => array());
            }
        } catch (PDOException $e) {
            $response = array('status' => 'EXCEPTION', 'result' => $e);
        }
        return $response;
    }

    // -- Eliminar todos los permisos de un user_role
    public function delete_all_permissions($id_user_role) {
        try {
            $sql = 'DELETE FROM permission_user_roles WHERE id_user_role = :id_user_role';
            $bind = array('id_user_role' => $id_user_role);
            $result = $this->pdo->perform($sql, $bind);

            if ($result !== false) {
                $response = array('status' => 'OK', 'result' => array());
            } else {
                $response = array('status' => 'ERROR', 'result' => array());
            }
        } catch (PDOException $e) {
            $response = array('status' => 'EXCEPTION', 'result' => $e);
        }
        return $response;
    }

    // -- Insertar un permiso nuevo
    public function insert_permission($bind) {
        try {
            $sql = 'INSERT INTO permission_user_roles (id_user_role, id_sub_menu, status)
                    VALUES (:id_user_role, :id_sub_menu, :status)';

            $result = $this->pdo->perform($sql, $bind);

            if ($result) {
                $response = array('status' => 'OK', 'result' => array());
            } else {
                $response = array('status' => 'ERROR', 'result' => array());
            }
        } catch (PDOException $e) {
            $response = array('status' => 'EXCEPTION', 'result' => $e);
        }
        return $response;
    }

}

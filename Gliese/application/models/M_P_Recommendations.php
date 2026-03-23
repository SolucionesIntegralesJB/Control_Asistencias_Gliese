<?php
// --
class M_P_Recommendations extends Model {

    public function __construct() {
        parent::__construct();
    }

    public function get_all() {
        try {
                 $sql = 'SELECT r.id, r.title, r.description, r.created_by, r.created_at, r.updated_at, r.status,
                          c.first_name AS creator_first, c.last_name AS creator_last
                      FROM bot_recommendations r
                      LEFT JOIN user c ON c.id = r.created_by
                      ORDER BY r.created_at DESC';

            $result = $this->pdo->fetchAll($sql);
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

    public function get_by_id($id) {
        try {
                 $sql = 'SELECT r.id, r.title, r.description, r.created_by, r.created_at, r.updated_at, r.status,
                          c.first_name AS creator_first, c.last_name AS creator_last
                      FROM bot_recommendations r
                      LEFT JOIN user c ON c.id = r.created_by
                      WHERE r.id = :id LIMIT 1';
            $result = $this->pdo->fetchOne($sql, array('id' => $id));
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

    public function create($bind) {
        try {
            $sql = 'INSERT INTO bot_recommendations (title, description, created_by, created_at, status) VALUES (:title, :description, :created_by, NOW(), 1)';
            $result = $this->pdo->perform($sql, $bind);
            if ($result) {
                $id = $this->pdo->lastInsertId();
                $response = array('status' => 'OK', 'result' => array('id' => $id));
            } else {
                $response = array('status' => 'ERROR', 'result' => array());
            }
        } catch (PDOException $e) {
            $response = array('status' => 'EXCEPTION', 'result' => $e);
        }
        return $response;
    }

    public function update($bind) {
        try {
            // Build SQL dynamically to update status only when provided
            $sql = 'UPDATE bot_recommendations SET title = :title, description = :description';
            if (isset($bind['status'])) {
                $sql .= ', status = :status';
            }
            $sql .= ', updated_at = NOW() WHERE id = :id';

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

    public function delete($bind) {
        try {
            // Physical delete (remove row from database)
            $sql = 'DELETE FROM bot_recommendations WHERE id = :id';
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

}

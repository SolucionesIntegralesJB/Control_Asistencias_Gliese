<?php

class M_Company extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_company()
    {
        try {
            $sql = "SELECT * FROM company WHERE id = 1";
            $result = $this->pdo->fetchOne($sql);
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
    public function update_company($bind)
    {
        try {
            // Verificar si 'logo_sitio' está en $bind y manejarlo adecuadamente
            $sql = "UPDATE company
                        SET 
                        business_name = :razon_social,
                        company_name = :nombre_comercial,
                        RUC = :ruc,
                        address = :direccion,
                        district = :distrito,
                        province = :provincia,
                        department = :departamento,
                        postal_code = :codigo_postal,
                        ubigeo = :ubigeo,
                        phone = :telefono,
                        email = :email,
                        web = :web,";

            if (isset($bind['logo_sitio'])) {
                $sql .= " logo = :logo_sitio,";
            }

            $sql .= " country = :pais,
                        start_date = :fecha_autorizacion,
                        address2 = :direccion_secundaria,
                        industry = :publicidad
                        WHERE id = :id_company";

            // Ejecutar la consulta
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

    public function create_config($bind, $certificado_actualizado)
    {
        try {

            $checkSql = "SELECT COUNT(*) FROM sunat WHERE id = 1";
            $stmt = $this->pdo->prepare($checkSql);
            $stmt->execute();
            $count = $stmt->fetchColumn();
            if ($count == 0) {
                // Si no existe, hacemos un INSERT
                $sql = "INSERT INTO sunat (id, sunat_endpoint, cert_password, user, password";
                $sql .= $certificado_actualizado ? ", certificate" : "";
                $sql .= ") VALUES (1, :modo_emision, :contrasena_certificado, :usuario_sunat, :contrasena_sunat";
                $sql .= $certificado_actualizado ? ", :certificado" : "";
                $sql .= ")";
            } else {
                // Si existe, hacemos un UPDATE
                $sql = "UPDATE sunat 
                        SET sunat_endpoint = :modo_emision,
                            cert_password = :contrasena_certificado,
                            user = :usuario_sunat,
                            password = :contrasena_sunat";

                if ($certificado_actualizado) {
                    $sql .= ", certificate = :certificado";
                }

                $sql .= " WHERE id = 1";
            }

            $result = $this->pdo->perform($sql, $bind);

            if ($result) {
                $response = array('status' => 'OK', 'result' => array());
            } else {
                $response = array('status' => 'ERROR', 'result' => array());
            }
        } catch (PDOException $e) {
            $response = array('status' => 'EXCEPTION', 'result' => $e->getMessage());
        }
        // --
        return $response;
    }

    public function get_sunat()
    {
        try {
            $sql = "SELECT * FROM sunat WHERE id = 1";
            $result = $this->pdo->fetchOne($sql);
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

    public function get_config()
    {
        try {
            $sql = "SELECT * FROM token WHERE id = 1";
            $result = $this->pdo->fetchOne($sql);
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

    public function update_token($bind)
    {
        try {
            $checkSql = "SELECT COUNT(*) FROM token WHERE id = 1";
            $stmt = $this->pdo->prepare($checkSql);
            $stmt->execute();
            $count = $stmt->fetchColumn();
            if ($count > 0) {
                $sql = "UPDATE token 
                    SET token = :token, 
                        host = :host, 
                        email = :email, 
                        password = :password 
                    WHERE id = 1";
            } else {
                $sql = "INSERT INTO token (id, token, host, email, password) 
                    VALUES (1, :token, :host, :email, :password)";
            }
            $result = $this->pdo->perform($sql, $bind);
            if ($result) {
                $response = array('status' => 'OK', 'result' => array());
            } else {
                $response = array('status' => 'ERROR', 'result' => array());
            }
        } catch (PDOException $e) {
            $response = array('status' => 'EXCEPTION', 'result' => $e->getMessage());
        }
        return $response;
    }
}

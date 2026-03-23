<?php

class C_Schedule_Config extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Método index requerido por la clase abstracta Controller
     */
    public function index()
    {
        // Este método no se usa directamente, pero es requerido
        $json = [
            'status' => 'ERROR',
            'msg' => 'Método no implementado'
        ];
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    /**
     * Obtiene todas las configuraciones
     */
    public function get_configs()
    {
        $this->functions->validate_session($this->segment->get('isActive'));

        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'GET') {
            try {
                // Conectar directamente a la base de datos
                $host = DB_HOST;
                $dbname = DB_NAME;
                $username = DB_USER;
                $password = DB_PASS;

                $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $sql = 'SELECT id, config_key, config_value FROM schedule_config ORDER BY config_key ASC';
                $stmt = $pdo->query($sql);
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $json = [
                    'status' => 'OK',
                    'data' => $result
                ];
            } catch (PDOException $e) {
                $json = [
                    'status' => 'ERROR',
                    'data' => [],
                    'msg' => 'Error al obtener configuraciones: ' . $e->getMessage()
                ];
            }
        } else {
            $json = [
                'status' => 'ERROR',
                'msg' => 'Método no permitido'
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }

    /**
     * Actualiza una configuración
     */
    public function update_config()
    {
        $this->functions->validate_session($this->segment->get('isActive'));

        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'POST') {
            $config_key = filter_input(INPUT_POST, 'config_key', FILTER_SANITIZE_SPECIAL_CHARS);
            $config_value = filter_input(INPUT_POST, 'config_value', FILTER_SANITIZE_SPECIAL_CHARS);

            if (!$config_key || $config_value === null || $config_value === '') {
                $json = [
                    'status' => 'ERROR',
                    'type' => 'warning',
                    'msg' => 'Parámetros incompletos'
                ];
            } else {
                try {
                    // Conectar directamente a la base de datos
                    $host = DB_HOST;
                    $dbname = DB_NAME;
                    $username = DB_USER;
                    $password = DB_PASS;

                    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                    $sql = 'UPDATE schedule_config SET config_value = ?, updated_at = CURRENT_TIMESTAMP WHERE config_key = ?';
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$config_value, $config_key]);

                    if ($stmt->rowCount() > 0) {
                        $json = [
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => 'Configuración actualizada correctamente'
                        ];
                    } else {
                        $json = [
                            'status' => 'ERROR',
                            'type' => 'warning',
                            'msg' => 'No se encontró la configuración o el valor es el mismo'
                        ];
                    }
                } catch (PDOException $e) {
                    $json = [
                        'status' => 'ERROR',
                        'type' => 'error',
                        'msg' => 'Error al actualizar: ' . $e->getMessage()
                    ];
                }
            }
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido'
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }
}

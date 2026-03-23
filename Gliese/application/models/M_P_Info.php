<?php
/**
 * ============================================================================
 * MODELO: MI INFORMACIÓN (M_P_Info)
 * ============================================================================
 * 
 * PROPÓSITO:
 * Gestiona las consultas a la base de datos para obtener información
 * personal de practicantes y supervisores.
 * 
 * TABLAS UTILIZADAS:
 * - practicantes: Información de los estudiantes en práctica
 * - supervisores: Información de los supervisores de práctica
 * 
 * MÉTODOS:
 * 1. get_practicante_info($id_user) - Consulta datos del practicante
 * 2. get_supervisor_info($id_user) - Consulta datos del supervisor
 * 
 * NOTA IMPORTANTE:
 * Ambas tablas deben tener el campo 'id_user' que relaciona con
 * la tabla de usuarios del sistema para la autenticación.
 * 
 * AUTOR: Sistema Gliese
 * FECHA: 2025
 * ============================================================================
 */
class M_P_Info extends Model {

    /**
     * Constructor del modelo
     * Inicializa el modelo padre y la conexión PDO
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * ========================================================================
     * MÉTODO: get_practicante_info($id_user)
     * ========================================================================
     * 
     * PROPÓSITO:
     * Obtiene toda la información personal y académica de un practicante
     * desde la tabla 'practicantes' usando su id_user.
     * 
     * PARÁMETROS:
     * @param int $id_user - ID del usuario en la tabla de usuarios
     * 
     * TABLA CONSULTADA:
     * - practicantes
     * 
     * CAMPOS RETORNADOS:
     * - nombre_completo: Concatenación de nombres + apellidos
     * - documento: Número de documento de identidad
     * - tipo_documento: Tipo de documento (DNI, CE, Pasaporte, etc.)
     * - email: Correo electrónico del practicante
     * - telefono: Número de teléfono de contacto
     * - institucion: Institución educativa de procedencia
     * - especialidad: Carrera o especialidad que estudia
     * - area_interes: Área de interés para las prácticas
     * - fecha_inicio_formatted: Fecha de inicio de prácticas (DD/MM/YYYY)
     * - fecha_termino_formatted: Fecha de término de prácticas (DD/MM/YYYY)
     * - fecha_nacimiento_formatted: Fecha de nacimiento (DD/MM/YYYY)
     * - ciclo: Ciclo académico actual
     * - modalidad: Modalidad de práctica (presencial, remoto, híbrido)
     * - tarea_asignada: Tarea o proyecto asignado al practicante
     * - Todos los campos originales de la tabla (p.*)
     * 
     * PROCESO:
     * 1. Valida que el id_user sea un número válido > 0
     * 2. Ejecuta consulta SQL con JOIN si es necesario
     * 3. Formatea las fechas al formato DD/MM/YYYY
     * 4. Concatena nombre completo
     * 5. Retorna el resultado
     * 
     * RETORNO:
     * Array con estructura:
     * - status: 'OK' si encuentra datos, 'ERROR' si no, 'EXCEPTION' si hay error SQL
     * - result: Array asociativo con los datos del practicante
     * - msg: Mensaje de error (solo si status != OK)
     * 
     * EJEMPLO DE USO:
     * $response = $obj->get_practicante_info(25);
     * if ($response['status'] == 'OK') {
     *     $datos = $response['result'];
     *     echo $datos['nombre_completo'];
     * }
     */
    public function get_practicante_info($id_user) {
        try {
            // === PASO 1: Validar el parámetro ===
            $id_user = intval($id_user);
            if ($id_user <= 0) {
                return array('status' => 'ERROR', 'msg' => 'ID de usuario inválido.');
            }

            // === PASO 2: Construir consulta SQL ===
            // Obtiene todos los campos de practicantes más campos calculados
            $sql = 'SELECT 
                        p.*,
                        CONCAT(COALESCE(p.nombres, ""), " ", COALESCE(p.apellidos, "")) AS nombre_completo,
                        p.numero_documento AS documento,
                        p.tipo_documento AS tipo_documento,
                        p.email,
                        p.telefono,
                        p.institucion,
                        p.especialidad,
                        p.area_interes,
                        DATE_FORMAT(p.fecha_inicio, "%d/%m/%Y") AS fecha_inicio_formatted,
                        DATE_FORMAT(p.fecha_termino, "%d/%m/%Y") AS fecha_termino_formatted,
                        DATE_FORMAT(p.fecha_nacimiento, "%d/%m/%Y") AS fecha_nacimiento_formatted,
                        p.ciclo,
                        p.modalidad,
                        p.tarea_asignada
                    FROM practicantes p
                    WHERE p.id_user = :id_user
                    LIMIT 1';
            
            // === PASO 3: Ejecutar consulta con parámetros preparados ===
            $result = $this->pdo->fetchOne($sql, array('id_user' => $id_user));

            // === PASO 4: Verificar si se encontró el practicante ===
            if ($result) {
                return array('status' => 'OK', 'result' => $result);
            }

            // No se encontró ningún practicante con ese id_user
            return array('status' => 'ERROR', 'msg' => 'No se encontró información del practicante.', 'result' => array());
            
        } catch (PDOException $e) {
            // Error en la consulta SQL
            return array('status' => 'EXCEPTION', 'msg' => $e->getMessage());
        }
    }

    /**
     * ========================================================================
     * MÉTODO: get_supervisor_info($id_user)
     * ========================================================================
     * 
     * PROPÓSITO:
     * Obtiene toda la información laboral y personal de un supervisor
     * desde la tabla 'supervisores' usando su id_user.
     * 
     * PARÁMETROS:
     * @param int $id_user - ID del usuario en la tabla de usuarios
     * 
     * TABLA CONSULTADA:
     * - supervisores
     * 
     * CAMPOS RETORNADOS:
     * - nombre_completo: Concatenación de nombres + apellidos
     * - documento: Número de documento de identidad
     * - tipo_documento: Tipo de documento (DNI, CE, Pasaporte, etc.)
     * - email: Correo electrónico del supervisor
     * - telefono: Número de teléfono de contacto
     * - area_supervision: Área que supervisa (contabilidad, TI, ventas, etc.)
     * - cargo: Cargo o puesto del supervisor
     * - fecha_ingreso_formatted: Fecha de ingreso a la empresa (DD/MM/YYYY)
     * - Todos los campos originales de la tabla (s.*)
     * 
     * PROCESO:
     * 1. Valida que el id_user sea un número válido > 0
     * 2. Ejecuta consulta SQL
     * 3. Formatea la fecha al formato DD/MM/YYYY
     * 4. Concatena nombre completo
     * 5. Retorna el resultado
     * 
     * RETORNO:
     * Array con estructura:
     * - status: 'OK' si encuentra datos, 'ERROR' si no, 'EXCEPTION' si hay error SQL
     * - result: Array asociativo con los datos del supervisor
     * - msg: Mensaje de error (solo si status != OK)
     * 
     * EJEMPLO DE USO:
     * $response = $obj->get_supervisor_info(15);
     * if ($response['status'] == 'OK') {
     *     $datos = $response['result'];
     *     echo $datos['cargo'];
     * }
     */
    public function get_supervisor_info($id_user) {
        try {
            // === PASO 1: Validar el parámetro ===
            $id_user = intval($id_user);
            if ($id_user <= 0) {
                return array('status' => 'ERROR', 'msg' => 'ID de usuario inválido.');
            }

            // === PASO 2: Construir consulta SQL ===
            // Obtiene todos los campos de supervisores más campos calculados
            $sql = 'SELECT 
                        s.*,
                        CONCAT(COALESCE(s.nombres, ""), " ", COALESCE(s.apellidos, "")) AS nombre_completo,
                        s.numero_documento AS documento,
                        s.tipo_documento AS tipo_documento,
                        s.email,
                        s.telefono,
                        s.area_supervision,
                        s.cargo,
                        DATE_FORMAT(s.fecha_ingreso, "%d/%m/%Y") AS fecha_ingreso_formatted
                    FROM supervisores s
                    WHERE s.id_user = :id_user
                    LIMIT 1';
            
            // === PASO 3: Ejecutar consulta con parámetros preparados ===
            $result = $this->pdo->fetchOne($sql, array('id_user' => $id_user));

            // === PASO 4: Verificar si se encontró el supervisor ===
            if ($result) {
                return array('status' => 'OK', 'result' => $result);
            }

            // No se encontró ningún supervisor con ese id_user
            return array('status' => 'ERROR', 'msg' => 'No se encontró información del supervisor.', 'result' => array());
            
        } catch (PDOException $e) {
            // Error en la consulta SQL
            return array('status' => 'EXCEPTION', 'msg' => $e->getMessage());
        }
    }
}

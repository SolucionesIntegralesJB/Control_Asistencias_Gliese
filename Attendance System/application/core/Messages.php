<?php
// Messages Class - Standard messages
class Messages {
    public $message = array();

    public function __construct() {
        $this->message = array(
            'list' => 'Listado de registros encontrados.',
            'empty_list' => 'No se encontraron registros en el sistema.',
            'success_created' => 'Registro almacenado en el sistema con éxito.',
            'success_updated' => 'Registro actualizado en el sistema con éxito.',
            'success_deleted' => 'Registro eliminado del sistema con éxito.',
            'error_created' => 'No fue posible almacenar el registro ingresado, verificar.',
            'error_updated' => 'No fue posible actualizar el registro ingresado, verificar.',
            'error_deleted' => 'No fue posible eliminar el registro, verificar.',
            'empty_params' => 'No se enviaron los campos necesarios, verificar.',
            'method_denied' => 'Método no permitido.',
        );
    }
}

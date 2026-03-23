<?php 
// --
class M_Supporttechnical extends Model {
    // --
    public function __construct() {
		parent::__construct();
    } 

    // --
    public function get_reason_transfer() { /** Función Modificada */
      try {
          $sql = 'SELECT 
                      id, 
                      description, 
                      status 
                  FROM reason_transfer;';
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
  
}
<?php
/*~ Bd.php
.---------------------------------------------------------------------------.
|  Software: PHP database class                                                |
|   Version: 1.0                                                            |
| ------------------------------------------------------------------------- |
|     Admin: Oscar David Gallardo Prada (project admininistrator)           |
|   Authors: Oscar David Gallardo Prada - oscar.gallardop@gmail.com
| This program is distributed in the hope that it will be useful - WITHOUT  |
| ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or     |
| FITNESS FOR A PARTICULAR PURPOSE.                                         |
'---------------------------------------------------------------------------'
*/
?>
<?php include_once $_SERVER['DOCUMENT_ROOT'].'/app_libraries/includes/phpmaker_10_conn/ewcfg10.php' ?>
<?php include_once $_SERVER['DOCUMENT_ROOT'].'/app_libraries/includes/phpmaker_10_conn/adodb5/adodb.inc.php' ?>
<?php include_once $_SERVER['DOCUMENT_ROOT'].'/app_libraries/includes/phpmaker_10_conn/phpfn10.php' ?>

<?php
$classLoader;

class Bd
{
    var $conn;
	//var $sentencia;
    private $result;
    
    function __construct($arrConnParms = null)
    {
        $this->conectar($arrConnParms);
    }
    
    function conectar($arrConnParms = null){
		$this->conn = ew_Connect();
    }

    function query($sql, $arrParms = array()){
		try{
			$this->sentencia = $sql;
			$return = $this->sentencia;
        }
        catch (Exception $e){
            echo ' Hubo un Error: ',  $e->getMessage(), "\n".$sql;
            $return = false;
        }
        return $return;
    }
    
    /**
     * Obtiene una fila de resultados como un array asociativo, numérico, o ambos
    */
    function fetch($estilo = 4){
		$row = $this->conn->Execute($this->sentencia);
		$array = $row->fields; 
		$row->Close();
		return $array;
    }

    function fetchAll($indice){
		$row = $this->conn->Execute($this->sentencia);		
		$array["elementos"] = $row->GetRows();
		$array["num"] = $row->RecordCount();
		$row->Close();
		return $array;
		// Ejemplo para recorrer el arreglo
		/*
			for($i=0;$i< $fetchAll["num"];$i++){
				echo "<br>".$fetchAll["elementos"][$i]['NOMBRE_CAMPO'];
			}
		/*/
    }
    
    function insert($tabla, $arrParms = array())
    {
        return $this->conn->insert($tabla, $arrParms);        
    }
    
    function update($tabla, $arrParms, $arrIdentifier = array())
    {
        return $this->conn->update($tabla, $arrParms, $arrIdentifier);        
    }
    
    function delete($tabla, $arrParms = array())
    {
        return $this->conn->delete($tabla, $arrParms);        
    }
    
}

?>
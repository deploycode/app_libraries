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

/**
 * Bd - PHP database clase
 * NOTE: Requiere PHP version 5 or superior
 */

use Doctrine\Common\ClassLoader;

require $_SERVER['DOCUMENT_ROOT'].'\app_libraries\includes\doctrine\Doctrine\Common\ClassLoader.php';

$classLoader = new ClassLoader('Doctrine', $_SERVER['DOCUMENT_ROOT'].'\app_libraries\includes\doctrine');
$classLoader->register();

class Bd
{
    var $conn;
    private $result;
    
    function __construct($arrConnParms = null)
    {
        $this->conectar($arrConnParms);
    }
    
    /**
     *
     * @param type $arrConnParms *['motor'] -> motor de base de datos por defecto
     *                           *['bdNombre'] -> nombre de la base de datos
     *                           *['host'] -> ip del servidor
     *                           *['puerto'] -> puerto de conexion
     *                           *['usuario'] -> usuario de base de datos
     *                           *['clave'] -> clave de usuario de base de datos
     * por defecto se conecta con la base de famisanar
     * 
     */
    function conectar($arrConnParms = null)
    {
        $config = new \Doctrine\DBAL\Configuration();
        
        if(isset($arrConnParms['bdNombre'])) $connParams['dbname'] = $arrConnParms['bdNombre']; else $connParams['dbname'] = 'APPRT';
        if(isset($arrConnParms['usuario'])) $connParams['user'] = $arrConnParms['usuario']; else $connParams['user'] = 'app_pac';
        if(isset($arrConnParms['clave'])) $connParams['password'] = $arrConnParms['clave']; else $connParams['password'] = 'neps_2016';
        if(isset($arrConnParms['host'])) $connParams['host'] = $arrConnParms['host']; else $connParams['host'] = '192.168.88.162';
        if(isset($arrConnParms['puerto'])) $connParams['port'] = $arrConnParms['puerto']; else $connParams['port'] = '1521';
        
        $connParams['driver'] = 'oci8';
        if(isset($arrConnParms['motor']))
        {
            if($arrConnParms['motor'] == 'oracle')
            {
                $connParams['driver'] = 'oci8';
            }
            elseif($arrConnParms['motor'] == 'mysql')
            {
                $connParams['driver'] = 'pdo_mysql';
            }
        }

        $this->conn = \Doctrine\DBAL\DriverManager::getConnection($connParams, $config);        
    }

    function query($sql, $arrParms = array())
    {
        try 
        {
            $this->result = $this->conn->prepare($sql);

            if(sizeof($arrParms) > 0)
            {
                foreach ($arrParms as $i => $valor)
                {
                    $this->result->bindValue($i, $valor);
                }
            }
            $return = $this->result->execute();
        }
        catch (Exception $e) 
        {
            echo ' Hubo un Error: ',  $e->getMessage(), "\n".$sql;
            $return = false;
        }
        
        return $return;
    }
    
    /**
     * 1 -> Objeto
     * 2 -> array asociativo(por defecto)
     * 3 -> array numerico
     * 4 -> ambos
     */
    function fetch($estilo = 4)
    {
        return $this->result->fetch($estilo);
    }

    function fetchAll($estilo = 2)
    {
        $arrRet = array(
            'elementos' => array(),
            'num' => 0
        );
//        while ($row = $this->result->fetch())
//        {
//            $arrRet['elementos'][] = $row;
//        }

        $arrRet['elementos'] = $this->result->fetchAll($estilo);
        
        $arrRet['num'] = $this->result->rowCount();
        return $arrRet;
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
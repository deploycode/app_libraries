<?php
/*~ File.php
.---------------------------------------------------------------------------.
|  Software: PHP archivo class                                                |
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
 * Archivo - PHP archivo clase
 * NOTE: Requiere PHP version 5 or superior
 */
ini_set("error_reporting",(E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR | E_NOTICE | E_COMPILE_WARNING | E_RECOVERABLE_ERROR | E_CORE_ERROR));
ini_set('display_errors','1');

require $_SERVER['DOCUMENT_ROOT'].'/app_libraries/includes/File-1.4.0/File.php';
require $_SERVER['DOCUMENT_ROOT'].'/app_libraries/includes/Spyc/spyc.php';
require $_SERVER['DOCUMENT_ROOT'].'/app_libraries/includes/Zipper/Zip.php';


class Archivo 
{
    private $usuario;
    private $clave;
    
    function __construct($usuario = '', $clave = '') 
    {
        if(isset($usuario))
        {
            $this->usuario = $usuario;
        }
        if(isset($clave))
        {
            $this->clave = $clave;
        }
    }
    
    function grabarLinea($path, $string)
    {
        $e = File::writeLine($path, $string, FILE_MODE_APPEND, "\r\n");
        if (PEAR::isError($e)) 
        {
            echo 'Could not write to file : ' . $e->getMessage();
        } 
    }
    
    public function loadConfig($path)
    {
        $array = Spyc::YAMLLoad($path);

        return $array;

    }
    
    /**
     * Metodo que permite copiar archivos autenticandose en el dominio
     * @param type $nombreArchivo
     * @param type $rutaOrigen
     * @param type $rutaDestino 
     */
    public function copiarAuth($nombreArchivo, $rutaOrigen, $rutaDestino)
    {
        if(!empty($nombreArchivo) and !empty($rutaOrigen) and !empty($rutaDestino))
        {
            $command = $_SERVER['DOCUMENT_ROOT'].'/app_libraries/includes/FileManager/FileManager.exe copiar "'.$this->usuario.'" "'.$this->clave.'" "';
            $command .= $nombreArchivo.'" "'.$rutaOrigen.'" "'.$rutaDestino.'"';
            $salida = shell_exec($command);
        }
    }
    
    /**
     * Metodo que permite mover archivos autenticandose en el dominio
     * @param type $nombreArchivo
     * @param type $rutaOrigen
     * @param type $rutaDestino 
     */
    public function moverAuth($nombreArchivo, $rutaOrigen, $rutaDestino)
    {
        if(!empty($nombreArchivo) and !empty($rutaOrigen) and !empty($rutaDestino))
        {
            $command = $_SERVER['DOCUMENT_ROOT'].'/app_libraries/includes/FileManager/FileManager.exe mover "'.$this->usuario.'" "'.$this->clave.'" "';
            $command .= $nombreArchivo.'" "'.$rutaOrigen.'" "'.$rutaDestino.'"';
            $salida = shell_exec($command);
        }
    }
    
    /**
     * Metodo que permite eliminar archivos autenticandose en el dominio
     * @param type $pathArchivo 
     */
    public function borrarAuth($pathArchivo)
    {
        if(!empty($pathArchivo))
        {
            $command = $_SERVER['DOCUMENT_ROOT'].'/app_libraries/includes/FileManager/FileManager.exe borrar "'.$this->usuario.'" "'.$this->clave.'" "';
            $command .= $pathArchivo.'"';
            $salida = shell_exec($command);
        }
    }
    
    public function borrar($pathArchivo)
    {
        try
        {
            $return = true;
            if(is_array($pathArchivo))
            {
                foreach($pathArchivo as $fileToDel)
                {
                    if(file_exists ($fileToDel))
                        unlink($fileToDel);
                }
            }
            else
            {
                if(file_exists ($pathArchivo))
                {
                    $return = unlink($pathArchivo);
                }
                else
                {
                    $return = false;
                }
            }
        }
        catch (Exception $e)
        {
            echo ' Hubo un Error: ',  $e->getMessage(), "\n".$sql;
            $return = false;
        }

        return $return;
    }

    /**
     *
     * @param type $arrParr ['toZip'] //archivo a comprimir
     * @param type $arrParr ['zipDir'] //Direccion donde almacena el archivo
     * @param type $arrParr ['zipName'] //Nombre del archivo destino
     */
    public function zip($arrParr)
    {
        $objZip = new ZipArchive();
        $zipName = $arrParr['zipDir'].$arrParr ['zipName'];
        
        if ($objZip->open($zipName, ZIPARCHIVE::CREATE)!==TRUE) 
        {
            exit("cannot open <{$zipName}>\n");
        }

        if(is_array($arrParr ['toZip']))
        {
            foreach($arrParr ['toZip'] as $fileToZip)
            {
                $objZip->addFile($fileToZip, basename($fileToZip));
            }
        }
        else
        {
            $objZip->addFile($arrParr ['toZip'], basename($arrParr ['toZip']));            
        }
        $objZip->close();        
    }
}
?>
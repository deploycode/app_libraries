<?php

/*~ Excel.php
.---------------------------------------------------------------------------.
|  Software: PHP excel class                                                |
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
 * Description of Excel
 *
 * @author ogallardo
 */

ini_set("error_reporting",(E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR | E_NOTICE | E_COMPILE_WARNING | E_RECOVERABLE_ERROR | E_CORE_ERROR));
ini_set('display_errors','1');

require_once $_SERVER['DOCUMENT_ROOT'].'/app_libraries/includes/Spreadsheet/Excel/Writer.php';
$workbook = new Spreadsheet_Excel_Writer();

class Excel 
{
    function __construct($nombreArchivo, $datosExcel = array()) 
    {
        $this->genExcel($nombreArchivo, $datosExcel);
    }
    
    //nombre del path y archivo donde se debe generar
    private function genExcel($nombreArchivo, $datosExcel)
    {
        $workbook = new Spreadsheet_Excel_Writer($nombreArchivo);

        $fNegrilla =& $workbook->addformat();
        $fNegrilla->setBold();
        
        //formato para el titulo
        $dgeneral =& $workbook->addformat();
        $dgeneral->setMerge();
        $dgeneral->setBold();
        $dgeneral->setSize(8);
        $dgeneral->setBorder('1px solid');//Borde
        $dgeneral->setAlign('vcenter');        
        $dgeneral->setFgColor('grey');
        
        
        //formato para las alineaciones
        $normalC=& $workbook->addFormat();
        $normalC->setAlign('center');//alinear
        $normalC->setBorder('1px solid');
        $normalC->setSize(8);//tam

        $normalL=& $workbook->addFormat();
        $normalL->setAlign('left');//alinear
        $normalL->setBorder('1px solid');
        $normalL->setSize(8);//tama
        $normalL->setTextwrap();

        $normalR=& $workbook->addFormat();
        $normalR->setAlign('right');//alinear
        $normalR->setBorder('1px solid');
        $normalR->setSize(8);//tama
        $normalR->setTextWrap();

        $worksheet =& $workbook->addWorksheet('Hoja');

        /////Arreglo de las letras
        $arrTitulos = (isset($datosExcel['arrTitulos'])) ? $datosExcel['arrTitulos']:array();
        
        $i=0;
        $k=0;
        $tituloColumna = '';
        ///Le asigna el ancho a las columnas e Imprime los titulos y
        //Se utiliza $k para las filas y $ i para las columnas
        foreach($arrTitulos AS $i => $titulo)
        {
            ///Asigna el ancho de la columna
            $worksheet->setColumn($titulo['col'], $titulo['col'], $titulo['anchoCol']);
            ///Asigna el valor de la columna
            $tituloColumna = utf8_decode($titulo['titulo']);
            $worksheet->write($k, $i, $tituloColumna, $dgeneral);
            ///Asigna la alineacion de los datos de la columna
            $arrAlign[$i] = (strtoupper($titulo['alineado']) == 'C') ? $normalC:(strtoupper($titulo['alineado']) == 'R' ? $normalR:$normalL);
        }
        
        // While we are at it, why not throw some more numbers around
        $k=1;
        $i=0;
        $j=0;
        //Se utiliza $k para las filas y $ j para las columnas
        //Se imprimen todos los datos
        
        if(sizeof($datosExcel['arrDatos']) > 0)
        {
            foreach($datosExcel['arrDatos'] AS $i => $fDatos)
            {
                foreach($fDatos AS $index => $cDatos)
                {
                    if(is_integer($index))
                    {
                        $strDato = (is_string($cDatos)) ? utf8_decode($cDatos) : $cDatos;
                        $worksheet->write($k, $j, $strDato);
                        $j++;
                    }
                }
                $j = 0;
                $k++;
            }
        }
        
        $workbook->close();        
    }
    
}

?>

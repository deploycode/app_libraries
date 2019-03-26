<?php
//realizamos la carga de la clase que este en el arrglo modulos
if(isset($modulos))
{
    if(sizeof($modulos) > 0)
    {
        foreach ($modulos as $clase)
        {
            include_once($_SERVER['DOCUMENT_ROOT'].'/app_libraries/includes/Clases/'.$clase.'.php');
        }
    }
}

/// Debido a los problemas de compatibilidad de la libreria FILE con PHP 5.2 se carga la configuracion de conexion directamente en el INIT
///$arrConfig = Archivo::loadConfig("Config.yaml");
//$arrConfig = Archivo::loadConfig($_SERVER['DOCUMENT_ROOT'].'/app_libraries/Config.yaml');
$arrConfig["host"] = "192.168.88.219";
$arrConfig["port"] = "1529";
$arrConfig["nombre"] = "appneps";
$arrConfig["usuario"] = "app_actactc";
$arrConfig["clave"] = "actactc";
$arrConfig["pdo"] = "oracle";

///////////////cargar configuracion de bases de datos/////////////////
if(_AMBIENTE == 1)//produccion
{
    $iBd = 'dbProduccion';
}
elseif(_AMBIENTE == 2)//pruebas de usuario
{
    $iBd = 'dbPruebasUsuario';
}
else//(_AMBIENTE == 3) pruebas locales
{
    $iBd = 'dbPruebasLocales';
}

$arrBdIntegral = $arrConfig;

//$arrBdGeneral = $arrConfig[$iBd]['General'];

/****
if(isset($arrConfig[$iBd]['Apoteosys']))
{
    $arrBdApoteosys = (isset($arrConfig[$iBd]['Apoteosys'])) ? $arrConfig[$iBd]['Apoteosys'] : array();
    define ('_BD_APOTEOSYS', $arrBdApoteosys['nombre']);
    define ('_HOST_APOTEOSYS', $arrBdApoteosys['host']);
    define ('_PORT_APOTEOSYS', $arrBdApoteosys['port']);
    define ('_USER_APOTEOSYS', $arrBdApoteosys['usuario']);
    define ('_PASS_APOTEOSYS', $arrBdApoteosys['clave']);
    define ('_PDO_APOTEOSYS', $arrBdApoteosys['pdo']);    
}

if(isset($arrConfig[$iBd]['ServiCapitados']))
{
    $arrBdCapitados = $arrConfig[$iBd]['ServiCapitados'];
    define ('_BD_CAPITADOS', $arrBdCapitados['nombre']);
    define ('_HOST_CAPITADOS', $arrBdCapitados['host']);
    define ('_PORT_CAPITADOS', $arrBdCapitados['port']);
    define ('_USER_CAPITADOS', $arrBdCapitados['usuario']);
    define ('_PASS_CAPITADOS', $arrBdCapitados['clave']);
    define ('_PDO_CAPITADOS', $arrBdCapitados['pdo']);
}
 
if(isset($arrConfig[$iBd]['appSufpc']))
{
    $arrBdCapitados = $arrConfig[$iBd]['appSufpc'];
    define ('_BD_SUFPC', $arrBdCapitados['nombre']);
    define ('_HOST_SUFPC', $arrBdCapitados['host']);
    define ('_PORT_SUFPC', $arrBdCapitados['port']);
    define ('_USER_SUFPC', $arrBdCapitados['usuario']);
    define ('_PASS_SUFPC', $arrBdCapitados['clave']);
    define ('_PDO_SUFPC', $arrBdCapitados['pdo']);
}
*///
define ('_BD_INTEGRAL', $arrBdIntegral['nombre']);
define ('_HOST_INTEGRAL', $arrBdIntegral['host']);
define ('_PORT_INTEGRAL', $arrBdIntegral['port']);
define ('_USER_INTEGRAL', $arrBdIntegral['usuario']);
define ('_PASS_INTEGRAL', $arrBdIntegral['clave']);
define ('_PDO_INTEGRAL', $arrBdIntegral['pdo']);

/*
define ('_BD_GENERAL', $arrBdGeneral['nombre']);
define ('_HOST_GENERAL', $arrBdGeneral['host']);
define ('_PORT_GENERAL', $arrBdGeneral['port']);
define ('_USER_GENERAL', $arrBdGeneral['usuario']);
define ('_PASS_GENERAL', $arrBdGeneral['clave']);
define ('_PDO_GENERAL', $arrBdGeneral['pdo']);
*/

?>
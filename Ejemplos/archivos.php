<?php
if(empty ($_SERVER['DOCUMENT_ROOT']))
    $_SERVER['DOCUMENT_ROOT'] = 'C:/xampp/htdocs';

require "../includes/Clases/Archivo.php";

$objArchivo = new Archivo('adminapp', '!QAZ2wsx');
$objArchivo->copiar('log.txt', 'C:\apoteosy', '\\\\fsbog001\shr_app');
//$objArchivo->mover('log.txt', 'C:\apoteosy', '\\\\fsbog001\DAD\Proyectos\PHP(desarrollos Nueva EPS)');
//$objArchivo->borrar('\\\\fsbog001\DAD\Proyectos\PHP(desarrollos Nueva EPS)\log.txt');


?>
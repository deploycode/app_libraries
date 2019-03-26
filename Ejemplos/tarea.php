<?php


echo '------------------------ORACLE---------------------------------';
//$objBd = new Bd(array(
//        'bdNombre' => 'prubfami', 
//        'host' => '192.168.88.145',
//        'puerto' => '1521',
//        'usuario' => 'SEI',
//        'clave' => 'pruebasneps',
//    ));
$objBd = new Bd();
$sql = 'SELECT table_name FROM user_tables';
$objBd->query($sql);
$elementos = $objBd->fetchAll();

echo '<pre>';
print_r($elementos);
echo '</pre>';
echo '-----------------------MYSQL------------------------------------';

$objBd = new Bd(array(
        'motor' => 'mysql', 
        'bdNombre' => 'db_general', 
        'host' => 'localhost',
        'puerto' => '3306',
        'usuario' => 'root',
        'clave' => '',
    ));
$sql = 'select * from tarea_programada';
$objBd->query($sql);
$elementos = $objBd->fetchAll();

echo '<pre>';
print_r($elementos);
echo '</pre>';

//
//$objCorreo = new Correo();
//$objCorreo->asunto = 'Prueba mailer';
//$objCorreo->cuerpoMsg = 'Cuerpo del mensaje';
//$objCorreo->dirRemite = 'no_responder@nuevaeps.com.co';
//$objCorreo->nombre = 'Servidor Local';
//$arrMsg['para'] = 'oscar.gallardo@nuevaeps.com.co';
//$objCorreo->enviar($arrMsg);

?>

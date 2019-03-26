<?php
$objCorreo = new Correo();
$objCorreo->asunto = 'Prueba mailer';
$objCorreo->cuerpoMsg = 'Cuerpo del mensaje';
$objCorreo->dirRemite = 'no_responder@nuevaeps.com.co';
$objCorreo->nombre = 'Servidor Local';
$arrMsg['para'] = 'oscar.gallardo@nuevaeps.com.co';
$objCorreo->enviar($arrMsg);

?>
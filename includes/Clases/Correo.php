<?php
/*~ Correo.php
.---------------------------------------------------------------------------.
|  Software: PHP email class                                                |
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
 * Correo - PHP email clase
 * NOTE: Requiere PHP version 5 or superior
 */
require $_SERVER['DOCUMENT_ROOT'].'/app_libraries/includes/PHPMailer_5.2.0/class.phpmailer.php';

class Correo 
{
    private $mailer;//canal de envio
    private $host;//direccion ip del servidor smtp
    private $timeOut;//tiempo de espera de smtp
    private $mail;
    
    var $nombre;//nombre del remitente
    var $dirRemite;//direccion del remitente
    var $asunto;
    var $cuerpoMsg;//cuerpo del mensaje
    
    function __construct() 
    {
        $this->mailer = 'smtp';
        $this->host = '192.168.88.143';
        $this->timeOut = 300;

        $this->mail = new phpmailer();
    }
    
    /**
     *
     * @param type $arrMsg 
     *              ['para'] Indica las direcciones de correo destino
     *              ['cc'] Indica con copia a...
     *              ['cco'] Indica con copia oculta
     * @return type Boolean
     */
    function enviar($arrMsg)
    {
        $this->mail->Mailer = $this->mailer;
        $this->mail->Host = $this->host;
        $this->mail->Timeout = $this->timeOut;

        $this->mail->FromName = $this->nombre;
        $this->mail->From = $this->dirRemite;
        $this->mail->ClearAddresses();

        if(isset($arrMsg['para']))
        {
            if(sizeof($arrMsg['para']) == 1 and !is_array($arrMsg['para']))
            {
                $arrPara = explode(";", $arrMsg['para']);
                foreach($arrPara as $cor)
                {
					$cor = trim($cor);
                    if ($this->comprobarEmail($cor))
                    {
                        $this->mail->AddAddress($cor);
                    }
                    else
                    {
                        //reporta error en direccion de correo
                        $this->repError(array());
                    } 
                }                       
            }
            else
            {
                foreach($arrMsg['para'] as $para)
                {                    
                    $arrPara = explode(";", $para);
                    foreach($arrPara as $cor)
                    {
                        if ($this->comprobarEmail($cor)) 
                        {
                            $this->mail->AddAddress($cor);
                        }
                        else
                        {
                            //reporta error en direccion de correo
                            $this->repError(array());
                        } 
                    }                       
                }                
            }
        }//fi para
        
        if(isset($arrMsg['cco']))
        {
            if(sizeof($arrMsg['cco']) == 1 and !is_array($arrMsg['cco']))
            {
                $arrPara = explode(";", $arrMsg['cco']);
                foreach($arrPara as $cor)
                {
					$cor = trim($cor);				
                    if ($this->comprobarEmail($cor))
                    {
                        $this->mail->AddBCC($cor);
                    }
                    else
                    {
                        //reporta error en direccion de correo
                        $this->repError(array());
                    } 
                }                       
            }
            else
            {
                foreach($arrMsg['cco'] as $para)
                {                    
                    $arrPara = explode(";", $para);
                    foreach($arrPara as $cor)
                    {
						$cor = trim($cor);
                        if ($this->comprobarEmail($cor)) 
                        {
                        $this->mail->AddBCC($cor);
                        }
                        else
                        {
                            //reporta error en direccion de correo
                            $this->repError(array());
                        } 
                    }                       
                }                
            }
        }
        
        if(isset($arrMsg['cc']))
        {
            if(sizeof($arrMsg['cc']) == 1 and !is_array($arrMsg['cc']))
            {
                $arrPara = explode(";", $arrMsg['cc']);
                foreach($arrPara as $cor)
                {
                    if ($this->comprobarEmail($cor))
                    {
                        $this->mail->AddCC($cor);
                    }
                    else
                    {
                        //reporta error en direccion de correo
                        $this->repError(array());
                    } 
                }                       
            }
            else
            {
                foreach($arrMsg['cc'] as $para)
                {                    
                    $arrPara = explode(";", $para);
                    foreach($arrPara as $cor)
                    {
						$cor = trim($cor);					
                        if ($this->comprobarEmail($cor)) 
                        {
                        $this->mail->AddCC($cor);
                        }
                        else
                        {
                            //reporta error en direccion de correo
                            $this->repError(array());
                        } 
                    }                       
                }                
            }
        }        
        //indica que el contenido del mensaje es HTML
        $this->mail->IsHTML();
        
        $this->mail->Subject = $this->asunto;
        $this->mail->Body = $this->cuerpoMsg;

        if ($this->mail->Send())
        {
            $this->mail->ClearAllRecipients();
            $this->mail->ClearAttachments();
            return true;
        }
        else
            return false;
    }
    
    /**
     *
     * @param type $email
     * @return type Boolean 
     * Verifica la direccion de correo
     */
    function comprobarEmail($email)
    { 
        $mail_correcto = 0; 
        //compruebo unas cosas primeras 
        if ((strlen($email) >= 6) && (substr_count($email,"@") == 1) && (substr($email,0,1) != "@") && (substr($email,strlen($email)-1,1) != "@")){ 
           if ((!strstr($email,"'")) && (!strstr($email,"\"")) && (!strstr($email,"\\")) && (!strstr($email,"\$")) && (!strstr($email," "))) { 
              //miro si tiene caracter . 
              if (substr_count($email,".")>= 1){ 
                 //obtengo la terminacion del dominio 
                 $term_dom = substr(strrchr ($email, '.'),1); 
                 //compruebo que la terminacion del dominio sea correcta 
                 if (strlen($term_dom)>1 && strlen($term_dom)<5 && (!strstr($term_dom,"@")) ){ 
                    //compruebo que lo de antes del dominio sea correcto 
                    $antes_dom = substr($email,0,strlen($email) - strlen($term_dom) - 1); 
                    $caracter_ult = substr($antes_dom,strlen($antes_dom)-1,1); 
                    if ($caracter_ult != "@" && $caracter_ult != "."){ 
                       $mail_correcto = 1; 
                    } 
                 } 
              } 
           } 
        } 
        if ($mail_correcto) 
           return true; 
        else 
           return false; 
    }
    
    /**
     *
     * @param type $path direccion del archivo que se desea adjuntar
     */
    function adjuntarArchivo($path, $reNombra = '')
    {
        $this->mail->AddAttachment($path, $reNombra);
    }
    
    /**
     *
     * @param type $arrError 
     *              ['tipo'] -> codigo del tipo de error reportado
     *              ['txt'] -> Texto del error reportado
     */
    private function repError($arrError)
    {
        echo 'Error EMAIL reportado';
    }
    
}

?>
<?php
/*~ Bd.php
.---------------------------------------------------------------------------.
|  Software: PHP pdf class                                                |
|   Version: 1.0                                                            |
| ------------------------------------------------------------------------- |
|     Admin: Oscar David Gallardo Prada (project admininistrator)           |
|   Authors: Oscar David Gallardo Prada - oscar.gallardop@gmail.com
| This program is distributed in the hope that it will be useful - WITHOUT  |
| ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or     |
| FITNESS FOR A PARTICULAR PURPOSE.                                         |
'---------------------------------------------------------------------------'
*/
require($_SERVER['DOCUMENT_ROOT'] . '/app_libraries/includes/Fpdf/fpdf.php');
//Complemento para seguridad de apertura de archivo con clave	
	if(function_exists('mcrypt_encrypt'))
	{
		function RC4($key, $data)
		{
			return mcrypt_encrypt(MCRYPT_ARCFOUR, $key, $data, MCRYPT_MODE_STREAM, '');
		}
	}
	else
	{
		function RC4($key, $data)
		{
			static $last_key, $last_state;

			if($key != $last_key)
			{
				$k = str_repeat($key, 256/strlen($key)+1);
				$state = range(0, 255);
				$j = 0;
				for ($i=0; $i<256; $i++){
					$t = $state[$i];
					$j = ($j + $t + ord($k[$i])) % 256;
					$state[$i] = $state[$j];
					$state[$j] = $t;
				}
				$last_key = $key;
				$last_state = $state;
			}
			else
				$state = $last_state;

			$len = strlen($data);
			$a = 0;
			$b = 0;
			$out = '';
			for ($i=0; $i<$len; $i++){
				$a = ($a+1) % 256;
				$t = $state[$a];
				$b = ($b+$t) % 256;
				$state[$a] = $state[$b];
				$state[$b] = $t;
				$k = $state[($state[$a]+$state[$b]) % 256];
				$out .= chr(ord($data[$i]) ^ $k);
			}
			return $out;
		}
	}
// FIN Complemento para seguridad de apertura de archivo con clave


class Pdf extends FPDF
{
	
	function Circle($x, $y, $r, $style='D')
	{
		$this->Ellipse($x,$y,$r,$r,$style);
	}

	function Ellipse($x, $y, $rx, $ry, $style='D')
	{
		if($style=='F')
			$op='f';
		elseif($style=='FD' || $style=='DF')
			$op='B';
		else
			$op='S';
		$lx=4/3*(M_SQRT2-1)*$rx;
		$ly=4/3*(M_SQRT2-1)*$ry;
		$k=$this->k;
		$h=$this->h;
		$this->_out(sprintf('%.2F %.2F m %.2F %.2F %.2F %.2F %.2F %.2F c',
			($x+$rx)*$k,($h-$y)*$k,
			($x+$rx)*$k,($h-($y-$ly))*$k,
			($x+$lx)*$k,($h-($y-$ry))*$k,
			$x*$k,($h-($y-$ry))*$k));
		$this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',
			($x-$lx)*$k,($h-($y-$ry))*$k,
			($x-$rx)*$k,($h-($y-$ly))*$k,
			($x-$rx)*$k,($h-$y)*$k));
		$this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',
			($x-$rx)*$k,($h-($y+$ly))*$k,
			($x-$lx)*$k,($h-($y+$ry))*$k,
			$x*$k,($h-($y+$ry))*$k));
		$this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c %s',
			($x+$lx)*$k,($h-($y+$ry))*$k,
			($x+$rx)*$k,($h-($y+$ly))*$k,
			($x+$rx)*$k,($h-$y)*$k,
			$op));
	}	
	
	function BasicTable($header, $data, $border = 1)
	{
		// Cabecera
		foreach($header as $col)
			$this->MultiCell(3,0.5,$col, $border);
		$this->Ln();
		// Datos
		foreach($data as $row)
		{
			foreach($row as $col)
				$this->Cell(3,0.5,$col, $border);
			$this->Ln();
		}
	}
	
	
	function Tabla($arrHd, $arrData,$align,$saltos = 1)
	{
		// Cabecera
		$arrAnchoCol = array();
		$x = $this->GetX();
		$y = $this->GetY();
		$this->SetFont('Arial','B',6);
		foreach($arrHd as $i => $hd)
		{
			$anchoCol = 2;
			if(isset ($hd['ancho']))
			{
				$anchoCol = $hd['ancho'];
			}
			
			$altoCol = 0.5;
			if(isset ($hd['alto']))
			{
				$altoCol = $hd['alto'];
			}
			$arrAnchoCol[] = $anchoCol;
			
			$alignCol = 'C';
			if(isset ($hd['align']))
			{
				$alignCol = $hd['align'];
			}
			
			$val = $hd['val'];
			
			$this->MultiCell($anchoCol, $altoCol, $val, $hd['border'], $alignCol, 0);
			$x += $anchoCol;
			$this->SetXY($x, $y);
		}
		
		for ($i = 0; $i< $saltos; ++$i)
		{
			$this->Ln();
		}
		
		// Datos
		foreach($arrData as $row)
		{
			foreach($row as $i => $col)
			{
				$this->SetFont('Arial','',6);
				$this->Cell($arrAnchoCol[$i],0.5,$col, 1,0,$align[$i]);
			}
			$this->Ln();
		}
	}

	function RoundedRect($x, $y, $w, $h, $r, $corners = '1234', $style = '')
	{
		$k = $this->k;
		$hp = $this->h;
		if($style=='F')
			$op='f';
		elseif($style=='FD' || $style=='DF')
			$op='B';
		else
			$op='S';
		$MyArc = 4/3 * (sqrt(2) - 1);
		$this->_out(sprintf('%.2F %.2F m',($x+$r)*$k,($hp-$y)*$k ));

		$xc = $x+$w-$r;
		$yc = $y+$r;
		$this->_out(sprintf('%.2F %.2F l', $xc*$k,($hp-$y)*$k ));
		if (strpos($corners, '2')===false)
			$this->_out(sprintf('%.2F %.2F l', ($x+$w)*$k,($hp-$y)*$k ));
		else
			$this->_Arc($xc + $r*$MyArc, $yc - $r, $xc + $r, $yc - $r*$MyArc, $xc + $r, $yc);

		$xc = $x+$w-$r;
		$yc = $y+$h-$r;
		$this->_out(sprintf('%.2F %.2F l',($x+$w)*$k,($hp-$yc)*$k));
		if (strpos($corners, '3')===false)
			$this->_out(sprintf('%.2F %.2F l',($x+$w)*$k,($hp-($y+$h))*$k));
		else
			$this->_Arc($xc + $r, $yc + $r*$MyArc, $xc + $r*$MyArc, $yc + $r, $xc, $yc + $r);

		$xc = $x+$r;
		$yc = $y+$h-$r;
		$this->_out(sprintf('%.2F %.2F l',$xc*$k,($hp-($y+$h))*$k));
		if (strpos($corners, '4')===false)
			$this->_out(sprintf('%.2F %.2F l',($x)*$k,($hp-($y+$h))*$k));
		else
			$this->_Arc($xc - $r*$MyArc, $yc + $r, $xc - $r, $yc + $r*$MyArc, $xc - $r, $yc);

		$xc = $x+$r ;
		$yc = $y+$r;
		$this->_out(sprintf('%.2F %.2F l',($x)*$k,($hp-$yc)*$k ));
		if (strpos($corners, '1')===false)
		{
			$this->_out(sprintf('%.2F %.2F l',($x)*$k,($hp-$y)*$k ));
			$this->_out(sprintf('%.2F %.2F l',($x+$r)*$k,($hp-$y)*$k ));
		}
		else
			$this->_Arc($xc - $r, $yc - $r*$MyArc, $xc - $r*$MyArc, $yc - $r, $xc, $yc - $r);
		$this->_out($op);
	}

	function _Arc($x1, $y1, $x2, $y2, $x3, $y3)
	{
		$h = $this->h;
		$this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c ', $x1*$this->k, ($h-$y1)*$this->k,
			$x2*$this->k, ($h-$y2)*$this->k, $x3*$this->k, ($h-$y3)*$this->k));
	}
	
        var $flowingBlockAttr;

	function saveFont()
	{

		$saved = array();

		$saved[ 'family' ] = $this->FontFamily;
		$saved[ 'style' ] = $this->FontStyle;
		$saved[ 'sizePt' ] = $this->FontSizePt;
		$saved[ 'size' ] = $this->FontSize;
		$saved[ 'curr' ] =& $this->CurrentFont;

		return $saved;

	}

	function restoreFont( $saved )
	{

		$this->FontFamily = $saved[ 'family' ];
		$this->FontStyle = $saved[ 'style' ];
		$this->FontSizePt = $saved[ 'sizePt' ];
		$this->FontSize = $saved[ 'size' ];
		$this->CurrentFont =& $saved[ 'curr' ];

		if( $this->page > 0)
			$this->_out( sprintf( 'BT /F%d %.2F Tf ET', $this->CurrentFont[ 'i' ], $this->FontSizePt ) );

	}

	function newFlowingBlock( $w, $h, $b = 0, $a = 'J', $f = 0 )
	{

		// cell width in points
		$this->flowingBlockAttr[ 'width' ] = $w * $this->k;

		// line height in user units
		$this->flowingBlockAttr[ 'height' ] = $h;

		$this->flowingBlockAttr[ 'lineCount' ] = 0;

		$this->flowingBlockAttr[ 'border' ] = $b;
		$this->flowingBlockAttr[ 'align' ] = $a;
		$this->flowingBlockAttr[ 'fill' ] = $f;

		$this->flowingBlockAttr[ 'font' ] = array();
		$this->flowingBlockAttr[ 'content' ] = array();
		$this->flowingBlockAttr[ 'contentWidth' ] = 0;

	}

	function finishFlowingBlock()
	{

		$maxWidth =& $this->flowingBlockAttr[ 'width' ];

		$lineHeight =& $this->flowingBlockAttr[ 'height' ];

		$border =& $this->flowingBlockAttr[ 'border' ];
		$align =& $this->flowingBlockAttr[ 'align' ];
		$fill =& $this->flowingBlockAttr[ 'fill' ];

		$content =& $this->flowingBlockAttr[ 'content' ];
		$font =& $this->flowingBlockAttr[ 'font' ];

		// set normal spacing
		$this->_out( sprintf( '%.3F Tw', 0 ) );

		// print out each chunk

		// the amount of space taken up so far in user units
		$usedWidth = 0;

		foreach ( $content as $k => $chunk )
		{

			$b = '';

                        if ( is_int( strpos( $border, 'T' ) ) )
				$b .= 'T';
                        
			if ( is_int( strpos( $border, 'B' ) ) )
				$b .= 'B';

			if ( $k == 0 && is_int( strpos( $border, 'L' ) ) )
				$b .= 'L';

			if ( $k == count( $content ) - 1 && is_int( strpos( $border, 'R' ) ) )
				$b .= 'R';

			$this->restoreFont( $font[ $k ] );

			// if it's the last chunk of this line, move to the next line after
			if ( $k == count( $content ) - 1 )
				$this->Cell( ( $maxWidth / $this->k ) - $usedWidth + 2 * $this->cMargin, $lineHeight, $chunk, $b, 1, $align, $fill );
			else
				$this->Cell( $this->GetStringWidth( $chunk ), $lineHeight, $chunk, $b, 0, $align, $fill );

			$usedWidth += $this->GetStringWidth( $chunk );

		}

	}

	function WriteFlowingBlock( $s )
	{

		// width of all the content so far in points
		$contentWidth =& $this->flowingBlockAttr[ 'contentWidth' ];

		// cell width in points
		$maxWidth =& $this->flowingBlockAttr[ 'width' ];

		$lineCount =& $this->flowingBlockAttr[ 'lineCount' ];

		// line height in user units
		$lineHeight =& $this->flowingBlockAttr[ 'height' ];

		$border =& $this->flowingBlockAttr[ 'border' ];
		$align =& $this->flowingBlockAttr[ 'align' ];
		$fill =& $this->flowingBlockAttr[ 'fill' ];

		$content =& $this->flowingBlockAttr[ 'content' ];
		$font =& $this->flowingBlockAttr[ 'font' ];

		$font[] = $this->saveFont();
		$content[] = '';

		$currContent =& $content[ count( $content ) - 1 ];

		// where the line should be cutoff if it is to be justified
		$cutoffWidth = $contentWidth;

		// for every character in the string
		for ( $i = 0; $i < strlen( $s ); $i++ )
		{

			// extract the current character
			$c = $s[ $i ];

			// get the width of the character in points
			$cw = $this->CurrentFont[ 'cw' ][ $c ] * ( $this->FontSizePt / 1000 );

			if ( $c == ' ' )
			{

				$currContent .= ' ';
				$cutoffWidth = $contentWidth;

				$contentWidth += $cw;

				continue;

			}

			// try adding another char
			if ( $contentWidth + $cw > $maxWidth )
			{

				// won't fit, output what we have
				$lineCount++;

				// contains any content that didn't make it into this print
				$savedContent = '';
				$savedFont = array();

				// first, cut off and save any partial words at the end of the string
				$words = explode( ' ', $currContent );

				// if it looks like we didn't finish any words for this chunk
				if ( count( $words ) == 1 )
				{

					// save and crop off the content currently on the stack
					$savedContent = array_pop( $content );
					$savedFont = array_pop( $font );

					// trim any trailing spaces off the last bit of content
					$currContent =& $content[ count( $content ) - 1 ];

					$currContent = rtrim( $currContent );

				}

				// otherwise, we need to find which bit to cut off
				else
				{

					$lastContent = '';

					for ( $w = 0; $w < count( $words ) - 1; $w++)
						$lastContent .= "{$words[ $w ]} ";

					$savedContent = $words[ count( $words ) - 1 ];
					$savedFont = $this->saveFont();

					// replace the current content with the cropped version
					$currContent = rtrim( $lastContent );

				}

				// update $contentWidth and $cutoffWidth since they changed with cropping
				$contentWidth = 0;

				foreach ( $content as $k => $chunk )
				{

					$this->restoreFont( $font[ $k ] );

					$contentWidth += $this->GetStringWidth( $chunk ) * $this->k;

				}

				$cutoffWidth = $contentWidth;

				// if it's justified, we need to find the char spacing
				if( $align == 'J' )
				{

					// count how many spaces there are in the entire content string
					$numSpaces = 0;

					foreach ( $content as $chunk )
						$numSpaces += substr_count( $chunk, ' ' );

					// if there's more than one space, find word spacing in points
					if ( $numSpaces > 0 )
						$this->ws = ( $maxWidth - $cutoffWidth ) / $numSpaces;
					else
						$this->ws = 0;

					$this->_out( sprintf( '%.3F Tw', $this->ws ) );

				}

				// otherwise, we want normal spacing
				else
					$this->_out( sprintf( '%.3F Tw', 0 ) );

				// print out each chunk
				$usedWidth = 0;

				foreach ( $content as $k => $chunk )
				{

					$this->restoreFont( $font[ $k ] );

					$stringWidth = $this->GetStringWidth( $chunk ) + ( $this->ws * substr_count( $chunk, ' ' ) / $this->k );

					// determine which borders should be used
					$b = '';

					if ( $lineCount == 1 && is_int( strpos( $border, 'T' ) ) )
						$b .= 'T';

					if ( $k == 0 && is_int( strpos( $border, 'L' ) ) )
						$b .= 'L';

					if ( $k == count( $content ) - 1 && is_int( strpos( $border, 'R' ) ) )
						$b .= 'R';

					// if it's the last chunk of this line, move to the next line after
					if ( $k == count( $content ) - 1 )
						$this->Cell( ( $maxWidth / $this->k ) - $usedWidth + 2 * $this->cMargin, $lineHeight, $chunk, $b, 1, $align, $fill );
					else
					{

						$this->Cell( $stringWidth + 2 * $this->cMargin, $lineHeight, $chunk, $b, 0, $align, $fill );
						$this->x -= 2 * $this->cMargin;

					}

					$usedWidth += $stringWidth;

				}

				// move on to the next line, reset variables, tack on saved content and current char
				$this->restoreFont( $savedFont );

				$font = array( $savedFont );
				$content = array( $savedContent . $s[ $i ] );

				$currContent =& $content[ 0 ];

				$contentWidth = $this->GetStringWidth( $currContent ) * $this->k;
				$cutoffWidth = $contentWidth;

			}

			// another character will fit, so add it on
			else
			{

				$contentWidth += $cw;
				$currContent .= $s[ $i ];

			}

		}

	}
	
	 //MultiCell with bullet
    function MultiCellBlt($w, $h, $blt, $txt, $border=0, $align='J', $fill=false)
    {
        //Get bullet width including margins
        $blt_width = $this->GetStringWidth($blt)+$this->cMargin*3;

        //Save x
        $bak_x = $this->x;

        //Output bullet
        $this->Cell($blt_width,$h,$blt,0,'',$fill);

        //Output text
        $this->MultiCell($w-$blt_width,$h,$txt,$border,$align,$fill);

        //Restore x
        $this->x = $bak_x;
    }
}

class PdfAnexo Extends Pdf
{
	function Header()
	{
		// Logo
		$this->Image($_SERVER['DOCUMENT_ROOT'] . '/sipac_oper/reporte/facturacion/Cabezote.jpg',1,0.5,19);
		//$this->Image($_SERVER['DOCUMENT_ROOT'] . '/sipac_oper/reporte/facturacion/logo_encasa.jpg',5,0.5,4);
		
//		$this->SetFont('Arial','B',12);
//
//		$this->SetXY(11.8,1.2);
//		$this->Cell(8,0.5,utf8_decode('ANEXO FACTURACIÓN COLECTIVO PAC'),0,1,'C');
//		$this->SetXY(11.8,1.7);
//		$this->Cell(8,0.5,utf8_decode('PLAN DE ATENCIÓN COMPLEMENTARIA'),0,0,'C');

		// Salto de línea
		$this->Ln();
	}

	// Pie de página
	function Footer()
	{

		$this->SetXY(1,-2.3);

		$this->SetFont('Arial','I',8);

		$this->Cell(19,0.5,'__________________________________________________________________________________________________________________________',0,0,'C');
		$this->Image($_SERVER['DOCUMENT_ROOT'] . '/sipac_oper/reporte/facturacion/logo_nuevaeps.jpg',18.5,26.3,1.5);
		
	}
}

class Pdfcontcolpac Extends Pdf
{
	function Header()
	{
		// Logo
		$this->Image($_SERVER['DOCUMENT_ROOT'] . '/sipac_oper/reporte/contrato_afi/formato/Cabezote_col.jpg',1,0.5,19);
		
		
		$this->SetFont('Arial','B',11);
		$this->SetXY(12,1);
		/*$this->Cell(8,0.5,utf8_decode('Nueva Empresa Promotora de Salud S.A.'),0,1,'R');
		$this->SetX(12);
                $this->SetFont('Arial','B',10);
		$this->Cell(8,0.5,utf8_decode('CARÁTULA CONTRATO COLECTIVO PAC'),0,1,'R');
                $this->SetX(12);
                $this->SetFont('Arial','',8);
		$this->Cell(8,0.5,utf8_decode('Plan de Atención Complementaria'),0,0,'R');*/
                //$this->Image($_SERVER['DOCUMENT_ROOT'] . '/sipac_oper/reporte/facturacion/logo_nuevaeps.jpg',18,$this->GetY()+1.3,2);
		// Salto de línea
		$this->Ln();
	}
        
        // Pie de página
	function Footer()
	{

		$this->SetXY(1,-1.5);
                $this->SetTextColor(132,132,132);
		$this->SetFont('Arial','B',9);

		$this->Cell(3,0.5,'NIT. 900.156.264-2',0,0,'C');
		$this->Image($_SERVER['DOCUMENT_ROOT'] . '/sipac_oper/reporte/contrato_afi/formato/Logo.jpg',16.8,25,3.2);
		
	}

}
/*
//INI.Req.1336 Actualizar caratula contrato PAC- 10-04-2018
class Pdfcontfampac Extends Pdf
{
	
	// FUNCIONES DE SEGURIDAD de PDF con clave de apertura 
		var $encrypted = false;  //whether document is protected
		var $Uvalue;             //U entry in pdf document
		var $Ovalue;             //O entry in pdf document
		var $Pvalue;             //P entry in pdf document
		var $enc_obj_id;         //encryption object id

		/**
		* Function to set permissions as well as user and owner passwords
		*
		* - permissions is an array with values taken from the following list:
		*   copy, print, modify, annot-forms
		*   If a value is present it means that the permission is granted
		* - If a user password is set, user will be prompted before document is opened
		* - If an owner password is set, document can be opened in privilege mode with no
		*   restriction if that password is entered
		*//*
		function SetProtection($permissions=array(), $user_pass='', $owner_pass=null)
		{
			$options = array('print' => 4, 'modify' => 8, 'copy' => 16, 'annot-forms' => 32 );
			$protection = 192;
			foreach($permissions as $permission)
			{
				if (!isset($options[$permission]))
					$this->Error('Incorrect permission: '.$permission);
				$protection += $options[$permission];
			}
			if ($owner_pass === null)
				$owner_pass = uniqid(rand());
			$this->encrypted = true;
			$this->padding = "\x28\xBF\x4E\x5E\x4E\x75\x8A\x41\x64\x00\x4E\x56\xFF\xFA\x01\x08".
							"\x2E\x2E\x00\xB6\xD0\x68\x3E\x80\x2F\x0C\xA9\xFE\x64\x53\x69\x7A";
			$this->_generateencryptionkey($user_pass, $owner_pass, $protection);
		}

		function _putstream($s)
		{
			if ($this->encrypted)
				$s = RC4($this->_objectkey($this->n), $s);
			parent::_putstream($s);
		}

		function _textstring($s)
		{
			if (!$this->_isascii($s))
				$s = $this->_UTF8toUTF16($s);
			if ($this->encrypted)
				$s = RC4($this->_objectkey($this->n), $s);
			return '('.$this->_escape($s).')';
		}

		/**
		* Compute key depending on object number where the encrypted data is stored
		*//*
		function _objectkey($n)
		{
			return substr($this->_md5_16($this->encryption_key.pack('VXxx',$n)),0,10);
		}

		function _putresources()
		{
			parent::_putresources();
			if ($this->encrypted) {
				$this->_newobj();
				$this->enc_obj_id = $this->n;
				$this->_put('<<');
				$this->_putencryption();
				$this->_put('>>');
				$this->_put('endobj');
			}
		}

		function _putencryption()
		{
			$this->_put('/Filter /Standard');
			$this->_put('/V 1');
			$this->_put('/R 2');
			$this->_put('/O ('.$this->_escape($this->Ovalue).')');
			$this->_put('/U ('.$this->_escape($this->Uvalue).')');
			$this->_put('/P '.$this->Pvalue);
		}

		function _puttrailer()
		{
			parent::_puttrailer();
			if ($this->encrypted) {
				$this->_put('/Encrypt '.$this->enc_obj_id.' 0 R');
				$this->_put('/ID [()()]');
			}
		}

		/**
		* Get MD5 as binary string
		*//*
		function _md5_16($string)
		{
			return pack('H*',md5($string));
		}

		/**
		* Compute O value
		*//*
		function _Ovalue($user_pass, $owner_pass)
		{
			$tmp = $this->_md5_16($owner_pass);
			$owner_RC4_key = substr($tmp,0,5);
			return RC4($owner_RC4_key, $user_pass);
		}

		/**
		* Compute U value
		*//*
		function _Uvalue()
		{
			return RC4($this->encryption_key, $this->padding);
		}

		/**
		* Compute encryption key
		*//*
		function _generateencryptionkey($user_pass, $owner_pass, $protection)
		{
			// Pad passwords
			$user_pass = substr($user_pass.$this->padding,0,32);
			$owner_pass = substr($owner_pass.$this->padding,0,32);
			// Compute O value
			$this->Ovalue = $this->_Ovalue($user_pass,$owner_pass);
			// Compute encyption key
			$tmp = $this->_md5_16($user_pass.$this->Ovalue.chr($protection)."\xFF\xFF\xFF");
			$this->encryption_key = substr($tmp,0,5);
			// Compute U value
			$this->Uvalue = $this->_Uvalue();
			// Compute P value
			$this->Pvalue = -(($protection^255)+1);
		}
	// FIN FUNCIONES DE SEGURIDAD de PDF con clave de apertura 

	/*function Header()
	{
		// Logo
		$this->Image($_SERVER['DOCUMENT_ROOT'] . '/sipac_oper/reporte/contrato_afi/formato/Cabezote_fam.jpg',1,0.5,19);
		
		
		$this->SetFont('Arial','B',11);
		$this->SetXY(12,1);
		/*$this->Cell(8,0.5,utf8_decode('Nueva Empresa Promotora de Salud S.A.'),0,1,'R');
		$this->SetX(12);
                $this->SetFont('Arial','B',10);
		$this->Cell(8,0.5,utf8_decode('CARÁTULA CONTRATO COLECTIVO PAC'),0,1,'R');
                $this->SetX(12);
                $this->SetFont('Arial','',8);
		$this->Cell(8,0.5,utf8_decode('Plan de Atención Complementaria'),0,0,'R');*/
                //$this->Image($_SERVER['DOCUMENT_ROOT'] . '/sipac_oper/reporte/facturacion/logo_nuevaeps.jpg',18,$this->GetY()+1.3,2);
		// Salto de línea
		/*$this->Ln();
	}
        
        // Pie de página
	function Footer()
	{

		$this->SetXY(1,-1.5);
                $this->SetTextColor(132,132,132);
		$this->SetFont('Arial','B',9);

		$this->Cell(3,0.5,'NIT. 900.156.264-2',0,0,'C');
		$this->Image($_SERVER['DOCUMENT_ROOT'] . '/sipac_oper/reporte/contrato_afi/formato/Logo.jpg',16.8,25,3.2);
		
	}
	
	
	/////////////////////////////////////////////////////////////////
	var $var_widths;
	var $var_aligns;

	function Setvar_widths($w)
	{
		//Set the array of column var_widths
		$this->var_widths=$w;
	}

	function Setvar_aligns($a)
	{
		//Set the array of column alignments
		$this->var_aligns=$a;
	}

	function Row($data)
	{
		//Calculate the height of the row
		$nb=0;
		for($i=0;$i<count($data);$i++)
			$nb=max($nb,$this->NbLines($this->var_widths[$i],$data[$i]));
		$h=($nb/2);
		//Issue a page break first if needed
		$this->CheckPageBreak($h);
		//Draw the cells of the row
		for($i=0;$i<count($data);$i++)
		{
			$w=$this->var_widths[$i];
			$a=isset($this->var_aligns[$i]) ? $this->var_aligns[$i] : 'L';
			//Save the current position
			$x=$this->GetX();
			$y=$this->GetY();
			//Draw the border
			$this->Rect($x,$y,$w,$h);
			//Print the text
			$this->MultiCell($w,0.4,$data[$i],0,$a);
			//Put the position to the right of the cell
			$this->SetXY($x+$w,$y);
		}
		//Go to the next line
		$this->Ln($h);
	}

	function CheckPageBreak($h)
	{
		//If the height h would cause an overflow, add a new page immediately
		if($this->GetY()+$h>$this->PageBreakTrigger)
			$this->AddPage($this->CurOrientation);
	}

	function NbLines($w,$txt)
	{
		//Computes the number of lines a MultiCell of width w will take
		$cw=&$this->CurrentFont['cw'];
		if($w==0)
			$w=$this->w-$this->rMargin-$this->x;
		$wmax=($w-$this->cMargin)*1000/$this->FontSize;
		$s=str_replace("\r",'',$txt);
		$nb=strlen($s);
		if($nb>0 and $s[$nb-1]=="\n")
			$nb--;
		$sep=-1;
		$i=0;
		$j=0;
		$l=0;
		$nl=1;
		while($i<$nb)
		{
			$c=$s[$i];
			if($c=="\n")
			{
				$i++;
				$sep=-1;
				$j=$i;
				$l=0;
				$nl++;
				continue;
			}
			if($c==' ')
				$sep=$i;
			$l+=$cw[$c];
			if($l>$wmax)
			{
				if($sep==-1)
				{
					if($i==$j)
						$i++;
				}
				else
					$i=$sep+1;
				$sep=-1;
				$j=$i;
				$l=0;
				$nl++;
			}
			else
				$i++;
		}
		return $nl;
	}	
	/////////////////////////////////////////////////////////////////
}
//FIN.Req.1336 Actualizar caratula contrato PAC- 10-04-2018
*/

class PdfAutscpac Extends Pdf
{
	function Header()
	{
		// Logo
		$this->Image($_SERVER['DOCUMENT_ROOT'] . '/sipac_sc/reporte/Autorizacion/formato/Cabezote_autorizacion.jpg',2,0.3,18.1);
		///$this->Image($_SERVER['DOCUMENT_ROOT'] . '/sipac_sc/reporte/Autorizacion/formato/logo_encasa.jpg',5,0.5,3.5);
		
		$this->SetFont('Arial','B',11);
		$this->SetXY(12,1);
		/*$this->Cell(8,0.5,utf8_decode('Nueva Empresa Promotora de Salud S.A.'),0,1,'R');
		$this->SetX(12);
                $this->SetFont('Arial','B',10);
		$this->Cell(8,0.5,utf8_decode('CARÁTULA CONTRATO COLECTIVO PAC'),0,1,'R');
                $this->SetX(12);
                $this->SetFont('Arial','',8);
		$this->Cell(8,0.5,utf8_decode('Plan de Atención Complementaria'),0,0,'R');*/
        $this->Image($_SERVER['DOCUMENT_ROOT'] . '/sipac_sc/reporte/Autorizacion/formato/logo_nuevaeps.jpg',18.3,$this->GetY()+1.1,1.8);
		// Salto de línea
		$this->Ln();
	}
        
        
        // Pie de página
	function Footer()
	{

		$this->SetXY(2,-2.8);

		$this->SetFont('Arial','B',7);

		$this->Cell(6,0.5,utf8_decode('Firma afiliado'),'T',0,'L');
                $this->Cell(5,0.5,'',0,0,'L');
                $this->Cell(7.2,0.5,utf8_decode('Firma funcionario / nombre funcionario / cargo / teléfono'),'T',1,'L');
                $this->Ln();
                $this->SetFont('Arial','B',6);
                $this->RoundedRect(2, $this->GetY(), 18, 0.5, 0.1, '1234', '');
                $this->Cell(18,0.5,utf8_decode('La presente autorización certifica que el usuario tiene derecho a la prestación del servicio. La pertinencia queda sujeta a la verificación de audítoria médica.'),0,0,'C');
		//$this->Image($_SERVER['DOCUMENT_ROOT'] . '/sipac_oper/reporte/facturacion/logo_nuevaeps.jpg',19,26.3,1.5);
		
	}

}

class PdfNegpac Extends Pdf
{
	function Header()
	{
		// Logo
		$this->Image($_SERVER['DOCUMENT_ROOT'] . '/sipac_sc/reporte/Autorizacion/formato/Cabezote_negacion.jpg',1,0.3,19.7);
		
		$this->AddFont('Arial Narrow','','Arial Narrow.php');
		$this->AddFont('Arial Narrow Fett','','Arial Narrow Fett.php');
		$this->SetFont('Arial Narrow','',7);
		$y = $this->GetY()+3;
		$this->SetY($y);
        $this->Image($_SERVER['DOCUMENT_ROOT'] . '/sipac_sc/reporte/Autorizacion/formato/logo_nuevaeps.jpg',18.2,$y,1.8);
		$this->SetFont('Arial Narrow','',6);
		$y = $y+1.2;
		$this->SetY($y);
		$this->Cell(19,0.3,utf8_decode('NIT.  900.156.264-2  '),0,1,'R');  
	}
        
        
        // Pie de página
	function Footer()
	{
		$h = 0.5;
		$y = -2.6;
		$ancho = 19;
		if($this->PageNo() == 1){
			$firma_iz = "Nombre completo y cargo del funcionario";
			$firma_der = "Firma del usuario o de quien recibe";
			$texto = "Si está en desacuerdo con la decisión adoptada, acuda a la Oficina de Atención al Usuario de la entidad. Si su queja no es resuelta, eleve consulta ante la \nSuperintendencia Nacional de Salud, anexando copia de este formato debidamente diligenciado, a la carrera 13 No. 32-76. PBX. 3300210. ";
		}else{	
			$firma_iz = "";
			$firma_der = "";
			$texto = "";
		}
		$this->SetFont('Arial Narrow','',8);
		$this->SetY($y);
		$this->Cell(7,$h,utf8_decode($firma_iz),'T',0,'L');        
		$this->Cell(5,$h,'',0,0,'L');
		$this->Cell(7,$h,utf8_decode($firma_der),'T',1,'L');
		$y = $y + 0.8;
		$this->SetY($y);
		$this->RoundedRect(1, $this->GetY(), $ancho, 1, 0.1, '1234', '');
		$y = $y + 0.2;
		$this->SetY($y);
		$this->MultiCell($ancho,0.3,utf8_decode($texto),0,'C');	
	}
}


class PdfFactura Extends Pdf
{
	function Header(){
		// Logo
		$this->Image($_SERVER['DOCUMENT_ROOT'] . '/sipac_oper/reporte/facturacion/factura/Cabezote.jpg',1,0.3,19);
	}

	// Pie de página
	function Footer(){
		$border = 0;
		$w = 7.1;
		$h = 0.3;
		$font_xxxs = 6.6;
		$this->SetXY(1,-2);
		$this->SetFont('Arial Narrow','',$font_xxxs);
		$this->Cell($w,$h,utf8_decode('* Pago únicamente con cheque de gerencia girado a nombre de NUEVA EPS S.A.'),$border,0,'L');
		$this->SetFont('Arial Narrow Fett','',$font_xxxs);
		$this->Cell($w,$h,utf8_decode('cuenta corriente Bancolombia No. 031 7232288 1.'),$border,1,'L');
		$this->SetFont('Arial Narrow','',$font_xxxs);
		$this->Cell($w,$h,utf8_decode('* No se recibe pago combinado de cheque y efectivo'),$border,1,'L');
		$this->Cell($w,$h,utf8_decode('* No se reciben pagos parciales'),$border,1,'L');
	}
	
	function TextWithRotation($x, $y, $txt, $txt_angle, $font_angle=0)
    {
        $font_angle+=90+$txt_angle;
        $txt_angle*=M_PI/180;
        $font_angle*=M_PI/180;
    
        $txt_dx=cos($txt_angle);
        $txt_dy=sin($txt_angle);
        $font_dx=cos($font_angle);
        $font_dy=sin($font_angle);
    
        $s=sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET',$txt_dx,$txt_dy,$font_dx,$font_dy,$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
        if ($this->ColorFlag)
            $s='q '.$this->TextColor.' '.$s.' Q';
        $this->_out($s);
    }
}

  class eFPDF extends FPDF{
    function TextWithRotation($x, $y, $txt, $txt_angle, $font_angle=0)
    {
        $font_angle+=90+$txt_angle;
        $txt_angle*=M_PI/180;
        $font_angle*=M_PI/180;
    
        $txt_dx=cos($txt_angle);
        $txt_dy=sin($txt_angle);
        $font_dx=cos($font_angle);
        $font_dy=sin($font_angle);
    
        $s=sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET',$txt_dx,$txt_dy,$font_dx,$font_dy,$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
        if ($this->ColorFlag)
            $s='q '.$this->TextColor.' '.$s.' Q';
        $this->_out($s);
    }
  }	
  
class pdf_formato_integralidad Extends Pdf{
	function Header(){
		$this->SetFont('Arial','B',9);
		$this->SetXY(4,1);
		$this->MultiCell(14,0.4,utf8_decode('FORMATO DE JUSTIFICACIÓN MEDICA DE TECNOLOGÍAS EN SALUD ORDENADAS POR FALLOS DE TUTELA QUE NO SEAN EXPRESOS O QUE ORDENEN TRATAMIENTO INTEGRAL CON O SIN COMPARADOR ADMINISTRATIVO'),0,'C');
		$this->Image('logo.png', 1, 0.8, 3);
		$this->Image('logo_nuevaeps.jpg', 18, 0.7, 2);
		$this->SetFillColor(216,216,216);
	}
	
	function Footer(){
	
	}


//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Funciones para uso de Tablas
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////	
	var $widths;
	var $aligns;

	function SetWidths($w)
	{
		//Set the array of column widths
		$this->widths=$w;
	}

	function SetAligns($a)
	{
		//Set the array of column alignments
		$this->aligns=$a;
	}

	function Row($data)
	{
		//Calculate the height of the row
		$nb=0;
		for($i=0;$i<count($data);$i++)
			$nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
		$h=0.3*$nb;
		//Issue a page break first if needed
		$this->CheckPageBreak($h);
		//Draw the cells of the row
		for($i=0;$i<count($data);$i++)
		{
			$w=$this->widths[$i];
			$a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
			//Save the current position
			$x=$this->GetX();
			$y=$this->GetY();
			//Draw the border
			$this->Rect($x,$y,$w,$h);
			//Print the text
			$this->MultiCell($w,0.3,$data[$i],0,$a);
			//Put the position to the right of the cell
			$this->SetXY($x+$w,$y);
		}
		//Go to the next line
		$this->Ln($h);
	}

	function CheckPageBreak($h)
	{
		//If the height h would cause an overflow, add a new page immediately
		if($this->GetY()+$h>$this->PageBreakTrigger)
			$this->AddPage($this->CurOrientation);
	}

	function NbLines($w,$txt)
	{
		//Computes the number of lines a MultiCell of width w will take
		$cw=&$this->CurrentFont['cw'];
		if($w==0)
			$w=$this->w-$this->rMargin-$this->x;
		$wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
		$s=str_replace("\r",'',$txt);
		$nb=strlen($s);
		if($nb>0 and $s[$nb-1]=="\n")
			$nb--;
		$sep=-1;
		$i=0;
		$j=0;
		$l=0;
		$nl=1;
		while($i<$nb)
		{
			$c=$s[$i];
			if($c=="\n")
			{
				$i++;
				$sep=-1;
				$j=$i;
				$l=0;
				$nl++;
				continue;
			}
			if($c==' ')
				$sep=$i;
			$l+=$cw[$c];
			if($l>$wmax)
			{
				if($sep==-1)
				{
					if($i==$j)
						$i++;
				}
				else
					$i=$sep+1;
				$sep=-1;
				$j=$i;
				$l=0;
				$nl++;
			}
			else
				$i++;
		}
		return $nl;
	}	
////////////////////////////////////////////////////////////////////////////////
}

class oficina_virtual_reporte_solicitud Extends Pdf{
	var $widths;
	var $aligns;
	
	function SetWidths($w)
	{
		//Set the array of column widths
		$this->widths=$w;
	}
	
	function SetAligns($a)
	{
		//Set the array of column alignments
		$this->aligns=$a;
	}
	
	function Row($data)
	{
		//Calculate the height of the row
		$nb=0;
		for($i=0;$i<count($data);$i++)
			$nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
		$h=5*$nb;
		//Issue a page break first if needed
		$this->CheckPageBreak($h);
		//Draw the cells of the row
		for($i=0;$i<count($data);$i++)
		{
			$w=$this->widths[$i];
			$a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
			//Save the current position
			$x=$this->GetX();
			$y=$this->GetY();
			//Draw the border
			$this->Rect($x,$y,$w,$h);
			//Print the text
			$this->MultiCell($w,5,$data[$i],0,$a);
			//Put the position to the right of the cell
			$this->SetXY($x+$w,$y);
		}
		//Go to the next line
		$this->Ln($h);
	}
	
	function CheckPageBreak($h)
	{
		//If the height h would cause an overflow, add a new page immediately
		if($this->GetY()+$h>$this->PageBreakTrigger)
			$this->AddPage($this->CurOrientation);
	}
	
	function NbLines($w,$txt)
	{
		//Computes the number of lines a MultiCell of width w will take
		$cw=&$this->CurrentFont['cw'];
		if($w==0)
			$w=$this->w-$this->rMargin-$this->x;
		$wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
		$s=str_replace("\r",'',$txt);
		$nb=strlen($s);
		if($nb>0 and $s[$nb-1]=="\n")
			$nb--;
		$sep=-1;
		$i=0;
		$j=0;
		$l=0;
		$nl=1;
		while($i<$nb)
		{
			$c=$s[$i];
			if($c=="\n")
			{
				$i++;
				$sep=-1;
				$j=$i;
				$l=0;
				$nl++;
				continue;
			}
			if($c==' ')
				$sep=$i;
			$l+=$cw[$c];
			if($l>$wmax)
			{
				if($sep==-1)
				{
					if($i==$j)
						$i++;
				}
				else
					$i=$sep+1;
				$sep=-1;
				$j=$i;
				$l=0;
				$nl++;
			}
			else
				$i++;
		}
		return $nl;
	}
	
	function Header(){
		$this->SetFont('Arial','B',15);		
		$this->Rect(20,10,170,20,'D');
		$this->Image('../logo.jpg',150,12);		
		
		$this->SetXY(20,15);		
		$this->MultiCell(140,10,utf8_decode('RADICACIÓN DE SERVICIOS OFICINA VIRTUAL'),0,'J');			
	}
	function Footer(){
		
	}
}


class PDF_WriteTag extends FPDF
{
    var $wLine; // Maximum width of the line
    var $hLine; // Height of the line
    var $Text; // Text to display
    var $border;
    var $align; // Justification of the text
    var $fill;
    var $Padding;
    var $lPadding;
    var $tPadding;
    var $bPadding;
    var $rPadding;
    var $TagStyle; // Style for each tag
    var $Indent;
    var $Space; // Minimum space between words
    var $PileStyle; 
    var $Line2Print; // Line to display
    var $NextLineBegin; // Buffer between lines 
    var $TagName;
    var $Delta; // Maximum width minus width
    var $StringLength; 
    var $LineLength;
    var $wTextLine; // Width minus paddings
    var $nbSpace; // Number of spaces in the line
    var $Xini; // Initial position
    var $href; // Current URL
    var $TagHref; // URL for a cell

    // Public Functions

    function WriteTag($w, $h, $txt, $border=0, $align="J", $fill=false, $padding=0)
    {
        $this->wLine=$w;
        $this->hLine=$h;
        $this->Text=trim($txt);
        $this->Text=preg_replace("/\n|\r|\t/","",$this->Text);
        $this->border=$border;
        $this->align=$align;
        $this->fill=$fill;
        $this->Padding=$padding;

        $this->Xini=$this->GetX();
        $this->href="";
        $this->PileStyle=array();        
        $this->TagHref=array();
        $this->LastLine=false;

        $this->SetSpace();
        $this->Padding();
        $this->LineLength();
        $this->BorderTop();

        while($this->Text!="")
        {
            $this->MakeLine();
            $this->PrintLine();
        }

        $this->BorderBottom();
    }


    function SetStyle($tag, $family, $style, $size, $color, $indent=-1)
    {
         $tag=trim($tag);
         $this->TagStyle[$tag]['family']=trim($family);
         $this->TagStyle[$tag]['style']=trim($style);
         $this->TagStyle[$tag]['size']=trim($size);
         $this->TagStyle[$tag]['color']=trim($color);
         $this->TagStyle[$tag]['indent']=$indent;
    }


    // Private Functions

    function SetSpace() // Minimal space between words
    {
        $tag=$this->Parser($this->Text);
        $this->FindStyle($tag[2],0);
        $this->DoStyle(0);
        $this->Space=$this->GetStringWidth(" ");
    }


    function Padding()
    {
        if(preg_match("/^.+,/",$this->Padding)) {
            $tab=explode(",",$this->Padding);
            $this->lPadding=$tab[0];
            $this->tPadding=$tab[1];
            if(isset($tab[2]))
                $this->bPadding=$tab[2];
            else
                $this->bPadding=$this->tPadding;
            if(isset($tab[3]))
                $this->rPadding=$tab[3];
            else
                $this->rPadding=$this->lPadding;
        }
        else
        {
            $this->lPadding=$this->Padding;
            $this->tPadding=$this->Padding;
            $this->bPadding=$this->Padding;
            $this->rPadding=$this->Padding;
        }
        if($this->tPadding<$this->LineWidth)
            $this->tPadding=$this->LineWidth;
    }


    function LineLength()
    {
        if($this->wLine==0)
            $this->wLine=$this->w - $this->Xini - $this->rMargin;

        $this->wTextLine = $this->wLine - $this->lPadding - $this->rPadding;
    }


    function BorderTop()
    {
        $border=0;
        if($this->border==1)
            $border="TLR";
        $this->Cell($this->wLine,$this->tPadding,"",$border,0,'C',$this->fill);
        $y=$this->GetY()+$this->tPadding;
        $this->SetXY($this->Xini,$y);
    }


    function BorderBottom()
    {
        $border=0;
        if($this->border==1)
            $border="BLR";
        $this->Cell($this->wLine,$this->bPadding,"",$border,0,'C',$this->fill);
    }


    function DoStyle($tag) // Applies a style
    {
        $tag=trim($tag);
        $this->SetFont($this->TagStyle[$tag]['family'],
            $this->TagStyle[$tag]['style'],
            $this->TagStyle[$tag]['size']);

        $tab=explode(",",$this->TagStyle[$tag]['color']);
        if(count($tab)==1)
            $this->SetTextColor($tab[0]);
        else
            $this->SetTextColor($tab[0],$tab[1],$tab[2]);
    }


    function FindStyle($tag, $ind) // Inheritance from parent elements
    {
        $tag=trim($tag);

        // Family
        if($this->TagStyle[$tag]['family']!="")
            $family=$this->TagStyle[$tag]['family'];
        else
        {
            reset($this->PileStyle);
            while(list($k,$val)=each($this->PileStyle))
            {
                $val=trim($val);
                if($this->TagStyle[$val]['family']!="") {
                    $family=$this->TagStyle[$val]['family'];
                    break;
                }
            }
        }

        // Style
        $style="";
        $style1=strtoupper($this->TagStyle[$tag]['style']);
        if($style1!="N")
        {
            $bold=false;
            $italic=false;
            $underline=false;
            reset($this->PileStyle);
            while(list($k,$val)=each($this->PileStyle))
            {
                $val=trim($val);
                $style1=strtoupper($this->TagStyle[$val]['style']);
                if($style1=="N")
                    break;
                else
                {
                    if(strpos($style1,"B")!==false)
                        $bold=true;
                    if(strpos($style1,"I")!==false)
                        $italic=true;
                    if(strpos($style1,"U")!==false)
                        $underline=true;
                } 
            }
            if($bold)
                $style.="B";
            if($italic)
                $style.="I";
            if($underline)
                $style.="U";
        }

        // Size
        if($this->TagStyle[$tag]['size']!=0)
            $size=$this->TagStyle[$tag]['size'];
        else
        {
            reset($this->PileStyle);
            while(list($k,$val)=each($this->PileStyle))
            {
                $val=trim($val);
                if($this->TagStyle[$val]['size']!=0) {
                    $size=$this->TagStyle[$val]['size'];
                    break;
                }
            }
        }

        // Color
        if($this->TagStyle[$tag]['color']!="")
            $color=$this->TagStyle[$tag]['color'];
        else
        {
            reset($this->PileStyle);
            while(list($k,$val)=each($this->PileStyle))
            {
                $val=trim($val);
                if($this->TagStyle[$val]['color']!="") {
                    $color=$this->TagStyle[$val]['color'];
                    break;
                }
            }
        }
         
        // Result
        $this->TagStyle[$ind]['family']=$family;
        $this->TagStyle[$ind]['style']=$style;
        $this->TagStyle[$ind]['size']=$size;
        $this->TagStyle[$ind]['color']=$color;
        $this->TagStyle[$ind]['indent']=$this->TagStyle[$tag]['indent'];
    }


    function Parser($text)
    {
        $tab=array();
        // Closing tag
        if(preg_match("|^(</([^>]+)>)|",$text,$regs)) {
            $tab[1]="c";
            $tab[2]=trim($regs[2]);
        }
        // Opening tag
        else if(preg_match("|^(<([^>]+)>)|",$text,$regs)) {
            $regs[2]=preg_replace("/^a/","a ",$regs[2]);
            $tab[1]="o";
            $tab[2]=trim($regs[2]);

            // Presence of attributes
            if(preg_match("/(.+) (.+)='(.+)'/",$regs[2])) {
                $tab1=preg_split("/ +/",$regs[2]);
                $tab[2]=trim($tab1[0]);
                while(list($i,$couple)=each($tab1))
                {
                    if($i>0) {
                        $tab2=explode("=",$couple);
                        $tab2[0]=trim($tab2[0]);
                        $tab2[1]=trim($tab2[1]);
                        $end=strlen($tab2[1])-2;
                        $tab[$tab2[0]]=substr($tab2[1],1,$end);
                    }
                }
            }
        }
         // Space
         else if(preg_match("/^( )/",$text,$regs)) {
            $tab[1]="s";
            $tab[2]=' ';
        }
        // Text
        else if(preg_match("/^([^< ]+)/",$text,$regs)) {
            $tab[1]="t";
            $tab[2]=trim($regs[1]);
        }

        $begin=strlen($regs[1]);
         $end=strlen($text);
         $text=substr($text, $begin, $end);
        $tab[0]=$text;

        return $tab;
    }


    function MakeLine()
    {
        $this->Text.=" ";
        $this->LineLength=array();
        $this->TagHref=array();
        $Length=0;
        $this->nbSpace=0;

        $i=$this->BeginLine();
        $this->TagName=array();

        if($i==0) {
            $Length=$this->StringLength[0];
            $this->TagName[0]=1;
            $this->TagHref[0]=$this->href;
        }

        while($Length<$this->wTextLine)
        {
            $tab=$this->Parser($this->Text);
            $this->Text=$tab[0];
            if($this->Text=="") {
                $this->LastLine=true;
                break;
            }

            if($tab[1]=="o") {
                array_unshift($this->PileStyle,$tab[2]);
                $this->FindStyle($this->PileStyle[0],$i+1);

                $this->DoStyle($i+1);
                $this->TagName[$i+1]=1;
                if($this->TagStyle[$tab[2]]['indent']!=-1) {
                    $Length+=$this->TagStyle[$tab[2]]['indent'];
                    $this->Indent=$this->TagStyle[$tab[2]]['indent'];
                }
                if($tab[2]=="a")
                    $this->href=$tab['href'];
            }

            if($tab[1]=="c") {
                array_shift($this->PileStyle);
                if(isset($this->PileStyle[0]))
                {
                    $this->FindStyle($this->PileStyle[0],$i+1);
                    $this->DoStyle($i+1);
                }
                $this->TagName[$i+1]=1;
                if($this->TagStyle[$tab[2]]['indent']!=-1) {
                    $this->LastLine=true;
                    $this->Text=trim($this->Text);
                    break;
                }
                if($tab[2]=="a")
                    $this->href="";
            }

            if($tab[1]=="s") {
                $i++;
                $Length+=$this->Space;
                $this->Line2Print[$i]="";
                if($this->href!="")
                    $this->TagHref[$i]=$this->href;
            }

            if($tab[1]=="t") {
                $i++;
                $this->StringLength[$i]=$this->GetStringWidth($tab[2]);
                $Length+=$this->StringLength[$i];
                $this->LineLength[$i]=$Length;
                $this->Line2Print[$i]=$tab[2];
                if($this->href!="")
                    $this->TagHref[$i]=$this->href;
             }

        }

        trim($this->Text);
        if($Length>$this->wTextLine || $this->LastLine==true)
            $this->EndLine();
    }


    function BeginLine()
    {
        $this->Line2Print=array();
        $this->StringLength=array();

        if(isset($this->PileStyle[0]))
        {
            $this->FindStyle($this->PileStyle[0],0);
            $this->DoStyle(0);
        }

        if(count($this->NextLineBegin)>0) {
            $this->Line2Print[0]=$this->NextLineBegin['text'];
            $this->StringLength[0]=$this->NextLineBegin['length'];
            $this->NextLineBegin=array();
            $i=0;
        }
        else {
            preg_match("/^(( *(<([^>]+)>)* *)*)(.*)/",$this->Text,$regs);
            $regs[1]=str_replace(" ", "", $regs[1]);
            $this->Text=$regs[1].$regs[5];
            $i=-1;
        }

        return $i;
    }


    function EndLine()
    {
        if(end($this->Line2Print)!="" && $this->LastLine==false) {
            $this->NextLineBegin['text']=array_pop($this->Line2Print);
            $this->NextLineBegin['length']=end($this->StringLength);
            array_pop($this->LineLength);
        }

        while(end($this->Line2Print)==="")
            array_pop($this->Line2Print);

        $this->Delta=$this->wTextLine-end($this->LineLength);

        $this->nbSpace=0;
        for($i=0; $i<count($this->Line2Print); $i++) {
            if($this->Line2Print[$i]=="")
                $this->nbSpace++;
        }
    }


    function PrintLine()
    {
        $border=0;
        if($this->border==1)
            $border="LR";
        $this->Cell($this->wLine,$this->hLine,"",$border,0,'C',$this->fill);
        $y=$this->GetY();
        $this->SetXY($this->Xini+$this->lPadding,$y);

        if($this->Indent!=-1) {
            if($this->Indent!=0)
                $this->Cell($this->Indent,$this->hLine);
            $this->Indent=-1;
        }

        $space=$this->LineAlign();
        $this->DoStyle(0);
        for($i=0; $i<count($this->Line2Print); $i++)
        {
            if(isset($this->TagName[$i]))
                $this->DoStyle($i);
            if(isset($this->TagHref[$i]))
                $href=$this->TagHref[$i];
            else
                $href='';
            if($this->Line2Print[$i]=="")
                $this->Cell($space,$this->hLine,"         ",0,0,'C',false,$href);
            else
                $this->Cell($this->StringLength[$i],$this->hLine,$this->Line2Print[$i],0,0,'C',false,$href);
        }

        $this->LineBreak();
        if($this->LastLine && $this->Text!="")
            $this->EndParagraph();
        $this->LastLine=false;
    }


    function LineAlign()
    {
        $space=$this->Space;
        if($this->align=="J") {
            if($this->nbSpace!=0)
                $space=$this->Space + ($this->Delta/$this->nbSpace);
            if($this->LastLine)
                $space=$this->Space;
        }

        if($this->align=="R")
            $this->Cell($this->Delta,$this->hLine);

        if($this->align=="C")
            $this->Cell($this->Delta/2,$this->hLine);

        return $space;
    }


    function LineBreak()
    {
        $x=$this->Xini;
        $y=$this->GetY()+$this->hLine;
        $this->SetXY($x,$y);
    }


    function EndParagraph()
    {
        $border=0;
        if($this->border==1)
            $border="LR";
        $this->Cell($this->wLine,$this->hLine/2,"",$border,0,'C',$this->fill);
        $x=$this->Xini;
        $y=$this->GetY()+$this->hLine/2;
        $this->SetXY($x,$y);
    }

} // End of class


?>

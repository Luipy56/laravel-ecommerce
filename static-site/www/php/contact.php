<?php
session_start();
//setlocale(LC_ALL,"es_ES");
date_default_timezone_set("Europe/Madrid");
include_once 'PHPMailer/PHPMailerAutoload.php';

function EnviaEmail($Email, $NombreDestinatario, $Titulo, $HTML, $Archivo = "")
{
	// SMTP from container env (.env.static) — never hardcode secrets in git
	$Configuracion_CORREOSISTEMASERVIDOR = getenv('STATIC_SMTP_HOST') ?: 'ssl0.ovh.net';
	$Configuracion_CORREOSISTEMAUSUARIO = getenv('STATIC_SMTP_USER') ?: '';
	$Configuracion_CORREOSISTEMAPASSWORD = getenv('STATIC_SMTP_PASSWORD') ?: '';
	$Configuracion_CORREOSISTEMACORREOREMITENTE = getenv('STATIC_SMTP_FROM') ?: (getenv('STATIC_SMTP_USER') ?: '');
	$Configuracion_CORREOSISTEMANOMBREREMITENTE = getenv('STATIC_SMTP_FROM_NAME') ?: 'Serralleria Solidària';
	$Configuracion_CORREOSISTEMAPUERTO = (int) (getenv('STATIC_SMTP_PORT') ?: 465);
	$Configuracion_CORREOSISTEMASECURE = getenv('STATIC_SMTP_SECURE') ?: 'ssl';
	//Create a new PHPMailer instance
	$mail = new PHPMailer;

	//Tell PHPMailer to use SMTP
	$mail->isSMTP();

	//Enable SMTP debugging
	// 0 = off (for production use)
	// 1 = client messages
	// 2 = client and server messages
	$mail->SMTPDebug = 0;

	//Ask for HTML-friendly debug output
	$mail->Debugoutput = 'html';
	$mail->CharSet = 'utf-8';

	//Set the hostname of the mail server
	$mail->Host = $Configuracion_CORREOSISTEMASERVIDOR;
	// use
	// $mail->Host = gethostbyname('smtp.gmail.com');
	// if your network does not support SMTP over IPv6

	//Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
	$mail->Port = $Configuracion_CORREOSISTEMAPUERTO;

	//Set the encryption system to use - ssl (deprecated) or tls
	$mail->SMTPSecure = $Configuracion_CORREOSISTEMASECURE;

	//Whether to use SMTP authentication
	$mail->SMTPAuth = true;

	//Username to use for SMTP authentication - use full email address for gmail
	$mail->Username = $Configuracion_CORREOSISTEMAUSUARIO;

	//Password to use for SMTP authentication
	$mail->Password = $Configuracion_CORREOSISTEMAPASSWORD;

	//Set who the message is to be sent from
	$mail->setFrom($Configuracion_CORREOSISTEMACORREOREMITENTE, $Configuracion_CORREOSISTEMANOMBREREMITENTE);

	//Set an alternative reply-to address
	$mail->addReplyTo($Configuracion_CORREOSISTEMACORREOREMITENTE, $Configuracion_CORREOSISTEMANOMBREREMITENTE);

	//Set who the message is to be sent to
	$mail->addAddress($Email, $NombreDestinatario);
	//$mail->addAddress('jaibanyez@gmail.com', 'John Doe');
	if ($Archivo) {
		$mail->AddAttachment($Archivo);
	}

	//Set the subject line
	$mail->Subject = $Titulo;

	//Read an HTML message body from an external file, convert referenced images to embedded,
	//convert HTML into a basic plain-text alternative body
	//$mail->msgHTML(file_get_contents('examples/contents.html'), dirname(__FILE__));
	$mail->msgHTML($HTML);

	//$mail­>addAttachment($Archivo,"Factura");

	//Replace the plain text body with one created manually
	$mail->AltBody = 'This is a plain-text message body';

	//send the message, check for errors
	return $mail->send();
}


?>
<?php
// echo $Nom . "<br><br>";
// echo $Email . "<br><br>";
// echo $Telefon . "<br><br>";
// echo $Assumpte . "<br><br>";
// echo $Missatge . "<br><br>";
// echo $Mensaje . "<br><br>";

// echo "<pre>";
// print_r($_POST);
// echo "</pre>";
// echo "<pre>";
// print_r($_SERVER);
// echo "</pre>";
?>
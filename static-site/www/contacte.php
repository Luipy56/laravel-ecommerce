<?php
session_start();
include_once("php/contact.php");
include_once("plugins/securimage-master/securimage.php");
$ResultOk = "d-none";
$ResultKo = "d-none";
$ResultCaptcha = "d-none";
$PaginaActual = "contacte";

function DetectaSpam($Email, $Missatge)
{
	if (strpos(strtolower($Email), "serralleria") !== false) {
		return true;
	}
    $Missatge = str_replace(
        array(
            "\\", "¨", "º", "ª", "-", "~",
            "#", "@", "|", "!", "\"", "¬",
            "·", "$", "%", "&", "/",
            "(", ")", "?", "'", "¡",
            "¿", "[", "^", "<code>", "]",
            "+", "}", "{", "¨", "´", "=", "*",
            ">", "<", ";", ",", ":",
            ".", "_"
        ),
        ' ',
        $Missatge
    );
	$Missatge = strtolower($Missatge);
	$arrMissatge = explode(" ", $Missatge);
	if (array_search("lead", $arrMissatge)) {
		return true;
	}
	if (array_search("leads", $arrMissatge)) {
		return true;
	}

	return false;
}

if (isset($_POST["enviar"])) {
	$Nom = $_POST["contact"]["name"]["required"];
	$Email = $_POST["contact"]["email"]["required"];
	$Telefon = $_POST["contact"]["phone"]["required"];
	$Assumpte = $_POST["contact"]["subject"]["required"];
	$Missatge = $_POST["contact"]["message"];

	$securimage = new Securimage(array('namespace' => 'contact'));

	// validate user input
	$valid = $securimage->check(@$_POST['captcha_code']);

	if ($valid) {
		$Data = date("d") . "/" . date("m") . "/" . date("Y") . " " . date("G") . ":" . date("i") . ":" . date("s");

		$Mensaje = '';
		$Mensaje .= '<html>';
		$Mensaje .= '<head>';
		$Mensaje .= '  <link href="http://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet" type="text/css">';
		$Mensaje .= '  <link href="http://fonts.googleapis.com/css?family=Roboto+Slab" rel="stylesheet" type="text/css">';
		$Mensaje .= '  <meta charset="UTF-8">';
		$Mensaje .= '  <title></title>';
		$Mensaje .= '</head>';
		$Mensaje .= '<body style="';
		$Mensaje .= '    font-family: Arial, "Helvetica", sans-serif';
		$Mensaje .= '    color: #535B60;">';
		$Mensaje .= '   <img src="http://www.serralleriasolidaria.cat/images/logoweb.png"><br>';
		$Mensaje .= '   <p style="font-size: 18px;">Consulta desde la pàgina web</p>';
		$Mensaje .= '  <p style="font-size: 14px;">Data: ' . $Data . '</p>';
		$Mensaje .= '  <p style="font-size: 14px;">Nom: ' . $Nom . '</p>';
		$Mensaje .= '  <p style="font-size: 14px;">Email: ' . $Email . '</p>';
		$Mensaje .= '  <p style="font-size: 14px;">Tel&egrave;fon: ' . $Telefon . '</p>';
		$Mensaje .= '  <p style="font-size: 14px;">Assumpte: ' . $Assumpte . '</p>';
		$Mensaje .= '  <p style="font-size: 14px;">Missatge: ' . $Missatge . '</p>';
		$Mensaje .= '  <p style="font-size: 14px;">IP consulta: ' . $_SERVER["REMOTE_ADDR"] . '</p>';
		$Mensaje .= '  <br>';
		$Mensaje .= '  <br>';
		$Mensaje .= '  <p style="font-size: 14px;">Salutacions</p>';
		$Mensaje .= '  <p style="font-size: 14px;">La pàgina web <b>Serralleria Solidària</b></p>';
		$Mensaje .= '</body>';
		$Mensaje .= '</html>';
		if (DetectaSpam($Email, $Missatge)) {
			$EmailOK = EnviaEmail("jaibanyez@gmail.com", "Serralleria Solidària", "[SPAM] Consulta desde la web", $Mensaje);
		} else {
			$EmailOK = EnviaEmail("jj@serralleriasolidaria.cat", "Serralleria Solidària", "Consulta desde la web", $Mensaje);
		}

		if ($EmailOK) {
			$ResultKo = "d-none";
			$ResultOk = "";
			$Nom = "";
			$Email = "";
			$Telefon = "";
			$Assumpte = "";
			$Missatge = "";
		} else {
			$ResultKo = "";
			$ResultOk = "d-none";
		}
	} else {
		$ResultCaptcha = "";
	}
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION["Idioma"] ?>">

<head>
	<meta charset="utf-8" />
	<title>Contacta amb nosaltres</title>
	<meta name="description" content="" />
	<meta name="Author" content="" />
	<link rel="icon" type="image/png" href="/web/images/favicon.png">

	<!-- mobile settings -->
	<meta name="viewport" content="width=device-width, maximum-scale=1, initial-scale=1, user-scalable=0" />
	<!--[if IE]><meta http-equiv='X-UA-Compatible' content='IE=edge,chrome=1'><![endif]-->

	<!-- WEB FONTS : use %7C instead of | (pipe) -->
	<link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600%7CRaleway:300,400,500,600,700%7CLato:300,400,400italic,600,700" rel="stylesheet" type="text/css" />

	<!-- CORE CSS -->
	<link href="/web/css/bootstrap.min.css" rel="stylesheet" type="text/css" />

	<!-- THEME CSS -->
	<link href="/web/css/essentials.css" rel="stylesheet" type="text/css" />
	<link href="/web/css/layout.css?v=1.1" rel="stylesheet" type="text/css" />

	<!-- PAGE LEVEL SCRIPTS -->
	<link href="/web/css/header-1.css" rel="stylesheet" type="text/css" />
	<link href="/web/css/orange.css" rel="stylesheet" type="text/css" id="color_scheme" />
</head>

<!--
		AVAILABLE BODY CLASSES:
		
		smoothscroll 			= create a browser smooth scroll
		enable-animation		= enable WOW animations

		bg-grey					= grey background
		grain-grey				= grey grain background
		grain-blue				= blue grain background
		grain-green				= green grain background
		grain-blue				= blue grain background
		grain-orange			= orange grain background
		grain-yellow			= yellow grain background
		
		boxed 					= boxed layout
		pattern1 ... patern11	= pattern background
		menu-vertical-hide		= hidden, open on click
		
		BACKGROUND IMAGE [together with .boxed class]
		data-background="assets/images/_smarty/boxed_background/1.jpg"
	-->

<body class="smoothscroll enable-animation">

	<!-- wrapper -->
	<div id="wrapper">

		<!-- 
				AVAILABLE HEADER CLASSES

				Default nav height: 96px
				.header-md 		= 70px nav height
				.header-sm 		= 60px nav height

				.b-0 		= remove bottom border (only with transparent use)
				.transparent	= transparent header
				.translucent	= translucent header
				.sticky			= sticky header
				.static			= static header
				.dark			= dark header
				.bottom			= header on bottom
				
				shadow-before-1 = shadow 1 header top
				shadow-after-1 	= shadow 1 header bottom
				shadow-before-2 = shadow 2 header top
				shadow-after-2 	= shadow 2 header bottom
				shadow-before-3 = shadow 3 header top
				shadow-after-3 	= shadow 3 header bottom

				.clearfix		= required for mobile menu, do not remove!

				Example Usage:  class="clearfix sticky header-sm transparent b-0"
			-->
		<?php
		include_once("php/cabecera.php");
		?>




		<!-- 
			Available heights
				h-100
				h-150
				h-200
				h-250
				h-300
				h-350
				h-400
				h-450
				h-500
				h-550
				h-600
			-->



		<!-- 
				PAGE HEADER 
				
				CLASSES:
					.page-header-xs	= 20px margins
					.page-header-md	= 50px margins
					.page-header-lg	= 80px margins
					.page-header-xlg= 130px margins
					.dark			= dark page header

					.shadow-before-1 	= shadow 1 header top
					.shadow-after-1 	= shadow 1 header bottom
					.shadow-before-2 	= shadow 2 header top
					.shadow-after-2 	= shadow 2 header bottom
					.shadow-before-3 	= shadow 3 header top
					.shadow-after-3 	= shadow 3 header bottom
			-->
		<section class="header-solidaritat page-header page-header-xlg b-0 dark" data-background-delay="5000" data-background-fade="1000" data-background="
					demo_files/images/packs/photography/cover/sean-mcauliffe-12854-min.jpg
					,demo_files/images/packs/photography/cover/jorge-rojas-182706-min.jpg
			">

			<span class="overlay dark-4">
				<!-- dark overlay [1 to 9 opacity] -->
			</span>
			<div class="container">

				<small>Contacta amb la</small>
				<h2 class="fs-40 fw-300">SERRALLERIA</h2>

			</div>
		</section>


		<!-- /PAGE HEADER -->



		<!-- -->
		<section>
			<div class="container">

				<div class="row">

					<!-- FORM -->
					<div class="col-md-8">

						<h3>Consulta'ns els teus dubtes</strong></h3>


						<!--
								MESSAGES
								
									How it works?
									The form data is posted to php/contact.php where the fields are verified!
									php.contact.php will redirect back here and will add a hash to the end of the URL:
										#alert_success 		= email sent
										#alert_failed		= email not sent - internal server error (404 error or SMTP problem)
										#alert_mandatory	= email not sent - required fields empty
										Hashes are handled by assets/js/contact.js

									Form data: required to be an array. Example:
										contact[email][required]  WHERE: [email] = field name, [required] = only if this field is required (PHP will check this)
										Also, add `required` to input fields if is a mandatory field. 
										Example: <input required type="email" value="" class="form-control" name="contact[email][required]">

									PLEASE NOTE: IF YOU WANT TO ADD OR REMOVE FIELDS (EXCEPT CAPTCHA), JUST EDIT THE HTML CODE, NO NEED TO EDIT php/contact.php or javascript
												 ALL FIELDS ARE DETECTED DINAMICALY BY THE PHP

									WARNING! Do not change the `email` and `name`!
												contact[name][required] 	- should stay as it is because PHP is using it for AddReplyTo (phpmailer)
												contact[email][required] 	- should stay as it is because PHP is using it for AddReplyTo (phpmailer)
							-->

						<!-- Alert Success -->
						<div id="alert_ok" class="alert alert-success mb-30 <?php echo $ResultOk ?>">
							<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
							<strong>Gracies</strong> El teu missatge s'ha enviat.
						</div><!-- /Alert Success -->


						<!-- Alert Failed -->
						<div id="alert_ko" class="alert alert-danger mb-30 <?php echo $ResultKo ?>">
							<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
							<strong>Ha hagut un error</strong> Intenta-ho més tard.
						</div><!-- /Alert Failed -->


						<!-- Captcha Failed -->
						<div id="alert_ko" class="alert alert-danger mb-30 <?php echo $ResultCaptcha ?>">
							<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
							<strong>El text de comprovació no es correcte.</strong>
						</div><!-- /Alert Failed -->


						<!-- Alert Mandatory -->
						<div id="alert_mandatory" class="alert alert-danger mb-30">
							<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
							<strong>Sorry!</strong> You need to complete all mandatory (*) fields!
						</div><!-- /Alert Mandatory -->


						<form id="formcontacto" action="contacte.php" method="post" enctype="multipart/form-data">
							<fieldset>
								<input type="hidden" name="action" value="contact_send" />

								<div class="row">
									<div class="col-md-4">
										<label for="contact:name">Nom *</label>
										<input required type="text" value="<?php echo $Nom ?>" class="form-control" name="contact[name][required]" id="contact:name">
									</div>
									<div class="col-md-4">
										<label for="contact:email">E-mail *</label>
										<input required type="email" value="<?php echo $Email ?>" class="form-control" name="contact[email][required]" id="contact:email">
									</div>
									<div class="col-md-4">
										<label for="contact:phone">Telèfon *</label>
										<input type="text" value="<?php echo $Telefon ?>" class="form-control" name="contact[phone][required]" id="contact:phone">
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<label for="contact:subject">Assumpte *</label>
										<input required type="text" value="<?php echo $Assumpte ?>" class="form-control" name="contact[subject][required]" id="contact:subject">
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<label for="contact:message">Missatge *</label>
										<textarea required maxlength="10000" rows="8" class="form-control" name="contact[message]" id="contact:message"><?php echo $Missatge ?></textarea>
									</div>
								</div>

								<div class="row mt-5">
									<div class="col-md-12">
										<?php
										// options controlling output of getCaptchaHtml()
										$options1 = array(
											'input_id'   => 'contact_captcha',     // ID of the text input field
											'input_name' => 'captcha_code',        // name of the captcha text field for POST
											'image_id'   => 'contact_captcha_img', // ID of the captcha image
											'namespace'  => 'contact',             // namespace for storing and validating
											'input_text'  => 'Escriu el text de comprovació *',
											'refresh_alt_text' => 'Canviar d\'imatge',
											'refresh_title_text' => 'Canviar d\'imatge',
											'error_html' => $error_html1,          // error (or success) to display to the user above text input
										);
										echo Securimage::getCaptchaHtml($options1);
										?>
									</div>
								</div>
							</fieldset>

							<div class="row">
								<div class="col-md-12">
									<button type="submit" name="enviar" class="btn btn-primary"><i class="fa fa-check"></i>ENVIAR MISSATGE</button>
								</div>
							</div>
						</form>

					</div>
					<!-- /FORM -->


					<!-- INFO -->
					<div class="col-md-4">

						<h2>Contacta'ns</h2>

						<p>
							Estem a la vostra disposició per qualsevol consulta o dubte que tingueu.
						</p>

						<hr />

						<p>
							<!--<span class="block"><strong><i class="fa fa-map-marker"></i> Address:</strong> Street Name, City Name, Country</span> -->
							<span class="block"><strong><i class="fa fa-phone"></i> Telèfon:</strong> <a href="tel:">600 500 517</a></span>
							<span class="block"><strong><i class="fa fa-envelope"></i> Email:</strong> <a href="mailto:empresa@serralleriasolidaria.cat">empresa@serralleriasolidaria.cat</a></span>
						</p>

						<hr />

					</div>
					<!-- /INFO -->

				</div>

			</div>
		</section>
		<!-- / -->




		<!-- FOOTER -->
		<?php
		include_once("php/pie.php");
		?>
		<!-- /FOOTER -->

	</div>
	<!-- /wrapper -->




	<!-- JAVASCRIPT FILES -->
	<script>
		var plugin_path = 'plugins/';
	</script>
	<script src="/web/js/jquery-3.3.1.min.js"></script>

	<script src="/web/js/scripts.js"></script>

	<!-- PAGELEVEL SCRIPTS -->
	<script src="assets/js/contact.js"></script>

	<!-- 
			GMAP.JS 
			http://hpneo.github.io/gmaps/
		-->
	<script src="//maps.google.com/maps/api/js?key=AIzaSyCqCn84CgZN6o1Xc3P4dM657HIxkX3jzPY"></script>
	<script src="assets/plugins/gmaps.js"></script>
	<script>
		jQuery(document).ready(function() {

			/**
				@BASIC GOOGLE MAP
			**/
			var map2 = new GMaps({
				div: '#map2',
				lat: -12.043333,
				lng: -77.028333,
				scrollwheel: false
			});

			var marker = map2.addMarker({
				lat: -12.043333,
				lng: -77.028333,
				title: 'Company, Inc.'
			});

		});
	</script>

</body>

</html>
<?php
session_start();
$PaginaActual = "webs";
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION["Idioma"] ?>">

<head>
	<meta charset="utf-8" />
	<title>Webs d'interès</title>
	<meta name="description" content="" />
	<meta name="Author" content="Dorin Grigoras [www.stepofweb.com]" />
	<link rel="icon" type="image/png" href="/web/images/favicon.png">

	<!-- mobile settings -->
	<meta name="viewport" content="width=device-width, maximum-scale=1, initial-scale=1, user-scalable=0" />
	<!--[if IE]><meta http-equiv='X-UA-Compatible' content='IE=edge,chrome=1'><![endif]-->

	<!-- WEB FONTS : use %7C instead of | (pipe) -->
	<link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600%7CRaleway:300,400,500,600,700%7CLato:300,400,400italic,600,700" rel="stylesheet" type="text/css" />

	<!-- CORE CSS -->
	<link href="/web/css/bootstrap.min.css" rel="stylesheet" type="text/css" />

	<!-- REVOLUTION SLIDER -->
	<link href="/web/css/slider.revolution/extralayers.css" rel="stylesheet" type="text/css" />
	<link href="/web/css/slider.revolution/settings.css" rel="stylesheet" type="text/css" />

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

		<?php
		include_once("php/cabecera.php");
		?>

		<section class="header-solidaritat page-header page-header-xlg b-0 dark" data-background-delay="5000" data-background-fade="1000" data-background="
					demo_files/images/packs/photography/cover/sean-mcauliffe-12854-min.jpg
					,demo_files/images/packs/photography/cover/jorge-rojas-182706-min.jpg
			">

			<span class="overlay dark-4">
				<!-- dark overlay [1 to 9 opacity] --></span>
			<div class="container">

				<small>Selecció de pàgines web de la</small>
				<h2 class="fs-40 fw-300">SERRALLERIA</h2>

			</div>
		</section>

		<section class="section-sm b-0">
			<div class="container">

				<div class="row">

					<div class="col-md-8">

						<div class="heading-title heading-border-bottom heading-color">
							<h3>Selecció de pàgines web de la Serralleria</h3>
						</div>


					</div>


				</div>

				<div class="row">
					<div class="col-12">
						<b>Proveïdors</b> <i class="fa fa-chevron-down"></i><br><br>
					</div>
				</div>

				<div class="row">

					<div class="col-12 col-md-6 col-lg-4 col-xl-3">

						<div class="linkswebs">
							<div class="logoweb">
								<img alt="Maldonado" src="images/webs/logo_maldonado.png">
							</div>
							<hr />
							<div class="textweb">
								<p>Maldonado</p>
							</div>
							<hr />
							<a href="https://www.maldonado.es/" target="_blank" class="btn btn-3d btn-teal">Obrir web</a>
						</div>
					</div>

					<div class="col-12 col-md-6 col-lg-4 col-xl-3">

						<div class="linkswebs">
							<div class="logoweb">
								<img alt="Seguridad Bilma" src="images/webs/logo_bilma.png">
							</div>
							<hr />
							<div class="textweb">
								<p>Seguridad Bilma</p>
							</div>
							<hr />
							<a href="https://www.seguridadbilma.com/" target="_blank" class="btn btn-3d btn-teal">Obrir web</a>
						</div>
					</div>

					<div class="col-12 col-md-6 col-lg-4 col-xl-3">

						<div class="linkswebs">
							<div class="logoweb">
								<img alt="INN Solutions" src="images/webs/logo_inn.png">
							</div>
							<hr />
							<div class="textweb">
								<p>INN Solutions</p>
							</div>
							<hr />
							<a href="https://innmotion.es/" target="_blank" class="btn btn-3d btn-teal">Obrir web</a>
						</div>
					</div>

				</div>

				<div class="row">
					<div class="col-12">
						<b>Companys de feina</b> <i class="fa fa-chevron-down"></i><br><br>
					</div>
				</div>

				<div class="row">

					<div class="col-12 col-md-6 col-lg-4 col-xl-3">

						<div class="linkswebs">
							<div class="logoweb">
								<img alt="Serralleria Obre i tanca" src="images/webs/logo_obreitanca.jpg">
							</div>
							<hr />
							<div class="textweb">
								<p>Serralleria Obre i tanca</p>
							</div>
							<hr />
							<a href="https://www.facebook.com/Obre-i-Tanca-Serrallers-Barcelona-197395303615537/" target="_blank" class="btn btn-3d btn-teal">Obrir web</a>
						</div>
					</div>

					<div class="col-12 col-md-6 col-lg-4 col-xl-3">

						<div class="linkswebs">
							<div class="logoweb">
								<img alt="Serralleria Panys i Panys" src="images/webs/logo_panysipanys.png">
							</div>
							<hr />
							<div class="textweb">
								<p>Serralleria Panys i Panys</p>
							</div>
							<hr />
							<a href="https://www.panysipanys.cat/" target="_blank" class="btn btn-3d btn-teal">Obrir web</a>
						</div>
					</div>

					<div class="col-12 col-md-6 col-lg-4 col-xl-3">

						<div class="linkswebs">
							<div class="logoweb">
								<img alt="Carol Automatismos" src="images/webs/carol_automatismos.png">
							</div>
							<hr />
							<div class="textweb">
								<p>Carol Automatismos</p>
							</div>
							<hr />
							<a href="http://carolautomatismos.com/" target="_blank" class="btn btn-3d btn-teal">Obrir web</a>
						</div>
					</div>

				</div>

				<div class="row">
					<div class="col-12">
						<b>Col·laborem amb...</b> <i class="fa fa-chevron-down"></i><br><br>
					</div>
				</div>

				<div class="row">

					<div class="col-12 col-md-6 col-lg-4 col-xl-3">

						<div class="linkswebs">
							<div class="logoweb">
								<img alt="Club de Futbol Sala CO Compartir (Associació Gabella)" src="images/webs/logo_associaciogabella.jpg">
							</div>
							<hr />
							<div class="textweb">
								<p>Club de Futbol Sala CO Compartir (Associació Gabella)</p>
							</div>
							<hr />
							<a href="http://www.maristes.cat/obres-socials/club-futbol-sala-compartir-ciutat-vella-barcelona" target="_blank" class="btn btn-3d btn-teal">Obrir web</a>
						</div>
					</div>

					<div class="col-12 col-md-6 col-lg-4 col-xl-3">

						<div class="linkswebs">
							<div class="logoweb">
								<img alt="Centre Obert Rialles" src="images/webs/logo_centreobertrialles.jpg">
							</div>
							<hr />
							<div class="textweb">
								<p>Centre Obert Rialles</p>
							</div>
							<hr />
							<a href="http://www.maristes.cat/obres-socials/centre-obert-rialles-sta-coloma-de-gramenet" target="_blank" class="btn btn-3d btn-teal">Obrir web</a>
						</div>
					</div>

					<div class="col-12 col-md-6 col-lg-4 col-xl-3">

						<div class="linkswebs">
							<div class="logoweb">
								<img alt="Associació Aprenem" src="images/webs/logo_aprenem.png">
							</div>
							<hr />
							<div class="textweb">
								<p>Associació Aprenem</p>
							</div>
							<hr />
							<a href="https://www.associacioaprenem.org/" target="_blank" class="btn btn-3d btn-teal">Obrir web</a>
						</div>
					</div>

					<div class="col-12 col-md-6 col-lg-4 col-xl-3">

						<div class="linkswebs">
							<div class="logoweb">
								<img alt="Fundació Esperanza" src="images/webs/logo_fundacioesperanzah.png">
							</div>
							<hr />
							<div class="textweb">
								<p>Fundació Esperanzah</p>
							</div>
							<hr />
							<a href="http://www.fundacioesperanzah.org" target="_blank" class="btn btn-3d btn-teal">Obrir web</a>
						</div>
					</div>

					<div class="col-12 col-md-6 col-lg-4 col-xl-3">

						<div class="linkswebs">
							<div class="logoweb">
								<img alt="Centre Educatiu Esclat" src="images/webs/centre-educatiu-esclat.png">
							</div>
							<hr />
							<div class="textweb">
								<p>Centre Educatiu Esclat</p>
							</div>
							<hr />
							<a href="https://esclat.org/" target="_blank" class="btn btn-3d btn-teal">Obrir web</a>
						</div>
					</div>

				</div>


				<div class="row">
					<div class="col-12">
						<b>Economia Social i Solidària</b> <i class="fa fa-chevron-down"></i><br><br>
					</div>
				</div>

				<div class="row">

					<div class="col-12 col-md-6 col-lg-4 col-xl-3">

						<div class="linkswebs">
							<div class="logoweb">
								<img alt="Pam a Pam" src="images/webs/logo_pamapam.png">
							</div>
							<hr />
							<div class="textweb">
								<p>Pam a Pam</p>
							</div>
							<hr />
							<a href="https://pamapam.org/ca/" target="_blank" class="btn btn-3d btn-teal">Obrir web</a>
						</div>
					</div>

					<div class="col-12 col-md-6 col-lg-4 col-xl-3">

						<div class="linkswebs">
							<div class="logoweb">
								<img alt="Compras solidarias" src="images/webs/wapsi.png">
							</div>
							<hr />
							<div class="textweb">
								<p>Compras solidarias</p>
							</div>
							<hr />
							<a href="https://www.wapsi.org/" target="_blank" class="btn btn-3d btn-teal">Obrir web</a>
						</div>
					</div>

					<div class="col-12 col-md-6 col-lg-4 col-xl-3">

						<div class="linkswebs">
							<div class="logoweb">
								<img alt="Fundació Nzuri Daima" src="images/webs/nzuri-daima.png">
							</div>
							<hr />
							<div class="textweb">
								<p>Fundació Nzuri Daima</p>
							</div>
							<hr />
							<a href="https://nzuri-daima.org/" target="_blank" class="btn btn-3d btn-teal">Obrir web</a>
						</div>
					</div>

					<div class="col-12 col-md-6 col-lg-4 col-xl-3">

						<div class="linkswebs">
							<div class="logoweb">
								<img alt="Kalli Luz Marina" src="images/webs/kalli-luz-marina.png">
							</div>
							<hr />
							<div class="textweb">
								<p>Kalli Luz Marina</p>
							</div>
							<hr />
							<a href="https://kalliluzmarina.org/" target="_blank" class="btn btn-3d btn-teal">Obrir web</a>
						</div>
					</div>

					<div class="col-12 col-md-6 col-lg-4 col-xl-3">

						<div class="linkswebs">
							<div class="logoweb">
								<img alt="Xarxa d’Economia Solidària" src="images/webs/logo_xes.png">
							</div>
							<hr />
							<div class="textweb">
								<p>Xarxa d’Economia Solidària</p>
							</div>
							<hr />
							<a href="http://xes.cat/" target="_blank" class="btn btn-3d btn-teal">Obrir web</a>
						</div>
					</div>

					<div class="col-12 col-md-6 col-lg-4 col-xl-3">

						<div class="linkswebs">
							<div class="logoweb">
								<img alt="Economia Social Catalunya" src="images/webs/logo_economiasocialbarcelona.png">
							</div>
							<hr />
							<div class="textweb">
								<p>Economia Social Catalunya</p>
							</div>
							<hr />
							<a href="https://economiasocialcatalunya.cat/economia-social/" target="_blank" class="btn btn-3d btn-teal">Obrir web</a>
						</div>
					</div>

					<div class="col-12 col-md-6 col-lg-4 col-xl-3">

						<div class="linkswebs">
							<div class="logoweb">
								<img alt="Ajuntament de Barcelona (ESS)" src="images/webs/logo_ajuntamentbarcelona.png">
							</div>
							<hr />
							<div class="textweb">
								<p>Ajuntament de Barcelona (ESS)</p>
							</div>
							<hr />
							<a href="https://ajuntament.barcelona.cat/economia-social-solidaria/ca" target="_blank" class="btn btn-3d btn-teal">Obrir web</a>
						</div>
					</div>

				</div>

				<div class="row">
					<div class="col-12">
						<b>Comerç de proximitat</b> <i class="fa fa-chevron-down"></i><br><br>
					</div>
				</div>


				<div class="row">

					<div class="col-12 col-md-6 col-lg-4 col-xl-3">

						<div class="linkswebs">
							<div class="logoweb">
								<img alt="Ferreteria Larsi" src="images/webs/ferreteria_larsi.png">
							</div>
							<hr />
							<div class="textweb">
								<p>Ferreteria Larsi</p>
							</div>
							<hr />
							<a href="https://www.ferreterialarsi.com/ca/" target="_blank" class="btn btn-3d btn-teal">Obrir web</a>
						</div>
					</div>

					<div class="col-12 col-md-6 col-lg-4 col-xl-3">

						<div class="linkswebs">
							<div class="logoweb">
								<img alt="Naikare" src="images/webs/naikare.png">
							</div>
							<hr />
							<div class="textweb">
								<p>Naikare</p>
							</div>
							<hr />
							<a href="https://www.naikare.es/" target="_blank" class="btn btn-3d btn-teal">Obrir web</a>
						</div>
					</div>

				</div>

				<div class="row">
					<div class="col-12">
						<b>Webs d'interès</b> <i class="fa fa-chevron-down"></i><br><br>
					</div>
				</div>


				<div class="row">

					<div class="col-12 col-md-6 col-lg-4 col-xl-3">

						<div class="linkswebs">
							<div class="logoweb">
								<img alt="Pimec" src="images/webs/logo_pimec.png">
							</div>
							<hr />
							<div class="textweb">
								<p>Pimec</p>
							</div>
							<hr />
							<a href="https://www.pimec.org/" target="_blank" class="btn btn-3d btn-teal">Obrir web</a>
						</div>
					</div>

					<div class="col-12 col-md-6 col-lg-4 col-xl-3">

						<div class="linkswebs">
							<div class="logoweb">
								<img alt="Cambra de Comerç de Barcelona" src="images/webs/logo_cambrabarcelona.png">
							</div>
							<hr />
							<div class="textweb">
								<p>Cambra de Comerç de Barcelona</p>
							</div>
							<hr />
							<a href="https://www.cambrabcn.org/" target="_blank" class="btn btn-3d btn-teal">Obrir web</a>
						</div>
					</div>

					<div class="col-12 col-md-6 col-lg-4 col-xl-3">

						<div class="linkswebs">
							<div class="logoweb">
								<img alt="Som energia" src="images/webs/logo-som-energia-blog.png">
							</div>
							<hr />
							<div class="textweb">
								<p>Som energia</p>
							</div>
							<hr />
							<a href="https://blog.somenergia.coop/" target="_blank" class="btn btn-3d btn-teal">Obrir web</a>
						</div>
					</div>

					<div class="col-12 col-md-6 col-lg-4 col-xl-3">

						<div class="linkswebs">
							<div class="logoweb">
								<img alt="Pont Solidari" src="images/webs/logo-pontsolidari.jpg">
							</div>
							<hr />
							<div class="textweb">
								<p>Pont Solidari</p>
							</div>
							<hr />
							<a href="https://www.pontsolidari.org/" target="_blank" class="btn btn-3d btn-teal">Obrir web</a>
						</div>
					</div>

					<div class="col-12 col-md-6 col-lg-4 col-xl-3">

						<div class="linkswebs">
							<div class="logoweb">
								<img alt="Fundació banc de recursos" src="images/webs/logo-banc-de-recursos.png">
							</div>
							<hr />
							<div class="textweb">
								<p>Fundació banc de recursos</p>
							</div>
							<hr />
							<a href="https://www.bancderecursos.org/" target="_blank" class="btn btn-3d btn-teal">Obrir web</a>
						</div>
					</div>

				</div>


			</div>
		</section>





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

	<!-- REVOLUTION SLIDER -->
	<script src="/web/js/slider.revolution/jquery.themepunch.tools.min.js"></script>
	<script src="/web/js/slider.revolution/jquery.themepunch.revolution.min.js"></script>
	<script src="/web/js/slider.revolution/demo.revolution_slider.js"></script>

	<!-- SCRIPTS -->
	<script src="/web/js/scripts.js"></script>

</body>

</html>
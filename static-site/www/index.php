<?php
session_start();
if (!isset($_SESSION["Idioma"])) {
	$_SESSION["Idioma"] = "ca";
}
$PaginaActual = "index";
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION["Idioma"] ?>">

<head>
	<meta charset="utf-8" />
	<title>Serralleria Solidària</title>
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

	<!-- THEME CSS -->
	<link href="/web/css/essentials.css" rel="stylesheet" type="text/css" />
	<link href="/web/css/layout.css?v=1.1" rel="stylesheet" type="text/css" />

	<!-- PAGE LEVEL SCRIPTS -->
	<link href="/web/css/header-1.css" rel="stylesheet" type="text/css" />
	<link href="/web/css/orange.css" rel="stylesheet" type="text/css" id="color_scheme" />

	<!-- REVOLUTION SLIDER -->
	<link href="/web/plugins/slider/css/extralayers.css" rel="stylesheet" type="text/css" />
	<link href="/web/plugins/slider/css/settings.css" rel="stylesheet" type="text/css" />
<?php include_once __DIR__ . '/php/seo-head.php'; ?>
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



		<!-- REVOLUTION SLIDER -->
		<section id="slider" class="fullwidthbanner-container roundedcorners">
			<!--
					Navigation Styles:
					
						data-navigationStyle="" theme default navigation
						
						data-navigationStyle="preview1"
						data-navigationStyle="preview2"
						data-navigationStyle="preview3"
						data-navigationStyle="preview4"
						
					Bottom Shadows
						data-shadow="1"
						data-shadow="2"
						data-shadow="3"
						
					Slider Height (do not use on fullscreen mode)
						data-height="300"
						data-height="350"
						data-height="400"
						data-height="450"
						data-height="500"
						data-height="550"
						data-height="600"
						data-height="650"
						data-height="700"
						data-height="750"
						data-height="800"
				-->
			<div class="fullscreenbanner" data-navigationStyle="preview4">
				<ul class="hide">

					<!-- SLIDE -->
					<li data-transition="fade" data-slotamount="1" data-masterspeed="1000" data-saveperformance="off" data-title="Serralleria Solidària" data-thumb="/web/images/neuron_thumb.jpg">

						<img src="/web/images/1x1.png" data-lazyload="/web/images/neuron.jpg" alt="video" data-bgposition="center center" data-bgfit="cover" data-bgrepeat="no-repeat">

						<div class="tp-caption tp-fade fadeout fullscreenvideo" data-x="0" data-y="0" data-speed="1000" data-start="1100" data-easing="Power4.easeOut" data-elementdelay="0.01" data-endelementdelay="0.1" data-endspeed="1500" data-endeasing="Power4.easeIn" data-autoplay="true" data-autoplayonlyfirsttime="false" data-nextslideatend="true" data-volume="mute" data-forceCover="1" data-aspectratio="16:9" data-forcerewind="on" style="z-index: 2;">

							<div class="overlay dark-4">
								<!-- dark overlay [1 to 9 opacity] -->
							</div>

							<video class="fullvideo" preload="none" poster="demo_files/images/video/neuron.jpg">
								<source src="/web/images/neuron.webm" type="video/webm" />
								<source src="/web/images/neuron.mp4" type="video/mp4" />
							</video>

						</div>

						<div class="tp-caption customin ltl tp-resizeme text_white" data-x="center" data-y="180" data-customin="x:0;y:150;z:0;rotationZ:0;scaleX:1;scaleY:1;skewX:0;skewY:0;opacity:0;transformPerspective:200;transformOrigin:50% 0%;" data-speed="800" data-start="1000" data-easing="easeOutQuad" data-splitin="none" data-splitout="none" data-elementdelay="0.01" data-endelementdelay="0.1" data-endspeed="1000" data-endeasing="Power4.easeIn" style="z-index: 3;">
							<span class="fw-300">BENVINGUTS A</span>
						</div>

						<div class="tp-caption customin ltl tp-resizeme large_bold_white" data-x="center" data-y="230" data-customin="x:0;y:150;z:0;rotationZ:0;scaleX:1;scaleY:1;skewX:0;skewY:0;opacity:0;transformPerspective:200;transformOrigin:50% 0%;" data-speed="800" data-start="1200" data-easing="easeOutQuad" data-splitin="none" data-splitout="none" data-elementdelay="0.01" data-endelementdelay="0.1" data-endspeed="1000" data-endeasing="Power4.easeIn" style="z-index: 3;">
							SERRALLERIA SOLIDÀRIA
						</div>

						<div class="tp-caption customin ltl tp-resizeme small_light_white" data-x="center" data-y="350" data-customin="x:0;y:150;z:0;rotationZ:0;scaleX:1;scaleY:1;skewX:0;skewY:0;opacity:0;transformPerspective:200;transformOrigin:50% 0%;" data-speed="800" data-start="1400" data-easing="easeOutQuad" data-splitin="none" data-splitout="none" data-elementdelay="0.01" data-endelementdelay="0.1" data-endspeed="1000" data-endeasing="Power4.easeIn" style="z-index: 3; width: 750px; max-width: 750px; white-space: normal; text-align:center;">
							Professionals qualificats al teu servei.
						</div>

						<div class="tp-caption customin ltl tp-resizeme" data-x="center" data-y="438" data-customin="x:0;y:150;z:0;rotationZ:0;scaleX:1;scaleY:1;skewX:0;skewY:0;opacity:0;transformPerspective:200;transformOrigin:50% 0%;" data-speed="800" data-start="1550" data-easing="easeOutQuad" data-splitin="none" data-splitout="none" data-elementdelay="0.01" data-endelementdelay="0.1" data-endspeed="1000" data-endeasing="Power4.easeIn" style="z-index: 3;">
							<a href="contacte.php" class="btn btn-default btn-lg">
								<span>Contacta'ns</span>
							</a>
						</div>

					</li>

					<!-- SLIDE  -->
					<li data-transition="random" data-slotamount="1" data-masterspeed="1000" data-saveperformance="off" data-title="Al teu servei" data-thumb="/web/images/LLAVE-Y-BOMBIN-INN.jpg">

						<img src="assets/images/_smarty/1x1.png" data-lazyload="/web/images/LLAVE-Y-BOMBIN-INN.jpg" alt="" data-bgposition="center center" data-kenburns="on" data-duration="10000" data-ease="Linear.easeNone" data-bgfit="100" data-bgfitend="110" />

						<div class="overlay dark-1">
							<!-- dark overlay [1 to 9 opacity] -->
						</div>

						<div class="tp-caption customin ltl tp-resizeme text_white" data-x="center" data-y="180" data-customin="x:0;y:150;z:0;rotationZ:0;scaleX:1;scaleY:1;skewX:0;skewY:0;opacity:0;transformPerspective:200;transformOrigin:50% 0%;" data-speed="800" data-start="1000" data-easing="easeOutQuad" data-splitin="none" data-splitout="none" data-elementdelay="0.01" data-endelementdelay="0.1" data-endspeed="1000" data-endeasing="Power4.easeIn" style="z-index: 10;">
							<span class="fw-300">SERRALLERIA SOLIDÀRIA</span>
						</div>

						<div class="tp-caption customin ltl tp-resizeme large_bold_white" data-x="center" data-y="230" data-customin="x:0;y:150;z:0;rotationZ:0;scaleX:1;scaleY:1;skewX:0;skewY:0;opacity:0;transformPerspective:200;transformOrigin:50% 0%;" data-speed="800" data-start="1200" data-easing="easeOutQuad" data-splitin="none" data-splitout="none" data-elementdelay="0.01" data-endelementdelay="0.1" data-endspeed="1000" data-endeasing="Power4.easeIn" style="z-index: 10;">
							600 500 517 - Al teu servei
						</div>

						<div class="tp-caption customin ltl tp-resizeme small_light_white" data-x="center" data-y="350" data-customin="x:0;y:150;z:0;rotationZ:0;scaleX:1;scaleY:1;skewX:0;skewY:0;opacity:0;transformPerspective:200;transformOrigin:50% 0%;" data-speed="800" data-start="1400" data-easing="easeOutQuad" data-splitin="none" data-splitout="none" data-elementdelay="0.01" data-endelementdelay="0.1" data-endspeed="1000" data-endeasing="Power4.easeIn" style="z-index: 10; width: 750px; max-width: 750px; white-space: normal; text-align:center;">
							Si tens dubtes sobre seguretat truca'ns.
						</div>

						<div class="tp-caption customin ltl tp-resizeme" data-x="center" data-y="438" data-customin="x:0;y:150;z:0;rotationZ:0;scaleX:1;scaleY:1;skewX:0;skewY:0;opacity:0;transformPerspective:200;transformOrigin:50% 0%;" data-speed="800" data-start="1550" data-easing="easeOutQuad" data-splitin="none" data-splitout="none" data-elementdelay="0.01" data-endelementdelay="0.1" data-endspeed="1000" data-endeasing="Power4.easeIn" style="z-index: 10;">
							<a href="contacte.php" class="btn btn-default btn-lg">
								<span>Contacta'ns</span>
							</a>
						</div>

					</li>

					<!-- SLIDE  funciona -->
					<li data-transition="random" data-slotamount="1" data-masterspeed="1000" data-saveperformance="off" data-title="Ens adaptem a tu" data-thumb="/web/images/Cil-INN-interior.png">

						<img src="assets/images/_smarty/1x1.png" data-lazyload="/web/images/Cil-INN-interior.png" alt="" data-bgposition="center center" data-kenburns="on" data-duration="10000" data-ease="Linear.easeNone" data-bgfit="100" data-bgfitend="110" />

						<div class="overlay dark-1">
							<!-- dark overlay [1 to 9 opacity] -->
						</div>

						<div class="tp-caption customin ltl tp-resizeme large_bold_white" data-x="center" data-y="180" data-customin="x:0;y:150;z:0;rotationZ:0;scaleX:1;scaleY:1;skewX:0;skewY:0;opacity:0;transformPerspective:200;transformOrigin:50% 0%;" data-speed="800" data-start="1200" data-easing="easeOutQuad" data-splitin="none" data-splitout="none" data-elementdelay="0.01" data-endelementdelay="0.1" data-endspeed="1000" data-endeasing="Power4.easeIn" style="z-index: 3;">
							SERRALLERIA SOLIDÀRIA
						</div>

						<div class="tp-caption customin ltl tp-resizeme text_white" data-x="center" data-y="270" data-customin="x:0;y:150;z:0;rotationZ:0;scaleX:1;scaleY:1;skewX:0;skewY:0;opacity:0;transformPerspective:200;transformOrigin:50% 0%;" data-speed="800" data-start="1200" data-easing="easeOutQuad" data-splitin="none" data-splitout="none" data-elementdelay="0.01" data-endelementdelay="0.1" data-endspeed="1000" data-endeasing="Power4.easeIn" style="z-index: 10;">
							Materials d'alta qualitat.
						</div>

						<div class="tp-caption customin ltl tp-resizeme small_light_white" data-x="center" data-y="350" data-customin="x:0;y:150;z:0;rotationZ:0;scaleX:1;scaleY:1;skewX:0;skewY:0;opacity:0;transformPerspective:200;transformOrigin:50% 0%;" data-speed="800" data-start="1400" data-easing="easeOutQuad" data-splitin="none" data-splitout="none" data-elementdelay="0.01" data-endelementdelay="0.1" data-endspeed="1000" data-endeasing="Power4.easeIn" style="z-index: 10; width: 750px; max-width: 750px; white-space: normal; text-align:center;">
							Adaptats al vostre pressupost i necessitats.
						</div>

						<div class="tp-caption customin ltl tp-resizeme" data-x="center" data-y="438" data-customin="x:0;y:150;z:0;rotationZ:0;scaleX:1;scaleY:1;skewX:0;skewY:0;opacity:0;transformPerspective:200;transformOrigin:50% 0%;" data-speed="800" data-start="1550" data-easing="easeOutQuad" data-splitin="none" data-splitout="none" data-elementdelay="0.01" data-endelementdelay="0.1" data-endspeed="1000" data-endeasing="Power4.easeIn" style="z-index: 10;">
							<a href="contacte.php" class="btn btn-default btn-lg">
								<span>Contacta'ns</span>
							</a>
						</div>
						<!-- 							<div class="tp-caption very_large_text lfb ltt tp-resizeme"
								data-x="right" data-hoffset="-100"
								data-y="center" data-voffset="-50"
								data-speed="600"
								data-start="800"
								data-easing="Power4.easeOut"
								data-splitin="none"
								data-splitout="none"
								data-elementdelay="0.01"
								data-endelementdelay="0.1"
								data-endspeed="500"
								data-endeasing="Power4.easeIn">
								RUN WILD
							</div>

							<div class="tp-caption medium_light_white lfb ltt tp-resizeme"
								data-x="right" data-hoffset="-110"
								data-y="center" data-voffset="60"
								data-speed="600"
								data-start="900"
								data-easing="Power4.easeOut"
								data-splitin="none"
								data-splitout="none"
								data-elementdelay="0.01"
								data-endelementdelay="0.1"
								data-endspeed="500"
								data-endeasing="Power4.easeIn">
								Happiness is an accident of nature,<br/>
								a beautiful and flawless aberration.<br/>
								<span style="font-size:24px;font-weight:400;">&ndash; Pat Conroy</span>
							</div>
 -->
					</li>

					<!-- SLIDE  no funciona -->

				</ul>
			</div>
		</section>
		<!-- /REVOLUTION SLIDER -->



		<!-- CALLOUT -->
		<div class="alert alert-transparent bordered-bottom m-0">
			<div class="container">

				<div class="row">

					<div class="col-md-9 col-sm-12">
						<!-- left text -->
						<h3>Truca'ns al <span><strong>600-500-517</strong></span> Som professionals al teu servei</h3>
						<p class="font-lato fw-300 fs-20 mb-0">
							També pots contactar amb nosaltes a través de l'apartat de contacte.
						</p>
					</div><!-- /left text -->


					<div class="col-md-3 col-sm-12 text-right">
						<!-- right btn -->
						<a href="contacte.php" class="btn btn-primary btn-lg">CONTACTA'NS</a>
					</div><!-- /right btn -->

				</div>

			</div>
		</div>
		<!-- /CALLOUT -->




		<!-- -->
		<section>
			<div class="container">

				<div class="row">

					<div class="col-md-5 col-sm-5">

						<img class="img-fluid" src="/web/images/solidaritat/Projecte-Solidari-Centre-Obert-Rialles-1.jpg" alt="...">

					</div>

					<div class="col-md-7 col-sm-7">

						<header class="mb-60">
							<h2>Som solidaris</h2>
							<p class="lead font-lato">El 10% dels nostres beneficis van destinats a projectes solidaris.</p>
						</header>

						<p>Sabem que en la societat en la que vivim i hi ha necessitats que les nostres administracions no cubreixen. Nosaltres som concients que entre tots podem ajudar a petits projectes solidaris. Contracta'ns i tambe aportaràs el teu granet de sorra a aquestos projectes.</p>

						<a class="btn btn-default btn-lg fs-15 mt-30 lightbox" href="solidaritat.php" data-plugin-options='{"type":"iframe"}'>VEURE PROJECTES</a>

					</div>

				</div>

			</div>
		</section>
		<!-- / -->




		<!-- PARALLAX -->
		<section class="parallax parallax-2 section-xs d-none" style="background-image: url('demo_files/images/vision-min.jpg');">
			<div class="overlay dark-4">
				<!-- dark overlay [0 to 9 opacity] -->
			</div>

			<div class="container">

				<div class="row countTo-md text-center">

					<div class="col-6 col-sm-3">
						<i class="fa fa-users"></i>
						<span class="countTo" data-speed="3000">1303</span>
						<h5>HAPPY CLIENTS</h5>
					</div>

					<div class="col-6 col-sm-3">
						<i class="fa fa-code"></i>
						<span class="countTo" data-speed="3000">56000</span>
						<h5>LINES OF CODE</h5>
					</div>

					<div class="col-6 col-sm-3">
						<i class="fa fa-twitter"></i>
						<span class="countTo" data-speed="3000">4897</span>
						<h5>TWITTER</h5>
					</div>

					<div class="col-6 col-sm-3">
						<i class="fa fa-facebook"></i>
						<span class="countTo" data-speed="3000">9877</span>
						<h5>FACEBOOK</h5>
					</div>

				</div>

			</div>
		</section>
		<!-- /PARALLAX -->




		<!-- Goodies -->
		<section>
			<div class="container">

				<div class="row">

					<div class="col-md-3">

						<div class="box-icon box-icon-center box-icon-transparent box-icon-large">
							<a class="box-icon-title" href="#">
								<i class="fa fa-euro"></i>
								<h2>Preus justos</h2>
							</a>
							<p class="text-muted">El preu justos per materials i ma d'obra.</p>
						</div>

					</div>

					<div class="col-md-3">

						<div class="box-icon box-icon-center box-icon-transparent box-icon-large">
							<a class="box-icon-title" href="#">
								<i class="fa fa-sticky-note"></i>
								<h2>Pressupostos</h2>
							</a>
							<p class="text-muted">Demanan'ns qualsevol presupost sense compromis.</p>
						</div>

					</div>

					<div class="col-md-3">

						<div class="box-icon box-icon-center box-icon-transparent box-icon-large">
							<a class="box-icon-title" href="#">
								<i class="fa fa-key"></i>
								<h2>Garantia</h2>
							</a>
							<p class="text-muted">Et donem fins a 10 anys de garantia.</p>
						</div>

					</div>

					<div class="col-md-3">

						<div class="box-icon box-icon-center box-icon-transparent box-icon-large">
							<a class="box-icon-title" href="#">
								<i class="fa fa-user"></i>
								<h2>Professionals</h2>
							</a>
							<p class="text-muted">Tots els nostres operaris son professionals i tenen anys d'experiència en el sector.</p>
						</div>

					</div>

				</div>

			</div>
		</section>
		<!-- Goodies -->




		<!-- Works -->
		<section>
			<div class="container">

				<header class="mb-60 text-center">
					<h2>Ejemples de treballs</h2>
					<p class="lead font-lato">Tenim la solució a la teva mida.</p>
					<hr />
				</header>

				<div class="owl-carousel buttons-autohide controlls-over" data-plugin-options='{"singleItem": true, "autoPlay": true, "navigation": true, "pagination": true, "transitionStyle":"goDown"}'>
					<div>
						<img class="img-fluid" src="/web/images/portes.jpg" alt="Obertures">
					</div>
					<div>
						<img class="img-fluid" src="/web/images/serveis/Portes-corredises.jpg" alt="Portes corredisses">
					</div>
					<div>
						<img class="img-fluid" src="/web/images/serveis/Control-de-Accesoss.jpg" alt="Control d’accessos">
					</div>
				</div>
			</div>
		</section>
		<!-- /Works -->

		<?php
		// include_once("modal.php");
		?>


		<!-- FOOTER -->
		<?php
		include_once("php/pie.php");
		?>
		<!-- /FOOTER -->

	</div>
	<!-- /wrapper -->



	<!-- JAVASCRIPT FILES -->
	<script>
		var plugin_path = '/web/plugins/';
	</script>
	<script src="/web/js/jquery-3.3.1.min.js"></script>

	<script src="/web/js/scripts.js"></script>
	<script src="/web/js/serralleria.js"></script>

	<!-- REVOLUTION SLIDER -->
	<script src="/web/plugins/slider/js/jquery.themepunch.tools.min.js"></script>
	<script src="/web/plugins/slider/js/jquery.themepunch.revolution.min.js"></script>
	<script src="/web/plugins/slider/js/revolution_slider.js"></script>
</body>

</html>
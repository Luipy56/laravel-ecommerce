<?php
session_start();
$PaginaActual = "nosaltres";
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION["Idioma"] ?>">
	<head>
		<meta charset="utf-8" />
		<title>Qui som</title>
		<meta name="description" content="" />
		<meta name="Author" content="Dorin Grigoras [www.stepofweb.com]" />
		<link rel="icon" type="image/png" href="/images/favicon.png">

		<!-- mobile settings -->
		<meta name="viewport" content="width=device-width, maximum-scale=1, initial-scale=1, user-scalable=0" />
		<!--[if IE]><meta http-equiv='X-UA-Compatible' content='IE=edge,chrome=1'><![endif]-->

		<!-- WEB FONTS : use %7C instead of | (pipe) -->
		<link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600%7CRaleway:300,400,500,600,700%7CLato:300,400,400italic,600,700" rel="stylesheet" type="text/css" />

		<!-- CORE CSS -->
		<link href="/css/bootstrap.min.css" rel="stylesheet" type="text/css" />

		<!-- THEME CSS -->
		<link href="/css/essentials.css" rel="stylesheet" type="text/css" />
		<link href="/css/layout.css?v=1.1" rel="stylesheet" type="text/css" />

		<!-- PAGE LEVEL SCRIPTS -->
		<link href="/css/header-1.css" rel="stylesheet" type="text/css" />
		<link href="/css/orange.css" rel="stylesheet" type="text/css" id="color_scheme" />
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

			<section class="header-solidaritat page-header page-header-xlg b-0 dark" 
				data-background-delay="5000" 
				data-background-fade="1000" 
				data-background="
					demo_files/images/packs/photography/cover/sean-mcauliffe-12854-min.jpg
					,demo_files/images/packs/photography/cover/jorge-rojas-182706-min.jpg
			">

				<span class="overlay dark-4"><!-- dark overlay [1 to 9 opacity] --></span>
				<div class="container">

					<small>Qui som, qué fem</small>
					<h2 class="fs-40 fw-300">SERRALLERIA SOLIDÀRIA</h2>

				</div>
			</section>



			<!-- 3 Cols -->
			<section class="b-0" style="background-color:#fbfbfb;">
				<div class="container">
					<div class="row">
					
						<div class="col-md-8">
							
							<div class="heading-title heading-border-bottom heading-color">
								<h3>Història de Serralleria Solidària</h3>
							</div>
							

						</div>


					</div>
					<div class="row">
					
						<div class="col-md-4">
							
							
							<p class="text-justify">Som un grup de professionals de la serralleria i dels automatismes que ens vam ajuntar per crear una empresa dedicada a les necessitats dels nostres clients de restar més segurs a les seves ubicacions, ja sigui a casa com a la feina. Treballem la seguretat per a les persones.<br><br>
							L’1 d’Abril de 2014 vam començar la nostra empresa i ens va motivar la vessant solidària, essent reflex d’altres empreses que col•laboren amb associacions, fundacions o ONG’s, des de llavors destinem un 10% dels beneficis a projectes solidaris que nosaltres mateixos escollim.<br><br>
							Des d’aquells dies hem anat ampliant la nostra xarxa de personal sempre tenint present els</p>

						</div>

						<div class="col-md-4">
							<p class="text-justify">
								nostres principis i les nostres aptituds, hi ha col•laboradors amb més de vint anys d’experiència i hi ha col•laboradors que encara estan en període de formació, però això no fa més que provocar un creixement i una millora qualitativa que veiem reflectida en les ressenyes dels nostres clients.
								Ens agrada dir que ens dediquem més a tancar que a obrir, últimament estem enfocats en el marcatge CE dels automatismes i sempre al dia en qüestió de seguretat. De la mateixa manera hi ha registrat el nom i la tipografia, no així el concepte que convidem a tothom a copiar-ho i que aportin el seu gra de sorra a aquesta societat.
								Nosaltres hem copiat la idea, ens agradaria que ens copiessin més empreses.
							</p>

						</div>

						<div class="col-md-4">
							<div class="thumbnail">
								<img class="img-fluid" src="images/Serralleria-Logos.png" title="Disenyant un logo" alt="Disenyant un logo" />
							</div>

						</div>

					</div>
					
				</div>
			</section>
			<!-- /3 Cols -->



			<!-- Counters -->
			<section class="section-sm b-0">
				<div class="container">

					<div class="row countTo-sm text-center">

						<div class="col-6 col-sm-3 col-6">
							<i class="ico-lg ico-transparent et-tools"></i>
							<div class="block fs-40" style="color: #3498db;">
								<strong class="countTo fs-40" data-speed="3000">1558</strong>
							</div>
							<h3 class="fs-15 mt-10 mb-0">TREBALLS REALITZATS</h3>
						</div>

						<div class="col-6 col-sm-3 col-6">
							<i class="ico-lg ico-transparent et-chat"></i>
							<div class="block fs-40" style="color: #e74c3c;">
								<strong class="countTo fs-40" data-speed="3000">98</strong> %
							</div>
							<h3 class="fs-15 mt-10 mb-0">ATENCIÓ POST-VENDA</h3>
						</div>

						<div class="col-6 col-sm-3 col-6">
							<i class="ico-lg ico-transparent et-happy"></i>
							<div class="block fs-40" style="color: #16a085;">
								<strong class="countTo fs-40" data-speed="3000">95</strong> %
							</div>
							<h3 class="fs-15 mt-10 mb-0">SATISFACCIÓ CLIENTS</h3>
						</div>

						<div class="col-6 col-sm-3 col-6">
							<i class="ico-lg ico-transparent et-heart"></i>
							<div class="block fs-40" style="color: #9b59b6;">
								<strong class="countTo fs-40" data-speed="3000">3104</strong> €
							</div>
							<h3 class="fs-15 mt-10 mb-0">€ PROJECTES SOLIDARIS</h3>
						</div>

					</div>

				</div>
			</section>
			<!-- /Counters -->




			<!-- 3 Cols -->
			<section class="b-0" style="background-color:#fbfbfb;">
				<div class="container">
					<div class="row">
					
						<div class="col-md-8">
							
							<div class="heading-title heading-border-bottom heading-color">
								<h3>La pregunta del milió… Per que Serralleria Solidària?</h3>
							</div>
							

						</div>


					</div>
					<div class="row">
					
						<div class="col-md-4">
							
							
							<img class="float-left mb-0" src="images/serralleria.png" width="100px" alt="" />
							<p class="text-justify">
								En general, ens agrada tancar més que obrir, tot i que també fem obertures, volem millorar la qualitat de vida dels nostres clients. <a href="serveis.php">Aqui</a> podeu comprovar totes les feines que fem i realitzem.
							</p>

							<div class="thumbnail">
								<img class="img-fluid" src="images/serveis/logo-sin-texto.png" alt="Logo Serralleria Solidària" />
							</div>

						</div>

						<div class="col-md-4">
							<img class="float-left mb-0" src="images/solidaria.png" width="100px" alt="Serralleria Solidària" />
							<p class="text-justify">
								Per que destinem un 10 % dels beneficis a projectes solidaris, sempre a prop nostre, i destinem també els diners que la gent ens ofereix desinteressadament. Convidem a col•laborar activament ja que molts dels nostres <a href="solidaritat">projectes</a> són oberts al públic en general.<br><br>

								Molta gent pensa que treballem gratis o que si no tens diners t’ho fem sense cost, però cal saber que és la nostra feina, molts de nosaltres participem i col•laborem amb entitats que treballen amb gent necessitada i de fet tenim un recull de material de serralleria, que la gent no els vol i que es poden reutilitzar en cas de necessitat, panys en bon estat, cilindres...

							</p>

						</div>

						<div class="col-md-4">
							<div class="thumbnail">
								<img class="img-fluid" src="images/Serralleria-Mans.png" alt="" />
							</div>

						</div>

					</div>
					
				</div>
			</section>
			<!-- /3 Cols -->



			<!-- Clients
			<section class="section-sm">
				<div class="container">
	
					<header class="text-center mb-80">
						<h3>HAPPY CLIENTS</h3>
					</header>
					

					<ul class="row clients-dotted list-inline">
						<li class="col-md-5th col-sm-5th col-6">
							<a href="#">
								<img class="img-fluid" src="demo_files/images/brands/1.jpg" alt="client" />
							</a>
						</li>
						<li class="col-md-5th col-sm-5th col-6">
							<a href="#">
								<img class="img-fluid" src="demo_files/images/brands/2.jpg" alt="client" />
							</a>
						</li>
						<li class="col-md-5th col-sm-5th col-6">
							<a href="#">
								<img class="img-fluid" src="demo_files/images/brands/3.jpg" alt="client" />
							</a>
						</li>
						<li class="col-md-5th col-sm-5th col-6">
							<a href="#">
								<img class="img-fluid" src="demo_files/images/brands/4.jpg" alt="client" />
							</a>
						</li>
						<li class="col-md-5th col-sm-5th col-6">
							<a href="#">
								<img class="img-fluid" src="demo_files/images/brands/5.jpg" alt="client" />
							</a>
						</li>
						<li class="col-md-5th col-sm-5th col-6">
							<a href="#">
								<img class="img-fluid" src="demo_files/images/brands/6.jpg" alt="client" />
							</a>
						</li>
						<li class="col-md-5th col-sm-5th col-6">
							<a href="#">
								<img class="img-fluid" src="demo_files/images/brands/7.jpg" alt="client" />
							</a>
						</li>
						<li class="col-md-5th col-sm-5th col-6">
							<a href="#">
								<img class="img-fluid" src="demo_files/images/brands/8.jpg" alt="client" />
							</a>
						</li>
						<li class="col-md-5th col-sm-5th col-6">
							<a href="#">
								<img class="img-fluid" src="demo_files/images/brands/1.jpg" alt="client" />
							</a>
						</li>
						<li class="col-md-5th col-sm-5th col-6">
							<a href="#">
								<img class="img-fluid" src="demo_files/images/brands/2.jpg" alt="client" />
							</a>
						</li>
					</ul>

				</div>
			</section>
			 /Clients -->





			<!-- VISUAL CHART -->
			<section class="bb-0">
				
				<!--
					Available Classes/Colors
						.cart-visual-warning
						.cart-visual-info
						.cart-visual-success
						.cart-visual-danger
						.cart-visual-default
				-->
				<div class="clearfix mt-60 cart-visual cart-visual-default">

					<svg id="cart-visual-svg-1" height="0" width="100%" preserveAspectRatio="none" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 1096 131.25" style="height: 140px;" xml:space="preserve">
						<path d="M782,49.418c-58.667-24.667-118.602-46-249.334-46s-159.43,36.157-209.333,62.207C259.04,99.186,0,131.25,0,131.25h1096C1096,131.25,840.667,74.085,782,49.418z"></path>
					</svg>

					<svg id="cart-visual-svg-2" height="0" width="100%" preserveAspectRatio="none" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 1133.338 144.499" style="height: 195px;" xml:space="preserve">
						<path d="M948.868,144.499H0V72.249c26.67-9.407,66.707-19.582,119.535-19.582c133.999,0,227.999,39.333,332.665,39.333c80,0,142.667-36.666,174.667-52.666S716.201,0,804.201,0s144.667,20,144.667,20c44.173,10.997,122.713,47.543,184.47,78.215v46.284H948.868z"></path>
					</svg>

					<!-- <div class="container">

						<div class="row">
						
							<div class="col-md-8">
								
								<div class="heading-title heading-border-bottom heading-color">
									<h3>COVID-19</h3>
								</div>
								

							</div>


						</div>

						<div class="row">

							<div class="col-md-8">
								
								
								<p class="text-justify">
								No direm Reial decrets o texts publicats al BOE... Nosaltres no deixem de treballar, sempre amb un protocol molt estricte de seguretat pels nostres clients i per nosaltres mateixos. Si és una feina de millora o no tan urgent, si us plau preguem sigueu conscients de la situació i... <span class="text-info">#joemquedoacasa</span>
								</p>

							</div>

							<div class="col-md-4">
								<p></p>

							</div>

						</div>

					</div> -->

				</div>
			</section>
			<!-- /VISUAL CHART -->






			<!-- FOOTER -->
<?php 
include_once("php/pie.php");
?>
			<!-- /FOOTER -->

		</div>
		<!-- /wrapper -->



		<!-- JAVASCRIPT FILES -->
		<script>var plugin_path = 'plugins/';</script>
		<script src="/js/jquery-3.3.1.min.js"></script>

		<script src="/js/scripts.js"></script>
		
	</body>
</html>
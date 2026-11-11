<?php
session_start();
$PaginaActual = "blog";
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION["Idioma"] ?>">
	<head>
		<meta charset="utf-8" />
		<title>Parts d'un pany</title>
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
		<?php include_once __DIR__ . '/php/analytics.php'; ?>
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

			<section class="header-solidaritat page-header page-header-xlg b-0 dark" 
				data-background-delay="5000" 
				data-background-fade="1000" 
				data-background="
					demo_files/images/packs/photography/cover/sean-mcauliffe-12854-min.jpg
					,demo_files/images/packs/photography/cover/jorge-rojas-182706-min.jpg
			">

				<span class="overlay dark-4"><!-- dark overlay [1 to 9 opacity] --></span>
				<div class="container">

					<small>El blog de la Serralleria</small>
					<h2 class="fs-40 fw-300">PARTS D'UN PANY</h2>

				</div>
			</section>



			<!-- -->
			<section>
				<div class="container">

					<h1 class="blog-post-title">PARTS D'UN PANY</h1>
					<ul class="blog-post-info list-inline">
						<li>
							<a href="#">
								<i class="fa fa-clock-o"></i> 
								<span class="font-lato">abril de 2020</span>
							</a>
						</li>
						<li>
							<i class="fa fa-folder-open-o"></i> 

							<a class="category" href="#">
								<span class="font-lato">Articles</span>
							</a>
							<a class="category" href="#">
								<span class="font-lato">Panys</span>
							</a>
						</li>
						<li>
							<a href="#">
								<i class="fa fa-user"></i> 
								<span class="font-lato">J.J. Gutiérrez</span>
							</a>
						</li>
					</ul>

					<row>
						<p>Hola a tots, molts de nosaltres no sabem ben bé com definir les parts d’un pany . Avui volem donar informació al respecte, gairebé ningú que no sigui manyà coneix del cert les correctes denominacions de les parts que componen el nostre sistema de SEGURETAT, i el seu funcionament, el perquè quan girem la clau es tanca la porta ni quina és la seguretat depenent de les característiques. Potser que només tinguis curiositat, el cert és que ens és molt útil als professionals un bon diagnòstic a l’hora de solucionar una avaria, i evitar confusions a l’hora del primer contacte amb el pany.</p>
					</row>
						

					<row>
						<div class="col-md-12">
							<p><span class="badge badge-orange badge-text-lg mr-20">1</span> <b>Tipus de panys</b></p>
						</div>
					</row>

					<row>
						<div class="col-md-12">
							<p class="ml-40"><span class="badge badge-orange mr-20">1.1</span> Bàsicament hi ha dos tipus de pany <b>l’embotit</b> és el més comú, està ficat a dins de la porta i només el veiem si la porta està oberta, podem per fer anar la clau per l’interior i per l’exterior.</p>

							<p class="ml-40"><span class="badge badge-orange mr-20">1.2</span> El segon tipus de pany és el de <b>sobreposar</b>, està completament a la vista i està col·locat a sobre de la porta per la part interior , el ports veure des de dins de casa i el tancador acostuma a estar a la tapeta lateral del marc de la porta.</p>
						</div>
					</row>

					<row>
						<div class="col-md-12">
							<p><span class="badge badge-orange badge-text-lg mr-20">2</span> <b>Components d’un pany</b></p>
						</div>
					</row>

					<row>
						<div class="col-md-12">
							<p>Farem un exemple de pany senzill per poder determinar les principals parts i les seves denominacions. (Tot i que no tots els panys tenen les mateixes parts, o els mateixos tipus de components).</p>
						</div>
					</row>

					<row>
						<div class="col-md-12">
							<p class="ml-40"><span class="badge badge-orange mr-20">2.1</span> <b>Frontal o Frontis</b>: És la part metàl·lica que hi ha al lateral de la porta i només es pot veure amb la porta oberta. Aquí tenim l’accés al cargol que subjecta el bombí i als cargols que fixen el pany a la porta. La seva funció és subjectar el pany a la porta a més de servir d’embellidor, a més hi ha el cargol que subjecta el bombí. Aquest component només el trobem amb panys embotits.</p>
						</div>

						<div class="col-md-12">
							<p class="ml-40"><span class="badge badge-orange mr-20">2.2</span> <b>Cop, resbaló, picaport</b>: És la part que tanca la porta de cop, que actua sense necessitat de acció manual, i s’amaga dins del pany quan accionem la maneta o obrim el pany amb la clau. És la peça que es recull i ens permet entrar, té una forma molt especial, en forma de cunya. Manté la porta tancada quan no hem tancat amb clau. Molta gent deixa tancada la porta només amb el cop i això fa que sigui molt senzill obrir la porta, hi ha molts mètodes amb poc esforç i sense soroll.</p>
						</div>

						<div class="col-md-12">
							<p class="ml-40"><span class="badge badge-orange mr-20">2.3</span> <b>Bolons, paletó o paletons, pastilla, petaca</b>: Aquesta és la part del pany que es mou quan diem que “hem tancat la porta amb clau”, només podem actuar sobre aquesta peça amb la clau i és l’element que ens ofereix la seguretat del pany, A l’accionar-los surten i s’introdueixen a la part del marc de la porta, cada joc de bolons el denominem “punt” així doncs un pany que tanca a dalt , a baix i al mig és un pany de “tres punts”, no confondre amb els bolons, un pany d’un punt pot tenir quatre bolons.</p>
						</div>

						<div class="col-md-12">
							<p class="ml-40"><span class="badge badge-orange mr-20">2.4</span> <b>Bombí, cilindre, “bombillo”</b>: És una de les peces més importants del pany, és la codificació d’entrada, és qui guarda el secret de la nostra seguretat, és la part on a l’introduir la clau podem accionar tots els mecanismes del pany, hi ha diferents tipus, el més comú és el de perfil europeu tot i que hi ha de perfil suís, ovalat, rodó... acostuma a sortir per ambdós cantons del pany. És l’element en que més actuen els amics de l’aliè, així que haurem de tenir molta cura a l’escollir aquesta part, es pot canviar fàcilment i no cal canviar el pany per aconseguir més seguretat. Hi ha una infinitat de models. És el cor del pany.  Una de les parts dels bombins son els pitons, quants més pitons més “forats o marques” en te la clau, i més segura i difícil de forçar és.</p>
						</div>

						<div class="col-md-12">
							<p class="ml-40"><span class="badge badge-orange mr-20">2.5</span> <b>Pom o maneta</b>: Aquesta part és la més senzilla d’identificar, des de casa és la part que accionem per obrir la porta.</p>
						</div>

						<div class="col-md-12">
							<p class="ml-40"><span class="badge badge-orange mr-20">2.6</span> <b>Tancador</b>: Aquesta part és la que està col·locada al marc de la porta i és on es fica tant el cop com els bolons, acostuma a ésser una part metàl·lica per poder exercir més força en cas de forçament.</p>
						</div>

						<div class="col-md-12">
							<p class="ml-40"><span class="badge badge-orange mr-20">2.7</span> <b>Bocaclau o embellidor</b>: És el forat on fiquem la clau i/o el bombí, no té una funció específica, es fa servir bàsicament per decorar o estètica.</p>
						</div>

						<div class="col-md-12">
							<p class="ml-40"><span class="badge badge-orange mr-20">2.8</span> <b>Escut</b>: Està situat a la part exterior del pany, protegeix el bombi i el pany, reforça la seguretat davant d’un intent d’obertura de la porta, aquest element evita l’extracció del cilindre o fer servir el trepant (mètode molt utilitzat pels lladres) No acostumem a donar importància a aquest element i és BÀSIC, no és un embellidor.</p>
						</div>

						<div class="col-md-12">
							<p class="ml-40"><span class="badge badge-orange mr-20">2.9</span> <b>Claus</b>: Van juntament amb el cilindre, hi ha de molts tipus, qualitats i facilitat de efectuar copies. Podeu trobar més informació sobre els tipus de claus en el següent <a href="blog-tipus-de-claus.php">article</a>.</p>
						</div>
					</row>


					<div class="heading-title heading-dotted text-center">
						<h4>Preguntes freqüents <span>FAQS</span></span></h4>
					</div>

					<div class="heading-title heading-border">
						<h4>Si acabo de llogar/comprar un pis, cal que canviï el pany?</h4>
						<p class="font-lato fs-16">No sempre, a vegades només canviant el bombí és suficient. El que si és convenient és canviar la clau, no sabem qui en pot tenir una còpia.</p>
					</div>


					<!-- SHARE POST -->
					<div class="clearfix mt-30">

						<span class="float-left mt-6 bold hidden-xs-down">
							Compartir Post: 
						</span>

						<a href="#" class="social-icon social-icon-sm social-icon-transparent social-instagram float-right" data-toggle="tooltip" data-placement="top" title="Instagram">
							<i class="icon-instagram"></i>
							<i class="icon-instagram"></i>
						</a>

						<a href="#" class="social-icon social-icon-sm social-icon-transparent social-twitter float-right" data-toggle="tooltip" data-placement="top" title="Twitter">
							<i class="icon-twitter"></i>
							<i class="icon-twitter"></i>
						</a>

						<a href="#" class="social-icon social-icon-sm social-icon-transparent social-facebook float-right" data-toggle="tooltip" data-placement="top" title="Facebook">
							<i class="icon-facebook"></i>
							<i class="icon-facebook"></i>
						</a>

					</div>
					<!-- /SHARE POST -->


					<div class="divider"><!-- divider --></div>

					
					<ul class="pager">
						<li class="previous"><a class="b-0" href="blog-mestrejaments.php">&larr; Post anterior</a></li>
						<li class="next"><a class="b-0" href="blog-tipus-de-claus.php">Post següent&rarr;</a></li>
					</ul>


<?php
if (0 == 1) {
?>
					<div class="divider"><!-- divider --></div>

					
					<!-- COMMENTS -->
					<div id="comments" class="comments">

						<h4 class="page-header mb-60 fs-20">
							<span>3</span> COMMENTS
						</h4>

						<!-- comment item -->
						<div class="comment-item">

							<!-- user-avatar -->
							<span class="user-avatar">
								<img class="float-left media-object" src="assets/images/_smarty/avatar.png" width="64" height="64" alt="">
							</span>

							<div class="media-body">
								<a href="#commentForm" class="scrollTo comment-reply">reply</a>
								<h4 class="media-heading bold">John Doe</h4>
								<small class="block">June 29, 2014 - 11:23</small>
								Proin eget tortor risus. Cras ultricies ligula sed magna dictum porta. Pellentesque in ipsum id orci porta dapibus. Lorem ipsum dolor sit amet, consectetur adipiscing elit. 
							</div>
						</div>

						<!-- comment item -->
						<div class="comment-item">

							<!-- user-avatar -->
							<span class="user-avatar">
								<img class="float-left media-object" src="assets/images/_smarty/avatar.png" width="64" height="64" alt="">
							</span>

							<div class="media-body">
								<a href="#commentForm" class="scrollTo comment-reply">reply</a>
								<h4 class="media-heading bold">Diana Doe</h4>
								<small class="block">June 29, 2014 - 11:23</small>
								Proin eget tortor risus. Cras ultricies ligula sed magna dictum porta. Pellentesque in ipsum id orci porta dapibus. Lorem ipsum dolor sit amet, consectetur adipiscing elit. 
							</div>
						</div>

						<!-- comment item -->
						<div class="comment-item">

							<!-- user-avatar -->
							<span class="user-avatar">
								<img class="media-object" src="assets/images/_smarty/avatar.png" width="64" height="64" alt="">
							</span>

							<div class="media-body">
								<a href="#commentForm" class="scrollTo comment-reply">reply</a>
								<h4 class="media-heading bold">Melissa Doe</h4>
								<small class="block">June 29, 2014 - 11:23</small>
								Proin eget tortor risus. Cras ultricies ligula sed magna dictum porta. Pellentesque in ipsum id orci porta dapibus. Lorem ipsum dolor sit amet, consectetur adipiscing elit. 

								<!-- comment reply -->
								<div class="media">

									<!-- user-avatar -->
									<span class="user-avatar">
										<img class="media-object" src="assets/images/_smarty/avatar.png" width="64" height="64" alt="">
									</span>

									<div class="media-body">
										<h4 class="media-heading bold">Peter Doe</h4>
										<small class="block">June 29, 2014 - 11:23</small>
										Proin eget tortor risus. Cras ultricies ligula sed magna dictum porta. Pellentesque in ipsum id orci porta dapibus. Lorem ipsum dolor sit amet, consectetur adipiscing elit. 
									</div>
								</div>

							</div>
						</div>



						<h4 class="page-header fs-20 mb-60 mt-100">
							LEAVE A <span>COMMENT</span>
						</h4>

								<!-- Form -->
								<form action="#" method="post">

									<div class="row">
										<div class="col-md-4">
											<label>NAME</label>
											<input required="required" type="text" value="" maxlength="100" class="form-control form-control-lg" name="author" id="author">
										</div>
										<div class="col-md-4">
											<label>EMAIL</label>
											<input required="required" type="email" value="" maxlength="100" class="form-control form-control-lg" name="contact_email" id="contact_email">
										</div>
										<div class="col-md-4">
											<label>WEBSITE</label>
											<input type="email" value="" maxlength="100" class="form-control form-control-lg" name="url" id="url">
										</div>
									</div>

									<div class="row">
										<div class="col-md-12">
											<label>COMMENT</label>
											<textarea required="required" maxlength="5000" rows="5" class="form-control" name="comment" id="comment"></textarea>
										</div>
									</div>

									<div class="row">
										<div class="col-md-12">

											<button class="btn btn-3d btn-lg btn-reveal btn-black">
												<i class="fa fa-check"></i>
												<span>SUBMIT MESSAGE</span>
											</button>

										</div>
									</div>

								</form>
								<!-- /Form -->

					</div>
					<!-- /COMMENTS -->
<?php
}
?>


				</div>
			</section>
			<!-- / -->

					

			<!-- FOOTER -->
<?php 
include_once("php/pie.php");
?>
			<!-- /FOOTER -->

		</div>
		<!-- JAVASCRIPT FILES -->
		<script>var plugin_path = 'plugins/';</script>
		<script src="/web/js/jquery-3.3.1.min.js"></script>

		<script src="/web/js/scripts.js"></script>
	</body>
</html>
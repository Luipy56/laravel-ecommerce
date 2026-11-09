<?php
session_start();
$PaginaActual = "blog";
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION["Idioma"] ?>">
	<head>
		<meta charset="utf-8" />
		<title>El blog de la serralleria</title>
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

					<small>El blog de la</small>
					<h2 class="fs-40 fw-300">SERRALLERIA</h2>

				</div>
			</section>

			<!-- -->
			<section>
				<div class="container">

<?php
	if (0 == 1) {
?>
					<!-- POST ITEM -->
					<div class="blog-post-item">

						<!-- OWL SLIDER -->
						<div class="owl-carousel buttons-autohide controlls-over" data-plugin-options='{"items": 1, "autoPlay": 3000, "autoHeight": false, "navigation": true, "pagination": true, "transitionStyle":"fadeUp", "progressBar":"false"}'>
							<div>
								<img class="img-fluid" src="/images/Claus-classiques.png" alt="Claus classiques">
							</div>
							<div>
								<img class="img-fluid" src="/images/Clau-de-pompa.png" alt="Clau de pompa">
							</div>
							<div>
								<img class="img-fluid" src="/images/Claus-de-alta-seguretat.png" alt="Claus de alta seguretat">
							</div>
						</div>
						<!-- /OWL SLIDER -->

						<h2><a href="blog-tipus-de-claus.php">TIPUS DE CLAUS</a></h2>

						<ul class="blog-post-info list-inline">
							<li>
								<a href="#">
									<i class="fa fa-clock-o"></i> 
									<span class="font-lato">maig de 2020</span>
								</a>
							</li>
							<li>
								<i class="fa fa-folder-open-o"></i> 

								<a class="category" href="#">
									<span class="font-lato">Articles</span>
								</a>
								<a class="category" href="#">
									<span class="font-lato">Claus</span>
								</a>
							</li>
							<li>
								<a href="#">
									<i class="fa fa-user"></i> 
									<span class="font-lato">J.J. Gutiérrez</span>
								</a>
							</li>
						</ul>

						<p>Depenent del tipus de clau que tingui el teu pany tindrà un mecanisme diferent, això determina el nivell de seguretat i sobretot la seva tipologia, molts cops la clau la determina el pany i d’altres podem mantenir el mateix pany i només canviar la clau per fer-lo més segur. Ara mostrarem un seguit de les claus més habituals.</p>

						<a href="blog-tipus-de-claus.php" class="btn btn-reveal btn-default b-0 btn-shadow-1">
							<i class="fa fa-plus"></i>
							<span>Llegir més</span>
						</a>

					</div>
					<!-- /POST ITEM -->

<?php
	}
?>

					<!-- POST ITEM -->
					<div class="blog-post-item">

						<!-- IMAGE -->
						<figure class="mb-20">
							<img class="img-fluid" src="demo_files/images/content_slider/10-min.jpg" alt="">
						</figure>

						<h2><a href="blog-mestrejaments.php">MESTREJAMENTS</a></h2>

						<ul class="blog-post-info list-inline">
							<li>
								<a href="#">
									<i class="fa fa-clock-o"></i> 
									<span class="font-lato">juny 2021</span>
								</a>
							</li>
							<li>
								<i class="fa fa-folder-open-o"></i> 

								<a class="category" href="#">
									<span class="font-lato">Articles</span>
								</a>
								<a class="category" href="#">
									<span class="font-lato">Mestrejaments</span>
								</a>
							</li>
							<li>
								<a href="#">
									<i class="fa fa-user"></i> 
									<span class="font-lato">J.J. Gutiérrez</span>
								</a>
							</li>
						</ul>

						<p>El mestrejament és una tècnica per la qual es pot tenir accés a diferents portes amb una mateixa clau, seguint una jerarquia i impedint que una clau obri una porta que no hauria. La famosa “clau mestra” és la base d’aquesta explicació.</p>

						<a href="blog-mestrejaments.php" class="btn btn-reveal btn-default b-0 btn-shadow-1">
							<i class="fa fa-plus"></i>
							<span>Llegir més</span>
						</a>

					</div>
					<!-- /POST ITEM -->

					<!-- POST ITEM -->
					<div class="blog-post-item">

						<!-- IMAGE -->
						<figure class="mb-20">
							<img class="img-fluid" src="demo_files/images/content_slider/10-min.jpg" alt="">
						</figure>

						<h2><a href="blog-tipus-de-claus.php">TIPUS DE CLAUS</a></h2>

						<ul class="blog-post-info list-inline">
							<li>
								<a href="#">
									<i class="fa fa-clock-o"></i> 
									<span class="font-lato">abril 2021</span>
								</a>
							</li>
							<li>
								<i class="fa fa-folder-open-o"></i> 

								<a class="category" href="#">
									<span class="font-lato">Articles</span>
								</a>
								<a class="category" href="#">
									<span class="font-lato">Claus</span>
								</a>
							</li>
							<li>
								<a href="#">
									<i class="fa fa-user"></i> 
									<span class="font-lato">J.J. Gutiérrez</span>
								</a>
							</li>
						</ul>

						<p>En l'actualitat es poden trobar en el mercat diferents tipus de claus. En aquesta entrada podem veure uns quants exemples de tipus de claus. Des de les més clàssiques, que encara es poden veure, als tipus més actuals.</p>

						<a href="blog-tipus-de-claus.php" class="btn btn-reveal btn-default b-0 btn-shadow-1">
							<i class="fa fa-plus"></i>
							<span>Llegir més</span>
						</a>

					</div>
					<!-- /POST ITEM -->

					<!-- POST ITEM -->
					<div class="blog-post-item">

						<!-- IMAGE -->
						<figure class="mb-20">
							<img class="img-fluid" src="demo_files/images/content_slider/10-min.jpg" alt="">
						</figure>

						<h2><a href="blog-parts-d-un-pany.php">PARTS D'UN PANY</a></h2>

						<ul class="blog-post-info list-inline">
							<li>
								<a href="#">
									<i class="fa fa-clock-o"></i> 
									<span class="font-lato">abril 2020</span>
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

						<p>Hola a tots, molts de nosaltres no sabem ben bé com definir les parts d’un pany . Avui volem donar informació al respecte, gairebé ningú que no sigui manyà coneix del cert les correctes denominacions de les parts que componen el nostre sistema de SEGURETAT, i el seu funcionament, el perquè quan girem la clau es tanca la porta ni quina és la seguretat depenent de les característiques. Potser que només tinguis curiositat, el cert és que ens és molt útil als professionals un bon diagnòstic a l’hora de solucionar una avaria, i evitar confusions a l’hora del primer contacte amb el pany.</p>

						<a href="blog-parts-d-un-pany.php" class="btn btn-reveal btn-default b-0 btn-shadow-1">
							<i class="fa fa-plus"></i>
							<span>Llegir més</span>
						</a>

					</div>
					<!-- /POST ITEM -->


<?php
if (0 == 1) {
?>
					<!-- POST ITEM -->
					<div class="blog-post-item">

						<!-- VIDEO -->
						<div class="mb-20">
							<div class="embed-responsive embed-responsive-16by9">
								<iframe class="embed-responsive-item" src="https://www.youtube.com/embed/93-X8q1Us6c" width="800" height="450"></iframe>
							</div>
						</div>

						<h2><a href="blog-single-default.html">BLOG VIDEO POST</a></h2>

						<ul class="blog-post-info list-inline">
							<li>
								<a href="#">
									<i class="fa fa-clock-o"></i> 
									<span class="font-lato">June 29, 2017</span>
								</a>
							</li>
							<li>
								<a href="#">
									<i class="fa fa-comment-o"></i> 
									<span class="font-lato">28 Comments</span>
								</a>
							</li>
							<li>
								<i class="fa fa-folder-open-o"></i> 

								<a class="category" href="#">
									<span class="font-lato">Vídeos</span>
								</a>
								<a class="category" href="#">
									<span class="font-lato">Tonterias</span>
								</a>
							</li>
							<li>
								<a href="#">
									<i class="fa fa-user"></i> 
									<span class="font-lato">John Doe</span>
								</a>
							</li>
						</ul>

						<p>Ejemple de post amb vídeo de Youtube.</p>

						<a href="blog-single-default.html" class="btn btn-reveal btn-default b-0 btn-shadow-1">
							<i class="fa fa-plus"></i>
							<span>Read More</span>
						</a>

					</div>
					<!-- /POST ITEM -->
<?php
}
?>

<?php
if (0 == 1) {
?>

					<!-- POST ITEM -->
					<div class="blog-post-item">

						<!-- VIDEO -->
						<div class="mb-20">
							<iframe width="100%" height="366" scrolling="no" frameborder="no" src="https://w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/tracks/154936822&color=a94545&auto_play=false&hide_related=false&visual=true"></iframe>
						</div>

						<h2><a href="blog-single-default.html">BLOG SOUND CLOUD POST</a></h2>

						<ul class="blog-post-info list-inline">
							<li>
								<a href="#">
									<i class="fa fa-clock-o"></i> 
									<span class="font-lato">June 29, 2017</span>
								</a>
							</li>
							<li>
								<a href="#">
									<i class="fa fa-comment-o"></i> 
									<span class="font-lato">28 Comments</span>
								</a>
							</li>
							<li>
								<i class="fa fa-folder-open-o"></i> 

								<a class="category" href="#">
									<span class="font-lato">Design</span>
								</a>
								<a class="category" href="#">
									<span class="font-lato">Photography</span>
								</a>
							</li>
							<li>
								<a href="#">
									<i class="fa fa-user"></i> 
									<span class="font-lato">John Doe</span>
								</a>
							</li>
						</ul>

						<p>There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which don't look even slightly believable There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour.</p>

						<a href="blog-single-default.html" class="btn btn-reveal btn-default b-0 btn-shadow-1">
							<i class="fa fa-plus"></i>
							<span>Read More</span>
						</a>

					</div>
					<!-- /POST ITEM -->
<?php
}
?>


<?php
if (0 == 1) {
?>
					<!-- POST ITEM -->
					<div class="blog-post-item">

						<h2><a href="blog-single-default.html">BLOG SIMPLE POST</a></h2>

						<ul class="blog-post-info list-inline">
							<li>
								<a href="#">
									<i class="fa fa-clock-o"></i> 
									<span class="font-lato">June 29, 2017</span>
								</a>
							</li>
							<li>
								<a href="#">
									<i class="fa fa-comment-o"></i> 
									<span class="font-lato">28 Comments</span>
								</a>
							</li>
							<li>
								<i class="fa fa-folder-open-o"></i> 

								<a class="category" href="#">
									<span class="font-lato">Design</span>
								</a>
								<a class="category" href="#">
									<span class="font-lato">Photography</span>
								</a>
							</li>
							<li>
								<a href="#">
									<i class="fa fa-user"></i> 
									<span class="font-lato">John Doe</span>
								</a>
							</li>
						</ul>

						<p>There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which don't look even slightly believable There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour.</p>

						<a href="blog-single-default.html" class="btn btn-reveal btn-default b-0 btn-shadow-1">
							<i class="fa fa-plus"></i>
							<span>Read More</span>
						</a>

					</div>
					<!-- /POST ITEM -->
<?php
}
?>

<?php
if (0 == 1) {
?>
					<!-- PAGINATION -->
					<div class="text-center">
						<!-- Pagination Default -->
						<ul class="pagination m-0">
							<li class="page-item"><a class="page-link" href="#">&laquo;</a></li>
							<li class="page-item active"><a class="page-link" href="#">1</a></li>
							<li class="page-item"><a class="page-link" href="#">2</a></li>
							<li class="page-item"><a class="page-link" href="#">3</a></li>
							<li class="page-item"><a class="page-link" href="#">4</a></li>
							<li class="page-item"><a class="page-link" href="#">5</a></li>
							<li class="page-item"><a class="page-link" href="#">&raquo;</a></li>
						</ul>
						<!-- /Pagination Default -->
					</div>
					<!-- /PAGINATION -->
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
		<!-- /wrapper -->


		<!-- JAVASCRIPT FILES -->
		<script>var plugin_path = 'plugins/';</script>
		<script src="/js/jquery-3.3.1.min.js"></script>

		<script src="/js/scripts.js"></script>
		
	</body>
</html>